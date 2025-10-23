<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\User;
use App\Models\Package;
use App\Models\Payment;
use App\Models\RegistrationParticipant;
use App\Services\ExchangeEmailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReceiptEmailService
{
    private $emailService;

    public function __construct()
    {
        $this->emailService = new ExchangeEmailService();
    }

    /**
     * Send receipt email for a registration
     */
    public function sendReceiptEmail(Registration $registration, $paymentMethod = null)
    {
        try {
            $user = $registration->user;
            $package = $registration->package;
            $payment = $registration->payment;

            if (!$user || !$package) {
                Log::error("Missing user or package for registration {$registration->id}");
                return false;
            }

            // Get payment method from payment record or parameter
            $actualPaymentMethod = $paymentMethod ?: ($payment ? $payment->payment_method : 'online');
            $paymentDate = $payment ? $payment->payment_date : now();

            if ($registration->registration_type === 'group') {
                return $this->sendGroupReceiptEmail($registration, $user, $package, $actualPaymentMethod, $paymentDate);
            } else {
                return $this->sendIndividualReceiptEmail($registration, $user, $package, $actualPaymentMethod, $paymentDate);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send receipt email for registration {$registration->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send individual receipt email
     */
    private function sendIndividualReceiptEmail(Registration $registration, User $user, Package $package, $paymentMethod, $paymentDate)
    {
        try {
            // Generate QR codes
            $qrCodes = $this->generateQRCodes($registration, $user);

            $templateData = [
                'conference_name' => config('app.conference_name', '4th International Conference on Public Health in Africa'),
                'conference_short_name' => config('app.conference_short_name', 'CPHIA 2025'),
                'conference_dates' => config('app.conference_dates', '22-25 October 2025'),
                'conference_location' => config('app.conference_location', 'Durban, South Africa'),
                'logo_url' => config('app.logo_url', 'https://cphia2025.com/images/logo.png'),
                'mail_from_address' => config('mail.from.address', 'noreply@cphia2025.com'),
                'support_email' => config('app.support_email', 'support@cphia2025.com'),
                'registration_id' => $registration->id,
                'participant_name' => $user->first_name . ' ' . $user->last_name,
                'participant_email' => $user->email,
                'phone' => $user->phone ?? 'N/A',
                'package_name' => $package->name,
                'organization' => $user->organization ?? 'N/A',
                'institution' => $user->institution ?? '',
                'nationality' => $user->nationality ?? 'N/A',
                'payment_date' => $paymentDate->format('F j, Y \a\t g:i A'),
                'total_amount' => '$' . number_format($registration->total_amount, 2),
                'qr_code' => $qrCodes['main'],
                'verification_qr_code' => $qrCodes['verification'],
                'navigation_qr_code' => $qrCodes['navigation']
            ];

            // Generate email body using Blade template
            $emailBody = view('admin.emails.individual_receipt', $templateData)->render();

            // Send email using Exchange Email Service
            $result = $this->emailService->sendEmail(
                $user->email,
                "Registration Receipt - {$package->name} - CPHIA 2025",
                $emailBody,
                true // isHtml
            );

            if ($result) {
                Log::info("Individual receipt email sent for registration {$registration->id}");
                return true;
            } else {
                Log::error("Failed to send individual receipt email for registration {$registration->id}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to send individual receipt email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send group receipt email
     */
    private function sendGroupReceiptEmail(Registration $registration, User $user, Package $package, $paymentMethod, $paymentDate)
    {
        try {
            // Get group participants
            $participants = RegistrationParticipant::where('registration_id', $registration->id)->get();
            
            if ($participants->isEmpty()) {
                Log::warning("No participants found for group registration {$registration->id}");
                return $this->sendIndividualReceiptEmail($registration, $user, $package, $paymentMethod, $paymentDate);
            }

            // Generate QR codes for all participants
            $participantsData = [];
            $qrCodes = [];
            $verificationQrCodes = [];
            $navigationQrCodes = [];

            foreach ($participants as $index => $participant) {
                $participantUser = new User([
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'email' => $participant->email,
                    'phone' => $participant->phone ?? 'N/A',
                    'organization' => $participant->organization ?? 'N/A',
                    'institution' => $participant->institution ?? '',
                    'nationality' => $participant->nationality ?? 'N/A'
                ]);

                $participantQrCodes = $this->generateQRCodes($registration, $participantUser, $index);
                
                $participantsData[] = [
                    'name' => $participant->first_name . ' ' . $participant->last_name,
                    'email' => $participant->email,
                    'phone' => $participant->phone ?? 'N/A',
                    'organization' => $participant->organization ?? 'N/A',
                    'institution' => $participant->institution ?? '',
                    'nationality' => $participant->nationality ?? 'N/A',
                    'payment_date' => $paymentDate->format('F j, Y \a\t g:i A')
                ];

                $qrCodes[] = $participantQrCodes['main'];
                $verificationQrCodes[] = $participantQrCodes['verification'];
                $navigationQrCodes[] = $participantQrCodes['navigation'];
            }

            $templateData = [
                'conference_name' => config('app.conference_name', '4th International Conference on Public Health in Africa'),
                'conference_short_name' => config('app.conference_short_name', 'CPHIA 2025'),
                'conference_dates' => config('app.conference_dates', '22-25 October 2025'),
                'conference_location' => config('app.conference_location', 'Durban, South Africa'),
                'logo_url' => config('app.logo_url', 'https://cphia2025.com/images/logo.png'),
                'mail_from_address' => config('mail.from.address', 'noreply@cphia2025.com'),
                'support_email' => config('app.support_email', 'support@cphia2025.com'),
                'registration_id' => $registration->id,
                'focal_person_name' => $user->first_name . ' ' . $user->last_name,
                'focal_person_email' => $user->email,
                'package_name' => $package->name,
                'total_amount' => '$' . number_format($registration->total_amount, 2),
                'payment_date' => $paymentDate->format('F j, Y \a\t g:i A'),
                'participants' => $participantsData,
                'qr_codes' => $qrCodes,
                'verification_qr_codes' => $verificationQrCodes,
                'navigation_qr_codes' => $navigationQrCodes
            ];

            // Generate email body using Blade template
            $emailBody = view('admin.emails.group_receipt', $templateData)->render();

            // Send email using Exchange Email Service
            $result = $this->emailService->sendEmail(
                $user->email,
                "Group Registration Receipt - {$package->name} - CPHIA 2025",
                $emailBody,
                true // isHtml
            );

            if ($result) {
                Log::info("Group receipt email sent for registration {$registration->id}");
                return true;
            } else {
                Log::error("Failed to send group receipt email for registration {$registration->id}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to send group receipt email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate QR codes for a participant
     */
    private function generateQRCodes(Registration $registration, $user, $participantIndex = 0)
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
                'participant_index' => $participantIndex,
                'email' => $user->email
            ]);

            $verificationUrl = url("verify_attendance.php?email=" . urlencode($user->email) . "&reg_id=" . $registration->id);

            // Generate QR codes using SimpleSoftwareIO QR Code library
            $mainQrBase64 = 'data:image/png;base64,' . base64_encode(
                QrCode::format('png')
                    ->size(200)
                    ->margin(1)
                    ->generate($mainQrData)
            );

            $verificationQrBase64 = 'data:image/png;base64,' . base64_encode(
                QrCode::format('png')
                    ->size(120)
                    ->margin(1)
                    ->generate($verificationQrData)
            );

            $navigationQrBase64 = 'data:image/png;base64,' . base64_encode(
                QrCode::format('png')
                    ->size(100)
                    ->margin(1)
                    ->generate($verificationUrl)
            );

            return [
                'main' => $mainQrBase64,
                'verification' => $verificationQrBase64,
                'navigation' => $navigationQrBase64
            ];
        } catch (\Exception $e) {
            Log::error("Failed to generate QR codes: " . $e->getMessage());
            
            // Return placeholder images if QR generation fails
            return [
                'main' => 'data:image/svg+xml;base64,' . base64_encode('<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="200" fill="#f0f0f0"/><text x="100" y="100" text-anchor="middle" fill="#666">QR Code</text></svg>'),
                'verification' => 'data:image/svg+xml;base64,' . base64_encode('<svg width="120" height="120" xmlns="http://www.w3.org/2000/svg"><rect width="120" height="120" fill="#f0f0f0"/><text x="60" y="60" text-anchor="middle" fill="#666">QR</text></svg>'),
                'navigation' => 'data:image/svg+xml;base64,' . base64_encode('<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="#f0f0f0"/><text x="50" y="50" text-anchor="middle" fill="#666">QR</text></svg>')
            ];
        }
    }


    /**
     * Send receipt email manually (for admin trigger)
     */
    public function sendManualReceiptEmail($registrationId)
    {
        try {
            $registration = Registration::with(['user', 'package', 'payment'])->find($registrationId);
            
            if (!$registration) {
                Log::error("Registration not found: {$registrationId}");
                return false;
            }

            if ($registration->payment_status !== 'completed') {
                Log::warning("Registration {$registrationId} is not marked as paid, cannot send receipt");
                return false;
            }

            return $this->sendReceiptEmail($registration);
        } catch (\Exception $e) {
            Log::error("Failed to send manual receipt email for registration {$registrationId}: " . $e->getMessage());
            return false;
        }
    }
}
