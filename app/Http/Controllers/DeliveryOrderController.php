<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeliveryOrderRequest;
use App\Models\AppSetting;
use App\Models\BusinessPartner;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Services\LabelBrandingService;
use App\Services\NumberSequenceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeliveryOrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $deliveryOrders = DeliveryOrder::query()
            ->with(['toPartner', 'shipToPartner'])
            ->withCount('items')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('number', 'like', "%{$search}%")
                    ->orWhere('purchase_order_no', 'like', "%{$search}%")
                    ->orWhere('to_company', 'like', "%{$search}%")
                    ->orWhere('ship_to_company', 'like', "%{$search}%");
            }))
            ->latest('delivery_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('delivery-orders.index', compact('deliveryOrders', 'search'));
    }

    public function create(): View
    {
        return $this->formView(new DeliveryOrder(['delivery_date' => now()->toDateString()]));
    }

    public function store(StoreDeliveryOrderRequest $request, NumberSequenceService $numbers): RedirectResponse
    {
        $deliveryOrder = DB::transaction(function () use ($request, $numbers) {
            $data = $request->validated();
            $partners = $this->customers($data['to_partner_id'], $data['ship_to_partner_id']);
            $deliveryDate = Carbon::parse($data['delivery_date']);
            $to = $partners->get((int) $data['to_partner_id']);
            $shipTo = $partners->get((int) $data['ship_to_partner_id']);
            $deliveryOrder = DeliveryOrder::create($this->orderData(
                $data,
                $to,
                $shipTo,
                $numbers->next('delivery_order', 'DO.', $deliveryDate->format('Y.m'), 3, '.'),
                $request->user()->id,
            ));

            $this->syncItems($deliveryOrder, $data['items']);

            return $deliveryOrder;
        });

        return redirect()->route('delivery-orders.show', $deliveryOrder)->with('success', 'Delivery Order berhasil dibuat.');
    }

    public function show(DeliveryOrder $deliveryOrder): View
    {
        return view('delivery-orders.show', $this->documentData($deliveryOrder));
    }

    public function edit(DeliveryOrder $deliveryOrder): View
    {
        $deliveryOrder->load('items');

        return $this->formView($deliveryOrder);
    }

    public function update(StoreDeliveryOrderRequest $request, DeliveryOrder $deliveryOrder): RedirectResponse
    {
        DB::transaction(function () use ($request, $deliveryOrder) {
            $data = $request->validated();
            $partners = $this->customers($data['to_partner_id'], $data['ship_to_partner_id']);
            $deliveryOrder->update($this->orderData(
                [...$data, 'uuid' => $deliveryOrder->uuid],
                $partners->get((int) $data['to_partner_id']),
                $partners->get((int) $data['ship_to_partner_id']),
                $deliveryOrder->number,
                $deliveryOrder->created_by,
            ));
            $deliveryOrder->items()->delete();
            $this->syncItems($deliveryOrder, $data['items']);
        });

        return redirect()->route('delivery-orders.show', $deliveryOrder)->with('success', 'Delivery Order berhasil diperbarui.');
    }

    public function pdf(DeliveryOrder $deliveryOrder, LabelBrandingService $branding)
    {
        return Pdf::loadView('delivery-orders.pdf', $this->documentData($deliveryOrder, $branding))
            ->setPaper('a4', 'portrait')
            ->stream($deliveryOrder->number.'.pdf');
    }

    public function shippingStickerPdf(DeliveryOrder $deliveryOrder, LabelBrandingService $branding)
    {
        return Pdf::loadView('delivery-orders.shipping-stickers', [
            'deliveryOrders' => collect([$deliveryOrder]),
            'company' => $this->company($branding),
        ])
            ->setPaper([0, 0, 425.197, 283.465])
            ->stream($deliveryOrder->number.'-shipping-sticker.pdf');
    }

    public function batch(Request $request): View
    {
        return view('delivery-orders.batch', [
            'deliveryOrders' => $this->batchOrders($request),
        ]);
    }

    public function batchPdf(Request $request, LabelBrandingService $branding)
    {
        $deliveryOrders = $this->batchOrders($request);

        return Pdf::loadView('delivery-orders.batch-pdf', [
            'deliveryOrders' => $deliveryOrders,
            'company' => $this->company($branding),
            'logoDataUri' => $branding->publicLabelLogoDataUri() ?: $branding->logoDataUri(),
        ])
            ->setPaper('a4', 'portrait')
            ->stream('delivery-orders-'.now()->format('Ymd-His').'.pdf');
    }

    public function batchShippingStickerPdf(Request $request, LabelBrandingService $branding)
    {
        return Pdf::loadView('delivery-orders.shipping-stickers', [
            'deliveryOrders' => $this->batchOrders($request),
            'company' => $this->company($branding),
        ])
            ->setPaper([0, 0, 425.197, 283.465])
            ->stream('delivery-order-shipping-stickers-'.now()->format('Ymd-His').'.pdf');
    }

    private function formView(DeliveryOrder $deliveryOrder): View
    {
        $products = Product::query()
            ->with('unit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'unit_id', 'sku', 'name', 'description', 'customer_part_no', 'supplier_code', 'barcode_value', 'uom'])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'item_name' => $product->description ?: $product->name,
                'waf_part_no' => $product->barcode_value ?: $product->sku,
                'customer_part_no' => $product->customer_part_no,
                'catalog_code' => $product->supplier_code,
                'unit' => $product->unit?->code ?: $product->uom,
            ]);
        $customers = BusinessPartner::query()
            ->where('is_customer', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'address', 'phone']);

        return view('delivery-orders.form', compact('deliveryOrder', 'products', 'customers'));
    }

    private function syncItems(DeliveryOrder $deliveryOrder, array $items): void
    {
        $products = Product::query()
            ->with('unit')
            ->where('is_active', true)
            ->whereIn('id', collect($items)->pluck('product_id'))
            ->get()
            ->keyBy('id');

        foreach ($items as $index => $item) {
            /** @var Product $product */
            $product = $products->get((int) $item['product_id']);
            abort_unless($product, 422, 'Produk tidak aktif atau tidak ditemukan.');
            $deliveryOrder->items()->create([
                'product_id' => $product->id,
                'unit_id' => $product->unit_id,
                'line_number' => $index + 1,
                'item_name' => $product->description ?: $product->name,
                'waf_part_no' => $product->barcode_value ?: $product->sku,
                'customer_part_no' => $product->customer_part_no,
                'catalog_code' => $product->supplier_code,
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'weight' => $item['weight'] ?? null,
            ]);
        }
    }

    private function customers(int $toPartnerId, int $shipToPartnerId): Collection
    {
        $partners = BusinessPartner::query()
            ->where('is_customer', true)
            ->where('is_active', true)
            ->whereIn('id', [$toPartnerId, $shipToPartnerId])
            ->get()
            ->keyBy('id');

        abort_unless($partners->has($toPartnerId) && $partners->has($shipToPartnerId), 422, 'Customer tujuan atau penerima tidak aktif.');

        return $partners;
    }

    private function orderData(array $data, BusinessPartner $to, BusinessPartner $shipTo, string $number, ?int $createdBy): array
    {
        return [
            'uuid' => $data['uuid'] ?? (string) Str::uuid(),
            'number' => $number,
            'delivery_date' => $data['delivery_date'],
            'to_partner_id' => $to->id,
            'ship_to_partner_id' => $shipTo->id,
            'to_company' => $to->name,
            'to_project_site' => $data['to_project_site'] ?? null,
            'to_address' => $data['to_address'],
            'ship_to_company' => $shipTo->name,
            'ship_to_project_site' => $data['ship_to_project_site'] ?? null,
            'ship_to_address' => $data['ship_to_address'],
            'ship_to_phone' => $data['ship_to_phone'] ?? null,
            'purchase_order_no' => $data['purchase_order_no'] ?? null,
            'fob' => $data['fob'] ?? null,
            'packages' => $data['packages'] ?? null,
            'ship_via' => $data['ship_via'] ?? null,
            'shipment' => $data['shipment'] ?? null,
            'description' => $data['description'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    private function documentData(DeliveryOrder $deliveryOrder, ?LabelBrandingService $branding = null): array
    {
        $deliveryOrder->loadMissing('items.product');
        $branding ??= app(LabelBrandingService::class);

        return [
            'deliveryOrder' => $deliveryOrder,
            'company' => $this->company($branding),
            'logoDataUri' => $branding->publicLabelLogoDataUri() ?: $branding->logoDataUri(),
        ];
    }

    private function company(LabelBrandingService $branding): array
    {
        $cityLine = trim(implode(' ', array_filter([
            AppSetting::value('company_city'),
            AppSetting::value('company_postal_code'),
        ])));
        $country = AppSetting::value('company_country');

        return [
            'name' => AppSetting::value('company_name', $branding->appName()),
            'address' => AppSetting::value('company_address'),
            'city_line' => trim($cityLine.($cityLine && $country ? ' - ' : '').$country),
            'phone' => AppSetting::value('company_phone'),
        ];
    }

    private function batchOrders(Request $request): Collection
    {
        $validated = $request->validate([
            'delivery_order_ids' => ['required', 'array', 'min:1', 'max:100'],
            'delivery_order_ids.*' => ['integer', 'distinct', 'exists:delivery_orders,id'],
        ]);
        $ids = collect($validated['delivery_order_ids'])->map(fn ($id) => (int) $id)->values();
        $orders = DeliveryOrder::query()
            ->with('items.product')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return $ids->map(fn (int $id) => $orders->get($id))->filter()->values();
    }
}
