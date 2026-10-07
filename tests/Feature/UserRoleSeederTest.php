<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_user_accounts_with_their_roles(): void
    {
        $this->seed(UserRoleSeeder::class);

        $this->assertDatabaseCount('users', 7);
        $this->assertDatabaseHas('users', ['email' => 'admin@labelin.test', 'role' => 'super-admin']);
        $this->assertDatabaseHas('users', ['email' => 'kasir@labelin.test', 'role' => 'pos-cashier', 'portal' => 'pos']);
        $this->assertTrue(User::where('email', 'admin@labelin.test')->firstOrFail()->isSuperAdmin());
        $this->assertTrue(User::where('email', 'operator@labelin.test')->firstOrFail()->canAccess('labels.create'));
    }
}
