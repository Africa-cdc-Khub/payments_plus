<?php

namespace App\Http\Controllers;

use App\Jobs\SendInvitationJob;
use App\Jobs\SendPassportRequestJob;
use App\Models\Registration;
use App\Models\RegistrationParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegistrationParticipantsController extends Controller
{
    public function index(Registration $registration)
    {
        // Authorization check - only admin and secretariat can access
        $admin = Auth::guard('admin')->user();
        if (!$admin || !in_array($admin->role, ['admin', 'secretariat'])) {
            abort(403, 'Unauthorized access.');
        }

        // Only non-individual registrations have participants
        if ($registration->registration_type === 'individual') {
            return redirect()->route('registrations.index')
                ->with('error', 'Individual registrations do not have additional participants.');
        }

        $registration->load(['user', 'package', 'participants']);

        return view('registration-participants.index', compact('registration'));
    }

    public function sendInvitation(Request $request, Registration $registration, RegistrationParticipant $participant)
    {
        // Authorization check - only admin and secretariat
        $admin = Auth::guard('admin')->user();
        if (!$admin || !in_array($admin->role, ['admin', 'secretariat'])) {
            abort(403, 'Unauthorized access.');
        }

        // Check if registration is paid or approved delegate
        $isDelegate = $registration->package_id == config('app.delegate_package_id');
        $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');

        if (!$canReceiveInvitation) {
            return redirect()->back()->with('error', 'This registration must be paid or approved before sending invitations.');
        }

        try {
            // Update participant invitation tracking
            $participant->update([
                'invitation_sent_at' => now(),
                'invitation_sent_by' => $admin->id,
            ]);

            // Dispatch invitation job for the participant
            SendInvitationJob::dispatch($registration->id, $participant->id);

            Log::info("Invitation email queued for participant #{$participant->id} by admin #" . $admin->id, [
                'admin' => $admin->username,
                'registration_id' => $registration->id,
            ]);

            return redirect()->back()->with('success', 'Invitation email has been queued for ' . $participant->full_name . '.');
        } catch (\Exception $e) {
            Log::error("Failed to queue invitation email for participant #{$participant->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to queue invitation email: ' . $e->getMessage());
        }
    }

    public function requestPassport(Request $request, Registration $registration, RegistrationParticipant $participant)
    {
        // Authorization check - only admin and secretariat
        $admin = Auth::guard('admin')->user();
        if (!$admin || !in_array($admin->role, ['admin', 'secretariat'])) {
            abort(403, 'Unauthorized access.');
        }

        // Check if passport is already uploaded
        if ($participant->passport_file) {
            return redirect()->back()->with('warning', 'Passport already uploaded for ' . $participant->full_name . '.');
        }

        try {
            // Dispatch passport request job
            SendPassportRequestJob::dispatch($registration->id, $participant->id);

            Log::info("Passport request email queued for participant #{$participant->id} by admin #" . $admin->id, [
                'admin' => $admin->username,
                'registration_id' => $registration->id,
            ]);

            return redirect()->back()->with('success', 'Passport request email has been queued for ' . $participant->full_name . '.');
        } catch (\Exception $e) {
            Log::error("Failed to queue passport request email for participant #{$participant->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to queue passport request email: ' . $e->getMessage());
        }
    }
}
