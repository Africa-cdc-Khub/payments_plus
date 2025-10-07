<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentController extends Controller
{
    /**
     * Get payments data for DataTables.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['registration.user']);

        // Apply filters
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_uuid', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhereHas('registration.user', function($userQuery) use ($search) {
                      $userQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Get paginated results
        $payments = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => $payments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'user_name' => $payment->registration && $payment->registration->user
                        ? ($payment->registration->user->first_name . ' ' . $payment->registration->user->last_name)
                        : 'N/A',
                    'email' => $payment->registration && $payment->registration->user
                        ? $payment->registration->user->email
                        : 'N/A',
                    'transaction_uuid' => $payment->transaction_uuid ?? 'N/A',
                    'payment_reference' => $payment->payment_reference ?? 'N/A',
                    'amount' => number_format($payment->amount, 2),
                    'currency' => $payment->currency,
                    'payment_status' => $payment->payment_status,
                    'payment_method' => $payment->payment_method ?? 'N/A',
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A',
                    'created_at' => $payment->created_at->format('M d, Y'),
                ];
            }),
            'total' => $payments->total(),
            'per_page' => $payments->perPage(),
            'current_page' => $payments->currentPage(),
            'last_page' => $payments->lastPage(),
        ]);
    }

    /**
     * Update payment status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,completed,failed',
        ]);

        $payment = Payment::findOrFail($id);
        $oldStatus = $payment->payment_status;

        $payment->update([
            'payment_status' => $request->payment_status,
            'payment_date' => $request->payment_status === 'completed' ? now() : $payment->payment_date,
        ]);

        // Update related registration payment status
        if ($payment->registration) {
            $payment->registration->update([
                'payment_status' => $request->payment_status,
                'payment_completed_at' => $request->payment_status === 'completed' ? now() : null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Payment status updated from {$oldStatus} to {$request->payment_status}",
            'payment' => $payment->fresh(),
        ]);
    }

    /**
     * Export payments to CSV.
     */
    public function export(Request $request)
    {
        $query = Payment::with(['registration.user']);

        // Apply same filters as index
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->latest()->get();

        $export = new class($payments) implements FromCollection, WithHeadings {
            protected $payments;

            public function __construct($payments)
            {
                $this->payments = $payments;
            }

            public function collection()
            {
                return $this->payments->map(function($payment) {
                    return [
                        'ID' => $payment->id,
                        'Transaction UUID' => $payment->transaction_uuid ?? 'N/A',
                        'Reference' => $payment->payment_reference ?? 'N/A',
                        'First Name' => $payment->registration && $payment->registration->user
                            ? $payment->registration->user->first_name
                            : 'N/A',
                        'Last Name' => $payment->registration && $payment->registration->user
                            ? $payment->registration->user->last_name
                            : 'N/A',
                        'Email' => $payment->registration && $payment->registration->user
                            ? $payment->registration->user->email
                            : 'N/A',
                        'Amount' => $payment->amount,
                        'Currency' => $payment->currency,
                        'Status' => $payment->payment_status,
                        'Method' => $payment->payment_method ?? 'N/A',
                        'Payment Date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d H:i:s') : 'N/A',
                        'Created At' => $payment->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'ID',
                    'Transaction UUID',
                    'Reference',
                    'First Name',
                    'Last Name',
                    'Email',
                    'Amount',
                    'Currency',
                    'Status',
                    'Method',
                    'Payment Date',
                    'Created At',
                ];
            }
        };

        return Excel::download($export, 'payments_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Get payment statistics.
     */
    public function stats()
    {
        return response()->json([
            'total' => Payment::count(),
            'total_amount' => Payment::where('payment_status', 'completed')->sum('amount'),
            'pending' => Payment::where('payment_status', 'pending')->count(),
            'completed' => Payment::where('payment_status', 'completed')->count(),
            'failed' => Payment::where('payment_status', 'failed')->count(),
        ]);
    }
}
