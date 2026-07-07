<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('access.users.index', ['users' => User::with('accessRole')->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('access.users.form', ['managedUser' => new User(['portal' => 'backoffice', 'is_active' => true]), 'roles' => Role::orderBy('portal')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);
        $role = Role::findOrFail($data['role_id']);
        $data['portal'] = $role->portal;
        $data['role'] = $role->slug;
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna baru berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('access.users.form', ['managedUser' => $user, 'roles' => Role::orderBy('portal')->orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->isSuperAdmin() && $request->user()->isNot($user)) {
            return back()->withErrors(['email' => 'Akun Super Admin utama tidak dapat diubah pengguna lain.']);
        }
        $data = $this->validateUser($request, $user);
        $role = Role::findOrFail($data['role_id']);
        $data['portal'] = $role->portal;
        $data['role'] = $role->slug;
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data dan hak akses pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user) || $user->isSuperAdmin()) {
            return back()->withErrors(['user' => 'Akun sendiri atau Super Admin tidak dapat dihapus.']);
        }
        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user)],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
