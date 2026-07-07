<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\NumberSequenceService;
use App\Services\StockService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErpFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_ledger_posts_atomic_movements_and_weighted_average_cost(): void
    {
        $unit = Unit::create(['code' => 'PCS', 'name' => 'Pieces']);
        $warehouse = Warehouse::create(['code' => 'MAIN', 'name' => 'Gudang Utama']);
        $product = Product::create([
            'sku' => 'RAW-001', 'name' => 'Bahan Baku', 'barcode_value' => 'RAW-001',
            'uom' => 'PCS', 'unit_id' => $unit->id, 'item_type' => 'raw_material',
            'track_inventory' => true,
        ]);
        $stock = app(StockService::class);

        $stock->post($product, $warehouse, 10, StockMovementType::PurchaseReceipt, 100);
        $stock->post($product, $warehouse, 10, StockMovementType::PurchaseReceipt, 200);
        $stock->post($product, $warehouse, -5, StockMovementType::ProductionIssue, 150);

        $balance = StockBalance::firstOrFail();
        $this->assertSame('15.0000', $balance->quantity);
        $this->assertSame('150.0000', $balance->average_cost);
        $this->assertSame(3, StockMovement::count());
        $this->assertSame('15.0000', StockMovement::latest('id')->first()->balance_after);
    }

    public function test_stock_cannot_go_negative_unless_warehouse_allows_it(): void
    {
        $warehouse = Warehouse::create(['code' => 'MAIN', 'name' => 'Gudang Utama']);
        $product = Product::create([
            'sku' => 'FIN-001', 'name' => 'Barang Jadi', 'barcode_value' => 'FIN-001',
            'uom' => 'PCS', 'item_type' => 'finished_good', 'track_inventory' => true,
        ]);

        $this->expectException(DomainException::class);
        app(StockService::class)->post($product, $warehouse, -1, StockMovementType::Sale, 100);
    }

    public function test_document_numbers_are_sequential_per_period(): void
    {
        $sequences = app(NumberSequenceService::class);

        $this->assertSame('PO-202607-00001', $sequences->next('purchase_order', 'PO-', '202607'));
        $this->assertSame('PO-202607-00002', $sequences->next('purchase_order', 'PO-', '202607'));
        $this->assertSame('PO-202608-00001', $sequences->next('purchase_order', 'PO-', '202608'));
    }
}
