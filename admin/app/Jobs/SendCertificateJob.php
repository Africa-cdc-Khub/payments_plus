<?php

namespace App\Jobs;

use App\Models\Registration;
use App\Models\RegistrationParticipant;
use App\Services\ExchangeEmailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $registrationId;
    protected $participantId;

    public function __construct($registrationId, $participantId = null)
    {
        $this->registrationId = $registrationId;
        $this->participantId = $participantId;
    }

    public function handle(ExchangeEmailService $emailService)
    {
        try {
            $registration = Registration::with(['user', 'package'])->findOrFail($this->registrationId);

            // Verify eligibility
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveCertificate = $registration->payment_status === 'completed' 
                || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveCertificate) {
                Log::warning("SendCertificateJob: Registration #{$this->registrationId} is not eligible for certificate");
                return;
            }

            $participant = null;
            $recipientEmail = $registration->user->email;
            $recipientName = $registration->user->first_name . ' ' . $registration->user->last_name;

            if ($this->participantId) {
                $participant = RegistrationParticipant::where('id', $this->participantId)
                    ->where('registration_id', $registration->id)
                    ->first();

                if ($participant) {
                    $recipientEmail = $participant->email;
                    $recipientName = $participant->first_name . ' ' . $participant->last_name;
                }
            }

            // Generate PDF
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

            // Generate filename and store temporarily
            $suffix = $participant ? "_participant_{$participant->id}" : '';
            $filename = 'certificate_' . $registration->id . $suffix . '_' . time() . '.pdf';
            $path = 'certificates/' . $filename;
            Storage::put($path, $pdf->output());

            // Prepare email body
            $emailBody = view('emails.certificate', [
                'registration' => $registration,
                'user' => $userData,
                'participant' => $participant,
            ])->render();

            // Get the full file path for the attachment
            $filePath = Storage::path($path);

            // Send email using Exchange Email Service with PDF attachment
            $result = $emailService->sendEmail(
                $recipientEmail,
                'CPHIA 2025 - Certificate of Attendance',
                $emailBody,
                true, // isHtml
                null, // fromEmail (use default)
                null, // fromName (use default)
                [], // cc
                [], // bcc
                [$filePath] // attachments array
            );

            // Clean up temporary file
            Storage::delete($path);

            if ($result) {
                $logMessage = $participant 
                    ? "Certificate sent successfully for participant #{$this->participantId} (registration #{$this->registrationId}) to {$recipientEmail}"
                    : "Certificate sent successfully for registration #{$this->registrationId} to {$recipientEmail}";
                Log::info($logMessage);
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            Log::error('SendCertificateJob failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
