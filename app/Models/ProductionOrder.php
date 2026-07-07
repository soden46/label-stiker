<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProductionOrderMaterial::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ProductionOrderOutput::class);
    }
}
