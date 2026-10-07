<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $categories = ProductCategory::query()
            ->withCount('products')
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('product-categories.index', [
            'categories' => $categories, 'search' => $search,
            'catalogUploadLimitMb' => $this->catalogUploadLimitKb() / 1024,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ProductCategory::create($this->validatedData($request));

        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function edit(ProductCategory $productCategory): View
    {
        return view('product-categories.edit', [
            'category' => $productCategory,
            'catalogUploadLimitMb' => $this->catalogUploadLimitKb() / 1024,
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        // Keep previous catalog files available to QR codes on historical labels.
        $productCategory->update($this->validatedData($request, $productCategory));

        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil diperbarui.');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        if ($productCategory->products()->withTrashed()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih digunakan master part dan tidak dapat dihapus.']);
        }

        $productCategory->delete();

        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil dihapus.');
    }

    public function catalog(string $filename)
    {
        $disk = Storage::disk('local');
        $path = 'catalogs/product-categories/'.$filename;
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), ['Content-Type' => 'application/pdf']);
    }

    private function validatedData(Request $request, ?ProductCategory $category = null): array
    {
        $maxUploadKb = $this->catalogUploadLimitKb();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('product_categories', 'name')->ignore($category)],
            'catalog_type' => ['required', Rule::in(['pdf', 'url'])],
            'catalog_file' => [
                'exclude_unless:catalog_type,pdf',
                Rule::requiredIf(fn () => $request->input('catalog_type') === 'pdf' && ! $category?->catalog_path),
                'nullable', 'file', 'mimes:pdf', 'max:'.$maxUploadKb,
            ],
            'catalog_url' => ['exclude_unless:catalog_type,url', 'required', 'url:http,https', 'max:2048'],
        ], [
            'catalog_file.required' => 'Upload katalog PDF.',
            'catalog_file.mimes' => 'Katalog harus berupa file PDF.',
            'catalog_file.max' => 'Ukuran katalog maksimal '.($maxUploadKb / 1024).' MB.',
            'catalog_url.required' => 'Isi link katalog.',
            'catalog_url.url' => 'Link katalog harus berupa URL HTTP atau HTTPS.',
        ]);

        if ($data['catalog_type'] === 'url') {
            return ['name' => $data['name'], 'catalog_url' => $data['catalog_url'], 'catalog_path' => null];
        }

        $path = $category?->catalog_path;
        if ($request->hasFile('catalog_file')) {
            $path = $request->file('catalog_file')->store('catalogs/product-categories', 'local');
            abort_unless($path, 500, 'Katalog gagal disimpan.');
        }

        return ['name' => $data['name'], 'catalog_path' => $path, 'catalog_url' => null];
    }

    private function catalogUploadLimitKb(): int
    {
        $limits = [20 * 1024 * 1024];
        foreach (['upload_max_filesize', 'post_max_size'] as $setting) {
            $bytes = ini_parse_quantity(ini_get($setting));
            if ($bytes > 0) {
                $limits[] = $bytes;
            }
        }

        return intdiv(min($limits), 1024);
    }
}
