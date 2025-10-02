<?php
/**
 * Clear OAuth Tokens and Test Fresh Authentication
 */

require_once 'bootstrap.php';
require_once 'db_connector.php';
require_once 'src/ExchangeOAuth.php';

use Cphia2025\ExchangeOAuth;

echo "<h2>CPHIA 2025 - Clear Tokens and Fresh Test</h2>";

try {
    // Clear existing tokens
    echo "<p><strong>Clearing existing OAuth tokens...</strong></p>";
    $pdo = getConnection();
    $pdo->exec("DELETE FROM oauth_tokens WHERE service = 'exchange'");
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Tokens Cleared</h4>";
    echo "<p>Existing OAuth tokens have been cleared from the database.</p>";
    echo "</div>";
    
    // Generate fresh OAuth URL
    $oauth = new ExchangeOAuth();
    $authUrl = $oauth->getAuthorizationUrl();
    
    echo "<div style='background: #f1f3f4; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;'>";
    echo "<h3>🔐 Fresh OAuth Authentication</h3>";
    echo "<p>Click the button below to authenticate with Microsoft and get a fresh token for sending emails to <strong>agabaandre@gmail.com</strong>.</p>";
    echo "<p><a href='" . htmlspecialchars($authUrl) . "' class='btn btn-primary' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block; margin: 10px;'>🔐 Authenticate with Microsoft & Send Email</a></p>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>📧 What Will Happen:</h4>";
    echo "<ol>";
    echo "<li>You'll be redirected to Microsoft to sign in</li>";
    echo "<li>Grant permission for the CPHIA 2025 Email Service</li>";
    echo "<li>You'll be redirected back to our callback</li>";
    echo "<li>A test email will be sent to <strong>agabaandre@gmail.com</strong></li>";
    echo "<li>You'll see a success confirmation</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>⚠️ Important</h4>";
    echo "<p>The previous token had an 'Invalid audience' error. This fresh authentication will generate a new token with the correct audience for Microsoft Graph API.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Error</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='admin/email-oauth.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to OAuth Setup</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #2E7D32; }
h4 { color: #1B5E20; }
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
</style>
