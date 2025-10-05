<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\Registration;
use App\Models\Payment;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SettingsController extends Controller
{
    /**
     * Get all roles with permission counts.
     */
    public function getRoles()
    {
        $roles = Role::where('guard_name', 'web')
            ->withCount('permissions')
            ->get()
            ->map(function($role) {
                return [
                    'name' => $role->name,
                    'display_name' => ucfirst(str_replace('_', ' ', $role->name)),
                    'permissions_count' => $role->permissions_count,
                    'description' => $this->getRoleDescription($role->name),
                ];
            });

        return response()->json(['roles' => $roles]);
    }

    /**
     * Get permissions for a specific role.
     */
    public function getRolePermissions($roleName)
    {
        $role = Role::findByName($roleName, 'web');

        $permissions = $role->permissions->map(function($permission) {
            return [
                'name' => $permission->name,
                'display_name' => ucfirst(str_replace('_', ' ', $permission->name)),
                'category' => $this->getPermissionCategory($permission->name),
            ];
        });

        return response()->json([
            'role' => $role->name,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get all permissions.
     */
    public function getAllPermissions()
    {
        $permissions = Permission::where('guard_name', 'web')
            ->get()
            ->map(function($permission) {
                return [
                    'name' => $permission->name,
                    'display_name' => ucfirst(str_replace('_', ' ', $permission->name)),
                    'category' => $this->getPermissionCategory($permission->name),
                ];
            });

        return response()->json([
            'permissions' => $permissions,
            'total' => $permissions->count(),
        ]);
    }

    /**
     * Get system statistics.
     */
    public function getStats()
    {
        return response()->json([
            'users' => User::count(),
            'registrations' => Registration::count(),
            'payments' => Payment::count(),
            'admins' => Admin::count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
        ]);
    }

    /**
     * Get role description.
     */
    private function getRoleDescription($roleName)
    {
        $descriptions = [
            'super_admin' => 'Full system access with all permissions',
            'admin' => 'General administrative access',
            'finance_team' => 'Payment and financial management',
            'visa_team' => 'Visa application management',
            'ticketing_team' => 'Attendance and ticketing management',
        ];

        return $descriptions[$roleName] ?? 'Custom role';
    }

    /**
     * Get permission category.
     */
    private function getPermissionCategory($permissionName)
    {
        if (str_contains($permissionName, 'dashboard')) return 'Dashboard';
        if (str_contains($permissionName, 'registration')) return 'Registrations';
        if (str_contains($permissionName, 'payment')) return 'Payments';
        if (str_contains($permissionName, 'user') && !str_contains($permissionName, 'admin')) return 'Users';
        if (str_contains($permissionName, 'admin')) return 'Admin Users';
        if (str_contains($permissionName, 'report')) return 'Reports';
        if (str_contains($permissionName, 'setting')) return 'Settings';
        if (str_contains($permissionName, 'package')) return 'Packages';
        if (str_contains($permissionName, 'role')) return 'Roles';
        if (str_contains($permissionName, 'permission')) return 'Permissions';

        return 'Other';
    }
}
