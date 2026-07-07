<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['order_date' => 'date', 'expected_date' => 'date'];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'supplier_id')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
