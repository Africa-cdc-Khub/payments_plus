<?php
/**
 * Fix OAuth Tokens Table Structure
 */

require_once 'bootstrap.php';
require_once 'db_connector.php';

echo "<h2>CPHIA 2025 - Fix OAuth Tokens Table</h2>";

try {
    $pdo = getConnection();
    
    // Check current table structure
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Current Table Structure:</h4>";
    $columns = $pdo->query("DESCRIBE oauth_tokens")->fetchAll();
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>" . $column['Field'] . ":</strong> " . $column['Type'] . " " . ($column['Null'] === 'YES' ? '(NULL)' : '(NOT NULL)') . "</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // Add missing columns
    echo "<p><strong>Adding missing columns...</strong></p>";
    
    // Add refresh_token column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE oauth_tokens ADD COLUMN refresh_token TEXT");
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Added refresh_token column</h4>";
        echo "</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>⚠️ refresh_token column already exists</h4>";
            echo "</div>";
        } else {
            throw $e;
        }
    }
    
    // Add service column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE oauth_tokens ADD COLUMN service VARCHAR(50) NOT NULL DEFAULT 'exchange'");
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Added service column</h4>";
        echo "</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>⚠️ service column already exists</h4>";
            echo "</div>";
        } else {
            throw $e;
        }
    }
    
    // Check updated table structure
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Updated Table Structure:</h4>";
    $columns = $pdo->query("DESCRIBE oauth_tokens")->fetchAll();
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>" . $column['Field'] . ":</strong> " . $column['Type'] . " " . ($column['Null'] === 'YES' ? '(NULL)' : '(NOT NULL)') . "</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // Update existing records to have service = 'exchange'
    $pdo->exec("UPDATE oauth_tokens SET service = 'exchange' WHERE service IS NULL OR service = ''");
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ OAuth Tokens Table Fixed!</h4>";
    echo "<p>The oauth_tokens table has been updated with the correct structure.</p>";
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
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
