<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['sold_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'customer_id')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }
}
