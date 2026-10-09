<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\BusinessPartner;
use App\Models\DeliveryOrder;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
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

    public function test_user_can_add_a_customer_from_the_delivery_order_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('delivery-orders.create'))
            ->assertOk()
            ->assertSee('value="__add_customer__"', false)
            ->assertSee('Tambah customer…')
            ->assertSee('data-add-customer', false);

        $response = $this->actingAs($user)->postJson(route('delivery-orders.customers.store'), [
            'name' => 'PT Customer Baru',
            'address' => 'Alamat Customer Baru',
            'phone' => '021-333',
        ]);

        $response->assertCreated()
            ->assertJsonPath('customer.name', 'PT Customer Baru')
            ->assertJsonPath('customer.address', 'Alamat Customer Baru');
        $this->assertDatabaseHas('business_partners', [
            'name' => 'PT Customer Baru',
            'is_customer' => true,
            'is_active' => true,
        ]);
    }

    public function test_delivery_order_pdf_templates_use_light_blue_table_accents(): void
    {
        foreach (['delivery-orders/pdf.blade.php', 'delivery-orders/batch-pdf.blade.php'] as $template) {
            $html = file_get_contents(resource_path('views/'.$template));

            $this->assertStringNotContainsString('#ffc400', $html);
            $this->assertStringContainsString('background:#bde7f7', $html);
            $this->assertStringContainsString('color:#050505', $html);
            $this->assertStringNotContainsString('background:#050505;color:#fff;border:0.3mm solid #111', $html);
        }
    }

    public function test_save_and_open_pdf_creates_only_one_order_and_edit_keeps_its_number(): void
    {
        $user = User::factory()->create();
        [$to, $shipTo] = $this->customers();
        $product = $this->product('EDIT-001', 'CAT-EDIT');
        $payload = $this->payload($to, $shipTo, $product, ['action' => 'print']);
        $payload['items'] = [4 => ['product_id' => $product->id, 'quantity' => 7.5, 'unit' => 'BOX', 'weight' => 0]];

        $response = $this->actingAs($user)->post(route('delivery-orders.store'), $payload);
        $order = DeliveryOrder::with('items')->firstOrFail();
        $response->assertSessionHasNoErrors()->assertRedirect(route('delivery-orders.pdf', $order));
        $number = $order->number;
        $uuid = $order->uuid;
        $this->assertSame(1, $order->items->first()->line_number);
        $this->assertSame('BOX', $order->items->first()->unit);

        $this->get(route('delivery-orders.edit', $order))->assertOk()
            ->assertViewHas('deliveryOrder', fn ($editing) => $editing->items->first()->unit === 'BOX');

        $payload['purchase_order_no'] = 'PO-REVISED';
        $payload['items'][4]['quantity'] = 25;
        $this->put(route('delivery-orders.update', $order), $payload)
            ->assertSessionHasNoErrors()->assertRedirect(route('delivery-orders.pdf', $order));
        $order->refresh();
        $this->assertSame($number, $order->number);
        $this->assertSame($uuid, $order->uuid);
        $this->assertSame('PO-REVISED', $order->purchase_order_no);
        $this->assertSame('25.0000', $order->items->first()->quantity);
        $this->assertSame('BOX', $order->items->first()->unit);
        $this->assertDatabaseCount('delivery_orders', 1);
        $this->assertDatabaseCount('delivery_order_items', 1);
        $this->get(route('delivery-orders.pdf', $order))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_without_print_permission_can_save_but_cannot_use_save_and_open_pdf(): void
    {
        $permission = Permission::create(['name' => 'Buat DO', 'slug' => 'delivery_orders.create', 'module' => 'Delivery Order', 'portal' => 'backoffice']);
        $role = Role::create(['name' => 'DO Creator', 'slug' => 'do-creator', 'portal' => 'backoffice']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create(['role' => 'staff', 'role_id' => $role->id]);
        [$to, $shipTo] = $this->customers();
        $product = $this->product('NO-PRINT', 'CAT-PRINT');
        $payload = $this->payload($to, $shipTo, $product);

        $this->actingAs($user)->get(route('delivery-orders.create'))->assertOk()->assertDontSee('Simpan &amp; buka PDF', false);
        $this->post(route('delivery-orders.store'), [...$payload, 'action' => 'print'])->assertForbidden();
        $this->assertDatabaseCount('delivery_orders', 0);
        $this->post(route('delivery-orders.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $order = DeliveryOrder::firstOrFail();
        $this->put(route('delivery-orders.update', $order), [...$payload, 'action' => 'print', 'purchase_order_no' => 'UNAUTHORIZED'])->assertForbidden();
        $this->assertSame($payload['purchase_order_no'], $order->fresh()->purchase_order_no);
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
