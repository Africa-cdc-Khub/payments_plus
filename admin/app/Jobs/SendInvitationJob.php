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
     * Create a new job instance.
     */
    public function __construct(int $registrationId)
    {
        $this->registrationId = $registrationId;
    }

    /**
     * Execute the job.
     */
    public function handle(ExchangeEmailService $emailService): void
    {
        try {
            // Load registration with relationships
            $registration = Registration::with(['user', 'package'])->find($this->registrationId);

            if (!$registration) {
                Log::warning("Registration #{$this->registrationId} not found for invitation sending");
                return;
            }

            // Allow if paid OR if it's an approved delegate
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            $canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');

            if (!$canReceiveInvitation) {
                Log::warning("Registration #{$this->registrationId} is neither paid nor an approved delegate, skipping invitation");
                return;
            }

            // Generate PDF
            $pdf = Pdf::loadView('invitations.template', [
                'registration' => $registration,
                'user' => $registration->user,
                'package' => $registration->package,
            ]);

            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'Arial');

            // Generate filename and store temporarily
            $filename = 'invitation_' . $registration->id . '_' . time() . '.pdf';
            $path = 'invitations/' . $filename;
            Storage::put($path, $pdf->output());

            // Prepare email body
            $emailBody = $this->getEmailBody($registration);

            // Get the full file path for the attachment
            $filePath = Storage::path($path);

            // Send email using Exchange Email Service with PDF attachment
            $result = $emailService->sendEmail(
                $registration->user->email,
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
                Log::info("Invitation sent successfully for registration #{$this->registrationId} to {$registration->user->email}");
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            Log::error("Failed to send invitation for registration #{$this->registrationId}: " . $e->getMessage());
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Get the email body HTML
     */
    protected function getEmailBody(Registration $registration): string
    {
        $userName = $registration->user->full_name;
        $packageName = $registration->package->name;
        
        return view('emails.invitation', [
            'user' => $registration->user,
            'package' => $registration->package,
            'registration' => $registration,
        ])->render();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendInvitationJob permanently failed for registration #{$this->registrationId}: " . $exception->getMessage());
    }
}
