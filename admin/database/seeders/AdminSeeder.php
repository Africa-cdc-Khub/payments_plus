<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = DB::table('admins')->where('username', 'adminstrator')->first();

        if ($admin) {
            $this->command->warn('Admin user already exists!');
            $this->command->info('Username: admin');
            $this->command->info('Use existing password from database');
        } else {
            DB::table('admins')->insert([
                'username' => 'adminstrator',
                'email' => 'adminstrator@cphia2025.com',
                'password' => Hash::make('Microinfo@2020'),
                'full_name' => 'System Administrator',
                'role' => 'super_admin',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Username: admin');
            $this->command->info('Password: Admin@2025');
        }
    }
}