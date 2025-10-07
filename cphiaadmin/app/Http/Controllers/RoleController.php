<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display roles management page.
     */
    public function index()
    {
        $roles = Role::with('permissions')->where('guard_name', 'web')->get();
        $permissions = Permission::where('guard_name', 'web')->get();
        
        return view('admin.roles.index', compact('roles', 'permissions'));
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

        // Don't allow editing system roles
        if (in_array($role->name, ['super_admin', 'admin', 'finance_team', 'visa_team', 'ticketing_team'])) {
            return response()->json([
                'success' => false,
                'message' => 'System roles cannot be edited!',
            ], 403);
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
        if (in_array($role->name, ['super_admin', 'admin', 'finance_team', 'visa_team', 'ticketing_team'])) {
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

