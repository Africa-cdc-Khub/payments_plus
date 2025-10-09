<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Payment::class);
        
        $query = Registration::with(['user', 'package', 'payment'])
            ->where('payment_status', 'completed');

        // Filter by package
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get all packages for filter dropdown
        $packages = Package::orderBy('name')->get();

        // Get all unique countries for filter dropdown
        $countries = User::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $payments = $query->latest('payment_completed_at')->paginate(20);

        return view('payments.index', compact('payments', 'packages', 'countries'));
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Payment::class);

        $query = Registration::with(['user', 'package', 'payment.completedBy'])
            ->where('payment_status', 'completed');

        // Apply same filters as index
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest('payment_completed_at')->get();

        $filename = 'payments_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $isTravels = auth('admin')->user()->role === 'travels';
        
        $callback = function() use ($payments, $isTravels) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            $headers = [
                'ID',
                'Name',
                'Email',
                'Country',
                'Package',
            ];
            
            if ($isTravels) {
                $headers[] = 'Passport Number';
                $headers[] = 'Airport of Origin';
            }
            
            $headers = array_merge($headers, [
                'Amount',
                'Payment Method',
                'Payment Reference',
                'Payment Date',
                'Marked By',
                'Remarks',
            ]);
            
            fputcsv($file, $headers);

            // CSV Rows
            foreach ($payments as $payment) {
                $row = [
                    $payment->id,
                    $payment->user->full_name,
                    $payment->user->email,
                    $payment->user->country ?? '',
                    $payment->package->name ?? '',
                ];
                
                if ($isTravels) {
                    $row[] = $payment->user->passport_number ?? '';
                    $row[] = $payment->user->airport_of_origin ?? '';
                }
                
                $row = array_merge($row, [
                    $payment->total_amount ?? ($payment->payment->amount ?? ''),
                    $payment->payment ? ucfirst(str_replace('_', ' ', $payment->payment->payment_method)) : '',
                    $payment->payment->payment_reference ?? '',
                    $payment->payment && $payment->payment->payment_date 
                        ? $payment->payment->payment_date->format('Y-m-d H:i:s') 
                        : '',
                    $payment->payment && $payment->payment->completedBy 
                        ? $payment->payment->completedBy->username 
                        : '',
                    $payment->payment->manual_payment_remarks ?? '',
                ]);
                
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(Registration $payment)
    {
        $this->authorize('view', $payment->payment);
        
        $payment->load(['user', 'package']);
        
        return view('payments.show', compact('payment'));
    }
}

