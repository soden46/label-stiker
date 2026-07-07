<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessPartner extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_supplier' => 'boolean', 'is_customer' => 'boolean', 'is_active' => 'boolean'];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }
}
