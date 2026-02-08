<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating admin user...');

        // Check if admin user already exists
        $adminEmail = 'admin@smartdelivery.com';
        $existingAdmin = User::where('email', $adminEmail)->first();

        if ($existingAdmin) {
            $this->command->warn('⚠️  Admin user already exists!');
            $this->command->info('Email: ' . $existingAdmin->email);
            return;
        }

        // Create admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => $adminEmail,
            'password' => Hash::make('password123'),
            'company_id' => null, // Admin is not tied to a specific company
            'phone_number' => '+94771234567',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $admin->assignRole('admin');

        $this->command->info('');
        $this->command->info('✅ Admin user created successfully!');
        $this->command->info('');
        $this->command->info('📧 Email: ' . $admin->email);
        $this->command->info('🔑 Password: password123');
        $this->command->info('👤 Name: ' . $admin->name);
        $this->command->info('🎭 Role: admin');
        $this->command->info('');
        $this->command->warn('⚠️  Please change the password after first login!');
    }
}
