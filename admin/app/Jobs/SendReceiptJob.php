<?php

namespace App\Jobs;

use App\Models\Registration;
use App\Services\ExchangeEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReceiptJob implements ShouldQueue
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
     * The registration ID to send receipt for.
     *
     * @var int
     */
    protected $registrationId;

    /**
     * The payment method (optional).
     *
     * @var string|null
     */
    protected $paymentMethod;

    /**
     * Create a new job instance.
     */
    public function __construct(int $registrationId, ?string $paymentMethod = null)
    {
        $this->registrationId = $registrationId;
        $this->paymentMethod = $paymentMethod;
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
                Log::warning("Registration #{$this->registrationId} not found for receipt sending");
                return;
            }

            // Check if registration is paid
            if ($registration->payment_status !== 'completed') {
                Log::warning("Registration #{$this->registrationId} is not marked as paid, skipping receipt");
                return;
            }

            // Generate QR codes using external API (no imagick dependency)
            $qrCodes = $this->generateQRCodes($registration, $registration->user);

            // Prepare email data
            $templateData = [
                'conference_name' => config('app.conference_name', '4th International Conference on Public Health in Africa'),
                'conference_short_name' => config('app.conference_short_name', 'CPHIA 2025'),
                'conference_dates' => config('app.conference_dates', '22-25 October 2025'),
                'conference_location' => config('app.conference_location', 'Durban, South Africa'),
                'logo_url' => config('app.logo_url', 'https://cphia2025.com/images/logo.png'),
                'mail_from_address' => config('mail.from.address', 'noreply@cphia2025.com'),
                'support_email' => config('app.support_email', 'support@cphia2025.com'),
                'registration_id' => $registration->id,
                'participant_name' => $registration->user->first_name . ' ' . $registration->user->last_name,
                'participant_email' => $registration->user->email,
                'phone' => $registration->user->phone ?? 'N/A',
                'package_name' => $registration->package->name,
                'organization' => $registration->user->organization ?? 'N/A',
                'institution' => $registration->user->institution ?? '',
                'nationality' => $registration->user->nationality ?? 'N/A',
                'payment_date' => now()->format('F j, Y \a\t g:i A'),
                'total_amount' => '$' . number_format($registration->total_amount, 2),
                'payment_method' => $this->paymentMethod ?: 'Online Payment',
                'qr_code' => $qrCodes['main'],
                'verification_qr_code' => $qrCodes['verification'],
                'navigation_qr_code' => $qrCodes['navigation']
            ];

            // Generate email body using Blade template
            $emailBody = view('admin.emails.individual_receipt', $templateData)->render();

            // Send email using Exchange Email Service
            $result = $emailService->sendEmail(
                $registration->user->email,
                "Registration Receipt - {$registration->package->name} - CPHIA 2025",
                $emailBody,
                true // isHtml
            );

            if ($result) {
                Log::info("Receipt sent successfully for registration #{$this->registrationId} to {$registration->user->email}");
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            Log::error("Failed to send receipt for registration #{$this->registrationId}: " . $e->getMessage());
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Generate QR codes using external API
     */
    protected function generateQRCodes(Registration $registration, $user)
    {
        try {
            $mainQrData = json_encode([
                'type' => 'registration',
                'registration_id' => $registration->id,
                'participant_name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'package' => $registration->package->name,
                'amount' => $registration->total_amount,
                'timestamp' => now()->toISOString()
            ]);

            $verificationQrData = json_encode([
                'type' => 'verification',
                'registration_id' => $registration->id,
                'email' => $user->email
            ]);

            $verificationUrl = url("verify_attendance.php?email=" . urlencode($user->email) . "&reg_id=" . $registration->id);

            // Generate QR codes using external API
            $mainQrBase64 = $this->generateQRCodeImage($mainQrData, 200);
            $verificationQrBase64 = $this->generateQRCodeImage($verificationQrData, 120);
            $navigationQrBase64 = $this->generateQRCodeImage($verificationUrl, 100);

            return [
                'main' => $mainQrBase64,
                'verification' => $verificationQrBase64,
                'navigation' => $navigationQrBase64
            ];
        } catch (\Exception $e) {
            Log::error("Failed to generate QR codes: " . $e->getMessage());
            return [
                'main' => $this->getPlaceholderQRCode(),
                'verification' => $this->getPlaceholderQRCode(),
                'navigation' => $this->getPlaceholderQRCode()
            ];
        }
    }

    /**
     * Generate QR code image using external API
     */
    protected function generateQRCodeImage($data, $size = 200)
    {
        try {
            // Try QR Server API first
            $qrServerUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
            $imageData = @file_get_contents($qrServerUrl);
            
            if ($imageData !== false) {
                return 'data:image/png;base64,' . base64_encode($imageData);
            }
            
            // Fallback to Google Charts API
            $googleChartsUrl = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . urlencode($data);
            $imageData = @file_get_contents($googleChartsUrl);
            
            if ($imageData !== false) {
                return 'data:image/png;base64,' . base64_encode($imageData);
            }
            
            // Final fallback to placeholder
            return $this->getPlaceholderQRCode();
            
        } catch (\Exception $e) {
            Log::error("Failed to generate QR code image: " . $e->getMessage());
            return $this->getPlaceholderQRCode();
        }
    }

    /**
     * Get placeholder QR code SVG
     */
    protected function getPlaceholderQRCode()
    {
        return 'data:image/svg+xml;base64,' . base64_encode('<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="200" fill="#f0f0f0"/><text x="100" y="100" text-anchor="middle" fill="#666">QR Code</text></svg>');
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendReceiptJob permanently failed for registration #{$this->registrationId}: " . $exception->getMessage());
    }
}
