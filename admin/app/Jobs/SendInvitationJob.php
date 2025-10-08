<?php

namespace App\Jobs;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
    public function handle(): void
    {
        try {
            // Load registration with relationships
            $registration = Registration::with(['user', 'package'])->find($this->registrationId);

            if (!$registration) {
                Log::warning("Registration #{$this->registrationId} not found for invitation sending");
                return;
            }

            if (!$registration->isPaid()) {
                Log::warning("Registration #{$this->registrationId} is not paid, skipping invitation");
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

            // Get PDF content
            $pdfContent = Storage::get($path);

            // Send email
            Mail::send('emails.invitation', [
                'user' => $registration->user,
                'package' => $registration->package,
                'registration' => $registration,
            ], function ($message) use ($registration, $pdfContent) {
                $message->to($registration->user->email, $registration->user->full_name)
                    ->subject('CPHIA 2025 - Invitation Letter')
                    ->attachData($pdfContent, 'invitation.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });

            // Clean up temporary file
            Storage::delete($path);

            Log::info("Invitation sent successfully for registration #{$this->registrationId}");

        } catch (\Exception $e) {
            Log::error("Failed to send invitation for registration #{$this->registrationId}: " . $e->getMessage());
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendInvitationJob permanently failed for registration #{$this->registrationId}: " . $exception->getMessage());
    }
}
