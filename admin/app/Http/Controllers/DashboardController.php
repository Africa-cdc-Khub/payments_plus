<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Redirect executive users to participants page
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->role === 'executive') {
            return redirect()->route('participants.index');
        }

        $stats = [
            'total_registrations' => Registration::count(),
            'paid_registrations' => Registration::where('payment_status', 'completed')->count(),
            'pending_registrations' => Registration::where('payment_status', 'pending')->count(),
            'total_revenue' => Registration::where('payment_status', 'completed')->sum('payment_amount'),
            'active_packages' => Package::where('is_active', true)->count(),
        ];

        $recent_registrations = Registration::with(['user', 'package'])
            ->latest('created_at')
            ->take(10)
            ->get();

        $recent_payments = Registration::with(['user', 'package'])
            ->where('payment_status', 'completed')
            ->latest('payment_completed_at')
            ->take(10)
            ->get();

        return view('dashboard.index', compact('stats', 'recent_registrations', 'recent_payments'));
    }
}

