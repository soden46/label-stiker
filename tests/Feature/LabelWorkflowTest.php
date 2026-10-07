<?php

namespace Tests\Feature;

use App\Models\LabelPrint;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\BarcodeService;
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
            ->assertSee('builder-preview-content')
            ->assertSee('builder-result-stage')
            ->assertSee('data-label-layout')
            ->assertSee('class="pdf-qr"', false)
            ->assertSee('DESCRIPTION.')
            ->assertSee('CUST NO.')
            ->assertSee('P.O NO.')
            ->assertSee('WAF NO.')
            ->assertSee('QTY.')
            ->assertDontSee('Company.')
            ->assertDontSee('>Catalog<', false)
            ->assertSee('MANUFACTURED TO WAF')
            ->assertSee('ISO 9001 2015 CERTIFIED')
            ->assertDontSee('DELIVERY ORDER OPSIONAL')
            ->assertDontSee('deliveryOrderSelect', false)
            ->assertDontSee('DIN')
            ->assertDontSee('CODE.')
            ->assertDontSee('STOCK INV.')
            ->assertDontSee('ALAMAT PENGIRIM')
            ->assertDontSee('ALAMAT PENERIMA')
            ->assertDontSee('preview-yellow', false)
            ->assertDontSee('fake-barcode', false);
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
            ->assertSee('<col style="width:25.5%"><col style="width:74.5%">', false)
            ->assertSee('<col style="width:55.5%"><col style="width:44.5%">', false)
            ->assertSee('class="barcode-standard"', false)
            ->assertSee('class="pdf-qr"', false)
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

    public function test_label_pdf_uses_yellow_panel_and_standard_text(): void
    {
        $label = new LabelPrint([
            'purchase_order_no' => 'PO-TEST', 'customer_part_no' => 'CUST-TEST', 'quantity' => 1,
            'uom' => 'PCS', 'barcode_value' => 'WAF-TEST',
            'product_snapshot' => ['name' => 'PRODUCT TEST', 'description' => 'SIZE TEST', 'supplier_code' => 'CAT-TEST'],
        ]);
        $html = view('labels.pdf', ['pages' => [[
            'label' => $label,
            'catalogQrDataUri' => app(BarcodeService::class)->qrDataUri(route('catalogs.patria')),
            'logoDataUri' => null,
        ]]])->render();

        $this->assertStringContainsString('background: #ffc400', $html);
        $this->assertStringContainsString('color: #050505', $html);
        $this->assertStringContainsString('font-family: "Anton", sans-serif;', $html);
        $this->assertStringContainsString('fonts/anton/Anton-Regular.ttf', $html);
        $this->assertStringContainsString('font-family: Helvetica, Arial, sans-serif;', $html);
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'font-family: Helvetica, Arial, sans-serif;'));
        $this->assertStringNotContainsString('#2b5a9e', $html);
        $this->assertStringContainsString('border-radius: 6mm 6mm 0 0', $html);
        $this->assertStringContainsString('DESCRIPTION.', $html);
        $this->assertStringContainsString('.sticker-description', $html);
        $this->assertStringContainsString('border-spacing: 3mm 0;', $html);
        $this->assertStringContainsString('<col style="width:25.5%"><col style="width:74.5%">', $html);
        $this->assertStringContainsString('border-spacing: 5mm 0;', $html);
        $this->assertStringContainsString('height: 20.6mm;', $html);
        $this->assertStringContainsString('max-width: 21.5mm;', $html);
        $this->assertStringNotContainsString('border: 0.3mm solid #050505;', $html);
        $this->assertStringNotContainsString('border-left: 2mm solid #050505;', $html);
        $this->assertStringNotContainsString('border-right: 2.2mm solid #050505;', $html);
        $this->assertStringContainsString('text-align: right;', $html);
        $this->assertStringContainsString('.barcode-standard', $html);
        $this->assertStringContainsString('.barcode-standard span', $html);
        $this->assertStringContainsString('font-size: 10pt;', $html);
        $this->assertStringContainsString('line-height: 1.1;', $html);
        $this->assertStringContainsString('width: 125%;', $html);
        $this->assertStringContainsString('transform: scaleX(1);', $html);
        $this->assertStringNotContainsString('class="field-title-spacer"', $html);
        $this->assertStringNotContainsString('<td class="sticker-standard">', $html);
        $this->assertStringNotContainsString('alt="Part barcode"', $html);
        $this->assertStringNotContainsString('rowspan="2"', $html);
        $this->assertSame(2, substr_count($html, '<col style="width:55.5%"><col style="width:44.5%">'));
        $this->assertSame(1, substr_count($html, '<col style="width:60%"><col style="width:40%">'));
        $this->assertStringContainsString('height: 11.5mm', $html);
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'font-size: 18pt;'));
        $this->assertStringContainsString('white-space: nowrap;', $html);
        $this->assertStringContainsString('clear: both;', $html);
        $this->assertStringContainsString('margin-top: 2mm;', $html);
        $this->assertStringContainsString('height: 20mm;', $html);
        $this->assertStringContainsString('height: 69mm;', $html);
        $this->assertStringContainsString('width: 93mm;', $html);
        $this->assertStringContainsString('margin: 4mm 0 0;', $html);
        $this->assertStringContainsString('top: 6mm;', $html);
        $this->assertStringContainsString('height: 24mm;', $html);
        $this->assertStringContainsString('width: 100%;', $html);
        $this->assertStringNotContainsString('.barcode-title', $html);
        $this->assertStringNotContainsString('Company.', $html);
        $this->assertStringNotContainsString('Catalog', $html);
        $this->assertStringContainsString('CUST NO.', $html);
        $this->assertStringContainsString('P.O NO.', $html);
        $this->assertStringContainsString('WAF NO.', $html);
        $this->assertStringContainsString('QTY.', $html);
        $this->assertStringNotContainsString('CODE.', $html);
        $this->assertStringContainsString('class="pdf-qr"', $html);
        $this->assertStringContainsString('alt="QR code katalog produk"', $html);
        $this->assertStringNotContainsString('Supplier barcode', $html);
        $this->assertStringNotContainsString('customerBarcode', $html);
        $this->assertStringNotContainsString('CAT-TEST', $html);
        $this->assertStringNotContainsString('.sticker-din', $html);
        $this->assertStringNotContainsString('.sticker-addresses', $html);
    }

    public function test_catalog_qr_uses_a_public_patria_catalog_pdf(): void
    {
        $catalogUrl = route('catalogs.patria');
        $qrDataUri = app(BarcodeService::class)->qrDataUri($catalogUrl);

        $this->get($catalogUrl)
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('data:image/png;base64,', $qrDataUri);
        $this->assertStringStartsWith(
            "\x89PNG\r\n\x1a\n",
            base64_decode(substr($qrDataUri, strlen('data:image/png;base64,')), true),
        );
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
