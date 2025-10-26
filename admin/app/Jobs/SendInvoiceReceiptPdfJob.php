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

class SendInvoiceReceiptPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
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
        Log::info("SendInvoiceReceiptPdfJob: Starting job for invoice {$this->invoiceId} to {$this->recipientEmail}");
        
        $invoice = Invoice::find($this->invoiceId);
        if (!$invoice) {
            Log::error("SendInvoiceReceiptPdfJob: Invoice {$this->invoiceId} not found");
            return;
        }

        Log::info("SendInvoiceReceiptPdfJob: Found invoice {$invoice->id} for {$invoice->biller_name}");

        try {
            // Generate PDF from template
            Log::info("SendInvoiceReceiptPdfJob: Generating PDF for invoice {$invoice->id}");
            $pdf = Pdf::loadView('invoices.receipt', [
                'invoice' => $invoice,
            ]);

            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'Arial');

            $filename = 'receipt_RCPT-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT) . '_' . time() . '.pdf';
            $path = 'receipts/' . $filename;
            Storage::put($path, $pdf->output());

            Log::info("SendInvoiceReceiptPdfJob: PDF generated successfully, filename: {$filename}");

            // Prepare email body
            $emailBody = $this->getEmailBody($invoice);

            // Get the full file path for the attachment
            $filePath = Storage::path($path);

            // Send email using Exchange Email Service with PDF attachment
            Log::info("SendInvoiceReceiptPdfJob: Sending email to {$this->recipientEmail}");
            $result = $emailService->sendEmail(
                $this->recipientEmail,
                'CPHIA 2025 Receipt - RCPT-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
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
                Log::info("SendInvoiceReceiptPdfJob: Email sent successfully to {$this->recipientEmail}");
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            Log::error("SendInvoiceReceiptPdfJob: Failed to send email for invoice {$this->invoiceId}: " . $e->getMessage());
            Log::error("SendInvoiceReceiptPdfJob: Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw to mark job as failed
        }
    }

    protected function getEmailBody(Invoice $invoice): string
    {
        return view('emails.invoice-receipt', [
            'invoice' => $invoice,
        ])->render();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendInvoiceReceiptPdfJob permanently failed for invoice #{$this->invoiceId}: " . $exception->getMessage());
    }
}
