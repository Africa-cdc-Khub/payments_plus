<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Package;
use App\Models\Payment;
use App\Models\RegistrationParticipant;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Redirect executive users to participants page
        if (Auth::guard('admin')->check() && (Auth::guard('admin')->user()->role === 'executive' || Auth::guard('admin')->user()->role === 'host')) {
            return redirect()->route('participants.index');
        }
        else if (Auth::guard('admin')->user()->role === 'travels') {
            return redirect()->route('approved-delegates.index');
        }

        $delegatePackageId = config('app.delegate_package_id');

        // Exclude voided registrations from all counts
        $activeRegistrations = Registration::whereNull('voided_at');

        // Total participants count
        // For individual registrations: count the registrant (1 person)
        // For group registrations: count registrant + all registration_participants
        $totalParticipants = $activeRegistrations->clone()->get()->sum(function($registration) {
            if ($registration->registration_type === 'individual') {
                return 1; // Just the registrant
            } else {
                // Registrant + all additional participants
                return 1 + $registration->participants()->count();
            }
        });

        // Paid participants (same logic)
        $paidParticipants = $activeRegistrations->clone()
            ->where('payment_status', 'completed')
            ->get()
            ->sum(function($registration) {
                if ($registration->registration_type === 'individual') {
                    return 1;
                } else {
                    return 1 + $registration->participants()->count();
                }
            });

        // Pending payments - EXCLUDE delegates package
        $pendingPaymentsCount = $activeRegistrations->clone()
            ->where('payment_status', 'pending')
            ->where('package_id', '!=', $delegatePackageId)
            ->get()
            ->sum(function($registration) {
                if ($registration->registration_type === 'individual') {
                    return 1;
                } else {
                    return 1 + $registration->participants()->count();
                }
            });

        // Invoice revenue calculations
        $paidInvoicesRevenue = Invoice::where('status', 'paid')->sum('amount');
        $pendingInvoicesRevenue = Invoice::where('status', 'pending')->sum('amount');

        // Delegates count (all statuses)
        $delegatesStats = [
            'total' => $activeRegistrations->clone()
                ->where('package_id', $delegatePackageId)
                ->count(),
            'approved' => $activeRegistrations->clone()
                ->where('package_id', $delegatePackageId)
                ->where('status', 'approved')
                ->count(),
            'pending' => $activeRegistrations->clone()
                ->where('package_id', $delegatePackageId)
                ->where('status', 'pending')
                ->count(),
            'rejected' => $activeRegistrations->clone()
                ->where('package_id', $delegatePackageId)
                ->where('status', 'rejected')
                ->count(),
        ];

        $stats = [
            'total_participants' => $totalParticipants,
            'paid_participants' => $paidParticipants,
            'pending_payments' => $pendingPaymentsCount,
            'total_revenue' => $activeRegistrations->clone()
                ->where('payment_status', 'completed')
                ->sum('payment_amount') + $paidInvoicesRevenue,
            'pending_invoices_revenue' => $pendingInvoicesRevenue,
            'paid_invoices_revenue' => $paidInvoicesRevenue,
            'delegates' => $delegatesStats,
        ];

        $recent_registrations = Registration::with(['user', 'package', 'participants'])
            ->whereNull('voided_at')
            ->latest('created_at')
            ->take(10)
            ->get();

        $recent_payments = Registration::with(['user', 'package', 'participants'])
            ->whereNull('voided_at')
            ->where('payment_status', 'completed')
            ->latest('payment_completed_at')
            ->take(10)
            ->get();

        return view('dashboard.index', compact('stats', 'recent_registrations', 'recent_payments'));
    }
}

