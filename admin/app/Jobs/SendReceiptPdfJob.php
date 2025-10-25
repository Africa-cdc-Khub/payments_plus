<?php

namespace App\Jobs;

use App\Models\Registration;
use App\Services\ExchangeEmailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendReceiptPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

    protected int $registrationId;
    protected string $recipientEmail;

    public function __construct(int $registrationId, string $recipientEmail)
    {
        $this->registrationId = $registrationId;
        $this->recipientEmail = $recipientEmail;
    }

    public function handle(ExchangeEmailService $emailService): void
    {
        Log::info("SendReceiptPdfJob: Starting job for registration {$this->registrationId} to {$this->recipientEmail}");
        
        $registration = Registration::with(['user', 'package', 'participants'])->find($this->registrationId);
        if (!$registration) {
            Log::error("SendReceiptPdfJob: Registration {$this->registrationId} not found");
            return;
        }

        Log::info("SendReceiptPdfJob: Found registration #{$registration->id} for {$registration->user->first_name} {$registration->user->last_name}");

        try {
            // Generate PDF from template
            Log::info("SendReceiptPdfJob: Generating PDF for registration #{$registration->id}");
            $pdf = Pdf::loadView('receipts.pdf', [
                'registration' => $registration,
                'user' => $registration->user,
                'package' => $registration->package,
                'participants' => $registration->participants ?? collect(),
            ]);

            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'Arial');

            $filename = 'receipt_' . $registration->id . '_' . time() . '.pdf';
            $path = 'receipts/' . $filename;
            Storage::put($path, $pdf->output());

            Log::info("SendReceiptPdfJob: PDF generated successfully, filename: {$filename}");

            // Prepare email body
            $emailBody = $this->getEmailBody($registration);

            // Get the full file path for the attachment
            $filePath = Storage::path($path);

            // Send email using Exchange Email Service with PDF attachment
            Log::info("SendReceiptPdfJob: Sending email to {$this->recipientEmail}");
            $result = $emailService->sendEmail(
                $this->recipientEmail,
                'CPHIA 2025 Receipt - Registration #' . $registration->id,
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
                Log::info("SendReceiptPdfJob: Email sent successfully to {$this->recipientEmail}");
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            Log::error("SendReceiptPdfJob: Failed to send email for registration {$this->registrationId}: " . $e->getMessage());
            Log::error("SendReceiptPdfJob: Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Get the email body HTML
     */
    protected function getEmailBody(Registration $registration): string
    {
        return view('emails.receipt', [
            'registration' => $registration,
            'user' => $registration->user,
            'package' => $registration->package,
        ])->render();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendReceiptPdfJob permanently failed for registration #{$this->registrationId}: " . $exception->getMessage());
    }
}
