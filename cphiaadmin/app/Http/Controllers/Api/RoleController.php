<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Get a specific role with permissions.
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'success' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
            ],
            'permissions' => $role->permissions->map(function($perm) {
                return ['id' => $perm->id, 'name' => $perm->name];
            }),
        ]);
    }

    /**
     * Store a new role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully!',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Update a role.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Don't allow editing system roles names
        $systemRoles = ['super_admin', 'admin', 'finance_team', 'visa_team', 'ticketing_team'];
        
        if (in_array($role->name, $systemRoles)) {
            // Allow permissions update but not name change
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role permissions updated successfully!',
                'role' => $role->fresh()->load('permissions'),
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully!',
            'role' => $role->fresh()->load('permissions'),
        ]);
    }

    /**
     * Delete a role.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Don't allow deleting system roles
        $systemRoles = ['super_admin', 'admin', 'finance_team', 'visa_team', 'ticketing_team'];
        
        if (in_array($role->name, $systemRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'System roles cannot be deleted!',
            ], 403);
        }

        // Check if role is assigned to any users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role that is assigned to users!',
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully!',
        ]);
    }
}

