<?php
/**
 * Email Testing Script for CPHIA 2025 Registration System
 * This script helps test the email notification system
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Check if .env file exists
if (!file_exists(__DIR__ . '/.env')) {
    die("Please copy env.example to .env and configure your email settings first.");
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Email Test - CPHIA 2025</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .test-section { background: #f8fafc; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #1e40af; }
        .success { border-left-color: #059669; background: #f0fdf4; }
        .error { border-left-color: #dc2626; background: #fef2f2; }
        .btn { background: #1e40af; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
        .btn:hover { background: #1e3a8a; }
        .form-group { margin: 10px 0; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>";

echo "<h1>📧 Email System Test - CPHIA 2025</h1>";

// Test email configuration
echo "<div class='test-section'>";
echo "<h2>Email Configuration Test</h2>";

$config = [
    'MAIL_HOST' => MAIL_HOST,
    'MAIL_PORT' => MAIL_PORT,
    'MAIL_USERNAME' => MAIL_USERNAME,
    'MAIL_FROM_ADDRESS' => MAIL_FROM_ADDRESS,
    'MAIL_FROM_NAME' => MAIL_FROM_NAME,
    'ADMIN_EMAIL' => ADMIN_EMAIL,
    'ENABLE_EMAIL_NOTIFICATIONS' => ENABLE_EMAIL_NOTIFICATIONS ? 'Enabled' : 'Disabled',
    'ADMIN_NOTIFICATIONS' => ADMIN_NOTIFICATIONS ? 'Enabled' : 'Disabled'
];

echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
foreach ($config as $key => $value) {
    echo "<tr><td>{$key}</td><td>" . htmlspecialchars($value) . "</td></tr>";
}
echo "</table>";
echo "</div>";

// Handle test email sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    $testType = $_POST['test_type'];
    
    echo "<div class='test-section'>";
    echo "<h2>Test Results</h2>";
    
    try {
        $emailService = new Cphia2025\EmailService();
        $success = false;
        
        switch ($testType) {
            case 'registration':
                $success = $emailService->sendRegistrationConfirmation(
                    $testEmail,
                    'Test User',
                    'TEST-001',
                    'Test Package',
                    200.00,
                    [
                        ['name' => 'Dr. John Doe', 'email' => 'john@example.com', 'nationality' => 'Ghana'],
                        ['name' => 'Prof. Jane Smith', 'email' => 'jane@example.com', 'nationality' => 'Nigeria']
                    ]
                );
                break;
                
            case 'payment_link':
                $success = $emailService->sendPaymentLink(
                    $testEmail,
                    'Test User',
                    'TEST-001',
                    200.00,
                    'https://example.com/payment-link'
                );
                break;
                
            case 'payment_confirmation':
                $success = $emailService->sendPaymentConfirmation(
                    $testEmail,
                    'Test User',
                    'TEST-001',
                    200.00,
                    'TXN-123456789',
                    [
                        ['name' => 'Dr. John Doe', 'email' => 'john@example.com', 'nationality' => 'Ghana']
                    ]
                );
                break;
                
            case 'admin_registration':
                $success = $emailService->sendAdminRegistrationNotification(
                    'TEST-001',
                    'Test User',
                    $testEmail,
                    'Test Package',
                    200.00,
                    'individual',
                    [
                        ['name' => 'Dr. John Doe', 'email' => 'john@example.com', 'nationality' => 'Ghana']
                    ]
                );
                break;
                
            case 'admin_payment':
                $success = $emailService->sendAdminPaymentNotification(
                    'TEST-001',
                    'Test User',
                    $testEmail,
                    200.00,
                    'TXN-123456789'
                );
                break;
        }
        
        if ($success) {
            echo "<p class='success'>✅ Test email sent successfully to {$testEmail}!</p>";
        } else {
            echo "<p class='error'>❌ Failed to send test email. Check your email configuration and logs.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

// Test form
echo "<div class='test-section'>";
echo "<h2>Send Test Email</h2>";
echo "<form method='POST'>";
echo "<div class='form-group'>";
echo "<label for='test_email'>Test Email Address:</label>";
echo "<input type='email' name='test_email' id='test_email' value='" . (isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : '') . "' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label for='test_type'>Email Type:</label>";
echo "<select name='test_type' id='test_type'>";
echo "<option value='registration'>Registration Confirmation</option>";
echo "<option value='payment_link'>Payment Link</option>";
echo "<option value='payment_confirmation'>Payment Confirmation</option>";
echo "<option value='admin_registration'>Admin Registration Notification</option>";
echo "<option value='admin_payment'>Admin Payment Notification</option>";
echo "</select>";
echo "</div>";

echo "<button type='submit' class='btn'>Send Test Email</button>";
echo "</form>";
echo "</div>";

// Email template preview
echo "<div class='test-section'>";
echo "<h2>Email Template Preview</h2>";
echo "<p>Preview of email templates (using default data):</p>";

$templates = [
    'Registration Confirmation' => 'registration_confirmation',
    'Payment Link' => 'payment_link',
    'Payment Confirmation' => 'payment_confirmation',
    'Admin Registration' => 'admin_registration_notification',
    'Admin Payment' => 'admin_payment_notification'
];

foreach ($templates as $name => $template) {
    echo "<a href='?preview={$template}' class='btn'>Preview {$name}</a>";
}

if (isset($_GET['preview'])) {
    $template = $_GET['preview'];
    echo "<h3>Preview: " . array_search($template, $templates) . "</h3>";
    
    try {
        $emailService = new Cphia2025\EmailService();
        $reflection = new ReflectionClass($emailService);
        $method = $reflection->getMethod('getDefaultTemplate');
        $method->setAccessible(true);
        $content = $method->invoke($emailService, $template);
        
        // Replace template variables with sample data
        $sampleData = [
            'user_name' => 'Dr. John Doe',
            'registration_id' => 'TEST-001',
            'package_name' => 'African Nationals',
            'amount' => '200.00',
            'payment_link' => 'https://example.com/payment',
            'transaction_id' => 'TXN-123456789',
            'conference_name' => CONFERENCE_NAME,
            'conference_short_name' => CONFERENCE_SHORT_NAME,
            'conference_dates' => CONFERENCE_DATES,
            'conference_location' => CONFERENCE_LOCATION,
            'conference_venue' => CONFERENCE_VENUE,
            'logo_url' => EMAIL_LOGO_URL,
            'mail_from_address' => MAIL_FROM_ADDRESS
        ];
        
        foreach ($sampleData as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        echo "<div style='border: 1px solid #ddd; padding: 20px; background: white;'>";
        echo $content;
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p class='error'>Error loading template: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "</div>";

// Troubleshooting
echo "<div class='test-section'>";
echo "<h2>Troubleshooting</h2>";
echo "<h3>Common Issues:</h3>";
echo "<ul>";
echo "<li><strong>SMTP Authentication Failed:</strong> Check MAIL_USERNAME and MAIL_PASSWORD in .env</li>";
echo "<li><strong>Connection Refused:</strong> Verify MAIL_HOST and MAIL_PORT settings</li>";
echo "<li><strong>SSL/TLS Issues:</strong> Try changing MAIL_ENCRYPTION to 'tls' or 'ssl'</li>";
echo "<li><strong>Gmail Issues:</strong> Use App Passwords instead of regular password</li>";
echo "<li><strong>Email Not Received:</strong> Check spam folder and email server logs</li>";
echo "</ul>";

echo "<h3>Gmail Setup:</h3>";
echo "<ol>";
echo "<li>Enable 2-Factor Authentication on your Google account</li>";
echo "<li>Generate an App Password for this application</li>";
echo "<li>Use the App Password as MAIL_PASSWORD in .env</li>";
echo "<li>Set MAIL_USERNAME to your Gmail address</li>";
echo "</ol>";

echo "<h3>Test SMTP Connection:</h3>";
echo "<p>You can test your SMTP settings using online tools like:</p>";
echo "<ul>";
echo "<li><a href='https://www.smtper.net/' target='_blank'>SMTP Tester</a></li>";
echo "<li><a href='https://www.mail-tester.com/' target='_blank'>Mail Tester</a></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
