<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        // Eager load permissions & users for display
        $roles = Role::with('permissions', 'users')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        // Build permission groups for view (module => ['name'=>..., 'permissions'=>[...] ])
        $permissionGroups = $this->buildPermissionGroups();

        return view('roles.create', compact('permissionGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')
                        ->with('success', 'Role created successfully!');
    }

    public function show(Role $role)
    {
        $role->load('permissions', 'users');

        return view('roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissionGroups = $this->buildPermissionGroups();

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissionGroups', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if (in_array(strtolower($role->name), ['admin', 'super admin', 'super_admin'])) {
            return redirect()->route('roles.index')
                             ->with('error', 'Cannot modify system roles!');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $validated['name']
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
                        ->with('success', 'Role updated successfully!');
    }

    public function destroy(Role $role)
    {
        if (in_array(strtolower($role->name), ['admin', 'super admin', 'super_admin'])) {
            return redirect()->route('roles.index')
                             ->with('error', 'Cannot delete system roles!');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                             ->with('error', 'Cannot delete role with assigned users!');
        }

        $role->delete();

        return redirect()->route('roles.index')
                        ->with('success', 'Role deleted successfully!');
    }

    /**
     * Build permission groups in the structure the view expects:
     * [
     *   'module_key' => [
     *       'name' => 'Module Display Name',
     *       'permissions' => [ 'view users' => 'view users', ... ]
     *   ],
     *   ...
     * ]
     *
     * This groups permissions by second word (as you used originally),
     * but also creates a safer mapping so view can index by action names.
     */
    protected function buildPermissionGroups(): array
    {
        $permissions = Permission::all()->pluck('name')->toArray();

        $groups = [];

        foreach ($permissions as $permName) {
            // split permission like "view users" -> action=view, module=users
            $parts = preg_split('/\s+/', $permName, 2);
            $action = strtolower($parts[0] ?? 'other');
            $module = strtolower(str_replace(' ', '_', $parts[1] ?? 'other'));

            // display name for module (human friendly)
            $displayName = ucwords(str_replace('_', ' ', $module));

            if (!isset($groups[$module])) {
                $groups[$module] = [
                    'name' => $displayName,
                    'permissions' => []
                ];
            }

            // store permission keyed by action so view can look up by action
            $groups[$module]['permissions'][$action] = $permName;
        }

        return $groups;
    }
}
