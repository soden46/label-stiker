<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\LabelBrandingService;
use App\Services\ProductImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $products = Product::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('customer_part_no', 'like', "%{$search}%")
                    ->orWhere('supplier_code', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:200'],
            'customer_part_no' => ['nullable', 'string', 'max:100'],
            'supplier_code' => ['nullable', 'string', 'max:100'],
            'barcode_value' => ['nullable', 'string', 'max:150', 'unique:products,barcode_value'],
            'uom' => ['required', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024', 'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'],
        ]);

        unset($data['logo']);
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos/products', 'public');
        }

        $data['barcode_value'] = $data['barcode_value'] ?? $data['sku'];

        if (Product::withTrashed()->where('barcode_value', $data['barcode_value'])->exists()) {
            return back()
                ->withErrors(['barcode_value' => 'Kode barcode otomatis dari SKU sudah dipakai produk lain.'])
                ->withInput();
        }

        Product::create($data);

        return back()->with('success', 'Part baru masuk ke master data.');
    }

    public function edit(Product $product, LabelBrandingService $branding): View
    {
        return view('products.edit', ['product' => $product, 'productLogoDataUri' => $branding->dataUriForPath($product->logo_path)]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:200'],
            'customer_part_no' => ['nullable', 'string', 'max:100'],
            'supplier_code' => ['nullable', 'string', 'max:100'],
            'barcode_value' => ['nullable', 'string', 'max:150', Rule::unique('products', 'barcode_value')->ignore($product)],
            'uom' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024', 'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'],
        ]);

        unset($data['logo']);
        if ($request->hasFile('logo')) {
            $oldLogo = $product->logo_path;
            $data['logo_path'] = $request->file('logo')->store('logos/products', 'public');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
        }

        $data['barcode_value'] = $data['barcode_value'] ?? $data['sku'];
        $data['is_active'] = $request->boolean('is_active');

        $barcodeConflict = Product::withTrashed()
            ->where('barcode_value', $data['barcode_value'])
            ->whereKeyNot($product->id)
            ->exists();

        if ($barcodeConflict) {
            return back()
                ->withErrors(['barcode_value' => 'Kode barcode sudah dipakai produk lain.'])
                ->withInput();
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Data part berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Part dihapus dari master data. Histori label tetap aman.');
    }

    public function import(Request $request, ProductImportService $importer): RedirectResponse
    {
        $request->validate([
            'product_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'product_file.required' => 'Pilih file Excel yang akan diimport.',
            'product_file.mimes' => 'Format file harus XLSX, XLS, atau CSV.',
            'product_file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $result = $importer->import($request->file('product_file'));

        return back()->with('import_result', $result);
    }
}
