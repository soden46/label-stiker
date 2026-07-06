<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);

        $data['barcode_value'] = $data['barcode_value'] ?? $data['sku'];

        if (Product::where('barcode_value', $data['barcode_value'])->exists()) {
            return back()
                ->withErrors(['barcode_value' => 'Kode barcode otomatis dari SKU sudah dipakai produk lain.'])
                ->withInput();
        }

        Product::create($data);

        return back()->with('success', 'Part baru masuk ke master data.');
    }
}
