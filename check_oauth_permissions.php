<?php
/**
 * Check OAuth Permissions
 * Verifies what permissions the OAuth token has
 */

require_once 'bootstrap.php';
require_once 'src/ExchangeOAuthClientCredentials.php';

use Cphia2025\ExchangeOAuthClientCredentials;

echo "<h2>CPHIA 2025 - OAuth Permissions Check</h2>";

try {
    $oauth = new ExchangeOAuthClientCredentials();
    
    if (!$oauth->isConfigured()) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ OAuth Not Configured</h4>";
        echo "<p>Please check your OAuth configuration in the .env file.</p>";
        echo "</div>";
        exit;
    }
    
    // Get access token
    $accessToken = $oauth->getAccessToken();
    
    if ($accessToken) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Access Token Obtained</h4>";
        echo "<p>Access token: " . substr($accessToken, 0, 20) . "...</p>";
        echo "</div>";
        
        // Test different endpoints to see what permissions we have
        echo "<h3>Testing Permissions:</h3>";
        
        // Test 1: Basic Graph API access
        testGraphAccess($accessToken);
        
        // Test 2: Mail permissions
        testMailPermissions($accessToken);
        
        // Test 3: User info
        testUserInfo($accessToken);
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ Failed to Get Access Token</h4>";
        echo "<p>Could not obtain access token.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Error</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

function testGraphAccess($accessToken) {
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Test 1: Basic Graph API Access</h4>";
    
    try {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "<p>✅ <strong>Basic Graph API access: SUCCESS</strong></p>";
            $data = json_decode($response, true);
            echo "<p>User: " . ($data['displayName'] ?? 'Unknown') . " (" . ($data['userPrincipalName'] ?? 'Unknown') . ")</p>";
        } else {
            echo "<p>❌ <strong>Basic Graph API access: FAILED</strong> (HTTP $httpCode)</p>";
            echo "<p>Response: " . htmlspecialchars($response) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ <strong>Basic Graph API access: ERROR</strong> - " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

function testMailPermissions($accessToken) {
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Test 2: Mail Permissions</h4>";
    
    try {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me/mailFolders',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "<p>✅ <strong>Mail folder access: SUCCESS</strong></p>";
        } else {
            echo "<p>❌ <strong>Mail folder access: FAILED</strong> (HTTP $httpCode)</p>";
            echo "<p>Response: " . htmlspecialchars($response) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ <strong>Mail folder access: ERROR</strong> - " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

function testUserInfo($accessToken) {
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Test 3: User Information</h4>";
    
    try {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me/profile',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "<p>✅ <strong>User profile access: SUCCESS</strong></p>";
        } else {
            echo "<p>❌ <strong>User profile access: FAILED</strong> (HTTP $httpCode)</p>";
            echo "<p>Response: " . htmlspecialchars($response) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ <strong>User profile access: ERROR</strong> - " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>💡 Recommendations:</h4>";
echo "<ul>";
echo "<li><strong>If all tests fail:</strong> The client credentials flow may not have the right permissions</li>";
echo "<li><strong>If basic access works but mail fails:</strong> Need to add Mail.Send permission to the Azure app</li>";
echo "<li><strong>If all tests pass:</strong> The issue might be with SMTP OAuth implementation</li>";
echo "</ul>";
echo "</div>";

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
