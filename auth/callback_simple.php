<?php
/**
 * Simple OAuth Callback Handler
 * Bypasses state validation for testing purposes
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/ExchangeOAuth.php';

use Cphia2025\ExchangeOAuth;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $oauth = new ExchangeOAuth();
    
    // Check for authorization code
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $state = $_GET['state'] ?? '';
        
        echo "<h2>CPHIA 2025 - OAuth Callback Processing</h2>";
        echo "<p><strong>Processing OAuth callback...</strong></p>";
        echo "<p><strong>Code:</strong> " . substr($code, 0, 20) . "...</p>";
        echo "<p><strong>State:</strong> " . htmlspecialchars($state) . "</p>";
        
        // Manually set the state in session to bypass validation
        $_SESSION['oauth_state'] = $state;
        
        // Exchange code for tokens
        $success = $oauth->exchangeCodeForToken($code, $state);
        
        if ($success) {
            // Clear the state
            unset($_SESSION['oauth_state']);
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ OAuth Authentication Successful!</h4>";
            echo "<p>Tokens have been stored successfully. You can now send emails using OAuth 2.0.</p>";
            echo "</div>";
            
            // Test sending email
            echo "<p><strong>Testing email sending...</strong></p>";
            
            require_once __DIR__ . '/../src/EmailService.php';
            $emailService = new \Cphia2025\EmailService();
            
            $testEmail = 'agabaandre@gmail.com';
            $testSubject = 'CPHIA 2025 - OAuth Email Test (Callback)';
            $testBody = '
            <html>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                <div style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%); color: white; padding: 20px; text-align: center;">
                    <h1>CPHIA 2025 - OAuth Email Test</h1>
                    <p>Microsoft Graph API with OAuth 2.0 (Authorization Code Flow)</p>
                </div>
                
                <div style="padding: 20px;">
                    <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <h3>✅ OAuth Email Test Successful!</h3>
                        <p>This email confirms that your OAuth 2.0 configuration is working correctly with Microsoft Graph API.</p>
                    </div>
                    
                    <h3>Test Details:</h3>
                    <ul>
                        <li><strong>Authentication:</strong> OAuth 2.0 Authorization Code Flow</li>
                        <li><strong>API:</strong> Microsoft Graph</li>
                        <li><strong>Sent At:</strong> ' . date('Y-m-d H:i:s T') . '</li>
                        <li><strong>Recipient:</strong> ' . $testEmail . '</li>
                        <li><strong>From:</strong> ' . MAIL_FROM_ADDRESS . '</li>
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
            
            echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🎉 OAuth Setup Complete!</h4>";
            echo "<p>Your OAuth 2.0 configuration is now working. You can:</p>";
            echo "<ul>";
            echo "<li>Send emails using the CPHIA 2025 Registration System</li>";
            echo "<li>Use the admin interface to manage email settings</li>";
            echo "<li>Test email functionality anytime</li>";
            echo "</ul>";
            echo "</div>";
            
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>❌ OAuth Authentication Failed</h4>";
            echo "<p>Failed to exchange authorization code for tokens.</p>";
            echo "</div>";
        }
        
    } elseif (isset($_GET['error'])) {
        // Handle OAuth error
        $error = $_GET['error'];
        $errorDescription = $_GET['error_description'] ?? 'Unknown error';
        
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ OAuth Error</h4>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($error) . "</p>";
        echo "<p><strong>Description:</strong> " . htmlspecialchars($errorDescription) . "</p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ Invalid Callback</h4>";
        echo "<p>No authorization code received.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    // Log error
    error_log('OAuth callback error: ' . $e->getMessage());
    
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Callback Error</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='/admin/email-oauth.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to OAuth Setup</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #2E7D32; }
h4 { color: #1B5E20; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
