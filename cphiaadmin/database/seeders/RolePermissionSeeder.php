<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Dashboard Permissions
            'view_dashboard',
            'view_statistics',
            'view_charts',

            // Registration Permissions
            'view_registrations',
            'view_registration_details',
            'edit_registrations',
            'delete_registrations',
            'export_registrations',
            'view_participants',
            'export_participants',

            // Payment Permissions
            'view_payments',
            'view_payment_details',
            'update_payment_status',
            'verify_payments',
            'export_payments',
            'view_financial_reports',
            'reconcile_payments',

            // User Permissions
            'view_users',
            'view_user_details',
            'edit_users',
            'delete_users',
            'export_users',

            // Visa Permissions
            'view_visa_applications',
            'update_visa_status',
            'view_visa_documents',
            'upload_visa_documents',
            'export_visa_applications',
            'view_visa_reports',

            // Attendance/Ticketing Permissions
            'view_attendance',
            'checkin_attendee',
            'checkout_attendee',
            'verify_attendance',
            'print_badges',
            'export_attendance',
            'view_attendance_reports',

            // Package Permissions
            'view_packages',
            'create_packages',
            'edit_packages',
            'delete_packages',

            // Report Permissions
            'generate_reports',
            'view_all_reports',
            'export_reports',
            'schedule_reports',

            // Admin User Management Permissions
            'view_admins',
            'create_admins',
            'edit_admins',
            'delete_admins',
            'assign_roles',
            'assign_permissions',

            // System Permissions
            'view_system_settings',
            'edit_system_settings',
            'view_activity_logs',
            'view_audit_trail',
            'manage_backups',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions

        // 1. SUPER ADMIN - Full Access
        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. ADMIN - General Admin Access
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            'view_dashboard',
            'view_statistics',
            'view_charts',
            'view_registrations',
            'view_registration_details',
            'export_registrations',
            'view_participants',
            'export_participants',
            'view_payments',
            'view_payment_details',
            'export_payments',
            'view_users',
            'view_user_details',
            'export_users',
            'view_packages',
            'generate_reports',
            'view_all_reports',
            'export_reports',
            'view_activity_logs',
        ]);

        // 3. FINANCE TEAM - Payment & Financial Management
        $financeTeam = Role::create(['name' => 'finance_team', 'guard_name' => 'web']);
        $financeTeam->givePermissionTo([
            'view_dashboard',
            'view_statistics',
            'view_charts',
            'view_payments',
            'view_payment_details',
            'update_payment_status',
            'verify_payments',
            'export_payments',
            'view_financial_reports',
            'reconcile_payments',
            'view_registrations',
            'view_registration_details',
            'export_registrations',
            'generate_reports',
            'export_reports',
            'view_activity_logs',
        ]);

        // 4. VISA TEAM - Visa Application Management
        $visaTeam = Role::create(['name' => 'visa_team', 'guard_name' => 'web']);
        $visaTeam->givePermissionTo([
            'view_dashboard',
            'view_statistics',
            'view_users',
            'view_user_details',
            'export_users',
            'view_visa_applications',
            'update_visa_status',
            'view_visa_documents',
            'upload_visa_documents',
            'export_visa_applications',
            'view_visa_reports',
            'view_registrations',
            'view_registration_details',
            'generate_reports',
            'export_reports',
        ]);

        // 5. TICKETING TEAM - Attendance & Badge Management
        $ticketingTeam = Role::create(['name' => 'ticketing_team', 'guard_name' => 'web']);
        $ticketingTeam->givePermissionTo([
            'view_dashboard',
            'view_statistics',
            'view_users',
            'view_user_details',
            'view_attendance',
            'checkin_attendee',
            'checkout_attendee',
            'verify_attendance',
            'print_badges',
            'export_attendance',
            'view_attendance_reports',
            'view_registrations',
            'view_registration_details',
            'export_registrations',
            'generate_reports',
            'export_reports',
        ]);

        $this->command->info('✅ Roles and permissions created successfully!');
        $this->command->info('');
        $this->command->info('Created Roles:');
        $this->command->info('1. super_admin - Full system access');
        $this->command->info('2. admin - General admin access');
        $this->command->info('3. finance_team - Payment management');
        $this->command->info('4. visa_team - Visa applications');
        $this->command->info('5. ticketing_team - Attendance tracking');
        $this->command->info('');
        $this->command->info('Total Permissions: ' . count($permissions));
    }
}
