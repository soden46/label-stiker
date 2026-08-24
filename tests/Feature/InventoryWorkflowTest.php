<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_staff_only_sees_stock_and_barcode_label_menu(): void
    {
        $user = $this->inventoryUser();
        $this->product();
        $this->warehouse();

        $this->actingAs($user)
            ->get(route('stock.in.create'))
            ->assertOk()
            ->assertSee('Stock masuk')
            ->assertSee('Stock keluar')
            ->assertSee('Label barcode')
            ->assertSee('MAIN - Gudang Utama')
            ->assertDontSee('Dashboard')
            ->assertDontSee('Bulk print')
            ->assertDontSee('Master part')
            ->assertDontSee('Master gudang')
            ->assertDontSee('Pengaturan');

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('labels.index'))->assertForbidden();
    }

    public function test_inventory_staff_is_redirected_to_stock_in_after_login(): void
    {
        $user = $this->inventoryUser(['password' => 'password']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/stock-masuk');
    }

    public function test_inventory_staff_can_post_stock_in_and_stock_out(): void
    {
        $user = $this->inventoryUser();
        $product = $this->product();
        $warehouse = $this->warehouse();

        $this->actingAs($user)->post(route('stock.in.store'), [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 12,
            'unit_cost' => 1500,
            'notes' => 'Penerimaan awal',
        ])->assertRedirect(route('stock.in.create'))->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('stock.out.store'), [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
            'unit_cost' => 0,
            'notes' => 'Pemakaian gudang',
        ])->assertRedirect(route('stock.out.create'))->assertSessionHasNoErrors();

        $this->assertSame('7.0000', StockBalance::firstOrFail()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'movement_type' => 'stock_in',
            'quantity' => '12.0000',
            'posted_by' => $user->id,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'movement_type' => 'stock_out',
            'quantity' => '-5.0000',
            'posted_by' => $user->id,
        ]);
    }

    public function test_stock_out_cannot_make_inventory_negative(): void
    {
        $user = $this->inventoryUser();
        $product = $this->product();
        $warehouse = $this->warehouse();

        $this->actingAs($user)->post(route('stock.out.store'), [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1,
            'unit_cost' => 0,
        ])->assertRedirect()->assertSessionHasErrors('quantity');

        $this->assertSame(0, StockMovement::count());
    }

    public function test_inventory_manager_can_manage_warehouses(): void
    {
        $user = $this->inventoryManagerUser();

        $this->actingAs($user)
            ->get(route('warehouses.index'))
            ->assertOk()
            ->assertSee('Master gudang')
            ->assertSee('Gudang baru');

        $this->actingAs($user)->post(route('warehouses.store'), [
            'code' => 'raw-1',
            'name' => 'Gudang Raw Material',
            'address' => 'Area A',
            'is_active' => '1',
        ])->assertRedirect(route('warehouses.index'))->assertSessionHasNoErrors();

        $warehouse = Warehouse::where('code', 'RAW-1')->firstOrFail();
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'Gudang Raw Material',
            'is_active' => true,
        ]);

        $this->actingAs($user)->put(route('warehouses.update', $warehouse), [
            'code' => 'FG',
            'name' => 'Gudang Finished Goods',
            'allow_negative_stock' => '1',
            'is_active' => '1',
        ])->assertRedirect(route('warehouses.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'code' => 'FG',
            'allow_negative_stock' => true,
        ]);

        $this->actingAs($user)
            ->delete(route('warehouses.destroy', $warehouse->fresh()))
            ->assertRedirect(route('warehouses.index'));

        $this->assertSoftDeleted('warehouses', ['id' => $warehouse->id]);
    }

    public function test_stock_form_points_admin_to_master_warehouse_when_none_exists(): void
    {
        $user = $this->inventoryManagerUser(['role' => 'inventory_manager']);
        $this->product();

        $this->actingAs($user)
            ->get(route('stock.in.create'))
            ->assertOk()
            ->assertSee('Belum ada gudang aktif')
            ->assertSee('Tambah gudang dulu')
            ->assertSee(route('warehouses.index'));
    }

    private function inventoryUser(array $attributes = []): User
    {
        $permissions = collect([
            ['Input Stock Masuk', 'inventory.stock_in', 'Inventory'],
            ['Input Stock Keluar', 'inventory.stock_out', 'Inventory'],
            ['Buat Label', 'labels.create', 'Label'],
            ['Cetak Label', 'labels.print', 'Label'],
        ])->map(fn (array $permission) => Permission::create([
            'name' => $permission[0],
            'slug' => $permission[1],
            'module' => $permission[2],
            'portal' => 'backoffice',
        ]));

        $role = Role::create([
            'name' => 'Inventory Staff',
            'slug' => 'inventory-staff',
            'portal' => 'backoffice',
        ]);
        $role->permissions()->attach($permissions->pluck('id'));

        return User::factory()->create($attributes + [
            'role' => 'inventory',
            'role_id' => $role->id,
            'portal' => 'backoffice',
        ]);
    }

    private function inventoryManagerUser(array $attributes = []): User
    {
        $permissions = collect([
            ['Kelola Inventory', 'inventory.manage', 'Inventory'],
            ['Input Stock Masuk', 'inventory.stock_in', 'Inventory'],
            ['Input Stock Keluar', 'inventory.stock_out', 'Inventory'],
        ])->map(fn (array $permission) => Permission::create([
            'name' => $permission[0],
            'slug' => $permission[1],
            'module' => $permission[2],
            'portal' => 'backoffice',
        ]));

        $role = Role::create([
            'name' => 'Inventory Manager',
            'slug' => 'inventory-manager',
            'portal' => 'backoffice',
        ]);
        $role->permissions()->attach($permissions->pluck('id'));

        return User::factory()->create($attributes + [
            'role' => 'inventory_manager',
            'role_id' => $role->id,
            'portal' => 'backoffice',
        ]);
    }

    private function product(): Product
    {
        return Product::create([
            'sku' => 'PART-001',
            'name' => 'Part Gudang',
            'barcode_value' => 'PART-001',
            'uom' => 'PCS',
            'track_inventory' => true,
            'is_active' => true,
        ]);
    }

    private function warehouse(): Warehouse
    {
        return Warehouse::create([
            'code' => 'MAIN',
            'name' => 'Gudang Utama',
            'is_active' => true,
            'allow_negative_stock' => false,
        ]);
    }
}
