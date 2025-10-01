<?php
/**
 * Complete OAuth Flow and Send Email
 * Handles the complete OAuth authentication and email sending process
 */

require_once 'bootstrap.php';
require_once 'src/ExchangeOAuth.php';

use Cphia2025\ExchangeOAuth;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$testEmail = 'andrewa@africacdc.org';

echo "<h2>CPHIA 2025 - Complete OAuth Flow</h2>";
echo "<p><strong>Target Email:</strong> " . htmlspecialchars($testEmail) . "</p>";

try {
    $oauth = new ExchangeOAuth();
    
    // Check if we have authorization code (OAuth callback)
    if (isset($_GET['code']) && isset($_GET['state'])) {
        echo "<h3>🔄 Processing OAuth Callback</h3>";
        
        $code = $_GET['code'];
        $state = $_GET['state'];
        
        // Exchange code for tokens
        $success = $oauth->exchangeCodeForToken($code, $state);
        
        if ($success) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ OAuth Authentication Successful!</h4>";
            echo "<p>Tokens have been stored. Now sending test email...</p>";
            echo "</div>";
            
            // Send test email using EmailService
            require_once 'src/EmailService.php';
            $emailService = new \Cphia2025\EmailService();
            
            $testSubject = 'CPHIA 2025 - OAuth Email Test';
            $testBody = '
            <html>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                <div style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%); color: white; padding: 20px; text-align: center;">
                    <h1>CPHIA 2025 - OAuth Email Test</h1>
                    <p>Microsoft Graph API with OAuth 2.0</p>
                </div>
                
                <div style="padding: 20px;">
                    <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <h3>✅ OAuth Email Test Successful!</h3>
                        <p>This email confirms that your OAuth 2.0 configuration is working correctly with Microsoft Graph API.</p>
                    </div>
                    
                    <h3>Test Details:</h3>
                    <ul>
                        <li><strong>Authentication:</strong> OAuth 2.0</li>
                        <li><strong>API:</strong> Microsoft Graph</li>
                        <li><strong>Sent At:</strong> ' . date('Y-m-d H:i:s T') . '</li>
                        <li><strong>Recipient:</strong> ' . $testEmail . '</li>
                        <li><strong>System:</strong> CPHIA 2025 Registration System</li>
                    </ul>
                    
                    <div style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <h4>OAuth Benefits:</h4>
                        <ul>
                            <li>Secure token-based authentication</li>
                            <li>No password storage required</li>
                            <li>Automatic token refresh</li>
                            <li>Modern Microsoft Graph API integration</li>
                        </ul>
                    </div>
                    
                    <p>If you received this email, your OAuth configuration is working perfectly!</p>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                    <p>This is an automated test email from the CPHIA 2025 Registration System</p>
                    <p>Generated on ' . date('Y-m-d H:i:s') . ' | OAuth 2.0 Authentication</p>
                </div>
            </body>
            </html>';
            
            $result = $emailService->sendEmail($testEmail, $testSubject, $testBody, true);
            
            if ($result) {
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>✅ Email Sent Successfully!</h4>";
                echo "<p>Test email has been sent to <strong>" . htmlspecialchars($testEmail) . "</strong> using OAuth 2.0</p>";
                echo "<p>Please check the recipient's inbox for the test email.</p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>❌ Email Sending Failed</h4>";
                echo "<p>OAuth authentication succeeded but email sending failed. Please check the error logs.</p>";
                echo "</div>";
            }
            
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>❌ OAuth Authentication Failed</h4>";
            echo "<p>Failed to exchange authorization code for tokens.</p>";
            echo "</div>";
        }
        
    } else {
        // Check if already authenticated
        if ($oauth->isConfigured()) {
            echo "<h3>📧 Sending Test Email (Already Authenticated)</h3>";
            
            require_once 'src/EmailService.php';
            $emailService = new \Cphia2025\EmailService();
            
            $testSubject = 'CPHIA 2025 - OAuth Test Email (Already Authenticated)';
            $testBody = '
            <html>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                <div style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%); color: white; padding: 20px; text-align: center;">
                    <h1>CPHIA 2025 - OAuth Test Email</h1>
                    <p>Microsoft Graph API with OAuth 2.0 (Already Authenticated)</p>
                </div>
                
                <div style="padding: 20px;">
                    <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <h3>✅ OAuth Email Test Successful!</h3>
                        <p>This email confirms that your OAuth 2.0 configuration is working correctly.</p>
                    </div>
                    
                    <p><strong>Sent at:</strong> ' . date('Y-m-d H:i:s T') . '</p>
                    <p><strong>Recipient:</strong> ' . $testEmail . '</p>
                </div>
            </body>
            </html>';
            
            $result = $emailService->sendEmail($testEmail, $testSubject, $testBody, true);
            
            if ($result) {
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>✅ Email Sent Successfully!</h4>";
                echo "<p>Test email has been sent to <strong>" . htmlspecialchars($testEmail) . "</strong></p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>❌ Email Sending Failed</h4>";
                echo "<p>Failed to send test email.</p>";
                echo "</div>";
            }
            
        } else {
            echo "<h3>🔐 OAuth Authentication Required</h3>";
            echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>⚠️ Authentication Required</h4>";
            echo "<p>To send emails using OAuth 2.0, you need to authenticate with Microsoft first.</p>";
            echo "<p><strong>Click the button below to authenticate and automatically send the test email:</strong></p>";
            echo "<p><a href='" . $oauth->getAuthorizationUrl() . "' class='btn btn-primary' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>🔐 Authenticate with Microsoft & Send Email</a></p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Error</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='admin/email-oauth.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to OAuth Setup</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #2E7D32; }
h4 { color: #1B5E20; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
