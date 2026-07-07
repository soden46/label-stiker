<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['dashboard.view', 'Lihat Dashboard', 'Dashboard', 'backoffice'],
            ['labels.view', 'Lihat Label', 'Label', 'backoffice'],
            ['labels.create', 'Buat Label', 'Label', 'backoffice'],
            ['labels.print', 'Cetak Label', 'Label', 'backoffice'],
            ['products.view', 'Lihat Master Part', 'Master Part', 'backoffice'],
            ['products.manage', 'Kelola Master Part', 'Master Part', 'backoffice'],
            ['products.import', 'Import Master Part', 'Master Part', 'backoffice'],
            ['branding.manage', 'Kelola Branding', 'Pengaturan', 'backoffice'],
            ['inventory.view', 'Lihat Inventory', 'Inventory', 'backoffice'],
            ['inventory.manage', 'Kelola Inventory', 'Inventory', 'backoffice'],
            ['purchasing.view', 'Lihat Purchasing', 'Purchasing', 'backoffice'],
            ['purchasing.manage', 'Kelola Purchasing', 'Purchasing', 'backoffice'],
            ['manufacturing.view', 'Lihat Produksi/MRP', 'Manufacturing', 'backoffice'],
            ['manufacturing.manage', 'Kelola Produksi/MRP', 'Manufacturing', 'backoffice'],
            ['reports.view', 'Lihat Laporan', 'Laporan', 'backoffice'],
            ['users.manage', 'Kelola Pengguna', 'Akses', 'backoffice'],
            ['roles.manage', 'Kelola Role & Hak Akses', 'Akses', 'backoffice'],
            ['pos.access', 'Akses Terminal POS', 'POS', 'pos'],
            ['sales.create', 'Buat Transaksi Penjualan', 'POS', 'pos'],
            ['sales.void', 'Void Transaksi Penjualan', 'POS', 'pos'],
        ];

        $permissions = collect($definitions)->mapWithKeys(function ($definition) {
            [$slug, $name, $module, $portal] = $definition;
            $permission = Permission::updateOrCreate(['slug' => $slug], compact('name', 'module', 'portal'));

            return [$slug => $permission];
        });

        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'portal' => 'backoffice', 'is_super_admin' => true, 'description' => 'Akses penuh seluruh Back Office.'],
        );
        $operator = Role::updateOrCreate(
            ['slug' => 'backoffice-operator'],
            ['name' => 'Back Office Operator', 'portal' => 'backoffice', 'is_super_admin' => false, 'description' => 'Operasional label dan master part.'],
        );
        $cashier = Role::updateOrCreate(
            ['slug' => 'pos-cashier'],
            ['name' => 'POS Cashier', 'portal' => 'pos', 'is_super_admin' => false, 'description' => 'Akses terminal kasir.'],
        );
        $inventory = Role::updateOrCreate(
            ['slug' => 'inventory-staff'],
            ['name' => 'Inventory Staff', 'portal' => 'backoffice', 'is_super_admin' => false, 'description' => 'Operasional stok dan gudang.'],
        );
        $purchasing = Role::updateOrCreate(
            ['slug' => 'purchasing-staff'],
            ['name' => 'Purchasing Staff', 'portal' => 'backoffice', 'is_super_admin' => false, 'description' => 'Purchase order dan penerimaan barang.'],
        );
        $production = Role::updateOrCreate(
            ['slug' => 'production-staff'],
            ['name' => 'Production Staff', 'portal' => 'backoffice', 'is_super_admin' => false, 'description' => 'BOM, MRP, dan production order.'],
        );
        $manager = Role::updateOrCreate(
            ['slug' => 'management-viewer'],
            ['name' => 'Management Viewer', 'portal' => 'backoffice', 'is_super_admin' => false, 'description' => 'Akses baca dashboard dan laporan manajemen.'],
        );

        $superAdmin->permissions()->sync($permissions->where('portal', 'backoffice')->pluck('id'));
        $operator->permissions()->sync($permissions->only([
            'dashboard.view', 'labels.view', 'labels.create', 'labels.print', 'products.view',
        ])->pluck('id'));
        $cashier->permissions()->sync($permissions->only(['pos.access', 'sales.create'])->pluck('id'));
        $inventory->permissions()->sync($permissions->only([
            'dashboard.view', 'products.view', 'inventory.view', 'inventory.manage',
        ])->pluck('id'));
        $purchasing->permissions()->sync($permissions->only([
            'dashboard.view', 'products.view', 'inventory.view', 'purchasing.view', 'purchasing.manage',
        ])->pluck('id'));
        $production->permissions()->sync($permissions->only([
            'dashboard.view', 'products.view', 'inventory.view', 'manufacturing.view', 'manufacturing.manage',
        ])->pluck('id'));
        $manager->permissions()->sync($permissions->only([
            'dashboard.view', 'inventory.view', 'purchasing.view', 'manufacturing.view', 'reports.view',
        ])->pluck('id'));

        User::where('email', 'admin@labelin.test')->update([
            'role_id' => $superAdmin->id, 'role' => 'super_admin', 'portal' => 'backoffice', 'is_active' => true,
        ]);
        User::updateOrCreate(
            ['email' => 'kasir@labelin.test'],
            [
                'name' => 'Kasir Demo', 'password' => Hash::make('password'), 'role' => 'cashier',
                'role_id' => $cashier->id, 'portal' => 'pos', 'is_active' => true, 'email_verified_at' => now(),
            ],
        );
    }
}
