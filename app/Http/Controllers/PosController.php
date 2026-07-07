<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        return view('pos.index', [
            'products' => Product::query()
                ->where('is_active', true)
                ->where('item_type', '!=', 'raw_material')
                ->orderBy('name')
                ->limit(30)
                ->get(),
        ]);
    }
}
