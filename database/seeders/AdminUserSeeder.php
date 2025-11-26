<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates the default admin user for the Smart Delivery System.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@smartdelivery.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $admin->assignRole('admin');

        $this->command->info('✓ Admin user created successfully!');
        $this->command->newLine();
        $this->command->info('Login Credentials:');
        $this->command->info('  Email: admin@smartdelivery.com');
        $this->command->info('  Password: password123');
        $this->command->newLine();
        $this->command->warn('⚠ Remember to change the password after first login!');
    }
}
