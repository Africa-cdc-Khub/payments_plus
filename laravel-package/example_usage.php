<?php
/**
 * Example Usage for CPHIA 2025 Laravel Email Service
 * 
 * This file shows practical examples of how to use the email service in your Laravel application
 */

// Example 1: Controller Usage
echo "<h2>Laravel Email Service - Example Usage</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>1. Controller Example</h3>";
echo "<p>RegistrationController with email sending:</p>";
echo "</div>";

$controllerExample = '<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cphia2025\LaravelEmailService;

class RegistrationController extends Controller
{
    protected $emailService;
    
    public function __construct(LaravelEmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            \'name\' => \'required|string|max:255\',
            \'email\' => \'required|email\',
            \'package\' => \'required|string\',
            \'amount\' => \'required|numeric\',
        ]);
        
        // Create registration record
        $registration = Registration::create($validated);
        
        // Send confirmation email
        try {
            $registrationData = [
                \'name\' => $registration->name,
                \'package\' => $registration->package,
                \'amount\' => \'$\' . $registration->amount,
                \'registration_id\' => $registration->id,
            ];
            
            $this->emailService->sendRegistrationConfirmation(
                $registration->email, 
                $registrationData
            );
            
            return response()->json([
                \'message\' => \'Registration successful and confirmation email sent!\',
                \'registration_id\' => $registration->id
            ]);
            
        } catch (\\Exception $e) {
            // Log error but don\'t fail registration
            \\Log::error(\'Email sending failed: \' . $e->getMessage());
            
            return response()->json([
                \'message\' => \'Registration successful but email failed to send\',
                \'registration_id\' => $registration->id,
                \'warning\' => \'Please check your email settings\'
            ]);
        }
    }
    
    public function processPayment(Request $request)
    {
        // Process payment logic here...
        
        // Send payment confirmation
        try {
            $paymentData = [
                \'name\' => $request->name,
                \'amount\' => \'$\' . $request->amount,
                \'transaction_id\' => $request->transaction_id,
                \'payment_method\' => $request->payment_method,
            ];
            
            $this->emailService->sendPaymentConfirmation(
                $request->email, 
                $paymentData
            );
            
            return response()->json([\'message\' => \'Payment processed and confirmation sent!\']);
            
        } catch (\\Exception $e) {
            \\Log::error(\'Payment confirmation email failed: \' . $e->getMessage());
            return response()->json([\'message\' => \'Payment processed but confirmation email failed\']);
        }
    }
}';

echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px;'>";
echo htmlspecialchars($controllerExample);
echo "</pre>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>2. Queue Job Example</h3>";
echo "<p>Background email processing with queue:</p>";
echo "</div>";

$jobExample = '<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cphia2025\LaravelEmailService;

class SendRegistrationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $email;
    protected $registrationData;
    
    public function __construct($email, $registrationData)
    {
        $this->email = $email;
        $this->registrationData = $registrationData;
    }
    
    public function handle(LaravelEmailService $emailService)
    {
        try {
            $emailService->sendRegistrationConfirmation($this->email, $this->registrationData);
            \\Log::info(\'Registration email sent successfully to \' . $this->email);
        } catch (\\Exception $e) {
            \\Log::error(\'Failed to send registration email: \' . $e->getMessage());
            // Optionally, you can fail the job or retry
            $this->fail($e);
        }
    }
    
    public function failed(\\Throwable $exception)
    {
        \\Log::error(\'Registration email job failed permanently: \' . $exception->getMessage());
    }
}';

echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px;'>";
echo htmlspecialchars($jobExample);
echo "</pre>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>3. Service Usage Example</h3>";
echo "<p>Using the service in other parts of your application:</p>";
echo "</div>";

$serviceExample = '<?php

namespace App\Services;

use Cphia2025\LaravelEmailService;

class RegistrationService
{
    protected $emailService;
    
    public function __construct(LaravelEmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    
    public function processRegistration($data)
    {
        // Process registration logic...
        $registration = $this->createRegistration($data);
        
        // Send emails
        $this->sendRegistrationEmails($registration);
        
        return $registration;
    }
    
    private function sendRegistrationEmails($registration)
    {
        // Send confirmation to user
        $this->emailService->sendRegistrationConfirmation(
            $registration->email,
            [
                \'name\' => $registration->name,
                \'package\' => $registration->package,
                \'amount\' => \'$\' . $registration->amount,
                \'registration_id\' => $registration->id,
            ]
        );
        
        // Send notification to admin
        $this->emailService->sendAdminNotification(
            config(\'mail.admin_email\'),
            [
                \'type\' => \'New Registration\',
                \'name\' => $registration->name,
                \'email\' => $registration->email,
                \'details\' => $registration->package . \' - $\' . $registration->amount,
            ]
        );
    }
}';

echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px;'>";
echo htmlspecialchars($serviceExample);
echo "</pre>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>4. OAuth Setup Routes</h3>";
echo "<p>Routes for OAuth authentication setup:</p>";
echo "</div>";

$routesExample = '<?php

// In routes/web.php

use Cphia2025\LaravelEmailService;

// OAuth setup route
Route::get(\'/email/oauth\', function (LaravelEmailService $emailService) {
    if (!$emailService->isConfigured()) {
        return response()->json([\'error\' => \'Email service not configured\'], 500);
    }
    
    if ($emailService->hasValidTokens()) {
        return response()->json([\'message\' => \'OAuth already configured\']);
    }
    
    $oauthUrl = $emailService->getOAuthUrl();
    return redirect($oauthUrl);
})->name(\'email.oauth\');

// OAuth callback route
Route::get(\'/email/oauth/callback\', function (Request $request, LaravelEmailService $emailService) {
    $code = $request->get(\'code\');
    $state = $request->get(\'state\');
    
    if (!$code || !$state) {
        return response()->json([\'error\' => \'Invalid callback parameters\'], 400);
    }
    
    try {
        $success = $emailService->processOAuthCallback($code, $state);
        
        if ($success) {
            return response()->json([\'message\' => \'OAuth setup successful!\']);
        } else {
            return response()->json([\'error\' => \'OAuth setup failed\'], 500);
        }
    } catch (\\Exception $e) {
        return response()->json([\'error\' => $e->getMessage()], 500);
    }
})->name(\'email.oauth.callback\');

// Test email route (for development)
Route::get(\'/email/test\', function (LaravelEmailService $emailService) {
    try {
        $result = $emailService->sendEmail(
            \'test@example.com\',
            \'Test Email\',
            \'<h1>This is a test email</h1>\'
        );
        
        return response()->json([\'success\' => $result]);
    } catch (\\Exception $e) {
        return response()->json([\'error\' => $e->getMessage()], 500);
    }
})->name(\'email.test\');';

echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px;'>";
echo htmlspecialchars($routesExample);
echo "</pre>";

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>✅ OAuth Authentication - One Time Only!</h3>";
echo "<p><strong>Important:</strong> You only need to complete OAuth authentication ONCE!</p>";
echo "<ul>";
echo "<li>✅ Complete OAuth setup at <code>/email/oauth</code></li>";
echo "<li>✅ Tokens are stored in database</li>";
echo "<li>✅ Tokens auto-refresh automatically</li>";
echo "<li>✅ No more user interaction needed</li>";
echo "<li>✅ Perfect for production systems</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>🚀 Ready for Production!</h3>";
echo "<p>Your Laravel email service is now ready with:</p>";
echo "<ul>";
echo "<li>✅ OAuth 2.0 authentication (one-time setup)</li>";
echo "<li>✅ Automatic token refresh</li>";
echo "<li>✅ Queue support for background processing</li>";
echo "<li>✅ Pre-built email templates</li>";
echo "<li>✅ Error handling and logging</li>";
echo "<li>✅ Clean, reusable API</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><a href='../test_working_email.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Email Service</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #2E7D32; }
h4 { color: #1B5E20; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
pre { font-size: 12px; }
</style>
