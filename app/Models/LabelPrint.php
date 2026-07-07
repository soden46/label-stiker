<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelPrint extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'product_id', 'created_by', 'purchase_order_no',
        'customer_part_no', 'quantity', 'uom', 'barcode_value', 'logo_path',
        'product_snapshot', 'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'product_snapshot' => 'array',
            'printed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
