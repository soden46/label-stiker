<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\BusinessPartner;
use App\Models\DeliveryOrder;
use App\Models\LabelPrint;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\LabelBrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_login_and_open_dashboard(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk()->assertSee('Dashboard label');
    }

    public function test_pos_url_returns_user_to_pos_after_login(): void
    {
        $permission = Permission::create(['name' => 'Akses POS', 'slug' => 'pos.access', 'module' => 'POS', 'portal' => 'pos']);
        $role = Role::create(['name' => 'Kasir', 'slug' => 'kasir', 'portal' => 'pos']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create(['password' => 'password', 'role' => 'cashier', 'role_id' => $role->id, 'portal' => 'pos']);

        $this->get('/pos')->assertRedirect('/pos/login');
        $this->post('/pos/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/pos');
        $this->get('/pos')->assertOk()->assertSee('Terminal penjualan');
    }

    public function test_create_label_preview_matches_reference_label_content(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('labels.create'))
            ->assertOk()
            ->assertSee('Description.')
            ->assertSee('CUST NO.')
            ->assertSee('P.O NO.')
            ->assertSee('WAF NO.')
            ->assertSee('QTY.')
            ->assertDontSee('Company.')
            ->assertDontSee('Catalog')
            ->assertSee('MANUFACTURED TO WAF')
            ->assertSee('ISO 9001 2015 CERTIFIED')
            ->assertDontSee('DIN')
            ->assertDontSee('CODE.')
            ->assertDontSee('STOCK INV.')
            ->assertDontSee('ALAMAT PENGIRIM')
            ->assertDontSee('ALAMAT PENERIMA');
    }

    public function test_label_form_can_prefill_data_from_a_delivery_order_item(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'sku' => 'DO-LABEL-001',
            'name' => 'DO LABEL PART',
            'description' => 'PART DARI DELIVERY ORDER',
            'customer_part_no' => 'MASTER-CUST-001',
            'barcode_value' => 'DO-LABEL-001',
            'uom' => 'PCS',
        ]);
        $to = BusinessPartner::create(['code' => 'TO-001', 'name' => 'PT Tujuan', 'is_customer' => true, 'is_active' => true, 'address' => 'Alamat Tujuan']);
        $shipTo = BusinessPartner::create(['code' => 'SHIP-001', 'name' => 'PT Penerima', 'is_customer' => true, 'is_active' => true, 'address' => 'Alamat Penerima', 'phone' => '021-123']);
        AppSetting::put('company_name', 'PT WAF Indonesia');
        AppSetting::put('company_address', 'Alamat Pengirim');

        $deliveryOrder = DeliveryOrder::create([
            'uuid' => 'f080936d-b093-4ea6-9684-29e10ae1da00',
            'number' => 'DO.2026.09.001',
            'delivery_date' => '2026-09-28',
            'to_partner_id' => $to->id,
            'ship_to_partner_id' => $shipTo->id,
            'to_company' => $to->name,
            'to_address' => $to->address,
            'ship_to_company' => $shipTo->name,
            'ship_to_project_site' => 'Site Penerima',
            'ship_to_address' => $shipTo->address,
            'ship_to_phone' => $shipTo->phone,
            'purchase_order_no' => 'PO-DO-001',
        ]);
        $deliveryOrder->items()->create([
            'product_id' => $product->id,
            'line_number' => 1,
            'item_name' => $product->name,
            'waf_part_no' => $product->sku,
            'customer_part_no' => 'DO-CUST-001',
            'quantity' => 12,
            'unit' => 'PCS',
        ]);

        $this->actingAs($user)->get(route('labels.create'))
            ->assertOk()
            ->assertSee('data-delivery-orders', false)
            ->assertSee('deliveryOrderItemSelect', false)
            ->assertSee('DO.2026.09.001')
            ->assertViewHas('deliveryOrders', function ($orders) use ($deliveryOrder) {
                $order = $orders->firstWhere('id', $deliveryOrder->id);

                return $order
                    && $order['purchase_order_no'] === 'PO-DO-001'
                    && $order['sender_address'] === "PT WAF Indonesia\nAlamat Pengirim"
                    && $order['recipient_address'] === "PT Penerima\nSite Penerima\nAlamat Penerima\nTLP. 021-123"
                    && $order['items'][0]['product_id'] === $deliveryOrder->items->first()->product_id
                    && $order['items'][0]['customer_part_no'] === 'DO-CUST-001'
                    && $order['items'][0]['quantity'] === 12.0;
            });
    }

    public function test_admin_can_generate_and_view_a_label_pdf(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'sku' => '1000-P12-M8',
            'name' => 'CON-STRAIGHT',
            'description' => 'SIZE.3/8X1/4NPT',
            'customer_part_no' => '40172501-0015',
            'supplier_code' => 'BP-CN',
            'barcode_value' => '1000-P12-M8',
            'uom' => 'PCS',
            'selling_price' => 150000,
        ]);
        $warehouse = Warehouse::create(['code' => 'MAIN', 'name' => 'Gudang Utama']);
        StockBalance::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 125]);

        $response = $this->actingAs($user)->post('/labels', [
            'product_id' => $product->id,
            'purchase_order_no' => '1011873938',
            'delivery_note_no' => 'SJ-2026-001',
            'customer_part_no' => '40172501-0015',
            'quantity' => 100,
            'uom' => 'PCS',
            'sender_address' => 'PT WAF Indonesia, Bekasi',
            'recipient_address' => 'PT Customer, Jakarta',
        ]);

        $label = LabelPrint::firstOrFail();
        $response->assertRedirect(route('labels.show', $label));
        $this->assertSame('CON-STRAIGHT', $label->product_snapshot['name']);
        $this->assertSame('SJ-2026-001', $label->delivery_note_no);
        $this->assertSame('125.0000', $label->inventory_stock);
        $this->assertSame('PT WAF Indonesia, Bekasi', $label->sender_address);
        $this->assertSame('PT Customer, Jakarta', $label->recipient_address);

        $this->actingAs($user)->get(route('labels.show', $label))
            ->assertOk()
            ->assertSee('is-result-label')
            ->assertSee('CUST NO.')
            ->assertSee('P.O NO.')
            ->assertSee('WAF NO.')
            ->assertSee('QTY.')
            ->assertDontSee('CODE.');

        $this->actingAs($user)->get(route('labels.pdf', $label))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=label-1011873938.pdf');
        $this->assertNotNull($label->fresh()->printed_at);
    }

    public function test_po_number_and_quantity_are_optional_when_generating_label(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'sku' => 'OPTIONAL-001',
            'name' => 'OPTIONAL PART',
            'description' => 'OPTIONAL LABEL',
            'customer_part_no' => 'CUST-OPTIONAL-001',
            'supplier_code' => 'ID',
            'barcode_value' => 'OPTIONAL-001',
            'uom' => 'PCS',
        ]);

        $this->actingAs($user)->get(route('labels.create'))
            ->assertOk()
            ->assertSee('P.O number</label><input', false)
            ->assertSee('Qty</label><input', false)
            ->assertDontSee('P.O number <b>*</b>', false)
            ->assertDontSee('Qty <b>*</b>', false);

        $response = $this->actingAs($user)->post(route('labels.store'), [
            'product_id' => $product->id,
            'delivery_note_no' => 'SJ-OPTIONAL-001',
            'customer_part_no' => 'CUST-OPTIONAL-001',
            'uom' => 'PCS',
            'sender_address' => 'PT WAF Indonesia, Bekasi',
            'recipient_address' => 'PT Customer, Jakarta',
        ]);

        $label = LabelPrint::latest('id')->firstOrFail();
        $response->assertRedirect(route('labels.show', $label));
        $this->assertSame('', $label->purchase_order_no);
        $this->assertSame(0, $label->quantity);

        $this->actingAs($user)->get(route('labels.pdf', $label))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_bulk_print_multiple_copies_on_separate_pages(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'sku' => 'BULK-001',
            'name' => 'BULK-PART',
            'description' => 'TEST PART',
            'customer_part_no' => 'CUST-BULK-001',
            'supplier_code' => 'ID',
            'barcode_value' => 'BULK-001',
            'uom' => 'PCS',
        ]);

        $this->actingAs($user)->post(route('labels.store'), [
            'product_id' => $product->id,
            'purchase_order_no' => 'PO-BULK-001',
            'customer_part_no' => 'CUST-BULK-001',
            'quantity' => 25,
            'uom' => 'PCS',
            'delivery_note_no' => 'SJ-BULK-001',
            'sender_address' => 'Sender Bulk',
            'recipient_address' => 'Recipient Bulk',
        ]);
        $label = LabelPrint::firstOrFail();

        $response = $this->actingAs($user)->post(route('labels.bulk-pdf'), [
            'label_ids' => [$label->id],
            'copies' => [$label->id => 3],
        ]);

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('inline; filename=label-bulk-', $response->headers->get('content-disposition'));
        $this->assertSame(3, preg_match_all('/\/Type\s*\/Page\b/', $response->getContent()));
        $this->assertNotNull($label->fresh()->printed_at);
    }

    public function test_label_pdf_uses_black_panel_and_standard_text(): void
    {
        $label = new LabelPrint([
            'purchase_order_no' => 'PO-TEST', 'customer_part_no' => 'CUST-TEST', 'quantity' => 1,
            'uom' => 'PCS', 'barcode_value' => 'WAF-TEST',
            'product_snapshot' => ['name' => 'PRODUCT TEST', 'description' => 'SIZE TEST', 'supplier_code' => 'CAT-TEST'],
        ]);
        $html = view('labels.pdf', ['pages' => [[
            'label' => $label,
            'partBarcodeDataUri' => '',
            'catalogBarcodeDataUri' => '',
            'logoDataUri' => null,
        ]]])->render();

        $this->assertStringNotContainsString('#ffc400', $html);
        $this->assertStringContainsString('background: #050505', $html);
        $this->assertStringContainsString('color: #fff', $html);
        $this->assertStringNotContainsString('#2b5a9e', $html);
        $this->assertStringContainsString('border-radius: 8mm 8mm 0 0', $html);
        $this->assertStringContainsString('border: 0.35mm solid #050505', $html);
        $this->assertStringContainsString('Description.', $html);
        $this->assertStringContainsString('.sticker-description', $html);
        $this->assertStringContainsString('.sticker-standard', $html);
        $this->assertStringContainsString('.sticker-standard span', $html);
        $this->assertStringNotContainsString('.barcode-title', $html);
        $this->assertStringNotContainsString('Company.', $html);
        $this->assertStringNotContainsString('Catalog', $html);
        $this->assertStringContainsString('CUST NO.', $html);
        $this->assertStringContainsString('P.O NO.', $html);
        $this->assertStringContainsString('WAF NO.', $html);
        $this->assertStringContainsString('QTY.', $html);
        $this->assertStringNotContainsString('CODE.', $html);
        $this->assertStringNotContainsString('customerBarcode', $html);
        $this->assertStringNotContainsString('CAT-TEST', $html);
        $this->assertStringNotContainsString('.sticker-din', $html);
        $this->assertStringNotContainsString('.sticker-addresses', $html);
    }

    public function test_label_uses_the_whatsapp_logo_asset(): void
    {
        $logo = app(LabelBrandingService::class)->publicLabelLogoDataUri();

        $this->assertNotNull($logo);
        $this->assertStringStartsWith('data:image/jpeg;base64,', $logo);
        $this->actingAs(User::factory()->create())->get(route('labels.create'))
            ->assertOk()
            ->assertSee('logo.jpeg');
    }

    public function test_create_label_product_payload_shows_inventory_without_price(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'sku' => 'STOCK-001',
            'name' => 'STOCK PART',
            'description' => 'WITH INVENTORY',
            'customer_part_no' => 'CUST-STOCK-001',
            'supplier_code' => 'ID',
            'barcode_value' => 'STOCK-001',
            'uom' => 'PCS',
            'selling_price' => 99000,
        ]);
        $warehouse = Warehouse::create(['code' => 'MAIN', 'name' => 'Gudang Utama']);
        StockBalance::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 42]);

        $response = $this->actingAs($user)->get(route('labels.create'));

        $response->assertOk()
            ->assertSee('&quot;inventory_stock&quot;:42', false)
            ->assertDontSee('selling_price', false)
            ->assertDontSee('99000', false);
    }
}
