<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExchangeEmailService;
use Illuminate\Support\Facades\Log;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {recipient?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email using Exchange Email Service';

    /**
     * Execute the console command.
     */
    public function handle(ExchangeEmailService $emailService)
    {
        $recipient = $this->argument('recipient') ?? 'henrynkukemayanja@gmail.com';
        
        $this->info("Sending test email to: {$recipient}");
        
        try {
            $result = $emailService->sendEmail(
                $recipient,
                'Test Email from Laravel Admin',
                '<html>
                    <body>
                        <h2>Test Email</h2>
                        <p>This is a test email sent from the Laravel Admin application using Microsoft Exchange Email Service.</p>
                        <p><strong>Sent at:</strong> ' . now()->format('Y-m-d H:i:s') . '</p>
                        <hr>
                        <p style="color: #666; font-size: 12px;">
                            This email was sent from the CPHIA 2025 Registration System.
                        </p>
                    </body>
                </html>'
            );
            
            if ($result) {
                $this->info("✓ Test email sent successfully to {$recipient}!");
                return Command::SUCCESS;
            } else {
                $this->error("✗ Failed to send test email to {$recipient}");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("✗ Error sending test email: " . $e->getMessage());
            Log::error('Test email failed', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
