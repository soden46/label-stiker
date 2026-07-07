<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4', 'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4', 'balance_after' => 'decimal:4',
            'occurred_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
