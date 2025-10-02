<?php
/**
 * Test Email Script
 * Sends a test email to agabaandre@gmail.com using the EmailService
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Load Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Cphia2025\EmailService;

echo "📧 CPHIA 2025 Email Test\n";
echo "========================\n\n";

try {
    // Initialize EmailService
    $emailService = new EmailService();
    echo "✅ EmailService initialized successfully\n";
    
    // Test email data
    $testEmail = 'agabaandre@gmail.com';
    $testName = 'Test User';
    $subject = 'CPHIA 2025 - Test Email';
    
    // Create test HTML content
    $htmlContent = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Test Email - CPHIA 2025</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
        <div style="background: #1e40af; color: white; padding: 20px; text-align: center;">
            <h1>CPHIA 2025</h1>
            <p>4th International Conference on Public Health in Africa</p>
        </div>
        
        <div style="padding: 20px; background: #f8fafc;">
            <h2>Test Email</h2>
            <p>Dear ' . $testName . ',</p>
            <p>This is a test email from the CPHIA 2025 Registration System.</p>
            
            <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3>System Status</h3>
                <p><strong>Email Service:</strong> ✅ Working</p>
                <p><strong>Test Time:</strong> ' . date('Y-m-d H:i:s') . '</p>
                <p><strong>Environment:</strong> ' . APP_ENV . '</p>
            </div>
            
            <p>If you received this email, the email system is working correctly!</p>
        </div>
        
        <div style="text-align: center; padding: 20px; background: #e2e8f0; color: #666;">
            <p>Best regards,<br>CPHIA 2025 Team</p>
        </div>
    </body>
    </html>';
    
    // Send the test email
    echo "📤 Sending test email to: $testEmail\n";
    $startTime = microtime(true);
    
    $result = $emailService->sendEmail($testEmail, $subject, $htmlContent, true);
    
    $endTime = microtime(true);
    $executionTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($result) {
        echo "✅ Email sent successfully!\n";
        echo "⏱️  Execution time: {$executionTime}ms\n";
        echo "📧 Check your inbox at $testEmail\n\n";
        
        // Test different email types
        echo "🧪 Testing different email types...\n";
        
        // Test registration confirmation
        $regResult = $emailService->sendRegistrationConfirmation(
            $testEmail,
            $testName,
            'TEST-001',
            'Test Package',
            100.00,
            []
        );
        echo $regResult ? "✅ Registration confirmation sent!\n" : "❌ Registration confirmation failed\n";
        
        // Test payment confirmation
        $payResult = $emailService->sendPaymentConfirmation(
            $testEmail,
            $testName,
            'TEST-001',
            100.00,
            'TEST-TXN-123',
            []
        );
        echo $payResult ? "✅ Payment confirmation sent!\n" : "❌ Payment confirmation failed\n";
        
        // Test admin notification
        $adminResult = $emailService->sendAdminRegistrationNotification(
            'TEST-001',
            $testName,
            $testEmail,
            'Test Package',
            100.00,
            'individual',
            []
        );
        echo $adminResult ? "✅ Admin notification sent!\n" : "❌ Admin notification failed\n";
        
        echo "\n🎉 Test completed successfully!\n";
        
    } else {
        echo "❌ Email sending failed!\n";
        echo "Please check the configuration and try again.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n";
