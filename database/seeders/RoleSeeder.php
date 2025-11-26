<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates roles and permissions for the Smart Delivery System.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $coordinator = Role::create(['name' => 'coordinator']);
        $viewer = Role::create(['name' => 'viewer']);

        // Create permissions
        $permissions = [
            // Warehouse permissions
            'view warehouses',
            'create warehouses',
            'edit warehouses',
            'delete warehouses',

            // Vehicle permissions
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',

            // Driver permissions
            'view drivers',
            'create drivers',
            'edit drivers',
            'delete drivers',

            // Route permissions
            'view routes',
            'create routes',
            'edit routes',
            'delete routes',
            'complete routes',

            // Company permissions
            'view companies',
            'create companies',
            'edit companies',
            'delete companies',

            // Report permissions
            'view reports',
            'export reports',

            // AI permissions
            'use ai predictions',

            // Admin permissions
            'manage users',
            'manage roles',
            'view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles

        // Admin has all permissions
        $admin->givePermissionTo(Permission::all());

        // Manager has most permissions except user management
        $manager->givePermissionTo([
            'view warehouses', 'create warehouses', 'edit warehouses', 'delete warehouses',
            'view vehicles', 'create vehicles', 'edit vehicles', 'delete vehicles',
            'view drivers', 'create drivers', 'edit drivers', 'delete drivers',
            'view routes', 'create routes', 'edit routes', 'delete routes', 'complete routes',
            'view companies', 'create companies', 'edit companies', 'edit companies', 'delete companies',
            'view reports', 'export reports',
            'use ai predictions',
        ]);

        // Coordinator can create and manage routes, view resources
        $coordinator->givePermissionTo([
            'view warehouses',
            'view vehicles',
            'view drivers',
            'view routes', 'create routes', 'edit routes', 'complete routes',
            'view companies',
            'view reports',
            'use ai predictions',
        ]);

        // Viewer can only view
        $viewer->givePermissionTo([
            'view warehouses',
            'view vehicles',
            'view drivers',
            'view routes',
            'view companies',
            'view reports',
        ]);

        $this->command->info('✓ Roles and permissions created successfully!');
        $this->command->info('✓ Created roles: admin, manager, coordinator, viewer');
        $this->command->info('✓ Created ' . count($permissions) . ' permissions');
    }
}
