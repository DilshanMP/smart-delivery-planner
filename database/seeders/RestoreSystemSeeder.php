<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Vehicle;
use App\Models\Driver;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestoreSystemSeeder extends Seeder
{
    /**
     * Run the database seeds - Complete system restoration
     * FULLY DYNAMIC - Adapts to any table structure!
     */
    public function run(): void
    {
        echo "🔄 Starting system restoration...\n\n";

        // Step 1: Create Permissions
        $this->createPermissions();

        // Step 2: Create Roles
        $this->createRoles();

        // Step 3: Assign Permissions to Roles
        $this->assignPermissionsToRoles();

        // Step 4: Create/Update Admin User
        $this->createAdminUser();

        // Step 5: Create Demo Companies
        $this->createDemoCompanies();

        // Step 6: Create Demo Warehouses
        $this->createDemoWarehouses();

        // Step 7: Create Demo Vehicles
        $this->createDemoVehicles();

        // Step 8: Create Demo Drivers
        $this->createDemoDrivers();

        echo "\n✅ System restoration complete!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📊 Summary:\n";
        echo "   ✓ Permissions: " . Permission::count() . "\n";
        echo "   ✓ Roles: " . Role::count() . "\n";
        echo "   ✓ Users: " . User::count() . "\n";
        echo "   ✓ Companies: " . Company::count() . "\n";
        echo "   ✓ Warehouses: " . Warehouse::count() . "\n";
        echo "   ✓ Vehicles: " . Vehicle::count() . "\n";
        echo "   ✓ Drivers: " . Driver::count() . "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🎉 Login with: admin@akvora.lk / Admin@123\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }

    /**
     * Helper: Filter data to only include existing columns
     */
    private function filterColumns($table, $data)
    {
        $columns = Schema::getColumnListing($table);
        return array_filter($data, function($key) use ($columns) {
            return in_array($key, $columns);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Create all permissions
     */
    private function createPermissions(): void
    {
        echo "1️⃣ Creating permissions...\n";

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view companies', 'create companies', 'edit companies', 'delete companies',
            'view warehouses', 'create warehouses', 'edit warehouses', 'delete warehouses',
            'view vehicles', 'create vehicles', 'edit vehicles', 'delete vehicles',
            'view drivers', 'create drivers', 'edit drivers', 'delete drivers',
            'view routes', 'create routes', 'edit routes', 'delete routes',
            'view stops', 'create stops', 'edit stops', 'delete stops',
            'view reports', 'export reports',
            'manage settings',
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        echo "   ✓ Created " . count($permissions) . " permissions\n";
    }

    /**
     * Create roles
     */
    private function createRoles(): void
    {
        echo "2️⃣ Creating roles...\n";

        $roles = ['Admin', 'Manager', 'Coordinator', 'Viewer'];

        foreach ($roles as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        echo "   ✓ Created " . count($roles) . " roles\n";
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(): void
    {
        echo "3️⃣ Assigning permissions to roles...\n";

        // Admin - All permissions
        $adminRole = Role::findByName('Admin');
        $adminRole->syncPermissions(Permission::all());
        echo "   ✓ Admin: All permissions\n";

        // Manager - Almost all except user/role deletion
        $managerRole = Role::findByName('Manager');
        $managerPermissions = Permission::whereNotIn('name', ['delete users', 'delete roles'])->pluck('name');
        $managerRole->syncPermissions($managerPermissions);
        echo "   ✓ Manager: " . $managerPermissions->count() . " permissions\n";

        // Coordinator - Operational permissions
        $coordinatorRole = Role::findByName('Coordinator');
        $coordinatorPermissions = [
            'view dashboard', 'view users', 'view roles', 'view companies', 'view warehouses',
            'view vehicles', 'view drivers', 'view routes', 'create routes', 'edit routes',
            'view stops', 'create stops', 'edit stops', 'view reports',
        ];
        $coordinatorRole->syncPermissions($coordinatorPermissions);
        echo "   ✓ Coordinator: " . count($coordinatorPermissions) . " permissions\n";

        // Viewer - Read-only permissions
        $viewerRole = Role::findByName('Viewer');
        $viewerPermissions = [
            'view dashboard', 'view users', 'view roles', 'view companies', 'view warehouses',
            'view vehicles', 'view drivers', 'view routes', 'view stops', 'view reports',
        ];
        $viewerRole->syncPermissions($viewerPermissions);
        echo "   ✓ Viewer: " . count($viewerPermissions) . " permissions\n";
    }

    /**
     * Create or update admin user
     */
    private function createAdminUser(): void
    {
        echo "4️⃣ Creating admin user...\n";

        $admin = User::updateOrCreate(
            ['email' => 'admin@akvora.lk'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['Admin']);

        echo "   ✓ Admin user: {$admin->email}\n";
        echo "   ✓ Password: Admin@123\n";
    }

    /**
     * Create demo companies - FULLY DYNAMIC!
     */
    private function createDemoCompanies(): void
    {
        echo "5️⃣ Creating demo companies...\n";

        $companiesData = [
            [
                'code' => 'AKV001',
                'name' => 'Akvora International (Pvt) Ltd',
                'registration_number' => 'PV12345',
                'address' => 'No. 123, Galle Road, Colombo 03',
                'phone' => '+94 11 234 5678',
                'email' => 'info@akvora.lk',
                'is_active' => true,
            ],
            [
                'code' => 'EXP001',
                'name' => 'Express Logistics Lanka',
                'registration_number' => 'PV67890',
                'address' => 'No. 456, Kandy Road, Kadawatha',
                'phone' => '+94 11 345 6789',
                'email' => 'contact@expresslogistics.lk',
                'is_active' => true,
            ],
            [
                'code' => 'SWF001',
                'name' => 'Swift Delivery Services',
                'registration_number' => 'PV11111',
                'address' => 'No. 789, Main Street, Galle',
                'phone' => '+94 91 222 3333',
                'email' => 'info@swiftdelivery.lk',
                'is_active' => true,
            ],
        ];

        foreach ($companiesData as $companyData) {
            // Filter to only existing columns
            $filteredData = $this->filterColumns('companies', $companyData);

            // Use name as unique identifier
            Company::firstOrCreate(
                ['name' => $companyData['name']],
                $filteredData
            );
        }

        echo "   ✓ Created " . count($companiesData) . " companies\n";
    }

    /**
     * Create demo warehouses - FULLY DYNAMIC!
     */
    private function createDemoWarehouses(): void
    {
        echo "6️⃣ Creating demo warehouses...\n";

        $company = Company::first();

        if (!$company) {
            echo "   ⚠️ No company found, skipping warehouses\n";
            return;
        }

        $warehousesData = [
            [
                'company_id' => $company->id,
                'name' => 'Colombo Main Warehouse',
                'code' => 'WH-CMB-001',
                'address' => 'No. 100, Industrial Zone, Colombo 15',
                'city' => 'Colombo',
                'latitude' => 6.9271,
                'longitude' => 79.8612,
                'capacity' => 5000,
                'current_stock' => 3500,
                'contact_person' => 'Nimal Perera',
                'phone' => '+94 11 456 7890',
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'Kandy Distribution Center',
                'code' => 'WH-KDY-001',
                'address' => 'No. 50, Peradeniya Road, Kandy',
                'city' => 'Kandy',
                'latitude' => 7.2906,
                'longitude' => 80.6337,
                'capacity' => 3000,
                'current_stock' => 1800,
                'contact_person' => 'Kamal Silva',
                'phone' => '+94 81 222 3333',
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'Galle Coastal Depot',
                'code' => 'WH-GLE-001',
                'address' => 'No. 25, Matara Road, Galle',
                'city' => 'Galle',
                'latitude' => 6.0535,
                'longitude' => 80.2210,
                'capacity' => 2000,
                'current_stock' => 1200,
                'contact_person' => 'Sunil Fernando',
                'phone' => '+94 91 444 5555',
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'Jaffna Northern Hub',
                'code' => 'WH-JAF-001',
                'address' => 'No. 75, Main Street, Jaffna',
                'city' => 'Jaffna',
                'latitude' => 9.6615,
                'longitude' => 80.0255,
                'capacity' => 1500,
                'current_stock' => 800,
                'contact_person' => 'Kumar Rajah',
                'phone' => '+94 21 666 7777',
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'Anuradhapura Storage Facility',
                'code' => 'WH-ANU-001',
                'address' => 'No. 30, Old Town Road, Anuradhapura',
                'city' => 'Anuradhapura',
                'latitude' => 8.3114,
                'longitude' => 80.4037,
                'capacity' => 2500,
                'current_stock' => 1500,
                'contact_person' => 'Chaminda Bandara',
                'phone' => '+94 25 888 9999',
                'is_active' => true,
            ],
        ];

        foreach ($warehousesData as $warehouseData) {
            $filteredData = $this->filterColumns('warehouses', $warehouseData);

            Warehouse::firstOrCreate(
                ['code' => $warehouseData['code']],
                $filteredData
            );
        }

        echo "   ✓ Created " . count($warehousesData) . " warehouses\n";
    }

    /**
     * Create demo vehicles - FULLY DYNAMIC!
     */
    private function createDemoVehicles(): void
    {
        echo "7️⃣ Creating demo vehicles...\n";

        $company = Company::first();

        if (!$company) {
            echo "   ⚠️ No company found, skipping vehicles\n";
            return;
        }

        $vehiclesData = [
            [
                'company_id' => $company->id,
                'registration_number' => 'WP CAB-1111',
                'vehicle_type' => 'lorry',
                'make' => 'TATA',
                'model' => 'Dyna',
                'year' => 2020,
                'capacity_weight' => 2000,
                'capacity_volume' => 15,
                'fuel_type' => 'diesel',
                'fuel_efficiency' => 8.0,
                'condition' => 'good',
                'last_service_date' => now()->subDays(30),
                'next_service_date' => now()->addDays(60),
                'insurance_expiry' => now()->addMonths(6),
                'license_expiry' => now()->addMonths(8),
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'registration_number' => 'WP CAB-2222',
                'vehicle_type' => 'truck',
                'make' => 'Isuzu',
                'model' => 'Elf',
                'year' => 2021,
                'capacity_weight' => 1500,
                'capacity_volume' => 12,
                'fuel_type' => 'diesel',
                'fuel_efficiency' => 10.0,
                'condition' => 'excellent',
                'last_service_date' => now()->subDays(15),
                'next_service_date' => now()->addDays(75),
                'insurance_expiry' => now()->addMonths(10),
                'license_expiry' => now()->addYear(),
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'registration_number' => 'WP CAB-3333',
                'vehicle_type' => 'van',
                'make' => 'Toyota',
                'model' => 'HiAce',
                'year' => 2022,
                'capacity_weight' => 1000,
                'capacity_volume' => 8,
                'fuel_type' => 'petrol',
                'fuel_efficiency' => 12.0,
                'condition' => 'excellent',
                'last_service_date' => now()->subDays(20),
                'next_service_date' => now()->addDays(70),
                'insurance_expiry' => now()->addMonths(9),
                'license_expiry' => now()->addMonths(11),
                'is_active' => true,
            ],
        ];

        foreach ($vehiclesData as $vehicleData) {
            $filteredData = $this->filterColumns('vehicles', $vehicleData);

            Vehicle::firstOrCreate(
                ['registration_number' => $vehicleData['registration_number']],
                $filteredData
            );
        }

        echo "   ✓ Created " . count($vehiclesData) . " vehicles\n";
    }

    /**
     * Create demo drivers - FULLY DYNAMIC!
     */
    private function createDemoDrivers(): void
    {
        echo "8️⃣ Creating demo drivers...\n";

        $company = Company::first();

        if (!$company) {
            echo "   ⚠️ No company found, skipping drivers\n";
            return;
        }

        $driversData = [
            [
                'company_id' => $company->id,
                'name' => 'W.A. Silva',
                'license_number' => 'B1234567',
                'license_type' => 'heavy_vehicle',
                'license_expiry' => now()->addYear(),
                'phone' => '+94 71 111 1111',
                'email' => 'silva@example.com',
                'address' => 'No. 45, Galle Road, Colombo 03',
                'date_of_birth' => now()->subYears(35),
                'experience_years' => 15,
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'R.P. Fernando',
                'license_number' => 'B2345678',
                'license_type' => 'lorry',
                'license_expiry' => now()->addMonths(8),
                'phone' => '+94 71 222 2222',
                'email' => 'fernando@example.com',
                'address' => 'No. 123, Kandy Road, Kadawatha',
                'date_of_birth' => now()->subYears(28),
                'experience_years' => 8,
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'K.L. Perera',
                'license_number' => 'B3456789',
                'license_type' => 'all',
                'license_expiry' => now()->addMonths(6),
                'phone' => '+94 71 333 3333',
                'email' => 'perera@example.com',
                'address' => 'No. 67, Main Street, Galle',
                'date_of_birth' => now()->subYears(42),
                'experience_years' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($driversData as $driverData) {
            $filteredData = $this->filterColumns('drivers', $driverData);

            Driver::firstOrCreate(
                ['license_number' => $driverData['license_number']],
                $filteredData
            );
        }

        echo "   ✓ Created " . count($driversData) . " drivers\n";
    }
}
