<?php

namespace App\Http\Controllers;

use App\Jobs\SendInvitationJob;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::with(['user', 'package', 'payment.completedBy', 'invitationSentBy']);

        // Filter by registration ID
        if ($request->filled('registration_id')) {
            $query->where('id', $request->registration_id);
        }

        // Filter by payment status
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        // Search by user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $registrations = $query
            ->orderByRaw("CASE WHEN payment_status = 'completed' THEN 0 ELSE 1 END")
            ->latest('created_at')
            ->paginate(20);

        return view('registrations.index', compact('registrations'));
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
            'payment_method' => 'required|in:bank,online',
            'remarks' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Get or create payment record
            $payment = $registration->payment;
            
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
                ]);
            }

            // Update registration payment status
            $registration->update([
                'payment_status' => 'completed',
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
                
                return redirect()
                    ->back()
                    ->with('success', "Registration for {$registration->user->full_name} has been marked as paid and invitation email has been queued.");
                    
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
            // Update invitation sent tracking
            $registration->update([
                'invitation_sent_at' => now(),
                'invitation_sent_by' => $admin->id,
            ]);

            // Dispatch invitation job
            SendInvitationJob::dispatch($registration->id);

            Log::info("Invitation email manually queued for registration #{$registration->id} by admin #" . $admin->id, [
                'admin' => $admin->username,
            ]);

            return redirect()->back()->with('success', 'Invitation email has been queued for ' . $registration->user->full_name . '.');
        } catch (\Exception $e) {
            Log::error("Failed to queue invitation email for registration #{$registration->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to queue invitation email: ' . $e->getMessage());
        }
    }
}

