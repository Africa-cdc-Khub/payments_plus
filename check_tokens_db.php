<?php
/**
 * Check OAuth Tokens in Database
 */

require_once 'bootstrap.php';
require_once 'db_connector.php';

echo "<h2>CPHIA 2025 - OAuth Tokens Database Check</h2>";

try {
    $pdo = getConnection();
    
    // Check if oauth_tokens table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'oauth_tokens'")->fetchAll();
    
    if (empty($tables)) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ OAuth Tokens Table Not Found</h4>";
        echo "<p>The oauth_tokens table does not exist in the database.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ OAuth Tokens Table Found</h4>";
        echo "<p>The oauth_tokens table exists in the database.</p>";
        echo "</div>";
        
        // Check for tokens
        $stmt = $pdo->query("SELECT * FROM oauth_tokens ORDER BY created_at DESC LIMIT 5");
        $tokens = $stmt->fetchAll();
        
        if (empty($tokens)) {
            echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>⚠️ No Tokens Found</h4>";
            echo "<p>No OAuth tokens found in the database.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ Tokens Found</h4>";
            echo "<p>Found " . count($tokens) . " token(s) in the database:</p>";
            echo "<ul>";
            foreach ($tokens as $token) {
                echo "<li><strong>Service:</strong> " . htmlspecialchars($token['service'] ?? 'N/A') . "</li>";
                echo "<li><strong>Access Token:</strong> " . substr($token['access_token'], 0, 20) . "...</li>";
                echo "<li><strong>Expires At:</strong> " . $token['expires_at'] . "</li>";
                echo "<li><strong>Created At:</strong> " . $token['created_at'] . "</li>";
                echo "<li><strong>Is Valid:</strong> " . (strtotime($token['expires_at']) > time() ? 'Yes' : 'No') . "</li>";
                echo "<hr>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Database Error</h4>";
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
