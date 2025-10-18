<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\ExchangeEmailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendInvoiceEmailJob implements ShouldQueue
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

    protected int $invoiceId;
    protected string $recipientEmail;

    public function __construct(int $invoiceId, string $recipientEmail)
    {
        $this->invoiceId = $invoiceId;
        $this->recipientEmail = $recipientEmail;
    }

    public function handle(ExchangeEmailService $emailService): void
    {
        Log::info("SendInvoiceEmailJob: Starting job for invoice {$this->invoiceId} to {$this->recipientEmail}");
        
        $invoice = Invoice::find($this->invoiceId);
        if (!$invoice) {
            Log::error("SendInvoiceEmailJob: Invoice {$this->invoiceId} not found");
            return;
        }

        Log::info("SendInvoiceEmailJob: Found invoice {$invoice->invoice_number} for {$invoice->biller_name}");

        try {
            // Generate PDF from template
            Log::info("SendInvoiceEmailJob: Generating PDF for invoice {$invoice->invoice_number}");
            $pdf = Pdf::loadView('invoices.template', [
                'invoice' => $invoice,
            ]);

            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'Arial');

            $filename = 'invoice_' . $invoice->invoice_number . '_' . time() . '.pdf';
            $path = 'invoices/' . $filename;
            Storage::put($path, $pdf->output());

            Log::info("SendInvoiceEmailJob: PDF generated successfully, filename: {$filename}");

            // Prepare email body
            $emailBody = $this->getEmailBody($invoice);

            // Get the full file path for the attachment
            $filePath = Storage::path($path);

            // Send email using Exchange Email Service with PDF attachment
            Log::info("SendInvoiceEmailJob: Sending email to {$this->recipientEmail}");
            $result = $emailService->sendEmail(
                $this->recipientEmail,
                'CPHIA 2025 Invoice',
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
                Log::info("SendInvoiceEmailJob: Email sent successfully to {$this->recipientEmail}");
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            Log::error("SendInvoiceEmailJob: Failed to send email for invoice {$this->invoiceId}: " . $e->getMessage());
            Log::error("SendInvoiceEmailJob: Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Get the email body HTML
     */
    protected function getEmailBody(Invoice $invoice): string
    {
        return view('emails.invoice', [
            'invoice' => $invoice,
        ])->render();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendInvoiceEmailJob permanently failed for invoice #{$this->invoiceId}: " . $exception->getMessage());
    }
}


