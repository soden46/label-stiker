<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderOutput extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['produced_at' => 'datetime'];
    }
}
