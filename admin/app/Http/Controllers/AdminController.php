<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $this->authorize('manage-admins');
        
        $admins = Admin::latest('created_at')->paginate(20);
        
        return view('admins.index', compact('admins'));
    }

    public function create()
    {
        $this->authorize('manage-admins');
        
        return view('admins.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage-admins');
        
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:admins,username'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'full_name' => ['required', 'string', 'max:200'],
            'role' => ['required', 'in:admin,secretariat,finance,executive'],
            'is_active' => ['boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        Admin::create($validated);

        return redirect()->route('admins.index')->with('success', 'Admin created successfully.');
    }

    public function edit(Admin $admin)
    {
        $this->authorize('manage-admins');
        
        return view('admins.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        $this->authorize('manage-admins');
        
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:admins,username,' . $admin->id],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,' . $admin->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'full_name' => ['required', 'string', 'max:200'],
            'role' => ['required', 'in:admin,secretariat,finance,executive'],
            'is_active' => ['boolean'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $admin->update($validated);

        return redirect()->route('admins.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(Admin $admin)
    {
        $this->authorize('manage-admins');
        
        // Prevent deleting yourself
        if ($admin->id === auth('admin')->id()) {
            return redirect()->route('admins.index')->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->route('admins.index')->with('success', 'Admin deleted successfully.');
    }
}

