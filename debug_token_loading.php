<?php
/**
 * Debug Token Loading
 */

require_once 'bootstrap.php';
require_once 'src/ExchangeOAuth.php';

use Cphia2025\ExchangeOAuth;

echo "<h2>CPHIA 2025 - Debug Token Loading</h2>";

try {
    $oauth = new ExchangeOAuth();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>OAuth Configuration:</h4>";
    echo "<ul>";
    echo "<li><strong>isConfigured():</strong> " . ($oauth->isConfigured() ? 'Yes' : 'No') . "</li>";
    echo "<li><strong>hasValidToken():</strong> " . ($oauth->hasValidToken() ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Load stored tokens
    echo "<p><strong>Loading stored tokens...</strong></p>";
    $oauth->loadStoredTokens();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>After Loading Tokens:</h4>";
    echo "<ul>";
    echo "<li><strong>isConfigured():</strong> " . ($oauth->isConfigured() ? 'Yes' : 'No') . "</li>";
    echo "<li><strong>hasValidToken():</strong> " . ($oauth->hasValidToken() ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Check database directly
    echo "<p><strong>Checking database directly...</strong></p>";
    require_once 'db_connector.php';
    $pdo = getConnection();
    
    $stmt = $pdo->query("SELECT access_token, refresh_token, expires_at FROM oauth_tokens WHERE service = 'exchange' ORDER BY created_at DESC LIMIT 1");
    $token = $stmt->fetch();
    
    if ($token) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Token Found in Database</h4>";
        echo "<ul>";
        echo "<li><strong>Access Token:</strong> " . substr($token['access_token'], 0, 20) . "...</li>";
        echo "<li><strong>Refresh Token:</strong> " . ($token['refresh_token'] ? substr($token['refresh_token'], 0, 20) . "..." : 'None') . "</li>";
        echo "<li><strong>Expires At:</strong> " . $token['expires_at'] . "</li>";
        echo "<li><strong>Is Valid:</strong> " . (strtotime($token['expires_at']) > time() ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
        echo "</div>";
        
        // Try to manually set the token
        echo "<p><strong>Manually setting token...</strong></p>";
        $oauth->accessToken = $token['access_token'];
        $oauth->refreshToken = $token['refresh_token'];
        $oauth->tokenExpiresAt = strtotime($token['expires_at']);
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>After Manual Token Setting:</h4>";
        echo "<ul>";
        echo "<li><strong>isConfigured():</strong> " . ($oauth->isConfigured() ? 'Yes' : 'No') . "</li>";
        echo "<li><strong>hasValidToken():</strong> " . ($oauth->hasValidToken() ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
        echo "</div>";
        
        // Test sending email
        if ($oauth->hasValidToken()) {
            echo "<p><strong>Testing email sending...</strong></p>";
            $testEmail = 'agabaandre@gmail.com';
            $result = $oauth->sendEmail($testEmail, 'CPHIA 2025 - Debug Test', 'This is a debug test email.');
            
            if ($result) {
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>✅ Email Sent Successfully!</h4>";
                echo "<p>Test email sent to " . htmlspecialchars($testEmail) . "</p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>❌ Email Sending Failed</h4>";
                echo "<p>Failed to send test email.</p>";
                echo "</div>";
            }
        }
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ No Token Found in Database</h4>";
        echo "<p>No OAuth tokens found in the database.</p>";
        echo "</div>";
    }
    
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
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
