<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Get admins data for DataTables.
     */
    public function index(Request $request)
    {
        $query = Admin::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        // Get all results (not paginated for simplicity)
        $admins = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $admins->map(function($admin) {
                return [
                    'id' => $admin->id,
                    'username' => $admin->username,
                    'email' => $admin->email,
                    'full_name' => $admin->full_name ?? 'N/A',
                    'role' => $admin->getRoleNames()->first() ?? 'N/A',
                    'is_active' => $admin->is_active,
                    'last_login' => $admin->last_login ? $admin->last_login->format('M d, Y H:i') : 'Never',
                    'created_at' => $admin->created_at->format('M d, Y'),
                ];
            }),
        ]);
    }

    /**
     * Get a single admin for editing.
     */
    public function show($id)
    {
        $admin = Admin::with('roles')->findOrFail($id);

        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'email' => $admin->email,
                'full_name' => $admin->full_name,
                'role' => $admin->getRoleNames()->first() ?? null,
                'is_active' => $admin->is_active,
                'last_login' => $admin->last_login ? $admin->last_login->format('M d, Y H:i') : 'Never',
                'created_at' => $admin->created_at->format('M d, Y'),
            ],
        ]);
    }

    /**
     * Store a new admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:100|unique:admins,username',
            'email' => 'required|email|max:255|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'full_name' => 'required|string|max:200',
            'role' => 'required|exists:roles,name',
        ]);

        $admin = Admin::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'is_active' => true,
        ]);

        $admin->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'Admin user created successfully!',
            'admin' => $admin->load('roles'),
        ]);
    }

    /**
     * Update an admin.
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'username' => 'required|string|max:100|unique:admins,username,' . $id,
            'email' => 'required|email|max:255|unique:admins,email,' . $id,
            'full_name' => 'required|string|max:200',
            'role' => 'sometimes|exists:roles,name',
            'is_active' => 'sometimes|boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'username' => $request->username,
            'email' => $request->email,
            'full_name' => $request->full_name,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->is_active;
        }

        $admin->update($data);

        if ($request->filled('role')) {
            $admin->syncRoles([$request->role]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Admin user updated successfully!',
            'admin' => $admin->fresh()->load('roles'),
        ]);
    }

    /**
     * Delete an admin.
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);

        // Don't allow deleting yourself
        if ($admin->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account!',
            ], 403);
        }

        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin user deleted successfully!',
        ]);
    }

    /**
     * Get all roles for dropdown.
     */
    public function roles()
    {
        $roles = Role::where('guard_name', 'web')->get(['id', 'name']);

        return response()->json([
            'roles' => $roles->map(function($role) {
                return [
                    'value' => $role->name,
                    'label' => ucfirst(str_replace('_', ' ', $role->name)),
                ];
            }),
        ]);
    }

    /**
     * Toggle admin active status.
     */
    public function toggleActive($id)
    {
        $admin = Admin::findOrFail($id);

        // Don't allow deactivating yourself
        if ($admin->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account!',
            ], 403);
        }

        $admin->update(['is_active' => !$admin->is_active]);

        return response()->json([
            'success' => true,
            'message' => $admin->is_active ? 'Admin activated successfully!' : 'Admin deactivated successfully!',
            'admin' => $admin->fresh(),
        ]);
    }
}
