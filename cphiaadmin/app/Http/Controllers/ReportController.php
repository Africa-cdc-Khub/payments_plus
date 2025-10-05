<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the reports page.
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Generate registration report.
     */
    public function registrations(Request $request)
    {
        $query = Registration::with(['user', 'package', 'participants']);

        // Apply filters
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('registration_type') && $request->registration_type !== 'all') {
            $query->where('registration_type', $request->registration_type);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        // Statistics
        $stats = [
            'total' => $registrations->count(),
            'individual' => $registrations->where('registration_type', 'individual')->count(),
            'group' => $registrations->where('registration_type', 'group')->count(),
            'completed' => $registrations->where('status', 'completed')->count(),
            'pending' => $registrations->where('status', 'pending')->count(),
            'total_amount' => $registrations->sum('total_amount'),
            'paid_amount' => $registrations->where('payment_status', 'completed')->sum('total_amount'),
        ];

        $data = [
            'registrations' => $registrations,
            'stats' => $stats,
            'filters' => $request->all(),
            'generated_at' => Carbon::now()->format('M d, Y H:i A'),
        ];

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.registrations', $data);
            return $pdf->download('registrations-report-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.registrations', $data);
    }

    /**
     * Generate financial report.
     */
    public function financial(Request $request)
    {
        $query = Payment::with(['registration.user', 'registration.package']);

        // Apply filters
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Statistics
        $stats = [
            'total_transactions' => $payments->count(),
            'completed' => $payments->where('payment_status', 'completed')->count(),
            'pending' => $payments->where('payment_status', 'pending')->count(),
            'failed' => $payments->where('payment_status', 'failed')->count(),
            'total_amount' => $payments->sum('amount'),
            'completed_amount' => $payments->where('payment_status', 'completed')->sum('amount'),
            'pending_amount' => $payments->where('payment_status', 'pending')->sum('amount'),
        ];

        // Group by currency
        $by_currency = $payments->groupBy('currency')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
                'completed' => $group->where('payment_status', 'completed')->sum('amount'),
            ];
        });

        $data = [
            'payments' => $payments,
            'stats' => $stats,
            'by_currency' => $by_currency,
            'filters' => $request->all(),
            'generated_at' => Carbon::now()->format('M d, Y H:i A'),
        ];

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.financial', $data);
            return $pdf->download('financial-report-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.financial', $data);
    }

    /**
     * Generate visa report.
     */
    public function visa(Request $request)
    {
        $query = User::whereHas('registrations');

        // Apply filters
        if ($request->has('requires_visa')) {
            if ($request->requires_visa === 'yes') {
                $query->where('requires_visa', true);
            } elseif ($request->requires_visa === 'no') {
                $query->where('requires_visa', false);
            }
        }

        if ($request->has('nationality') && $request->nationality !== 'all') {
            $query->where('nationality', $request->nationality);
        }

        if ($request->has('has_passport')) {
            if ($request->has_passport === 'yes') {
                $query->whereNotNull('passport_file');
            } elseif ($request->has_passport === 'no') {
                $query->whereNull('passport_file');
            }
        }

        $users = $query->with(['registrations.package'])->orderBy('last_name')->get();

        // Statistics
        $stats = [
            'total_participants' => $users->count(),
            'requires_visa' => $users->where('requires_visa', true)->count(),
            'no_visa_required' => $users->where('requires_visa', false)->count(),
            'has_passport_doc' => $users->whereNotNull('passport_file')->count(),
            'missing_passport_doc' => $users->whereNull('passport_file')->count(),
        ];

        // Group by nationality
        $by_nationality = $users->groupBy('nationality')->map->count()->sortDesc();

        $data = [
            'users' => $users,
            'stats' => $stats,
            'by_nationality' => $by_nationality,
            'filters' => $request->all(),
            'generated_at' => Carbon::now()->format('M d, Y H:i A'),
        ];

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.visa', $data);
            return $pdf->download('visa-report-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.visa', $data);
    }

    /**
     * Generate attendance report.
     */
    public function attendance(Request $request)
    {
        $query = User::whereHas('registrations');

        // Apply filters
        if ($request->has('attendance_status') && $request->attendance_status !== 'all') {
            $query->where('attendance_status', $request->attendance_status);
        }

        if ($request->has('delegate_category') && $request->delegate_category !== 'all') {
            $query->where('delegate_category', $request->delegate_category);
        }

        if ($request->has('nationality') && $request->nationality !== 'all') {
            $query->where('nationality', $request->nationality);
        }

        $users = $query->with(['registrations.package'])->orderBy('last_name')->get();

        // Statistics
        $stats = [
            'total_participants' => $users->count(),
            'present' => $users->where('attendance_status', 'present')->count(),
            'absent' => $users->where('attendance_status', 'absent')->count(),
            'pending' => $users->where('attendance_status', 'pending')->count(),
            'attendance_rate' => $users->count() > 0
                ? round(($users->where('attendance_status', 'present')->count() / $users->count()) * 100, 2)
                : 0,
        ];

        // Group by delegate category
        $by_category = $users->groupBy('delegate_category')->map->count()->sortDesc();

        // Group by nationality
        $by_nationality = $users->groupBy('nationality')->map->count()->sortDesc();

        $data = [
            'users' => $users,
            'stats' => $stats,
            'by_category' => $by_category,
            'by_nationality' => $by_nationality,
            'filters' => $request->all(),
            'generated_at' => Carbon::now()->format('M d, Y H:i A'),
        ];

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.attendance', $data);
            return $pdf->download('attendance-report-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.attendance', $data);
    }

    /**
     * Generate summary report (overview of all).
     */
    public function summary(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->subDays(30)->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->toDateString();

        $data = [
            'registrations' => [
                'total' => Registration::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'individual' => Registration::whereBetween('created_at', [$dateFrom, $dateTo])->where('registration_type', 'individual')->count(),
                'group' => Registration::whereBetween('created_at', [$dateFrom, $dateTo])->where('registration_type', 'group')->count(),
                'completed' => Registration::whereBetween('created_at', [$dateFrom, $dateTo])->where('status', 'completed')->count(),
            ],
            'payments' => [
                'total' => Payment::whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount'),
                'completed' => Payment::whereBetween('created_at', [$dateFrom, $dateTo])->where('payment_status', 'completed')->sum('amount'),
                'pending' => Payment::whereBetween('created_at', [$dateFrom, $dateTo])->where('payment_status', 'pending')->sum('amount'),
                'count' => Payment::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'participants' => [
                'total' => User::whereHas('registrations')->count(),
                'requires_visa' => User::where('requires_visa', true)->count(),
                'present' => User::where('attendance_status', 'present')->count(),
                'absent' => User::where('attendance_status', 'absent')->count(),
            ],
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'generated_at' => Carbon::now()->format('M d, Y H:i A'),
        ];

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.summary', $data);
            return $pdf->download('summary-report-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.summary', $data);
    }
}

