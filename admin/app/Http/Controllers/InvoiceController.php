<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $query = Invoice::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by biller name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('biller_name', 'like', "%{$search}%")
                  ->orWhere('biller_email', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        // Handle sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        switch ($sortField) {
            case 'biller_name':
                $query->orderBy('biller_name', $sortDirection);
                break;
            case 'biller_email':
                $query->orderBy('biller_email', $sortDirection);
                break;
            case 'invoice_number':
                $query->orderBy('invoice_number', $sortDirection);
                break;
            case 'amount':
                $query->orderBy('amount', $sortDirection);
                break;
            case 'status':
                $query->orderBy('status', $sortDirection);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        // Handle per page parameter
        $perPage = $request->get('per_page', 50);
        $perPage = min(max($perPage, 10), 200); // Min 10, Max 200
        
        $invoices = $query->paginate($perPage);

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        return view('invoices.create');
    }

    public function store(Request $request)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'biller_name' => 'required|string|max:255',
            'biller_email' => 'required|email|max:255',
            'biller_address' => 'required|string',
            'item' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric|min:1',
            'rate' => 'required|numeric|min:0',
            'currency' => 'required|string|in:USD,EUR,GBP,ZAR',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = $request->quantity * $request->rate;

            // Generate invoice number
            $invoiceNumber = 'INV' . str_pad(Invoice::max('id') + 1, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'biller_name' => $request->biller_name,
                'biller_email' => $request->biller_email,
                'biller_address' => $request->biller_address,
                'item' => $request->item,
                'description' => $request->description,
                'quantity' => $request->quantity,
                'rate' => $request->rate,
                'amount' => $totalAmount,
                'currency' => $request->currency,
                'status' => 'pending',
                'created_by' => $admin->id,
            ]);

            DB::commit();

            return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to create invoice: " . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to create invoice. Please try again.');
        }
    }

    public function show(Invoice $invoice)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Only allow editing pending invoices
        if ($invoice->status !== 'pending') {
            return redirect()->route('invoices.index')->with('error', 'Only pending invoices can be edited.');
        }

        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Only allow editing pending invoices
        if ($invoice->status !== 'pending') {
            return redirect()->route('invoices.index')->with('error', 'Only pending invoices can be edited.');
        }

        $request->validate([
            'biller_name' => 'required|string|max:255',
            'biller_email' => 'required|email|max:255',
            'biller_address' => 'required|string',
            'item' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric|min:1',
            'rate' => 'required|numeric|min:0',
            'currency' => 'required|string|in:USD,EUR,GBP,ZAR',
            'status' => 'required|string|in:pending,paid,cancelled',
        ]);

        try {
            // Calculate total amount
            $totalAmount = $request->quantity * $request->rate;

            $invoice->update([
                'biller_name' => $request->biller_name,
                'biller_email' => $request->biller_email,
                'biller_address' => $request->biller_address,
                'item' => $request->item,
                'description' => $request->description,
                'quantity' => $request->quantity,
                'rate' => $request->rate,
                'amount' => $totalAmount,
                'currency' => $request->currency,
                'status' => $request->status,
            ]);

            return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            Log::error("Failed to update invoice #{$invoice->id}: " . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to update invoice. Please try again.');
        }
    }

    public function download(Invoice $invoice)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.template', [
                'invoice' => $invoice
            ]);

            $filename = 'invoice_' . $invoice->invoice_number . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error("Failed to generate invoice PDF for #{$invoice->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate invoice PDF. Please try again.');
        }
    }

    public function preview(Invoice $invoice)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.template', [
                'invoice' => $invoice
            ]);

            $filename = 'invoice_' . $invoice->invoice_number . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error("Failed to generate invoice PDF for #{$invoice->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate invoice PDF. Please try again.');
        }
    }

    public function send(Request $request, Invoice $invoice)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Log::info("InvoiceController@send: Dispatching SendInvoiceEmailJob for invoice #{$invoice->id} to {$request->email}");
            \App\Jobs\SendInvoiceEmailJob::dispatch($invoice->id, $request->email);
            Log::info("InvoiceController@send: Job dispatched successfully");
            return redirect()->back()->with('success', 'Invoice is being sent to ' . $request->email . '.');
        } catch (\Exception $e) {
            Log::error("InvoiceController@send: Failed to dispatch SendInvoiceEmailJob for invoice #{$invoice->id}: " . $e->getMessage());
            Log::error("InvoiceController@send: Stack trace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to queue invoice email. Please try again.');
        }
    }

    public function markAsPaid(Invoice $invoice)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Only allow marking pending invoices as paid
        if ($invoice->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending invoices can be marked as paid.');
        }

        try {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_by' => $admin->id,
            ]);

            return redirect()->back()->with('success', 'Invoice marked as paid.');
        } catch (\Exception $e) {
            Log::error("Failed to mark invoice #{$invoice->id} as paid: " . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to mark invoice as paid. Please try again.');
        }
    }

    public function cancel(Invoice $invoice)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Only allow cancelling pending invoices
        if ($invoice->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending invoices can be cancelled.');
        }

        try {
            $invoice->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $admin->id,
            ]);

            return redirect()->back()->with('success', 'Invoice cancelled.');
        } catch (\Exception $e) {
            Log::error("Failed to cancel invoice #{$invoice->id}: " . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to cancel invoice. Please try again.');
        }
    }

    public function export(Request $request)
    {
        // Only admin can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $query = Invoice::query();

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('biller_name', 'like', "%{$search}%")
                  ->orWhere('biller_email', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        $filename = 'invoices_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($invoices) {
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
            fputcsv($file, [
                'Invoice Number',
                'Biller Name',
                'Biller Email',
                'Biller Address',
                'Item',
                'Description',
                'Amount',
                'Currency',
                'Status',
                'Created Date',
                'Paid Date',
                'Cancelled Date',
            ]);

            // CSV Data
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $safeValue($invoice->invoice_number),
                    $safeValue($invoice->biller_name),
                    $safeValue($invoice->biller_email),
                    $safeValue($invoice->biller_address),
                    $safeValue($invoice->item),
                    $safeValue($invoice->description),
                    $invoice->amount,
                    $safeValue($invoice->currency),
                    $safeValue(ucfirst($invoice->status)),
                    $invoice->created_at ? $invoice->created_at->format('Y-m-d H:i:s') : '',
                    $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : '',
                    $invoice->cancelled_at ? $invoice->cancelled_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
