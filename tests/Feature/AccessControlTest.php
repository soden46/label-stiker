<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_backoffice_and_pos_users_cannot_cross_portals(): void
    {
        $dashboard = Permission::create(['name' => 'Dashboard', 'slug' => 'dashboard.view', 'module' => 'Dashboard', 'portal' => 'backoffice']);
        $posAccess = Permission::create(['name' => 'POS', 'slug' => 'pos.access', 'module' => 'POS', 'portal' => 'pos']);
        $operatorRole = Role::create(['name' => 'Operator', 'slug' => 'operator', 'portal' => 'backoffice']);
        $cashierRole = Role::create(['name' => 'Cashier', 'slug' => 'cashier', 'portal' => 'pos']);
        $operatorRole->permissions()->attach($dashboard);
        $cashierRole->permissions()->attach($posAccess);
        $operator = User::factory()->create(['role' => 'operator', 'role_id' => $operatorRole->id, 'portal' => 'backoffice']);
        $cashier = User::factory()->create(['role' => 'cashier', 'role_id' => $cashierRole->id, 'portal' => 'pos']);

        $this->actingAs($operator)->get('/dashboard')->assertOk();
        $this->actingAs($operator)->get('/pos')->assertForbidden();
        $this->actingAs($cashier)->get('/pos')->assertOk();
        $this->actingAs($cashier)->get('/dashboard')->assertForbidden();
        $this->actingAs($operator)->get('/roles')->assertForbidden();
    }

    public function test_only_super_admin_can_manage_roles_and_users(): void
    {
        $superAdmin = User::factory()->create(['role' => 'admin']);
        $role = Role::create(['name' => 'Staff', 'slug' => 'staff', 'portal' => 'backoffice']);

        $this->actingAs($superAdmin)->get(route('roles.index'))->assertOk();
        $this->actingAs($superAdmin)->post(route('users.store'), [
            'name' => 'New Staff', 'email' => 'staff@example.test', 'role_id' => $role->id,
            'is_active' => '1', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'staff@example.test', 'portal' => 'backoffice', 'role_id' => $role->id]);
    }

    public function test_user_can_update_profile_change_and_reset_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->put(route('profile.update'), ['name' => 'Updated User', 'email' => 'updated@example.test'])->assertRedirect();
        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'old-password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));

        auth()->logout();
        $this->post(route('password.email'), ['email' => 'updated@example.test'])->assertSessionHasNoErrors();
        Notification::assertSentTo($user->fresh(), ResetPassword::class);

        $token = Password::createToken($user->fresh());
        $this->post(route('password.store'), [
            'token' => $token, 'email' => 'updated@example.test',
            'password' => 'reset-password', 'password_confirmation' => 'reset-password',
        ])->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('reset-password', $user->fresh()->password));
    }
}
