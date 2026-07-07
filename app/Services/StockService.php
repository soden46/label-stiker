<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockService
{
    public function post(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        StockMovementType|string $movementType,
        float $unitCost = 0,
        ?Model $reference = null,
        ?int $postedBy = null,
        ?string $notes = null,
    ): StockMovement {
        if ($quantity === 0.0) {
            throw new DomainException('Kuantitas pergerakan stok tidak boleh nol.');
        }

        if (! $product->track_inventory) {
            throw new DomainException('Produk ini tidak dikonfigurasi untuk tracking stok.');
        }

        $type = $movementType instanceof StockMovementType ? $movementType->value : $movementType;

        return DB::transaction(function () use ($product, $warehouse, $quantity, $type, $unitCost, $reference, $postedBy, $notes) {
            $balanceId = StockBalance::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => 0, 'average_cost' => 0],
            )->id;
            $balance = StockBalance::whereKey($balanceId)->lockForUpdate()->firstOrFail();

            $previousQuantity = (float) $balance->quantity;
            $newQuantity = round($previousQuantity + $quantity, 4);

            if ($newQuantity < 0 && ! $warehouse->allow_negative_stock) {
                throw new DomainException("Stok {$product->sku} tidak mencukupi di gudang {$warehouse->code}.");
            }

            $averageCost = (float) $balance->average_cost;
            if ($quantity > 0 && $newQuantity > 0) {
                $averageCost = round((($previousQuantity * $averageCost) + ($quantity * $unitCost)) / $newQuantity, 4);
            }

            $balance->update(['quantity' => $newQuantity, 'average_cost' => $averageCost]);

            return StockMovement::create([
                'uuid' => (string) Str::uuid(),
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => round(abs($quantity) * $unitCost, 4),
                'balance_after' => $newQuantity,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'occurred_at' => now(),
                'posted_by' => $postedBy,
                'notes' => $notes,
            ]);
        }, 3);
    }
}
