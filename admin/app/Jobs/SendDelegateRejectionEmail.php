<?php

namespace App\Jobs;

use App\Models\Registration;
use App\Services\ExchangeEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDelegateRejectionEmail implements ShouldQueue
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
     * The registration ID to send rejection email for.
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
                Log::warning("Registration #{$this->registrationId} not found for rejection email");
                return;
            }

            // Verify this is a rejected delegate
            $isDelegate = $registration->package_id == config('app.delegate_package_id');
            if (!$isDelegate || $registration->status !== 'rejected') {
                Log::warning("Registration #{$this->registrationId} is not a rejected delegate, skipping email");
                return;
            }

            // Prepare email body
            $emailBody = $this->getEmailBody($registration);

            // Send email using Exchange Email Service
            $result = $emailService->sendEmail(
                $registration->user->email,
                'CPHIA 2025 - Delegate Registration Status',
                $emailBody,
                true, // isHtml
                null, // fromEmail (use default)
                null, // fromName (use default)
                [], // cc
                [], // bcc
                [] // no attachments
            );

            if ($result) {
                Log::info("Rejection email sent successfully for registration #{$this->registrationId} to {$registration->user->email}");
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            Log::error("Failed to send rejection email for registration #{$this->registrationId}: " . $e->getMessage());
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Get the email body HTML
     */
    protected function getEmailBody(Registration $registration): string
    {
        return view('emails.delegate-rejection', [
            'user' => $registration->user,
            'registration' => $registration,
            'reason' => $registration->rejection_reason,
        ])->render();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendDelegateRejectionEmail permanently failed for registration #{$this->registrationId}: " . $exception->getMessage());
    }
}
