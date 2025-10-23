<?php

namespace App\Http\Controllers;

use App\Jobs\SendInvitationJob;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\ReceiptEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::with(['user', 'package', 'payment.completedBy', 'invitationSentBy', 'voidedBy']);

        // Filter by registration ID
        if ($request->filled('registration_id')) {
            $query->where('id', $request->registration_id);
        }

        // Filter by payment status
        if ($request->filled('status')) {
            $delegatePackageId = config('app.delegate_package_id');
            
            if ($request->status === 'voided') {
                // Show only voided registrations
                $query->whereNotNull('voided_at');
            } elseif ($request->status === 'pending') {
                // For pending: exclude voided, show pending payment status, exclude delegates
                $query->whereNull('voided_at');
                $query->where('payment_status', 'pending');
                $query->where('package_id', '!=', $delegatePackageId);
            } elseif ($request->status === 'completed') {
                // For paid: show only actual paid registrations (NOT delegates)
                $query->whereNull('voided_at');
                $query->where('payment_status', 'completed');
                $query->where('package_id', '!=', $delegatePackageId);
            } elseif ($request->status === 'delegates') {
                // For delegates: show all delegates (all statuses)
                $query->whereNull('voided_at');
                $query->where('package_id', $delegatePackageId);
            } elseif ($request->status === 'approved_delegates') {
                // For approved delegates: show only approved delegates
                $query->whereNull('voided_at');
                $query->where('package_id', $delegatePackageId);
                $query->where('status', 'approved');
            } elseif ($request->status === 'rejected') {
                // For rejected delegates: show only rejected delegates
                $query->whereNull('voided_at');
                $query->where('package_id', $delegatePackageId);
                $query->where('status', 'rejected');
            } else {
                // For other statuses, exclude voided and filter by status
                $query->whereNull('voided_at');
                $query->where('payment_status', $request->status);
            }
        } else {
            // Exclude voided registrations by default when no status filter is applied
            $query->whereNull('voided_at');
        }

        // Search by user
        if ($request->filled('search')) {
            $search = $request->search;
            $search = str_replace(' ', '%', $search);
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Handle sorting
        $sortField = $request->get('sort', 'payment_status');
        $sortDirection = $request->get('direction', 'asc');
        
        switch ($sortField) {
            case 'name':
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.first_name', $sortDirection)
                      ->orderBy('users.last_name', $sortDirection);
                break;
            case 'email':
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.email', $sortDirection);
                break;
            case 'package':
                $query->join('packages', 'registrations.package_id', '=', 'packages.id')
                      ->orderBy('packages.name', $sortDirection);
                break;
            case 'amount':
                $query->join('packages', 'registrations.package_id', '=', 'packages.id')
                      ->orderBy('packages.price', $sortDirection);
                break;
            case 'created_at':
                $query->orderBy('registrations.created_at', $sortDirection);
                break;
            case 'status':
                $query->orderBy('status', $sortDirection);
                break;
            case 'payment_status':
            default:
                $query->orderByRaw("CASE WHEN payment_status = 'completed' THEN 0 ELSE 1 END")
                      ->orderBy('created_at', 'desc');
                break;
        }

        // Handle per page parameter
        $perPage = $request->get('per_page', 50);
        $perPage = min(max($perPage, 10), 200); // Min 10, Max 200
        
        $registrations = $query->paginate($perPage);

        return view('registrations.index', compact('registrations'));
    }

    public function export(Request $request)
    {
        // Use the same filtering logic as index method
        $query = Registration::with(['user', 'package', 'payment.completedBy', 'invitationSentBy', 'voidedBy']);

        // Filter by registration ID
        if ($request->filled('registration_id')) {
            $query->where('id', $request->registration_id);
        }

        // Filter by payment status
        if ($request->filled('status')) {
            $delegatePackageId = config('app.delegate_package_id');
            
            if ($request->status === 'voided') {
                $query->whereNotNull('voided_at');
            } elseif ($request->status === 'pending') {
                $query->whereNull('voided_at');
                $query->where('payment_status', 'pending');
                $query->where('package_id', '!=', $delegatePackageId);
            } elseif ($request->status === 'completed') {
                $query->whereNull('voided_at');
                $query->where('payment_status', 'completed');
                $query->where('package_id', '!=', $delegatePackageId);
            } elseif ($request->status === 'delegates') {
                $query->whereNull('voided_at');
                $query->where('package_id', $delegatePackageId);
            } elseif ($request->status === 'approved_delegates') {
                $query->whereNull('voided_at');
                $query->where('package_id', $delegatePackageId);
                $query->where('status', 'approved');
            } elseif ($request->status === 'rejected') {
                $query->whereNull('voided_at');
                $query->where('package_id', $delegatePackageId);
                $query->where('status', 'rejected');
            } else {
                $query->whereNull('voided_at');
                $query->where('payment_status', $request->status);
            }
        } else {
            $query->whereNull('voided_at');
        }

        // Search by user
        if ($request->filled('search')) {
            $search = $request->search;
            $search = str_replace(' ', '%', $search);
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $registrations = $query
            ->orderByRaw("CASE WHEN payment_status = 'completed' THEN 0 ELSE 1 END")
            ->latest('created_at')
            ->get();

        // Generate CSV content
        $filename = 'registrations_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($registrations) {
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
            
            // CSV headers
            fputcsv($file, [
                'Registration ID',
                'Title',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Nationality',
                'Package',
                'Registration Type',
                'Payment Status',
                'Amount',
                'Payment Method',
                'Registration Status',
                'Registered Date',
                'Payment Date',
                'Marked By',
                'Invitation Sent',
                'Invitation Sent By',
                'Voided',
                'Voided By',
                'Void Reason'
            ]);

            // CSV data
            foreach ($registrations as $registration) {
                // Primary registrant
                fputcsv($file, [
                    $registration->id,
                    $safeValue($registration->user->title ?? ''),
                    $safeValue($registration->user->first_name ?? ''),
                    $safeValue($registration->user->last_name ?? ''),
                    $safeValue($registration->user->email ?? ''),
                    $safeValue($registration->user->phone ?? ''),
                    $safeValue($registration->user->nationality ?? ''),
                    $safeValue($registration->package->name ?? ''),
                    $safeValue($registration->registration_type ?? ''),
                    $safeValue($registration->payment_status ?? ''),
                    $registration->package->price ?? '',
                    $safeValue($registration->payment_method ?? ''),
                    $safeValue($registration->status ?? ''),
                    $registration->created_at ? $registration->created_at->format('Y-m-d H:i:s') : '',
                    $registration->payment && $registration->payment->created_at ? $registration->payment->created_at->format('Y-m-d H:i:s') : '',
                    $registration->payment && $registration->payment->completedBy ? $safeValue($registration->payment->completedBy->full_name) : '',
                    $registration->invitation_sent_at ? 'Yes (' . $registration->invitation_sent_at->format('Y-m-d H:i:s') . ')' : 'No',
                    $registration->invitationSentBy ? $safeValue($registration->invitationSentBy->full_name) : '',
                    $registration->voided_at ? 'Yes (' . $registration->voided_at->format('Y-m-d H:i:s') . ')' : 'No',
                    $registration->voidedBy ? $safeValue($registration->voidedBy->full_name) : '',
                    $safeValue($registration->void_reason ?? '')
                ]);

                // Registration participants (group members)
                foreach ($registration->participants as $participant) {
                    fputcsv($file, [
                        $registration->id . ' (Group Member)',
                        $safeValue($registration->user->title ?? ''),
                        $safeValue($participant->first_name ?? ''),
                        $safeValue($participant->last_name ?? ''),
                        $safeValue($participant->email ?? ''),
                        $safeValue($participant->phone ?? ''),
                        $safeValue($participant->nationality ?? ''),
                        $safeValue($registration->package->name ?? ''),
                        
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(Registration $registration)
    {
        $registration->load(['user', 'package', 'participants']);
        
        return view('registrations.show', compact('registration'));
    }

    public function markAsPaid(Request $request, Registration $registration)
    {
        // Authorization check - only admin and finance can mark as paid
        $this->authorize('markAsPaid', Payment::class);

        // Validate the request
        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|in:bank,online,cash',
            'remarks' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Get or create payment record
            $payment = $registration->payment;
            
            try{
            if (!$payment) {
                // Create a new payment record
                $payment = Payment::create([
                    'registration_id' => $registration->id,
                    'amount' => $request->amount_paid,
                    'currency' => 'USD',
                    'payment_status' => 'completed',
                    'payment_method' => $request->payment_method,
                    'payment_reference' => 'MANUAL-' . time() . '-' . $registration->id,
                    'payment_date' => now(),
                    'completed_by' => Auth::guard('admin')->id(),
                    'manual_payment_remarks' => $request->remarks,
                ]);
            } else {
                // Update existing payment
                $payment->update([
                    'amount' => $request->amount_paid,
                    'payment_status' => 'completed',
                    'payment_method' => $request->payment_method,
                    'payment_date' => $payment->payment_date ?? now(),
                    'completed_by' => Auth::guard('admin')->id(),
                    'manual_payment_remarks' => $request->remarks,
                    'payment_reference'=>$request->remarks
                ]);
            }
        } catch (\Exception $e) {
            //DB::rollBack();
            Log::error("Failed to update payment record for registration #{$registration->id}: " . $e->getMessage());
            //return redirect()->back()->with('error', 'Failed to update payment record. Please try again.');
        }

            // Update registration payment status
            $registration->update([
                'payment_status' => 'completed',
                'status' => 'paid',
                'invitation_sent_at' => now(),
                'invitation_sent_by' => Auth::guard('admin')->id(),
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->remarks
            ]);

            DB::commit();

            Log::info("Registration #{$registration->id} manually marked as paid by admin #" . Auth::guard('admin')->id(), [
                'remarks' => $request->remarks,
                'admin' => Auth::guard('admin')->user()->username,
            ]);

            // Dispatch invitation email job
            try {
                SendInvitationJob::dispatch($registration->id);
                Log::info("Invitation email queued for registration #{$registration->id} after manual payment marking");
                
                // Send receipt email
                try {
                    $receiptService = new ReceiptEmailService();
                    $receiptSent = $receiptService->sendReceiptEmail($registration, $request->payment_method);
                    
                    if ($receiptSent) {
                        Log::info("Receipt email queued for registration #{$registration->id}");
                        $message = "Registration for {$registration->user->full_name} has been marked as paid. Invitation and receipt emails have been queued.";
                    } else {
                        Log::warning("Failed to queue receipt email for registration #{$registration->id}");
                        $message = "Registration for {$registration->user->full_name} has been marked as paid and invitation email has been queued, but receipt email failed.";
                    }
                } catch (\Exception $receiptException) {
                    Log::error("Failed to send receipt email for registration #{$registration->id}: " . $receiptException->getMessage());
                    $message = "Registration for {$registration->user->full_name} has been marked as paid and invitation email has been queued, but receipt email failed.";
                }
                
                return redirect()
                    ->back()
                    ->with('success', $message);
                    
            } catch (\Exception $emailException) {
                Log::error("Failed to queue invitation email for registration #{$registration->id}: " . $emailException->getMessage());
                
                return redirect()
                    ->back()
                    ->with('warning', "Registration for {$registration->user->full_name} has been marked as paid, but failed to queue invitation email. You can send it manually.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to mark registration #{$registration->id} as paid: " . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Failed to mark registration as paid. Please try again.');
        }
    }

    public function sendInvitation(Request $request, Registration $registration)
    {
        // Authorization check - only admin can manually send invitations
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        // Validate the registration is eligible for invitation
        // $isDelegate = $registration->package_id == config('app.delegate_package_id');
        // $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');

        // if (!$canReceiveInvitation) {
        //     return redirect()->back()->with('error', 'This registration is not eligible to receive an invitation.');
        // }

        try {
            // Dispatch invitation job (job will handle invitation tracking)
            SendInvitationJob::dispatch($registration->id);

            Log::info("Invitation email manually queued for registration #{$registration->id} by admin #" . $admin->id, [
                'admin' => $admin->username,
            ]);

            $message = 'Invitation email has been queued for ' . $registration->user->full_name;
            
            // Check if this is a group registration
            if ($registration->registration_type !== 'individual' && $registration->participants()->count() > 0) {
                $participantCount = $registration->participants()->count();
                $message .= " and {$participantCount} additional participant" . ($participantCount > 1 ? 's' : '');
            }
            
            $message .= '.';
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error("Failed to queue invitation email for registration #{$registration->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to queue invitation email: ' . $e->getMessage());
        }
    }

    public function voidRegistration(Request $request, Registration $registration = null)
    {
        // Authorization check - only admin can void registrations
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access. Only admins can void registrations.');
        }

        // Validate the request
        $request->validate([
            'void_reason' => 'required|string|max:1000',
            'registration_ids' => 'sometimes|array',
            'registration_ids.*' => 'exists:registrations,id',
        ]);

        try {
            DB::beginTransaction();

            $registrationIds = [];
            $voidedCount = 0;
            $skippedCount = 0;
            $voidedNames = [];

            // If bulk voiding (multiple IDs)
            if ($request->has('registration_ids')) {
                $registrationIds = $request->registration_ids;
            } 
            // If single registration voiding
            elseif ($registration) {
                $registrationIds = [$registration->id];
            } else {
                return redirect()->back()->with('error', 'No registrations specified for voiding.');
            }

            // Process each registration
            foreach ($registrationIds as $regId) {
                $reg = Registration::with('user')->find($regId);
                
                if (!$reg) {
                    continue;
                }

                // Check if it's an approved delegate
                $isDelegate = $reg->package_id == config('app.delegate_package_id');
                $isApprovedDelegate = $isDelegate && $reg->status === 'approved';

                // Skip if not pending, already voided, or is an approved delegate
                if (!$reg->isPending() || $reg->isVoided() || $isApprovedDelegate) {
                    $skippedCount++;
                    continue;
                }

                // Update registration with void information
                $reg->update([
                    'voided_at' => now(),
                    'voided_by' => $admin->id,
                    'void_reason' => $request->void_reason,
                    'payment_status' => 'voided',
                ]);

                $voidedCount++;
                $voidedNames[] = $reg->user->full_name;

                Log::info("Registration #{$reg->id} voided by admin #" . $admin->id, [
                    'admin' => $admin->username,
                    'reason' => $request->void_reason,
                ]);
            }

            DB::commit();

            // Build success message
            if ($voidedCount > 0) {
                $message = $voidedCount === 1 
                    ? "Registration for {$voidedNames[0]} has been voided."
                    : "{$voidedCount} registration(s) have been voided.";
                
                if ($skippedCount > 0) {
                    $message .= " {$skippedCount} registration(s) were skipped (already voided or not pending).";
                }
                
                return redirect()->back()->with('success', $message);
            } else {
                return redirect()->back()->with('warning', 'No registrations were voided. All selected registrations are either already voided or not pending.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to void registrations: " . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to void registrations. Please try again.');
        }
    }

    public function undoVoid(Request $request, Registration $registration)
    {
        // Authorization check - only admin can undo void
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access. Only admins can undo void.');
        }

        // Check if registration is voided
        if (!$registration->isVoided()) {
            return redirect()->back()->with('error', 'This registration is not voided.');
        }

        try {
            DB::beginTransaction();

            // Restore registration by clearing void information
            $registration->update([
                'voided_at' => null,
                'voided_by' => null,
                'void_reason' => null,
                'payment_status' => 'pending', // Restore to pending status
            ]);

            DB::commit();

            Log::info("Registration #{$registration->id} void undone by admin #" . $admin->id, [
                'admin' => $admin->username,
            ]);

            return redirect()->back()->with('success', "Void has been undone for {$registration->user->full_name}. Registration restored to pending status.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to undo void for registration #{$registration->id}: " . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to undo void. Please try again.');
        }
    }

    public function invoice(Registration $registration)
    {
        // Only admin and finance can generate invoices
        $admin = Auth::guard('admin')->user();
        if (!$admin || !in_array($admin->role, ['admin', 'finance'])) {
            abort(403, 'Unauthorized access.');
        }

        // Check if registration is paid
        if (!$registration->isPaid()) {
            return redirect()->back()->with('error', 'Invoice can only be generated for paid registrations.');
        }

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.template', [
                'registration' => $registration,
                'user' => $registration->user
            ]);

            $filename = 'invoice_' . $registration->id . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error("Failed to generate invoice for registration #{$registration->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate invoice. Please try again.');
        }
    }

    /**
     * Send receipt email manually
     */
    public function sendReceipt(Request $request, Registration $registration)
    {
        // Authorization check - only admin and finance can send receipts
        $admin = Auth::guard('admin')->user();
        if (!$admin || !in_array($admin->role, ['admin', 'finance'])) {
            abort(403, 'Unauthorized access.');
        }

        // Check if registration is paid
        if ($registration->payment_status !== 'completed') {
            return redirect()->back()->with('error', 'This registration is not marked as paid. Receipt can only be sent for paid registrations.');
        }

        try {
            $receiptService = new ReceiptEmailService();
            $receiptSent = $receiptService->sendReceiptEmail($registration);

            if ($receiptSent) {
                Log::info("Manual receipt email sent for registration #{$registration->id} by admin #{$admin->id}");
                return redirect()->back()->with('success', "Receipt email has been queued for {$registration->user->full_name}.");
            } else {
                Log::error("Failed to send manual receipt email for registration #{$registration->id}");
                return redirect()->back()->with('error', 'Failed to send receipt email. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error("Failed to send manual receipt email for registration #{$registration->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send receipt email. Please try again.');
        }
    }

    /**
     * Send receipts to all participants with completed payments
     */
    public function sendBulkReceipts(Request $request)
    {
        // Authorization check - only admin can send bulk receipts
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        try {
            // Get all registrations with completed payments
            $completedRegistrations = Registration::where('payment_status', 'completed')
                ->with(['user', 'package'])
                ->get();

            if ($completedRegistrations->isEmpty()) {
                return redirect()->back()->with('warning', 'No registrations with completed payments found.');
            }

            $receiptService = new ReceiptEmailService();
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($completedRegistrations as $registration) {
                try {
                    $receiptSent = $receiptService->sendReceiptEmail($registration);
                    if ($receiptSent) {
                        $successCount++;
                        Log::info("Bulk receipt email sent for registration #{$registration->id}");
                    } else {
                        $errorCount++;
                        $errors[] = "Failed to queue receipt for registration #{$registration->id}";
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Error sending receipt for registration #{$registration->id}: " . $e->getMessage();
                    Log::error("Bulk receipt email failed for registration #{$registration->id}: " . $e->getMessage());
                }
            }

            $message = "Bulk receipt sending completed. Success: {$successCount}, Errors: {$errorCount}";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " and " . (count($errors) - 5) . " more errors.";
                }
            }

            Log::info("Bulk receipt sending completed by admin #{$admin->id}. Success: {$successCount}, Errors: {$errorCount}");

            if ($errorCount > 0) {
                return redirect()->back()->with('warning', $message);
            } else {
                return redirect()->back()->with('success', $message);
            }

        } catch (\Exception $e) {
            Log::error("Bulk receipt sending failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send bulk receipts. Please try again.');
        }
    }
}

