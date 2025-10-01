<?php
/**
 * CPHIA 2025 Registration System Installation Script
 * This script helps you set up the system with environment variables
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>CPHIA 2025 - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .step { background: #f8fafc; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #1e40af; }
        .success { background: #f0fdf4; border-left-color: #059669; }
        .error { background: #fef2f2; border-left-color: #dc2626; }
        .warning { background: #fef3c7; border-left-color: #f59e0b; }
        code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 8px; overflow-x: auto; }
        .btn { background: #1e40af; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
        .btn:hover { background: #1e3a8a; }
    </style>
</head>
<body>";

echo "<h1>🚀 CPHIA 2025 Registration System Installation</h1>";

// Step 1: Check PHP version
echo "<div class='step'>";
echo "<h2>Step 1: PHP Version Check</h2>";
$phpVersion = PHP_VERSION;
$requiredVersion = '7.4.0';
if (version_compare($phpVersion, $requiredVersion, '>=')) {
    echo "<p class='success'>✅ PHP version {$phpVersion} is compatible (requires {$requiredVersion}+)</p>";
} else {
    echo "<p class='error'>❌ PHP version {$phpVersion} is too old. Please upgrade to {$requiredVersion} or higher.</p>";
    exit;
}
echo "</div>";

// Step 2: Check for Composer
echo "<div class='step'>";
echo "<h2>Step 2: Composer Check</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<p class='success'>✅ Composer dependencies are installed</p>";
} else {
    echo "<p class='warning'>⚠️ Composer dependencies not found. Installing...</p>";
    echo "<p>Please run: <code>composer install</code> in the project directory</p>";
    echo "<p>Or download vlucas/phpdotenv manually and place it in the vendor directory</p>";
}
echo "</div>";

// Step 3: Environment file setup
echo "<div class='step'>";
echo "<h2>Step 3: Environment Configuration</h2>";
if (file_exists(__DIR__ . '/.env')) {
    echo "<p class='success'>✅ .env file exists</p>";
} else {
    echo "<p class='warning'>⚠️ .env file not found</p>";
    if (file_exists(__DIR__ . '/env.example')) {
        echo "<p>Copying env.example to .env...</p>";
        if (copy(__DIR__ . '/env.example', __DIR__ . '/.env')) {
            echo "<p class='success'>✅ .env file created successfully</p>";
            echo "<p>Please edit the .env file with your actual configuration values</p>";
        } else {
            echo "<p class='error'>❌ Failed to create .env file. Please copy env.example to .env manually</p>";
        }
    } else {
        echo "<p class='error'>❌ env.example file not found</p>";
    }
}
echo "</div>";

// Step 4: Database connection test
echo "<div class='step'>";
echo "<h2>Step 4: Database Connection Test</h2>";
try {
    require_once 'bootstrap.php';
    require_once 'db_connector.php';
    $pdo = getConnection();
    echo "<p class='success'>✅ Database connection successful!</p>";
    echo "<p>Connected to: <code>" . DB_NAME . "</code> on <code>" . DB_HOST . "</code></p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in the .env file:</p>";
    echo "<pre>DB_HOST=localhost
DB_NAME=cphia_payments
DB_USER=root
DB_PASS=password</pre>";
}
echo "</div>";

// Step 5: Directory permissions
echo "<div class='step'>";
echo "<h2>Step 5: Directory Permissions</h2>";
$directories = ['css', 'js', 'images', 'uploads'];
$allWritable = true;
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p class='success'>✅ {$dir}/ directory is writable</p>";
        } else {
            echo "<p class='error'>❌ {$dir}/ directory is not writable</p>";
            $allWritable = false;
        }
    } else {
        echo "<p class='warning'>⚠️ {$dir}/ directory does not exist</p>";
    }
}
if ($allWritable) {
    echo "<p class='success'>✅ All required directories have proper permissions</p>";
}
echo "</div>";

// Step 6: Installation options
echo "<div class='step'>";
echo "<h2>Step 6: Installation Options</h2>";
echo "<p>Choose your next step:</p>";
echo "<a href='setup.php' class='btn'>Run Database Setup</a>";
echo "<a href='index.php' class='btn'>View Registration Form</a>";
echo "<a href='checkout.php?registration_id=1' class='btn'>Test Checkout (requires setup)</a>";
echo "</div>";

// Step 7: Configuration summary
echo "<div class='step'>";
echo "<h2>Step 7: Current Configuration</h2>";
echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>App Name</td><td>" . (defined('APP_NAME') ? APP_NAME : 'Not loaded') . "</td></tr>";
echo "<tr><td>Environment</td><td>" . (defined('APP_ENV') ? APP_ENV : 'Not loaded') . "</td></tr>";
echo "<tr><td>Debug Mode</td><td>" . (defined('APP_DEBUG') ? (APP_DEBUG ? 'Enabled' : 'Disabled') : 'Not loaded') . "</td></tr>";
echo "<tr><td>Database Host</td><td>" . (defined('DB_HOST') ? DB_HOST : 'Not loaded') . "</td></tr>";
echo "<tr><td>Database Name</td><td>" . (defined('DB_NAME') ? DB_NAME : 'Not loaded') . "</td></tr>";
echo "<tr><td>Conference Name</td><td>" . (defined('CONFERENCE_NAME') ? CONFERENCE_NAME : 'Not loaded') . "</td></tr>";
echo "<tr><td>Email Notifications</td><td>" . (defined('ENABLE_EMAIL_NOTIFICATIONS') ? (ENABLE_EMAIL_NOTIFICATIONS ? 'Enabled' : 'Disabled') : 'Not loaded') . "</td></tr>";
echo "</table>";
echo "</div>";

echo "<div class='step success'>";
echo "<h2>🎉 Installation Complete!</h2>";
echo "<p>Your CPHIA 2025 Registration System is ready to use.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Run the database setup to create tables and insert sample data</li>";
echo "<li>Configure your payment gateway credentials in the .env file</li>";
echo "<li>Set up email configuration for notifications</li>";
echo "<li>Test the registration and payment process</li>";
echo "<li>Customize the styling and content as needed</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
