<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'number', 'delivery_date', 'to_partner_id', 'ship_to_partner_id',
        'to_company', 'to_project_site', 'to_address', 'ship_to_company',
        'ship_to_project_site', 'ship_to_address', 'ship_to_phone', 'purchase_order_no',
        'fob', 'packages', 'ship_via', 'shipment', 'description', 'created_by',
    ];

    protected function casts(): array
    {
        return ['delivery_date' => 'date'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function toPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'to_partner_id')->withTrashed();
    }

    public function shipToPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'ship_to_partner_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class)->orderBy('line_number');
    }
}
