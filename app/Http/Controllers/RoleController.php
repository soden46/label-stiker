<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('access.roles.index', ['roles' => Role::withCount(['users', 'permissions'])->orderBy('portal')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return $this->formView(new Role(['portal' => 'backoffice']));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRole($request);
        $role = Role::create([...$data, 'is_super_admin' => false]);
        $this->syncPermissions($role, $request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', 'Role baru berhasil dibuat.');
    }

    public function edit(Role $role): View
    {
        return $this->formView($role->load('permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->is_super_admin, 422, 'Role Super Admin tidak dapat diubah.');
        $role->update($this->validateRole($request, $role));
        $this->syncPermissions($role, $request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', 'Role dan hak akses berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_super_admin || $role->users()->exists()) {
            return back()->withErrors(['role' => 'Role bawaan atau role yang masih dipakai pengguna tidak dapat dihapus.']);
        }
        $role->delete();

        return back()->with('success', 'Role berhasil dihapus.');
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'alpha_dash', 'max:100', Rule::unique('roles', 'slug')->ignore($role)],
            'portal' => ['required', Rule::in(['backoffice', 'pos'])],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
    }

    private function syncPermissions(Role $role, array $ids): void
    {
        $validIds = Permission::where('portal', $role->portal)->whereIn('id', $ids)->pluck('id');
        $role->permissions()->sync($validIds);
    }

    private function formView(Role $role): View
    {
        return view('access.roles.form', [
            'role' => $role,
            'permissionGroups' => Permission::orderBy('module')->orderBy('name')->get()->groupBy(['portal', 'module']),
        ]);
    }
}
