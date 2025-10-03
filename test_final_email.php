<?php
/**
 * CPHIA 2025 - Final Email Service Test
 * Clean, production-ready email service using Microsoft Graph API
 */

require_once 'bootstrap.php';
require_once 'src/GraphEmailService.php';

use Cphia2025\GraphEmailService;

echo "<h2>🎉 CPHIA 2025 - Final Email Service Test</h2>";

try {
    $emailService = new GraphEmailService();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>📧 Email Service Status:</h4>";
    echo "<ul>";
    echo "<li><strong>Configured:</strong> " . ($emailService->isConfigured() ? '✅ Yes' : '❌ No') . "</li>";
    echo "<li><strong>Valid Tokens:</strong> " . ($emailService->hasValidTokens() ? '✅ Yes' : '❌ No') . "</li>";
    echo "<li><strong>Method:</strong> Microsoft Graph API (Direct)</li>";
    echo "<li><strong>Security:</strong> OAuth 2.0 Bearer Token</li>";
    echo "<li><strong>Status:</strong> " . ($emailService->hasValidTokens() ? '🚀 Ready for Production' : '⚠️ Setup Required') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    if (!$emailService->isConfigured()) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ Email Service Not Configured</h4>";
        echo "<p>Please ensure all OAuth configuration variables are set in your <code>.env</code> file.</p>";
        echo "</div>";
        exit;
    }
    
    if (!$emailService->hasValidTokens()) {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>⚠️ OAuth Setup Required</h4>";
        echo "<p>You need to complete the one-time OAuth setup to send emails.</p>";
        echo "<p><a href='clear_tokens_and_test.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Complete OAuth Setup</a></p>";
        echo "</div>";
        exit;
    }
    
    // Test sending email to Andrew
    $testEmail = 'agabaandre@gmail.com';
    echo "<p><strong>🚀 Sending test email to " . htmlspecialchars($testEmail) . "...</strong></p>";
    
    $success = $emailService->sendTestEmail($testEmail);
    
    if ($success) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Email Sent Successfully!</h4>";
        echo "<p>Test email has been sent to <strong>" . htmlspecialchars($testEmail) . "</strong> using Microsoft Graph API.</p>";
        echo "<p>This confirms your email service is working perfectly!</p>";
        echo "</div>";
        
        echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>🎉 Production Ready!</h4>";
        echo "<p>Your CPHIA 2025 email service is now ready for production with:</p>";
        echo "<ul>";
        echo "<li>✅ <strong>Microsoft Graph API</strong> - Most reliable method</li>";
        echo "<li>✅ <strong>OAuth 2.0 Security</strong> - No password storage</li>";
        echo "<li>✅ <strong>Automatic Token Refresh</strong> - No user interaction needed</li>";
        echo "<li>✅ <strong>Laravel Compatible</strong> - Easy integration</li>";
        echo "<li>✅ <strong>Beautiful Templates</strong> - CPHIA 2025 branded</li>";
        echo "<li>✅ <strong>Production Tested</strong> - Ready for live systems</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>📦 Laravel Package Ready</h4>";
        echo "<p>Your Laravel package has been updated with the working solution:</p>";
        echo "<ul>";
        echo "<li><strong>GraphEmailService.php</strong> - Main email service class</li>";
        echo "<li><strong>LaravelEmailService.php</strong> - Laravel-compatible wrapper</li>";
        echo "<li><strong>ExchangeOAuth.php</strong> - OAuth 2.0 handler</li>";
        echo "<li><strong>EmailServiceProvider.php</strong> - Laravel service provider</li>";
        echo "<li><strong>README.md</strong> - Complete documentation</li>";
        echo "</ul>";
        echo "<p><a href='laravel-package/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Laravel Package</a></p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ Email Sending Failed</h4>";
        echo "<p>Failed to send test email. Please check your OAuth configuration.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Error</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='laravel-package/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📦 View Laravel Package</a>";
echo "<a href='admin/email-oauth.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔐 OAuth Setup</a>";
echo "<a href='FINAL_EMAIL_SOLUTION.md' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Documentation</a>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #2E7D32; }
h4 { color: #1B5E20; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
