<?php

namespace Tests\Feature;

use App\Models\LabelPrint;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\Warehouse;
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
            ->assertSee('WAF No.')
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

    public function test_label_pdf_uses_reference_black_panel_and_standard_text(): void
    {
        $html = view('labels.pdf', ['pages' => []])->render();

        $this->assertStringContainsString('#050505', $html);
        $this->assertStringContainsString('background: #050505;', $html);
        $this->assertStringContainsString('.sticker-description', $html);
        $this->assertStringContainsString('.sticker-standard', $html);
        $this->assertStringContainsString('.sticker-standard span', $html);
        $this->assertStringNotContainsString('.barcode-title', $html);
        $this->assertStringNotContainsString('Company.', $html);
        $this->assertStringNotContainsString('Catalog', $html);
        $this->assertStringContainsString('letter-spacing: 1.3pt;', $html);
        $this->assertStringContainsString('letter-spacing: 1.1pt;', $html);
        $this->assertStringContainsString('letter-spacing: .33pt;', $html);
        $this->assertStringContainsString('height: 17mm;', $html);
        $this->assertStringNotContainsString('.sticker-din', $html);
        $this->assertStringNotContainsString('.sticker-addresses', $html);
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
