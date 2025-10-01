<?php
/**
 * Check OAuth Configuration
 * Displays current OAuth settings to help debug redirect URI issues
 */

require_once 'bootstrap.php';

echo "<h2>CPHIA 2025 - OAuth Configuration Check</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>Current OAuth Configuration:</h4>";
echo "<ul>";
echo "<li><strong>Tenant ID:</strong> " . EXCHANGE_TENANT_ID . "</li>";
echo "<li><strong>Client ID:</strong> " . EXCHANGE_CLIENT_ID . "</li>";
echo "<li><strong>Client Secret:</strong> " . (EXCHANGE_CLIENT_SECRET ? 'Configured' : 'Not configured') . "</li>";
echo "<li><strong>Redirect URI:</strong> " . EXCHANGE_REDIRECT_URI . "</li>";
echo "<li><strong>Scope:</strong> " . EXCHANGE_SCOPE . "</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>⚠️ Redirect URI Mismatch Error</h4>";
echo "<p>The error indicates that the redirect URI in the OAuth request doesn't match what's configured in Azure.</p>";
echo "<p><strong>Current Redirect URI:</strong> " . EXCHANGE_REDIRECT_URI . "</p>";
echo "<p><strong>Expected in Azure:</strong> The redirect URI must be exactly configured in Azure App Registration</p>";
echo "</div>";

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔧 How to Fix:</h4>";
echo "<ol>";
echo "<li>Go to <a href='https://portal.azure.com' target='_blank'>Azure Portal</a></li>";
echo "<li>Navigate to <strong>Azure Active Directory</strong> → <strong>App registrations</strong></li>";
echo "<li>Find your app: <strong>" . EXCHANGE_CLIENT_ID . "</strong></li>";
echo "<li>Go to <strong>Authentication</strong> section</li>";
echo "<li>Add this redirect URI: <strong>" . EXCHANGE_REDIRECT_URI . "</strong></li>";
echo "<li>Save the changes</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>✅ Alternative Redirect URIs to Try:</h4>";
echo "<p>If the above doesn't work, try adding these redirect URIs in Azure:</p>";
echo "<ul>";
echo "<li><strong>http://localhost:8000/oauth/callback</strong> (current)</li>";
echo "<li><strong>http://localhost:8000/oauth/callback.php</strong></li>";
echo "<li><strong>http://127.0.0.1:8000/oauth/callback</strong></li>";
echo "<li><strong>http://127.0.0.1:8000/oauth/callback.php</strong></li>";
echo "</ul>";
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
</style>
