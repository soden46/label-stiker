<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'description', 'customer_part_no', 'supplier_code',
        'barcode_value', 'logo_path', 'uom', 'is_active',
        'unit_id', 'item_type', 'track_inventory', 'cost_method',
        'standard_cost', 'selling_price', 'minimum_stock',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean', 'track_inventory' => 'boolean',
            'standard_cost' => 'decimal:4', 'selling_price' => 'decimal:4',
            'minimum_stock' => 'decimal:4',
        ];
    }

    public function labelPrints(): HasMany
    {
        return $this->hasMany(LabelPrint::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function billsOfMaterial(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class);
    }
}
