<?php
/**
 * Debug OAuth Configuration
 */

require_once 'bootstrap.php';

echo "<h2>CPHIA 2025 - OAuth Debug</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>Environment Variables:</h4>";
echo "<ul>";
echo "<li><strong>EXCHANGE_TENANT_ID:</strong> " . (defined('EXCHANGE_TENANT_ID') ? EXCHANGE_TENANT_ID : 'Not defined') . "</li>";
echo "<li><strong>EXCHANGE_CLIENT_ID:</strong> " . (defined('EXCHANGE_CLIENT_ID') ? EXCHANGE_CLIENT_ID : 'Not defined') . "</li>";
echo "<li><strong>EXCHANGE_CLIENT_SECRET:</strong> " . (defined('EXCHANGE_CLIENT_SECRET') ? 'Defined' : 'Not defined') . "</li>";
echo "<li><strong>EXCHANGE_REDIRECT_URI:</strong> " . (defined('EXCHANGE_REDIRECT_URI') ? EXCHANGE_REDIRECT_URI : 'Not defined') . "</li>";
echo "<li><strong>EXCHANGE_SCOPE:</strong> " . (defined('EXCHANGE_SCOPE') ? EXCHANGE_SCOPE : 'Not defined') . "</li>";
echo "<li><strong>MAIL_DRIVER:</strong> " . (defined('MAIL_DRIVER') ? MAIL_DRIVER : 'Not defined') . "</li>";
echo "</ul>";
echo "</div>";

// Test OAuth class
try {
    require_once 'src/ExchangeOAuth.php';
    $oauth = new \Cphia2025\ExchangeOAuth();
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ OAuth Class Created Successfully</h4>";
    echo "<p>OAuth class instantiated without errors.</p>";
    echo "</div>";
    
    // Check if configured
    if ($oauth->isConfigured()) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ OAuth is Configured</h4>";
        echo "<p>OAuth configuration is valid.</p>";
        echo "</div>";
        
        // Check for tokens
        if ($oauth->hasValidToken()) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ Valid Tokens Found</h4>";
            echo "<p>OAuth tokens are available and valid.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>⚠️ No Valid Tokens</h4>";
            echo "<p>No valid OAuth tokens found. You need to complete authentication.</p>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ OAuth Not Configured</h4>";
        echo "<p>OAuth configuration is incomplete.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ OAuth Class Error</h4>";
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
