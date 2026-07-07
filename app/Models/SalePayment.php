<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }
}
