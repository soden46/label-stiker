<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_without_existing_barcode_uses_sku_as_internal_barcode(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('products.store'), [
            'sku' => 'SKU-TANPA-BARCODE',
            'name' => 'Produk Tanpa Barcode',
            'barcode_value' => '',
            'uom' => 'PCS',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-TANPA-BARCODE',
            'barcode_value' => 'SKU-TANPA-BARCODE',
        ]);
    }
}
