<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $fillable = ['name', 'catalog_path', 'catalog_url'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function catalogUrl(): string
    {
        return $this->catalog_path
            ? route('catalogs.products', ['filename' => basename($this->catalog_path)])
            : $this->catalog_url;
    }
}
