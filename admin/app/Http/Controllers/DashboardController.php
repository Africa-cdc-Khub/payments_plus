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
                ->sum('total_amount') + $paidInvoicesRevenue,
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

        // Chart data for delegate categories
        $delegateCategoryStats = $activeRegistrations->clone()
            ->where('package_id', $delegatePackageId)
            ->join('users', 'registrations.user_id', '=', 'users.id')
            ->select('users.delegate_category', DB::raw('count(*) as total'))
            ->groupBy('users.delegate_category')
            ->orderBy('total', 'desc')
            ->get();

        $delegateApprovalStats = $activeRegistrations->clone()
            ->where('package_id', $delegatePackageId)
            ->join('users', 'registrations.user_id', '=', 'users.id')
            ->select('users.delegate_category', 'registrations.status', DB::raw('count(*) as total'))
            ->groupBy('users.delegate_category', 'registrations.status')
            ->orderBy('users.delegate_category')
            ->get();

        // Chart data for participants by continent
        $continentStats = $activeRegistrations->clone()
            ->join('users', 'registrations.user_id', '=', 'users.id')
            ->select('users.country', DB::raw('count(*) as total'))
            ->groupBy('users.country')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function($item) {
                // Map countries to continents
                $continent = $this->getContinentFromCountry($item->country);
                return [
                    'continent' => $continent,
                    'total' => $item->total
                ];
            })
            ->groupBy('continent')
            ->map(function($items) {
                return $items->sum('total');
            });

        // Chart data for participants by nationality
        $nationalityStats = $activeRegistrations->clone()
            ->join('users', 'registrations.user_id', '=', 'users.id')
            ->select('users.nationality', DB::raw('count(*) as total'))
            ->whereNotNull('users.nationality')
            ->where('users.nationality', '!=', '')
            ->groupBy('users.nationality')
            ->orderBy('total', 'desc')
            ->take(10) // Top 10 nationalities
            ->get();

        return view('dashboard.index', compact(
            'stats', 
            'recent_registrations', 
            'recent_payments',
            'delegateCategoryStats',
            'delegateApprovalStats',
            'continentStats',
            'nationalityStats'
        ));
    }

    private function getContinentFromCountry($country)
    {
        $continentMap = [
            // Africa
            'Algeria' => 'Africa', 'Angola' => 'Africa', 'Benin' => 'Africa', 'Botswana' => 'Africa',
            'Burkina Faso' => 'Africa', 'Burundi' => 'Africa', 'Cameroon' => 'Africa', 'Cape Verde' => 'Africa',
            'Central African Republic' => 'Africa', 'Chad' => 'Africa', 'Comoros' => 'Africa', 'Congo' => 'Africa',
            'Congo, Democratic Republic' => 'Africa', 'Côte d\'Ivoire' => 'Africa', 'Djibouti' => 'Africa',
            'Egypt' => 'Africa', 'Equatorial Guinea' => 'Africa', 'Eritrea' => 'Africa', 'Eswatini' => 'Africa',
            'Ethiopia' => 'Africa', 'Gabon' => 'Africa', 'Gambia' => 'Africa', 'Ghana' => 'Africa',
            'Guinea' => 'Africa', 'Guinea-Bissau' => 'Africa', 'Kenya' => 'Africa', 'Lesotho' => 'Africa',
            'Liberia' => 'Africa', 'Libya' => 'Africa', 'Madagascar' => 'Africa', 'Malawi' => 'Africa',
            'Mali' => 'Africa', 'Mauritania' => 'Africa', 'Mauritius' => 'Africa', 'Morocco' => 'Africa',
            'Mozambique' => 'Africa', 'Namibia' => 'Africa', 'Niger' => 'Africa', 'Nigeria' => 'Africa',
            'Rwanda' => 'Africa', 'São Tomé and Príncipe' => 'Africa', 'Senegal' => 'Africa', 'Seychelles' => 'Africa',
            'Sierra Leone' => 'Africa', 'Somalia' => 'Africa', 'South Africa' => 'Africa', 'South Sudan' => 'Africa',
            'Sudan' => 'Africa', 'Tanzania' => 'Africa', 'Togo' => 'Africa', 'Tunisia' => 'Africa',
            'Uganda' => 'Africa', 'Zambia' => 'Africa', 'Zimbabwe' => 'Africa',
            
            // Asia
            'Afghanistan' => 'Asia', 'Bangladesh' => 'Asia', 'China' => 'Asia', 'India' => 'Asia',
            'Indonesia' => 'Asia', 'Japan' => 'Asia', 'Malaysia' => 'Asia', 'Pakistan' => 'Asia',
            'Philippines' => 'Asia', 'Singapore' => 'Asia', 'South Korea' => 'Asia', 'Thailand' => 'Asia',
            'Vietnam' => 'Asia',
            
            // Europe
            'France' => 'Europe', 'Germany' => 'Europe', 'Italy' => 'Europe', 'Netherlands' => 'Europe',
            'Spain' => 'Europe', 'Sweden' => 'Europe', 'Switzerland' => 'Europe', 'United Kingdom' => 'Europe',
            
            // North America
            'Canada' => 'North America', 'Mexico' => 'North America', 'United States' => 'North America',
            
            // South America
            'Argentina' => 'South America', 'Brazil' => 'South America', 'Chile' => 'South America',
            'Colombia' => 'South America', 'Peru' => 'South America',
            
            // Oceania
            'Australia' => 'Oceania', 'New Zealand' => 'Oceania'
        ];

        return $continentMap[$country] ?? 'Other';
    }
}

