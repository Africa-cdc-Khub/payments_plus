<?php

namespace App\Jobs;

use App\Models\Registration;
use App\Services\ExchangeEmailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendInvitationJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The registration ID to send invitation for.
     *
     * @var int
     */
    protected $registrationId;

    /**
     * The participant ID (optional - for group registrations).
     *
     * @var int|null
     */
    protected $participantId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $registrationId, ?int $participantId = null)
    {
        $this->registrationId = $registrationId;
        $this->participantId = $participantId;
    }

    /**
     * Execute the job.
     */
    public function handle(ExchangeEmailService $emailService): void
    {
        try {
            // Load registration with relationships
            $registration = Registration::with(['user', 'package', 'participants'])->find($this->registrationId);

            if (!$registration) {
                Log::warning("Registration #{$this->registrationId} not found for invitation sending");
                return;
            }

            // Allow if paid OR if it's an approved delegate
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveInvitation) {
                Log::warning("Registration #{$this->registrationId} is neither paid nor an approved delegate, skipping invitation");
                //return;
            }

            // Determine recipient (participant or primary registrant)
            $participant = null;
            $recipientEmail = $registration->user->email;
            $recipientName = $registration->user->full_name;
            $userData = $registration->user;

            if ($this->participantId) {
                // Sending to a specific participant
                $participant = \App\Models\RegistrationParticipant::where('id', $this->participantId)
                    ->where('registration_id', $this->registrationId)
                    ->first();

                if (!$participant) {
                    Log::warning("Participant #{$this->participantId} not found for registration #{$this->registrationId}");
                    return;
                }

                $recipientEmail = $participant->email;
                $recipientName = $participant->full_name;
                
                // Create user-like object for template
                $userData = (object) [
                    'title' => $participant->title ?? '',
                    'full_name' => $participant->full_name,
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'email' => $participant->email,
                    'nationality' => $participant->nationality,
                    'passport_number' => $participant->passport_number,
                    'delegate_category' => $participant->delegate_category,
                    'airport_of_origin' => $participant->airport_of_origin,
                ];
            }

            // Generate PDF
            $pdf = Pdf::loadView('invitations.template', [
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
            $filename = 'invitation_' . $registration->id . $suffix . '_' . time() . '.pdf';
            $path = 'invitations/' . $filename;
            Storage::put($path, $pdf->output());

            // Prepare email body
            $emailBody = $this->getEmailBody($registration, $userData);

            // Get the full file path for the attachment
            $filePath = Storage::path($path);

            // Send email using Exchange Email Service with PDF attachment
            $result = $emailService->sendEmail(
                $recipientEmail,
                'CPHIA 2025 - Invitation Letter',
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
                    ? "Invitation sent successfully for participant #{$this->participantId} (registration #{$this->registrationId}) to {$recipientEmail}"
                    : "Invitation sent successfully for registration #{$this->registrationId} to {$recipientEmail}";
                Log::info($logMessage);
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            $errorContext = $this->participantId 
                ? "registration #{$this->registrationId}, participant #{$this->participantId}"
                : "registration #{$this->registrationId}";
            Log::error("Failed to send invitation for {$errorContext}: " . $e->getMessage());
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Get the email body HTML
     */
    protected function getEmailBody(Registration $registration, $userData): string
    {
        $userName = $userData->full_name ?? ($userData->first_name ?? 'Participant');
        $packageName = $registration->package->name;
        
        return view('emails.invitation', [
            'user' => $userData,
            'package' => $registration->package,
            'registration' => $registration,
        ])->render();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $errorContext = $this->participantId 
            ? "registration #{$this->registrationId}, participant #{$this->participantId}"
            : "registration #{$this->registrationId}";
        Log::error("SendInvitationJob permanently failed for {$errorContext}: " . $exception->getMessage());
    }
}
