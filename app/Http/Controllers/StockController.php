<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function createIn(): View
    {
        return $this->form('in');
    }

    public function createOut(): View
    {
        return $this->form('out');
    }

    public function storeIn(Request $request, StockService $stock): RedirectResponse
    {
        return $this->store($request, $stock, 'in');
    }

    public function storeOut(Request $request, StockService $stock): RedirectResponse
    {
        return $this->store($request, $stock, 'out');
    }

    private function form(string $direction): View
    {
        $type = $direction === 'in' ? StockMovementType::StockIn : StockMovementType::StockOut;

        return view('inventory.stock-form', [
            'direction' => $direction,
            'products' => Product::query()
                ->where('is_active', true)
                ->where('track_inventory', true)
                ->orderBy('name')
                ->get(['id', 'sku', 'name', 'uom', 'barcode_value']),
            'warehouses' => Warehouse::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'recentMovements' => StockMovement::query()
                ->with(['product', 'warehouse'])
                ->where('movement_type', $type->value)
                ->latest('occurred_at')
                ->limit(10)
                ->get(),
        ]);
    }

    private function store(Request $request, StockService $stock, string $direction): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'unit_cost' => [$direction === 'in' ? 'required' : 'nullable', 'numeric', 'min:0', 'max:999999999'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'product_id' => 'part/SKU',
            'warehouse_id' => 'gudang',
            'quantity' => 'jumlah',
            'unit_cost' => 'harga satuan',
            'notes' => 'catatan',
        ]);

        $product = Product::where('is_active', true)->findOrFail($validated['product_id']);
        $warehouse = Warehouse::where('is_active', true)->findOrFail($validated['warehouse_id']);
        $quantity = (float) $validated['quantity'];
        $unitCost = (float) ($validated['unit_cost'] ?? 0);

        try {
            $stock->post(
                $product,
                $warehouse,
                $direction === 'in' ? $quantity : -$quantity,
                $direction === 'in' ? StockMovementType::StockIn : StockMovementType::StockOut,
                $unitCost,
                postedBy: $request->user()->id,
                notes: $validated['notes'] ?? null,
            );
        } catch (DomainException $exception) {
            return back()->withErrors(['quantity' => $exception->getMessage()])->withInput();
        }

        return redirect()
            ->route($direction === 'in' ? 'stock.in.create' : 'stock.out.create')
            ->with('success', $direction === 'in' ? 'Stock masuk berhasil disimpan.' : 'Stock keluar berhasil disimpan.');
    }
}
