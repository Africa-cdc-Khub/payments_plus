<?php

namespace App\Http\Controllers;

use App\Jobs\SendAdminCredentials;
use App\Jobs\SendAdminPasswordReset;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            'full_name' => ['required', 'string', 'max:200'],
            'role' => ['required', 'in:admin,secretariat,finance,executive,travels,hosts'],
            'is_active' => ['boolean'],
        ]);

        // Generate a random secure password
        $plainPassword = Str::random(12) . rand(10, 99) . Str::upper(Str::random(2));
        
        $validated['password'] = Hash::make($plainPassword);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        

        $admin = Admin::create($validated);

        // Send credentials email
        try {
            SendAdminCredentials::dispatch($admin->toArray(), $plainPassword);
            Log::info("Admin credentials email queued for {$admin->email}");
            
            return redirect()
                ->route('admins.index')
                ->with('success', "Admin '{$admin->full_name}' created successfully. Login credentials have been sent to {$admin->email}");
        } catch (\Exception $e) {
            Log::error("Failed to queue credentials email for admin {$admin->id}: " . $e->getMessage());
            
            return redirect()
                ->route('admins.index')
                ->with('warning', "Admin '{$admin->full_name}' created successfully, but failed to send credentials email. Please reset their password manually.");
        }
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
            'role' => ['required', 'in:admin,secretariat,finance,executive,travels,hosts'],
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

    /**
     * Reset admin password
     */
    public function resetPassword(Admin $admin)
    {
        $this->authorize('manage-admins');
        
        // Prevent resetting your own password this way
        if ($admin->id === auth('admin')->id()) {
            return redirect()
                ->route('admins.index')
                ->with('error', 'You cannot reset your own password this way. Please use the profile settings.');
        }

        // Generate a new random secure password
        $newPassword = Str::random(12) . rand(10, 99) . Str::upper(Str::random(2));
        
        // Update password
        $admin->update([
            'password' => Hash::make($newPassword)
        ]);

        // Send password reset email
        try {
            SendAdminPasswordReset::dispatch($admin->toArray(), $newPassword);
            Log::info("Password reset email queued for admin {$admin->email}");
            
            return redirect()
                ->route('admins.index')
                ->with('success', "Password reset for '{$admin->full_name}'. New password has been sent to {$admin->email}");
        } catch (\Exception $e) {
            Log::error("Failed to queue password reset email for admin {$admin->id}: " . $e->getMessage());
            
            return redirect()
                ->route('admins.index')
                ->with('warning', "Password reset for '{$admin->full_name}', but failed to send email. Please contact them manually.");
        }
    }

    /**
     * Show the change password form for the current admin
     */
    public function showChangePasswordForm()
    {
        return view('admins.change-password');
    }

    /**
     * Change password for the current admin
     */
    public function changePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        // Update password
        $admin->update([
            'password' => Hash::make($request->password)
        ]);

        Log::info("Admin {$admin->username} changed their password");

        return redirect()
            ->route('change-password')
            ->with('success', 'Password changed successfully.');
    }
}

