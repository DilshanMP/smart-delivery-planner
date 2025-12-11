<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Company permissions
            'view companies',
            'create companies',
            'update companies',
            'delete companies',

            // Warehouse permissions
            'view warehouses',
            'create warehouses',
            'update warehouses',
            'delete warehouses',

            // Vehicle permissions
            'view vehicles',
            'create vehicles',
            'update vehicles',
            'delete vehicles',

            // Driver permissions
            'view drivers',
            'create drivers',
            'update drivers',
            'delete drivers',

            // Route permissions
            'view routes',
            'create routes',
            'update routes',
            'delete routes',

            // Cost Item permissions
            'view cost items',
            'create cost items',
            'update cost items',
            'delete cost items',

            // AI Prediction permissions
            'view ai predictions',
            'create ai predictions',
            'update ai predictions',
            'delete ai predictions',

            // Report permissions
            'view reports',
            'export data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('✅ Permissions created successfully!');

        // Create roles and assign permissions

        // 1. Admin Role - Full access
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
        $this->command->info('✅ Admin role created with all permissions!');

        // 2. Manager Role - Operational access
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view companies',
            'view warehouses', 'create warehouses', 'update warehouses',
            'view vehicles', 'create vehicles', 'update vehicles',
            'view drivers', 'create drivers', 'update drivers',
            'view routes', 'create routes', 'update routes', 'delete routes',
            'view cost items', 'create cost items', 'update cost items', 'delete cost items',
            'view ai predictions', 'create ai predictions',
            'view reports', 'export data',
        ]);
        $this->command->info('✅ Manager role created with operational permissions!');

        // 3. Coordinator Role - Route management
        $coordinatorRole = Role::firstOrCreate(['name' => 'coordinator']);
        $coordinatorRole->givePermissionTo([
            'view warehouses',
            'view vehicles',
            'view drivers',
            'view routes', 'create routes', 'update routes',
            'view cost items', 'create cost items', 'update cost items',
            'view ai predictions', 'create ai predictions',
            'view reports',
        ]);
        $this->command->info('✅ Coordinator role created with route management permissions!');

        // 4. Viewer Role - Read-only access
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
        $viewerRole->givePermissionTo([
            'view warehouses',
            'view vehicles',
            'view drivers',
            'view routes',
            'view cost items',
            'view ai predictions',
            'view reports',
        ]);
        $this->command->info('✅ Viewer role created with read-only permissions!');

        $this->command->info('');
        $this->command->info('🎉 All roles and permissions created successfully!');
        $this->command->info('Roles created: admin, manager, coordinator, viewer');
        $this->command->info('Total permissions: ' . count($permissions));
    }
}
