<?php

namespace App\Services;

use App\Jobs\SendCertificateJob;
use App\Models\Registration;
use App\Models\RegistrationParticipant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    /**
     * Get all eligible participants for certificates
     * Eligible: payment_status='completed' OR status='approved'
     */
    public function getEligibleParticipants()
    {
        $participants = collect();

        // Get individual registrations that are paid or approved delegates
        $individualRegistrations = Registration::with(['user', 'package'])
            ->where('registration_type', 'individual')
            ->where(function($query) {
                $query->where('payment_status', 'completed')
                      ->orWhere(function($q) {
                          $q->where('status', 'approved')
                            ->where('package_id', config('app.delegate_package_id'));
                      });
            })
            ->get();

        foreach ($individualRegistrations as $registration) {
            $participants->push([
                'type' => 'individual',
                'registration_id' => $registration->id,
                'participant_id' => null,
                'name' => $registration->user->first_name . ' ' . $registration->user->last_name,
                'first_name' => $registration->user->first_name,
                'last_name' => $registration->user->last_name,
                'email' => $registration->user->email,
                'registration' => $registration,
            ]);
        }

        // Get group registrations and their participants
        $groupRegistrations = Registration::with(['user', 'package', 'participants'])
            ->where('registration_type', 'group')
            ->where(function($query) {
                $query->where('payment_status', 'completed')
                      ->orWhere(function($q) {
                          $q->where('status', 'approved')
                            ->where('package_id', config('app.delegate_package_id'));
                      });
            })
            ->get();

        foreach ($groupRegistrations as $registration) {
            // Add focal person (main registrant)
            $participants->push([
                'type' => 'group_focal',
                'registration_id' => $registration->id,
                'participant_id' => null,
                'name' => $registration->user->first_name . ' ' . $registration->user->last_name,
                'first_name' => $registration->user->first_name,
                'last_name' => $registration->user->last_name,
                'email' => $registration->user->email,
                'registration' => $registration,
            ]);

            // Add group participants
            foreach ($registration->participants as $participant) {
                $participants->push([
                    'type' => 'group_participant',
                    'registration_id' => $registration->id,
                    'participant_id' => $participant->id,
                    'name' => $participant->first_name . ' ' . $participant->last_name,
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'email' => $participant->email,
                    'registration' => $registration,
                    'participant' => $participant,
                ]);
            }
        }

        return $participants;
    }

    /**
     * Generate certificate PDF
     */
    public function generatePDF($registration, $participant = null)
    {
        $userData = $participant 
            ? (object) [
                'first_name' => $participant->first_name,
                'last_name' => $participant->last_name,
                'full_name' => $participant->first_name . ' ' . $participant->last_name,
            ]
            : (object) [
                'first_name' => $registration->user->first_name,
                'last_name' => $registration->user->last_name,
                'full_name' => $registration->user->first_name . ' ' . $registration->user->last_name,
            ];

        $pdf = Pdf::loadView('certificates.template', [
            'registration' => $registration,
            'user' => $userData,
            'package' => $registration->package,
            'participant' => $participant,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'Arial');

        return $pdf;
    }

    /**
     * Preview certificate
     */
    public function preview($registration, $participant = null)
    {
        $pdf = $this->generatePDF($registration, $participant);
        return $pdf->stream('certificate_preview.pdf');
    }

    /**
     * Download certificate
     */
    public function download($registration, $participant = null)
    {
        $pdf = $this->generatePDF($registration, $participant);
        $name = $participant 
            ? $participant->first_name . '_' . $participant->last_name
            : $registration->user->first_name . '_' . $registration->user->last_name;
        return $pdf->download('certificate_' . $name . '.pdf');
    }

    /**
     * Send certificate via email
     */
    public function sendCertificate($registration, $participant = null)
    {
        try {
            SendCertificateJob::dispatch($registration->id, $participant ? $participant->id : null);
            Log::info("Certificate job dispatched for registration #{$registration->id}" . ($participant ? " participant #{$participant->id}" : ""));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to dispatch certificate job: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send bulk certificates
     */
    public function sendBulkCertificates(array $participantData)
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'queued' => 0,
        ];

        foreach ($participantData as $data) {
            $registration = Registration::with(['user', 'package'])->find($data['registration_id']);

            if (!$registration) {
                $results['failed']++;
                $results['errors'][] = "Registration #{$data['registration_id']} not found";
                continue;
            }

            // Verify eligibility
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveCertificate = $registration->payment_status === 'completed' 
                || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveCertificate) {
                $results['failed']++;
                $results['errors'][] = "Registration #{$data['registration_id']} is not eligible for certificate";
                continue;
            }

            $participant = null;
            if (isset($data['participant_id']) && $data['participant_id']) {
                $participant = RegistrationParticipant::where('id', $data['participant_id'])
                    ->where('registration_id', $registration->id)
                    ->first();
                
                if (!$participant) {
                    $results['failed']++;
                    $results['errors'][] = "Participant #{$data['participant_id']} not found";
                    continue;
                }
            }

            try {
                SendCertificateJob::dispatch($registration->id, $participant ? $participant->id : null);
                $results['queued']++;
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Failed to queue certificate for registration #{$data['registration_id']}: " . $e->getMessage();
                Log::error("Failed to dispatch certificate job for registration #{$data['registration_id']}: " . $e->getMessage());
            }
        }

        return $results;
    }
}
