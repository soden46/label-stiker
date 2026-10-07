<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RbacSeeder::class);

        $users = [
            ['name' => 'Admin Labelin', 'email' => 'admin@labelin.test', 'role' => 'super-admin'],
            ['name' => 'Operator Label', 'email' => 'operator@labelin.test', 'role' => 'backoffice-operator'],
            ['name' => 'Kasir Demo', 'email' => 'kasir@labelin.test', 'role' => 'pos-cashier'],
            ['name' => 'Staff Inventory', 'email' => 'inventory@labelin.test', 'role' => 'inventory-staff'],
            ['name' => 'Staff Purchasing', 'email' => 'purchasing@labelin.test', 'role' => 'purchasing-staff'],
            ['name' => 'Staff Produksi', 'email' => 'production@labelin.test', 'role' => 'production-staff'],
            ['name' => 'Manager Demo', 'email' => 'manager@labelin.test', 'role' => 'management-viewer'],
        ];

        foreach ($users as $data) {
            $role = Role::where('slug', $data['role'])->firstOrFail();
            $user = User::withTrashed()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => $role->slug,
                    'role_id' => $role->id,
                    'portal' => $role->portal,
                    'is_active' => true,
                ],
            );

            $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

            if ($user->trashed()) {
                $user->restore();
            }
        }
    }
}
