<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║   SMART DELIVERY PLANNER - DATABASE SEEDER            ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n";
        echo "\n";

        // Step 1: Seed Roles and Permissions
        echo "STEP 1: Creating Roles & Permissions...\n";
        echo "═══════════════════════════════════════\n";
        $this->call(RolePermissionSeeder::class);
        echo "\n";

        // Step 2: Create Admin User
        echo "STEP 2: Creating Admin User...\n";
        echo "═══════════════════════════════════════\n";

        // Check if admin exists
        $adminEmail = 'admin@smartdelivery.lk';
        $existingAdmin = User::where('email', $adminEmail)->first();

        if ($existingAdmin) {
            echo "⚠️  Admin user already exists: {$adminEmail}\n";
            echo "   Updating role to Admin...\n";
            $existingAdmin->syncRoles(['Admin']);
            $admin = $existingAdmin;
        } else {
            echo "Creating new admin user...\n";
            $admin = User::create([
                'name' => 'System Administrator',
                'email' => $adminEmail,
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('Admin');
            echo "✓ Created: {$admin->name} ({$admin->email})\n";
            echo "✓ Password: admin123\n";
        }

        echo "✓ Admin role assigned\n";
        echo "✓ Permissions: {$admin->getAllPermissions()->count()}\n";
        echo "\n";

        // Step 3: Create Test Users (Optional - comment out if not needed)
        echo "STEP 3: Creating Test Users...\n";
        echo "═══════════════════════════════════════\n";

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@smartdelivery.lk'],
            [
                'name' => 'Delivery Manager',
                'password' => Hash::make('manager123'),
                'email_verified_at' => now(),
            ]
        );
        $manager->syncRoles(['Manager']);
        echo "✓ Manager: {$manager->email} (password: manager123)\n";

        // Coordinator
        $coordinator = User::firstOrCreate(
            ['email' => 'coordinator@smartdelivery.lk'],
            [
                'name' => 'Route Coordinator',
                'password' => Hash::make('coordinator123'),
                'email_verified_at' => now(),
            ]
        );
        $coordinator->syncRoles(['Coordinator']);
        echo "✓ Coordinator: {$coordinator->email} (password: coordinator123)\n";

        // Viewer
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@smartdelivery.lk'],
            [
                'name' => 'Report Viewer',
                'password' => Hash::make('viewer123'),
                'email_verified_at' => now(),
            ]
        );
        $viewer->syncRoles(['Viewer']);
        echo "✓ Viewer: {$viewer->email} (password: viewer123)\n";
        echo "\n";

        // Summary
        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║   ✅ DATABASE SEEDING COMPLETE!                       ║\n";
        echo "╠════════════════════════════════════════════════════════╣\n";
        echo "║   LOGIN CREDENTIALS:                                  ║\n";
        echo "╠════════════════════════════════════════════════════════╣\n";
        echo "║   Admin:                                              ║\n";
        echo "║   Email: admin@smartdelivery.lk                       ║\n";
        echo "║   Password: admin123                                  ║\n";
        echo "╠════════════════════════════════════════════════════════╣\n";
        echo "║   Manager:                                            ║\n";
        echo "║   Email: manager@smartdelivery.lk                     ║\n";
        echo "║   Password: manager123                                ║\n";
        echo "╠════════════════════════════════════════════════════════╣\n";
        echo "║   Coordinator:                                        ║\n";
        echo "║   Email: coordinator@smartdelivery.lk                 ║\n";
        echo "║   Password: coordinator123                            ║\n";
        echo "╠════════════════════════════════════════════════════════╣\n";
        echo "║   Viewer:                                             ║\n";
        echo "║   Email: viewer@smartdelivery.lk                      ║\n";
        echo "║   Password: viewer123                                 ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n";
        echo "\n";

        // Verification
        echo "VERIFICATION:\n";
        echo "═══════════════════════════════════════\n";
        echo "Total Users: " . User::count() . "\n";
        echo "Total Roles: " . \Spatie\Permission\Models\Role::count() . "\n";
        echo "Total Permissions: " . \Spatie\Permission\Models\Permission::count() . "\n";
        echo "\n";

        echo "Admin User Check:\n";
        $adminUser = User::where('email', 'admin@smartdelivery.lk')->first();
        echo "  Name: {$adminUser->name}\n";
        echo "  Email: {$adminUser->email}\n";
        echo "  Role: " . $adminUser->roles->pluck('name')->first() . "\n";
        echo "  Permissions: {$adminUser->getAllPermissions()->count()}\n";
        echo "  Can edit roles: " . ($adminUser->can('edit roles') ? 'YES ✓' : 'NO ✗') . "\n";
        echo "  Can delete users: " . ($adminUser->can('delete users') ? 'YES ✓' : 'NO ✗') . "\n";
        echo "\n";

        echo "✅ ALL DONE! You can now login!\n\n";
    }
}
