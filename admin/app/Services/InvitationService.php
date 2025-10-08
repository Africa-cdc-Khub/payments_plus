<?php

namespace App\Services;

use App\Contracts\InvitationServiceInterface;
use App\Jobs\SendInvitationJob;
use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvitationService implements InvitationServiceInterface
{
    public function generatePDF(Registration $registration): string
    {
        $pdf = Pdf::loadView('invitations.template', [
            'registration' => $registration,
            'user' => $registration->user,
            'package' => $registration->package,
        ]);

        $filename = 'invitation_' . $registration->id . '_' . time() . '.pdf';
        $path = 'invitations/' . $filename;

        Storage::put($path, $pdf->output());

        return $path;
    }

    public function sendInvitation(Registration $registration): bool
    {
        try {
            // Dispatch job to send invitation
            SendInvitationJob::dispatch($registration->id);
            
            Log::info("Invitation job dispatched for registration #{$registration->id}");
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to dispatch invitation job: ' . $e->getMessage());
            return false;
        }
    }

    public function sendBulkInvitations(array $registrationIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'queued' => 0,
        ];

        foreach ($registrationIds as $id) {
            $registration = Registration::find($id);

            if (!$registration) {
                $results['failed']++;
                $results['errors'][] = "Registration #{$id} not found";
                continue;
            }

            // Allow if paid OR if it's an approved delegate
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveInvitation) {
                $results['failed']++;
                $results['errors'][] = "Registration #{$id} is neither paid nor an approved delegate";
                continue;
            }

            try {
                // Dispatch job for each invitation
                SendInvitationJob::dispatch($id);
                $results['queued']++;
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Failed to queue invitation for registration #{$id}: " . $e->getMessage();
                Log::error("Failed to dispatch job for registration #{$id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    public function previewInvitation(Registration $registration): string
    {
        $pdf = Pdf::loadView('invitations.template', [
            'registration' => $registration,
            'user' => $registration->user,
            'package' => $registration->package,
        ]);

        return $pdf->output();
    }
}

