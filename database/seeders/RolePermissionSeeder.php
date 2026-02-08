<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Truncate tables (clean start)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        Permission::truncate();
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define all permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Company Management
            'view companies',
            'create companies',
            'edit companies',
            'delete companies',

            // Warehouse Management
            'view warehouses',
            'create warehouses',
            'edit warehouses',
            'delete warehouses',

            // Vehicle Management
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',

            // Driver Management
            'view drivers',
            'create drivers',
            'edit drivers',
            'delete drivers',

            // Route Management
            'view routes',
            'create routes',
            'edit routes',
            'delete routes',

            // Report Management
            'view reports',
            'create reports',
            'edit reports',
            'delete reports',
        ];

        // Create all permissions
        echo "Creating permissions...\n";
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
            echo "✓ {$permission}\n";
        }

        echo "\nTotal permissions created: " . count($permissions) . "\n\n";

        // Create roles and assign permissions
        echo "Creating roles...\n";

        // 1. ADMIN - Full access to everything
        $adminRole = Role::create([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo(Permission::all());
        echo "✓ Admin (32 permissions)\n";

        // 2. MANAGER - Can manage operations but not system settings
        $managerRole = Role::create([
            'name' => 'Manager',
            'guard_name' => 'web'
        ]);
        $managerRole->givePermissionTo([
            'view users', 'create users', 'edit users',
            'view companies', 'create companies', 'edit companies',
            'view warehouses', 'create warehouses', 'edit warehouses',
            'view vehicles', 'create vehicles', 'edit vehicles',
            'view drivers', 'create drivers', 'edit drivers',
            'view routes', 'create routes', 'edit routes',
            'view reports', 'create reports',
        ]);
        echo "✓ Manager (21 permissions)\n";

        // 3. COORDINATOR - Can view and manage routes/deliveries
        $coordinatorRole = Role::create([
            'name' => 'Coordinator',
            'guard_name' => 'web'
        ]);
        $coordinatorRole->givePermissionTo([
            'view companies', 'view warehouses',
            'view vehicles', 'view drivers',
            'view routes', 'create routes', 'edit routes',
            'view reports',
        ]);
        echo "✓ Coordinator (8 permissions)\n";

        // 4. VIEWER - Read-only access
        $viewerRole = Role::create([
            'name' => 'Viewer',
            'guard_name' => 'web'
        ]);
        $viewerRole->givePermissionTo([
            'view companies', 'view warehouses',
            'view vehicles', 'view drivers',
            'view routes', 'view reports',
        ]);
        echo "✓ Viewer (6 permissions)\n";

        echo "\n✅ Roles and Permissions setup complete!\n";
        echo "Total Roles: 4\n";
        echo "Total Permissions: 32\n";

        // Summary
        echo "\n=== ROLE SUMMARY ===\n";
        foreach (Role::all() as $role) {
            echo "{$role->name}: {$role->permissions->count()} permissions\n";
        }
    }
}
