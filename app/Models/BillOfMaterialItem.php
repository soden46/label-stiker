<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillOfMaterialItem extends Model
{
    protected $guarded = [];

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'material_product_id')->withTrashed();
    }
}
