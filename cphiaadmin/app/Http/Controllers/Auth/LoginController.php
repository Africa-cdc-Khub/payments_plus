<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find admin by username
        $admin = Admin::where('username', $request->username)
                     ->where('is_active', true)
                     ->first();

        // Check if admin exists and password is correct
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Log the admin in
        Auth::guard('web')->login($admin, $request->filled('remember'));

        // Update last login
        $admin->update(['last_login' => now()]);

        // Create Sanctum token for API access
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Store token in session for API calls
        session(['api_token' => $token]);

        // Regenerate session
        $request->session()->regenerate();

        // Redirect based on role
        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        // Revoke all tokens
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->user()->tokens()->delete();
        }

        // Logout
        Auth::guard('web')->logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
