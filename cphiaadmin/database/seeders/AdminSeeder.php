<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admins table already has records
        if (Admin::count() > 0) {
            $this->command->info('⚠️  Admins already exist. Skipping seeding...');
            return;
        }

        // Create Super Admin
        $superAdmin = Admin::create([
            'username' => 'superadmin',
            'email' => 'superadmin@cphia2025.org',
            'password' => Hash::make('Admin@2025'),
            'full_name' => 'Super Administrator',
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');
        $this->command->info('✅ Super Admin created: superadmin / Admin@2025');

        // Create Regular Admin
        $admin = Admin::create([
            'username' => 'admin',
            'email' => 'admin@cphia2025.org',
            'password' => Hash::make('Admin@2025'),
            'full_name' => 'Administrator',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');
        $this->command->info('✅ Admin created: admin / Admin@2025');

        // Create Finance Team User
        $finance = Admin::create([
            'username' => 'finance',
            'email' => 'finance@cphia2025.org',
            'password' => Hash::make('Finance@2025'),
            'full_name' => 'Finance Manager',
            'is_active' => true,
        ]);
        $finance->assignRole('finance_team');
        $this->command->info('✅ Finance Team created: finance / Finance@2025');

        // Create Visa Team User
        $visa = Admin::create([
            'username' => 'visa',
            'email' => 'visa@cphia2025.org',
            'password' => Hash::make('Visa@2025'),
            'full_name' => 'Visa Manager',
            'is_active' => true,
        ]);
        $visa->assignRole('visa_team');
        $this->command->info('✅ Visa Team created: visa / Visa@2025');

        // Create Ticketing Team User
        $ticketing = Admin::create([
            'username' => 'ticketing',
            'email' => 'ticketing@cphia2025.org',
            'password' => Hash::make('Ticketing@2025'),
            'full_name' => 'Ticketing Manager',
            'is_active' => true,
        ]);
        $ticketing->assignRole('ticketing_team');
        $this->command->info('✅ Ticketing Team created: ticketing / Ticketing@2025');

        $this->command->info('');
        $this->command->info('🎉 All admin users created successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('==================');
        $this->command->info('Super Admin: superadmin / Admin@2025');
        $this->command->info('Admin:       admin / Admin@2025');
        $this->command->info('Finance:     finance / Finance@2025');
        $this->command->info('Visa:        visa / Visa@2025');
        $this->command->info('Ticketing:   ticketing / Ticketing@2025');
        $this->command->info('');
        $this->command->warn('⚠️  IMPORTANT: Please change these passwords after first login!');
    }
}
