<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $query = Registration::where('payment_status', 'completed')
            ->with(['user', 'package']);

        // Filter by registration type
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        // Search by participant name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Handle sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        switch ($sortField) {
            case 'participant_name':
                $query->orderBy('user_id', $sortDirection);
                break;
            case 'email':
                $query->orderBy('user_id', $sortDirection);
                break;
            case 'total_amount':
                $query->orderBy('total_amount', $sortDirection);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        $receipts = $query->paginate(20)->withQueryString();

        return view('receipts.index', compact('receipts'));
    }

    public function show(Registration $receipt)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Check if registration is paid
        if ($receipt->payment_status !== 'completed') {
            return redirect()->back()->with('error', 'Receipt can only be viewed for paid registrations.');
        }

        $receipt->load(['user', 'package', 'participants']);

        return view('receipts.show', compact('receipt'));
    }

    public function download(Registration $receipt)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Check if registration is paid
        if ($receipt->payment_status !== 'completed') {
            return redirect()->back()->with('error', 'Receipt can only be downloaded for paid registrations.');
        }

        try {
            // Load registration with relationships
            $receipt->load(['user', 'package', 'participants']);
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('receipts.pdf', [
                'registration' => $receipt,
                'user' => $receipt->user,
                'package' => $receipt->package,
                'participants' => $receipt->participants ?? collect(),
            ]);

            $filename = 'receipt_RCP-' . str_pad($receipt->id, 6, '0', STR_PAD_LEFT) . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error("Failed to generate receipt PDF for registration #{$receipt->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to generate receipt PDF. Please try again.');
        }
    }

    public function preview(Registration $receipt)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Check if registration is paid
        if ($receipt->payment_status !== 'completed') {
            return redirect()->back()->with('error', 'Receipt can only be previewed for paid registrations.');
        }

        try {
            // Load registration with relationships
            $receipt->load(['user', 'package', 'participants']);
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('receipts.pdf', [
                'registration' => $receipt,
                'user' => $receipt->user,
                'package' => $receipt->package,
                'participants' => $receipt->participants ?? collect(),
            ]);

            $filename = 'receipt_RCP-' . str_pad($receipt->id, 6, '0', STR_PAD_LEFT) . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error("Failed to generate receipt PDF for registration #{$receipt->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to generate receipt PDF. Please try again.');
        }
    }

    public function send(Request $request, Registration $receipt)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Check if registration is paid
        if ($receipt->payment_status !== 'completed') {
            return redirect()->back()->with('error', 'Receipt can only be sent for paid registrations.');
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Log::info("ReceiptController@send: Dispatching SendReceiptPdfJob for registration #{$receipt->id} to {$request->email}");
            \App\Jobs\SendReceiptPdfJob::dispatch($receipt->id, $request->email);
            Log::info("ReceiptController@send: Job dispatched successfully");
            return redirect()->back()->with('success', 'Receipt is being sent to ' . $request->email . '.');
        } catch (\Exception $e) {
            Log::error("ReceiptController@send: Failed to dispatch SendReceiptPdfJob for registration #{$receipt->id}: " . $e->getMessage());
            Log::error("ReceiptController@send: Stack trace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to queue receipt email. Please try again.');
        }
    }

    public function export(Request $request)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $query = Registration::where('payment_status', 'completed')
            ->with(['user', 'package']);

        // Apply same filters as index
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $receipts = $query->orderBy('created_at', 'desc')->get();

        $filename = 'receipts_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($receipts) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Receipt Number',
                'Participant Name',
                'Email',
                'Registration Type',
                'Package',
                'Amount (USD)',
                'Payment Method',
                'Payment Date',
                'Registration Date'
            ]);

            // CSV Data
            foreach ($receipts as $receipt) {
                fputcsv($file, [
                    'RCP-' . str_pad($receipt->id, 6, '0', STR_PAD_LEFT),
                    $receipt->user->first_name . ' ' . $receipt->user->last_name,
                    $receipt->user->email,
                    ucfirst($receipt->registration_type),
                    $receipt->package->name,
                    number_format($receipt->total_amount, 2),
                    ucfirst($receipt->payment_method ?? 'N/A'),
                    $receipt->payment_completed_at ? $receipt->payment_completed_at->format('Y-m-d H:i:s') : 'N/A',
                    $receipt->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
