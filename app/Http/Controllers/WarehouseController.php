<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $warehouses = Warehouse::query()
            ->withCount(['stockBalances', 'stockMovements'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            }))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('warehouses.index', compact('warehouses', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Warehouse::create($data);

        return redirect()->route('warehouses.index')->with('success', 'Gudang baru berhasil disimpan.');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', ['warehouse' => $warehouse]);
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($this->validated($request, $warehouse));

        return redirect()->route('warehouses.index')->with('success', 'Data gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->stockMovements()->exists() || $warehouse->stockBalances()->where('quantity', '!=', 0)->exists()) {
            return back()->withErrors(['warehouse' => 'Gudang yang sudah punya histori stok tidak bisa dihapus. Nonaktifkan gudang jika tidak dipakai lagi.']);
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Warehouse $warehouse = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('warehouses', 'code')->ignore($warehouse)],
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'allow_negative_stock' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.regex' => 'Kode gudang hanya boleh berisi huruf, angka, titik, strip, dan underscore.',
        ], [
            'code' => 'kode gudang',
            'name' => 'nama gudang',
            'address' => 'alamat',
            'allow_negative_stock' => 'izin stok minus',
            'is_active' => 'status aktif',
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['allow_negative_stock'] = $request->boolean('allow_negative_stock');
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
