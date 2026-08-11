<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLabelPrintRequest;
use App\Models\LabelPrint;
use App\Models\Product;
use App\Services\BarcodeService;
use App\Services\LabelBrandingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LabelPrintController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $labels = LabelPrint::query()
            ->with(['product', 'creator'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('purchase_order_no', 'like', "%{$search}%")
                    ->orWhere('delivery_note_no', 'like', "%{$search}%")
                    ->orWhere('customer_part_no', 'like', "%{$search}%")
                    ->orWhere('sender_address', 'like', "%{$search}%")
                    ->orWhere('recipient_address', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($product) => $product
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"));
            }))
            ->when($status === 'printed', fn ($query) => $query->whereNotNull('printed_at'))
            ->when($status === 'ready', fn ($query) => $query->whereNull('printed_at'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('labels.index', compact('labels', 'search', 'status'));
    }

    public function create(): View
    {
        $products = Product::query()
            ->select(['id', 'sku', 'name', 'description', 'customer_part_no', 'supplier_code', 'barcode_value', 'uom'])
            ->where('is_active', true)
            ->withSum('stockBalances as inventory_stock', 'quantity')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'description' => $product->description,
                'customer_part_no' => $product->customer_part_no,
                'supplier_code' => $product->supplier_code,
                'barcode_value' => $product->barcode_value,
                'uom' => $product->uom,
                'inventory_stock' => (float) ($product->inventory_stock ?? 0),
            ]);

        return view('labels.create', [
            'products' => $products,
        ]);
    }

    public function store(StoreLabelPrintRequest $request): RedirectResponse
    {
        $product = Product::findOrFail($request->integer('product_id'));
        $uuid = (string) Str::uuid();
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos/labels', 'public');
        } elseif ($product->logo_path && Storage::disk('public')->exists($product->logo_path)) {
            $extension = pathinfo($product->logo_path, PATHINFO_EXTENSION) ?: 'png';
            $logoPath = "logos/labels/{$uuid}.{$extension}";
            Storage::disk('public')->copy($product->logo_path, $logoPath);
        }
        $labelPrint = LabelPrint::create([
            ...collect($request->validated())->except('logo')->all(),
            'uuid' => $uuid,
            'created_by' => $request->user()->id,
            'barcode_value' => $product->barcode_value,
            'inventory_stock' => $product->stockBalances()->sum('quantity'),
            'logo_path' => $logoPath,
            'product_snapshot' => $product->only([
                'sku', 'name', 'description', 'customer_part_no', 'supplier_code', 'barcode_value', 'uom',
            ]),
        ]);

        return redirect()->route('labels.show', $labelPrint)->with('success', 'Label berhasil dibuat dan siap dicetak.');
    }

    public function show(LabelPrint $labelPrint, BarcodeService $barcode, LabelBrandingService $branding): View
    {
        return view('labels.show', $this->viewData($labelPrint, $barcode, $branding));
    }

    public function pdf(LabelPrint $labelPrint, BarcodeService $barcode, LabelBrandingService $branding)
    {
        $labelPrint->update(['printed_at' => now()]);

        return Pdf::loadView('labels.pdf', [
            'pages' => $this->printPages(collect([$labelPrint->fresh()]), [], $barcode, $branding),
        ])
            ->setPaper([0, 0, 283.465, 283.465])
            ->download('label-'.$labelPrint->purchase_order_no.'.pdf');
    }

    public function bulkPdf(Request $request, BarcodeService $barcode, LabelBrandingService $branding)
    {
        $validated = $request->validate([
            'label_ids' => ['required', 'array', 'min:1', 'max:100'],
            'label_ids.*' => ['integer', 'distinct', 'exists:label_prints,id'],
            'copies' => ['nullable', 'array'],
            'copies.*' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $ids = collect($validated['label_ids'])->map(fn ($id) => (int) $id)->values();
        $copies = collect($validated['copies'] ?? []);
        $totalPages = $ids->sum(fn ($id) => (int) ($copies->get($id, 1)));

        if ($totalPages > 300) {
            return back()->withErrors(['label_ids' => 'Maksimal 300 halaman dalam satu file PDF.'])->withInput();
        }

        $labelsById = LabelPrint::with('product')->whereIn('id', $ids)->get()->keyBy('id');
        $labels = $ids->map(fn ($id) => $labelsById->get($id))->filter();

        LabelPrint::whereIn('id', $ids)->update(['printed_at' => now()]);

        return Pdf::loadView('labels.pdf', [
            'pages' => $this->printPages($labels, $copies, $barcode, $branding),
        ])
            ->setPaper([0, 0, 283.465, 283.465])
            ->download('label-bulk-'.now()->format('Ymd-His').'.pdf');
    }

    private function viewData(LabelPrint $labelPrint, BarcodeService $barcode, LabelBrandingService $branding): array
    {
        $labelPrint->loadMissing('product');

        return [
            'label' => $labelPrint,
            'partBarcode' => $barcode->html($labelPrint->barcode_value),
            'customerBarcode' => $barcode->html($labelPrint->customer_part_no),
            'logoDataUri' => $branding->dataUriForPath($labelPrint->logo_path) ?: $branding->logoDataUri(),
        ];
    }

    private function printPages(Collection $labels, Collection|array $copies, BarcodeService $barcode, LabelBrandingService $branding): array
    {
        $copies = collect($copies);
        $pages = [];

        foreach ($labels as $label) {
            $data = $this->viewData($label, $barcode, $branding);

            foreach (range(1, (int) $copies->get($label->id, 1)) as $copy) {
                $pages[] = $data;
            }
        }

        return $pages;
    }
}
