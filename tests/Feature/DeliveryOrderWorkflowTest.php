<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\BusinessPartner;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_delivery_order_with_product_snapshots_and_atomic_number(): void
    {
        $user = User::factory()->create();
        [$to, $shipTo] = $this->customers();
        $product = $this->product('1000-P12-M8', 'CAT-BP-CN');

        $response = $this->actingAs($user)->post(route('delivery-orders.store'), $this->payload($to, $shipTo, $product, [
            'delivery_date' => '2026-06-01',
            'quantity' => 30,
            'weight' => 12.5,
        ]));

        $deliveryOrder = DeliveryOrder::with('items')->firstOrFail();
        $response->assertRedirect(route('delivery-orders.show', $deliveryOrder));
        $this->assertSame('DO.2026.06.001', $deliveryOrder->number);
        $this->assertSame('PT Customer Tujuan', $deliveryOrder->to_company);
        $this->assertSame('PT Customer Penerima', $deliveryOrder->ship_to_company);
        $this->assertSame('Alamat Penerima', $deliveryOrder->ship_to_address);
        $this->assertSame('1000-P12-M8', $deliveryOrder->items->first()->waf_part_no);
        $this->assertSame('CAT-BP-CN', $deliveryOrder->items->first()->catalog_code);
        $this->assertSame('30.0000', $deliveryOrder->items->first()->quantity);
    }

    public function test_do_and_shipping_sticker_pdfs_use_their_own_document_sizes(): void
    {
        $user = User::factory()->create();
        [$to, $shipTo] = $this->customers();
        $product = $this->product('PDF-001', 'CAT-PDF');
        AppSetting::put('company_name', 'PT WAF Indonesia');
        AppSetting::put('company_address', 'Alamat Pengirim');
        AppSetting::put('company_phone', '021-123');

        $this->actingAs($user)->post(route('delivery-orders.store'), $this->payload($to, $shipTo, $product));
        $deliveryOrder = DeliveryOrder::firstOrFail();

        $deliveryOrderPdf = $this->actingAs($user)->get(route('delivery-orders.pdf', $deliveryOrder));
        $deliveryOrderPdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertPdfPageCount($deliveryOrderPdf->getContent(), 1);

        $shippingStickerPdf = $this->actingAs($user)->get(route('delivery-orders.shipping-sticker', $deliveryOrder));
        $shippingStickerPdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertPdfPageCount($shippingStickerPdf->getContent(), 1);
    }

    public function test_batch_flow_preserves_the_selected_order_for_both_outputs(): void
    {
        $user = User::factory()->create();
        [$to, $shipTo] = $this->customers();
        $firstProduct = $this->product('BATCH-001', 'CAT-A');
        $secondProduct = $this->product('BATCH-002', 'CAT-B');
        $this->actingAs($user)->post(route('delivery-orders.store'), $this->payload($to, $shipTo, $firstProduct, ['delivery_date' => '2026-06-01']));
        $this->actingAs($user)->post(route('delivery-orders.store'), $this->payload($shipTo, $to, $secondProduct, ['delivery_date' => '2026-06-02']));
        $orders = DeliveryOrder::orderBy('id')->get();

        $this->actingAs($user)->post(route('delivery-orders.batch'), ['delivery_order_ids' => [$orders[1]->id, $orders[0]->id]])
            ->assertOk()
            ->assertViewHas('deliveryOrders', fn ($selected) => $selected->pluck('id')->all() === [$orders[1]->id, $orders[0]->id]);
        $batchPdf = $this->actingAs($user)->post(route('delivery-orders.batch.pdf'), ['delivery_order_ids' => [$orders[1]->id, $orders[0]->id]]);
        $batchPdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertPdfPageCount($batchPdf->getContent(), 2);

        $batchStickersPdf = $this->actingAs($user)->post(route('delivery-orders.batch.shipping-stickers'), ['delivery_order_ids' => [$orders[1]->id, $orders[0]->id]]);
        $batchStickersPdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertPdfPageCount($batchStickersPdf->getContent(), 2);
    }

    /** @return array{0: BusinessPartner, 1: BusinessPartner} */
    private function customers(): array
    {
        return [
            BusinessPartner::create(['code' => 'CUST-TO', 'name' => 'PT Customer Tujuan', 'is_customer' => true, 'is_active' => true, 'address' => 'Alamat Tujuan', 'phone' => '021-111']),
            BusinessPartner::create(['code' => 'CUST-SHIP', 'name' => 'PT Customer Penerima', 'is_customer' => true, 'is_active' => true, 'address' => 'Alamat Penerima', 'phone' => '021-222']),
        ];
    }

    private function product(string $sku, string $catalog): Product
    {
        return Product::create(['sku' => $sku, 'name' => 'PIPE CONNECTOR', 'description' => 'PIPE CONNECTOR 3/8', 'customer_part_no' => 'CUST-'.$sku, 'supplier_code' => $catalog, 'barcode_value' => $sku, 'uom' => 'PCS']);
    }

    private function payload(BusinessPartner $to, BusinessPartner $shipTo, Product $product, array $overrides = []): array
    {
        return [
            'delivery_date' => '2026-06-01',
            'to_partner_id' => $to->id,
            'ship_to_partner_id' => $shipTo->id,
            'to_project_site' => 'Project Tujuan',
            'to_address' => $to->address,
            'ship_to_project_site' => 'Project Penerima',
            'ship_to_address' => $shipTo->address,
            'ship_to_phone' => $shipTo->phone,
            'purchase_order_no' => 'PO-2026-001',
            'fob' => 'Jakarta',
            'packages' => '2-PACK',
            'ship_via' => 'Udara',
            'shipment' => 'Lionel',
            'description' => 'Manual',
            'items' => [['product_id' => $product->id, 'quantity' => 10, 'unit' => 'PCS', 'weight' => null]],
            ...collect($overrides)->except(['quantity', 'weight'])->all(),
            'items' => [['product_id' => $product->id, 'quantity' => $overrides['quantity'] ?? 10, 'unit' => 'PCS', 'weight' => $overrides['weight'] ?? null]],
        ];
    }

    private function assertPdfPageCount(string $content, int $expected): void
    {
        $this->assertSame($expected, preg_match_all('/\/Type\s*\/Page\b/', $content));
    }
}
