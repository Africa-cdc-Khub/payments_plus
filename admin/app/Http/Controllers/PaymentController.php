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
        
        // Calculate total payment amount
        $totalPaymentAmount = $query->clone()->sum('payment_amount');

        return view('payments.index', compact('payments', 'packages', 'countries', 'totalPaymentAmount'));
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Payment::class);

        $query = Registration::with(['user', 'package', 'payment.completedBy', 'participants'])
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
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $isTravels = auth('admin')->user()->role === 'travels';
        
        $callback = function() use ($payments, $isTravels) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper accent display in Excel and other applications
            fwrite($file, "\xEF\xBB\xBF");
            
            // Helper function: ensure value is UTF-8 and safe for Excel (handles accents/diacritics correctly)
            $safeValue = function($value) {
                if (is_null($value)) return '';
                // If already UTF-8, keep as-is, but ensure any improper bytes fixed
                $str = (string)$value;
                if (!mb_detect_encoding($str, 'UTF-8', true)) {
                    $str = mb_convert_encoding($str, 'UTF-8');
                }
                // Some Office/Excel versions may break on long accented chars if not normalized:
                return normalizer_is_normalized($str, \Normalizer::FORM_C) ? $str : normalizer_normalize($str, \Normalizer::FORM_C);
            };

            // CSV Headers
            $headers = [
                'ID',
                'Title',
                'First Name',   
                'Last Name',
                'Phone',
                'Nationality',
                'Email',
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
                    $safeValue($payment->user->title ?? ''),
                    $safeValue($payment->user->first_name ?? ''),
                    $safeValue($payment->user->last_name ?? ''),
                    $safeValue($payment->user->email),
                    $safeValue($payment->user->phone ?? ''),
                    $safeValue($payment->user->nationality ?? ''),
                    $safeValue($payment->package->name ?? ''),
                ];
                
                if ($isTravels) {
                    $row[] = $safeValue($payment->user->passport_number ?? '');
                    $row[] = $safeValue($payment->user->airport_of_origin ?? '');
                }
                
                $row = array_merge($row, [
                    $payment->total_amount ?? ($payment->payment->amount ?? ''),
                    $payment->payment ? $safeValue(ucfirst(str_replace('_', ' ', $payment->payment->payment_method))) : '',
                    $safeValue($payment->payment->payment_reference ?? ''),
                    $payment->payment && $payment->payment->payment_date 
                        ? $payment->payment->payment_date->format('Y-m-d H:i:s') 
                        : '',
                    $payment->payment && $payment->payment->completedBy 
                        ? $safeValue($payment->payment->completedBy->username) 
                        : '',
                    $safeValue($payment->payment->manual_payment_remarks ?? ''),
                ]);
                
                fputcsv($file, $row);
                
                // Include registration participants (group members) for payments
                foreach ($payment->participants as $participant) {
                    $participantRow = [
                        $payment->id . ' (Group Member)',
                        $safeValue($payment->user->title ?? ''),                    
                        $safeValue($payment->user->first_name ?? ''),
                        $safeValue($payment->user->last_name ?? ''),
                        $safeValue($payment->user->email ?? ''),
                        $safeValue($payment->user->phone ?? ''),
                        $safeValue($payment->user->nationality ?? ''),
                        $safeValue($payment->package->name ?? ''),
                    ];
                    
                    if ($isTravels) {
                        $participantRow[] = $safeValue($participant->passport_number ?? '');
                        $participantRow[] = $safeValue($participant->airport_of_origin ?? '');
                    }
                    
                    $participantRow = array_merge($participantRow, [
                        $payment->total_amount ?? ($payment->payment->amount ?? ''),
                        $payment->payment ? $safeValue(ucfirst(str_replace('_', ' ', $payment->payment->payment_method))) : '',
                        $safeValue($payment->payment->payment_reference ?? ''),
                        $payment->payment && $payment->payment->payment_date 
                            ? $payment->payment->payment_date->format('Y-m-d H:i:s') 
                            : '',
                        $payment->payment && $payment->payment->completedBy 
                            ? $safeValue($payment->payment->completedBy->username) 
                            : '',
                        $safeValue($payment->payment->manual_payment_remarks ?? ''),
                    ]);
                    
                    fputcsv($file, $participantRow);
                }
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

