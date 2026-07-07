<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Operator Label', 'email' => 'operator@labelin.test', 'role' => 'backoffice-operator'],
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
                    'email_verified_at' => now(),
                ],
            );
            if ($user->trashed()) {
                $user->restore();
            }
        }
    }
}
