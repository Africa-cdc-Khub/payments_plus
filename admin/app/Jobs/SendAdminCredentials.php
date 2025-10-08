<?php

namespace App\Jobs;

use App\Services\ExchangeEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAdminCredentials implements ShouldQueue
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
    protected $plainPassword;

    /**
     * Create a new job instance.
     */
    public function __construct(array $adminData, string $plainPassword)
    {
        $this->adminData = $adminData;
        $this->plainPassword = $plainPassword;
    }

    /**
     * Execute the job.
     */
    public function handle(ExchangeEmailService $emailService): void
    {
        try {
            // Prepare email body
            $emailBody = view('emails.admin-credentials', [
                'admin' => $this->adminData,
                'password' => $this->plainPassword,
                'loginUrl' => config('app.url') . '/admin/login',
            ])->render();

            // Send email
            $result = $emailService->sendEmail(
                $this->adminData['email'],
                'CPHIA 2025 Admin - Your Account Credentials',
                $emailBody,
                true
            );

            if ($result) {
                Log::info("Admin credentials sent successfully to {$this->adminData['email']}");
            } else {
                throw new \Exception("Email service returned false");
            }

        } catch (\Exception $e) {
            Log::error("Failed to send admin credentials to {$this->adminData['email']}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendAdminCredentials permanently failed for {$this->adminData['email']}: " . $exception->getMessage());
    }
}
