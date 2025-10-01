<?php
/**
 * Determine Correct Redirect URI
 * This script helps determine what redirect URI should be used
 */

echo "<h2>CPHIA 2025 - Determine Correct Redirect URI</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>Current Server Information:</h4>";
echo "<ul>";
echo "<li><strong>HTTP Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</li>";
echo "<li><strong>HTTPS:</strong> " . (isset($_SERVER['HTTPS']) ? 'Yes' : 'No') . "</li>";
echo "<li><strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Not set') . "</li>";
echo "<li><strong>Request URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</li>";
echo "<li><strong>Script Name:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "</li>";
echo "<li><strong>PHP Self:</strong> " . ($_SERVER['PHP_SELF'] ?? 'Not set') . "</li>";
echo "</ul>";
echo "</div>";

// Calculate possible redirect URIs
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$currentScript = $_SERVER['PHP_SELF'] ?? '/determine_redirect_uri.php';

$possibleRedirectUris = [
    $protocol . $host . '/oauth/callback',
    $protocol . $host . '/oauth/callback.php',
    $protocol . $host . $currentScript,
    'http://localhost:8000/oauth/callback',
    'http://localhost:8000/oauth/callback.php',
    'http://127.0.0.1:8000/oauth/callback',
    'http://127.0.0.1:8000/oauth/callback.php',
    'https://localhost:8000/oauth/callback',
    'https://localhost:8000/oauth/callback.php'
];

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔍 Possible Redirect URIs to Try in Azure:</h4>";
echo "<ol>";
foreach ($possibleRedirectUris as $uri) {
    echo "<li><strong>" . htmlspecialchars($uri) . "</strong></li>";
}
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>⚠️ Important Notes:</h4>";
echo "<ul>";
echo "<li>The redirect URI must be <strong>exactly</strong> configured in Azure App Registration</li>";
echo "<li>It must match the URL that Microsoft will redirect to after authentication</li>";
echo "<li>Common patterns are: <code>/oauth/callback</code> or <code>/oauth/callback.php</code></li>";
echo "<li>Make sure the protocol (http/https) matches your server setup</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>✅ Recommended Steps:</h4>";
echo "<ol>";
echo "<li>Go to <a href='https://portal.azure.com' target='_blank'>Azure Portal</a></li>";
echo "<li>Navigate to <strong>Azure Active Directory</strong> → <strong>App registrations</strong></li>";
echo "<li>Find your app: <strong>996dc2ed-b7c6-446d-a2ee-3b05e935e850</strong></li>";
echo "<li>Go to <strong>Authentication</strong> section</li>";
echo "<li>Add the redirect URI that matches your server setup</li>";
echo "<li>Save the changes</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><a href='admin/email-oauth.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to OAuth Setup</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #2E7D32; }
h4 { color: #1B5E20; }
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
</style>
