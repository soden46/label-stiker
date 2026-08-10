<?php

namespace Tests\Feature;

use App\Models\LabelPrint;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
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

    public function test_create_label_preview_includes_standard_certification_text(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('labels.create'))
            ->assertOk()
            ->assertSee('STANDARD')
            ->assertSee('MANUFACTURED TO WAF')
            ->assertSee('REGISTERED TRADEMARK')
            ->assertSee('ISO 9001:2015 CERTIFIED');
    }

    public function test_admin_can_generate_and_download_a_label(): void
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
        ]);

        $response = $this->actingAs($user)->post('/labels', [
            'product_id' => $product->id,
            'purchase_order_no' => '1011873938',
            'customer_part_no' => '40172501-0015',
            'quantity' => 100,
            'uom' => 'PCS',
        ]);

        $label = LabelPrint::firstOrFail();
        $response->assertRedirect(route('labels.show', $label));
        $this->assertSame('CON-STRAIGHT', $label->product_snapshot['name']);

        $this->actingAs($user)->get(route('labels.pdf', $label))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertNotNull($label->fresh()->printed_at);
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
        ]);
        $label = LabelPrint::firstOrFail();

        $response = $this->actingAs($user)->post(route('labels.bulk-pdf'), [
            'label_ids' => [$label->id],
            'copies' => [$label->id => 3],
        ]);

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertSame(3, preg_match_all('/\/Type\s*\/Page\b/', $response->getContent()));
        $this->assertNotNull($label->fresh()->printed_at);
    }
}
