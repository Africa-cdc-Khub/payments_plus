<?php
/**
 * Manual Token Test
 * Manually store a token and test email sending
 */

require_once 'bootstrap.php';
require_once 'db_connector.php';
require_once 'src/ExchangeOAuth.php';

use Cphia2025\ExchangeOAuth;

echo "<h2>CPHIA 2025 - Manual Token Test</h2>";

try {
    // Check database connection
    $pdo = getConnection();
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Database Connection Working</h4>";
    echo "<p>Database connection is working properly.</p>";
    echo "</div>";
    
    // Check if oauth_tokens table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'oauth_tokens'")->fetchAll();
    if (empty($tables)) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ OAuth Tokens Table Not Found</h4>";
        echo "<p>The oauth_tokens table does not exist.</p>";
        echo "</div>";
        exit;
    }
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ OAuth Tokens Table Found</h4>";
    echo "<p>The oauth_tokens table exists.</p>";
    echo "</div>";
    
    // Clear existing tokens
    $pdo->exec("DELETE FROM oauth_tokens WHERE service = 'exchange'");
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>⚠️ Cleared Existing Tokens</h4>";
    echo "<p>Cleared any existing OAuth tokens.</p>";
    echo "</div>";
    
    // Create a test token (this is just for testing - in real scenario, we'd get this from OAuth)
    $testAccessToken = 'test_access_token_' . time();
    $testRefreshToken = 'test_refresh_token_' . time();
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
    
    // Manually insert a test token
    $stmt = $pdo->prepare("
        INSERT INTO oauth_tokens (service, client_id, access_token, refresh_token, expires_at) 
        VALUES ('exchange', ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([EXCHANGE_CLIENT_ID, $testAccessToken, $testRefreshToken, $expiresAt]);
    
    if ($result) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Test Token Stored</h4>";
        echo "<p>Test token has been stored in the database.</p>";
        echo "</div>";
        
        // Test loading the token
        $oauth = new ExchangeOAuth();
        $oauth->loadStoredTokens();
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Token Loading Test:</h4>";
        echo "<ul>";
        echo "<li><strong>isConfigured():</strong> " . ($oauth->isConfigured() ? 'Yes' : 'No') . "</li>";
        echo "<li><strong>hasValidToken():</strong> " . ($oauth->hasValidToken() ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
        echo "</div>";
        
        // Check what's in the database
        $stmt = $pdo->query("SELECT * FROM oauth_tokens WHERE service = 'exchange' ORDER BY created_at DESC LIMIT 1");
        $token = $stmt->fetch();
        
        if ($token) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ Token Retrieved from Database</h4>";
            echo "<ul>";
            echo "<li><strong>Service:</strong> " . htmlspecialchars($token['service']) . "</li>";
            echo "<li><strong>Access Token:</strong> " . substr($token['access_token'], 0, 20) . "...</li>";
            echo "<li><strong>Refresh Token:</strong> " . substr($token['refresh_token'], 0, 20) . "...</li>";
            echo "<li><strong>Expires At:</strong> " . $token['expires_at'] . "</li>";
            echo "<li><strong>Created At:</strong> " . $token['created_at'] . "</li>";
            echo "</ul>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ Failed to Store Test Token</h4>";
        echo "<p>Failed to store test token in the database.</p>";
        echo "</div>";
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
