<?php

namespace App\Jobs;

use App\Services\ExchangeEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAdminPasswordReset implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = 60;

    /**
     * Admin credentials
     */
    protected $adminData;
    protected $newPassword;

    /**
     * Create a new job instance.
     */
    public function __construct(array $adminData, string $newPassword)
    {
        $this->adminData = $adminData;
        $this->newPassword = $newPassword;
    }

    /**
     * Execute the job.
     */
    public function handle(ExchangeEmailService $emailService): void
    {
        try {
            // Prepare email body
            $emailBody = view('emails.admin-password-reset', [
                'admin' => $this->adminData,
                'newPassword' => $this->newPassword,
                'loginUrl' => rtrim(config('domains.admin_url'), '/') . '/login',
            ])->render();

            // Send email
            $result = $emailService->sendEmail(
                $this->adminData['email'],
                'CPHIA 2025 Admin - Password Reset',
                $emailBody,
                true
            );

            if ($result) {
                Log::info("Password reset email sent successfully to {$this->adminData['email']}");
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            Log::error("Failed to send password reset email to {$this->adminData['email']}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendAdminPasswordReset permanently failed for {$this->adminData['email']}: " . $exception->getMessage());
    }
}
