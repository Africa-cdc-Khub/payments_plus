<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Payment;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        $admin = auth()->user();

        // Get statistics based on role
        $stats = $this->getDashboardStats($admin);

        // Get recent activity
        $recentRegistrations = Registration::with(['user', 'package'])
            ->latest()
            ->limit(5)
            ->get();

        $recentPayments = Payment::with(['registration.user'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRegistrations', 'recentPayments', 'admin'));
    }

    /**
     * Get dashboard statistics based on admin role.
     */
    private function getDashboardStats($admin)
    {
        $stats = [];

        // Total registrations
        $stats['total_registrations'] = Registration::count();
        $stats['individual_registrations'] = Registration::where('registration_type', 'individual')->count();
        $stats['side_event_registrations'] = Registration::where('registration_type', 'side_event')->count();
        $stats['exhibition_registrations'] = Registration::where('registration_type', 'exhibition')->count();

        // Payment statistics (for all except visa/ticketing team)
        if ($admin->hasAnyRole(['super_admin', 'admin', 'finance_team'])) {
            $stats['total_revenue'] = Payment::where('payment_status', 'completed')->sum('amount');
            $stats['pending_payments'] = Payment::where('payment_status', 'pending')->count();
            $stats['completed_payments'] = Payment::where('payment_status', 'completed')->count();
            $stats['failed_payments'] = Payment::where('payment_status', 'failed')->count();
        }

        // User statistics
        $stats['total_users'] = User::count();

        // Visa statistics (for visa team and admins)
        if ($admin->hasAnyRole(['super_admin', 'admin', 'visa_team'])) {
            $stats['total_visa_required'] = User::where('requires_visa', true)->count();
            $stats['visa_pending'] = User::where('requires_visa', true)
                ->whereNull('passport_file')
                ->count();
        }

        // Attendance statistics (for ticketing team and admins)
        if ($admin->hasAnyRole(['super_admin', 'admin', 'ticketing_team'])) {
            $stats['total_present'] = User::where('attendance_status', 'present')->count();
            $stats['total_absent'] = User::where('attendance_status', 'absent')->count();
            $stats['attendance_pending'] = User::where('attendance_status', 'pending')->count();
        }

        // Nationality breakdown
        $africanCountries = $this->getAfricanCountries();
        $stats['african_nationals'] = User::whereIn('country', $africanCountries)->count();
        $stats['non_african_nationals'] = User::whereNotIn('country', $africanCountries)
            ->orWhereNull('country')
            ->count();

        // Registration by type (last 7 days)
        $stats['registration_trend'] = Registration::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Payment by status
        if ($admin->hasAnyRole(['super_admin', 'admin', 'finance_team'])) {
            $stats['payment_by_status'] = Payment::select(
                    'payment_status',
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('payment_status')
                ->get()
                ->pluck('count', 'payment_status');
        }

        return $stats;
    }

    /**
     * Get list of African countries.
     */
    private function getAfricanCountries()
    {
        return [
            'Algeria', 'Angola', 'Benin', 'Botswana', 'Burkina Faso', 'Burundi',
            'Cameroon', 'Cape Verde', 'Central African Republic', 'Chad', 'Comoros',
            'Congo', 'Democratic Republic of Congo', 'Djibouti', 'Egypt',
            'Equatorial Guinea', 'Eritrea', 'Eswatini', 'Ethiopia', 'Gabon',
            'Gambia', 'Ghana', 'Guinea', 'Guinea-Bissau', 'Ivory Coast', 'Kenya',
            'Lesotho', 'Liberia', 'Libya', 'Madagascar', 'Malawi', 'Mali',
            'Mauritania', 'Mauritius', 'Morocco', 'Mozambique', 'Namibia', 'Niger',
            'Nigeria', 'Rwanda', 'Sao Tome and Principe', 'Senegal', 'Seychelles',
            'Sierra Leone', 'Somalia', 'South Africa', 'South Sudan', 'Sudan',
            'Tanzania', 'Togo', 'Tunisia', 'Uganda', 'Zambia', 'Zimbabwe'
        ];
    }

    /**
     * Get dashboard data as JSON (for AJAX requests).
     */
    public function stats()
    {
        $admin = auth()->user();
        $stats = $this->getDashboardStats($admin);

        return response()->json($stats);
    }
}
