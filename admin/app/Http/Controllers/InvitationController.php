<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvitationController extends Controller
{
    protected $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    public function preview(Request $request)
    {
        $this->authorize('viewInvitation', Registration::class);
        
        $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
        ]);

        try {
            $registration = Registration::with(['user', 'package'])->findOrFail($request->registration_id);

            // Allow if paid OR if it's an approved delegate
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveInvitation) {
                return response()->json([
                    'error' => 'Only paid registrations or approved delegates can receive invitations.'
                ], 400);
            }

            $pdf = Pdf::loadView('invitations.template', [
                'registration' => $registration,
                'user' => $registration->user,
                'package' => $registration->package,
            ]);

            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');
            
            // Set timeout
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'Arial');

            return $pdf->stream('invitation_preview.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Preview Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate preview: ' . $e->getMessage()
            ], 500);
        }
    }

    public function send(Request $request)
    {
        $this->authorize('sendInvitation', Registration::class);
        
        $request->validate([
            'registration_ids' => ['required', 'array'],
            'registration_ids.*' => ['exists:registrations,id'],
        ]);

        $results = $this->invitationService->sendBulkInvitations($request->registration_ids);

        if ($results['queued'] > 0) {
            $message = "Successfully queued {$results['queued']} invitation(s) for sending.";
            if ($results['failed'] > 0) {
                $message .= " {$results['failed']} could not be queued.";
            }
            $message .= " Emails will be sent in the background.";
            return redirect()->back()->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'Failed to queue invitations. ' . implode(', ', $results['errors']));
        }
    }

    public function download(Registration $registration)
    {
        $this->authorize('viewInvitation', Registration::class);
        
        // Allow if paid OR if it's an approved delegate
        $isDelegate = $registration->package_id == config('app.delegate_package_id');
        $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');

        if (!$canReceiveInvitation) {
            return redirect()->back()->with('error', 'Only paid registrations or approved delegates can download invitations.');
        }

        try {
            $registration->load(['user', 'package']);

            $pdf = Pdf::loadView('invitations.template', [
                'registration' => $registration,
                'user' => $registration->user,
                'package' => $registration->package,
            ]);

            // Set paper size and orientation  
            $pdf->setPaper('a4', 'portrait');
            
            // Configure PDF options
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'Arial');

            return $pdf->download('invitation_' . $registration->id . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Download Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate invitation PDF. Please check if all required images are present in public/images/ folder.');
        }
    }
}
