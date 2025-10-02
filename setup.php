<?php
/**
 * CPHIA 2025 Registration System Setup
 * Run this file to set up the database and initial data
 */

echo "<h1>CPHIA 2025 Registration System Setup</h1>";

// Check if .env file exists
if (!file_exists(__DIR__ . '/.env')) {
    echo "<div style='background: #fef3c7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>⚠️ Environment Configuration Required</h3>";
    echo "<p>Please copy <code>env.example</code> to <code>.env</code> and configure your environment variables.</p>";
    echo "<p><strong>Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Copy <code>env.example</code> to <code>.env</code></li>";
    echo "<li>Update database credentials in <code>.env</code></li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
    echo "</div>";
    exit;
}

// Check if database connection works
try {
    require_once 'bootstrap.php';
    require_once 'db_connector.php';
    $pdo = getConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in <code>.env</code> file</p>";
    exit;
}

// Run migrations
echo "<h2>Setting up database tables...</h2>";
try {
    require_once 'migrations.php';
    createTables();
    echo "<p style='color: green;'>✅ Database tables created successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating tables: " . $e->getMessage() . "</p>";
    exit;
}

// Insert packages
echo "<h2>Inserting package data...</h2>";
try {
    insertPackages();
    echo "<p style='color: green;'>✅ Package data inserted successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error inserting packages: " . $e->getMessage() . "</p>";
    exit;
}

// Test functions
echo "<h2>Testing system functions...</h2>";
try {
    require_once 'functions.php';
    $packages = getAllPackages();
    echo "<p style='color: green;'>✅ Found " . count($packages) . " packages in database</p>";
    
    $individualPackages = getPackagesByType('individual');
    echo "<p style='color: green;'>✅ Found " . count($individualPackages) . " individual packages</p>";
    
    $groupPackages = getPackagesByType('group');
    echo "<p style='color: green;'>✅ Found " . count($groupPackages) . " group packages</p>";
    
    $exhibitionPackages = getPackagesByType('exhibition');
    echo "<p style='color: green;'>✅ Found " . count($exhibitionPackages) . " exhibition packages</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error testing functions: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Setup Complete! 🎉</h2>";
echo "<div style='background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Visit <a href='index.php'>index.php</a> to see the registration form</li>";
echo "<li>Test the registration process</li>";
echo "<li>Configure your payment gateway settings in sa-sop/config.php if needed</li>";
echo "<li>Set up email configuration for payment links</li>";
echo "</ol>";
echo "</div>";

echo "<h3>System Information:</h3>";
echo "<ul>";
echo "<li><strong>Database:</strong> " . DB_NAME . "</li>";
echo "<li><strong>Host:</strong> " . DB_HOST . "</li>";
echo "<li><strong>User:</strong> " . DB_USER . "</li>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "</ul>";

echo "<h3>Available Packages:</h3>";
echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f8fafc;'>";
echo "<th>Name</th><th>Type</th><th>Price</th><th>Max People</th>";
echo "</tr>";

$allPackages = getAllPackages();
foreach ($allPackages as $package) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($package['name']) . "</td>";
    echo "<td>" . ucfirst($package['type']) . "</td>";
    echo "<td>$" . number_format($package['price'], 2) . "</td>";
    echo "<td>" . $package['max_people'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p style='margin-top: 30px; padding: 15px; background: #fef3c7; border-radius: 8px;'>";
echo "<strong>Note:</strong> This setup script can be deleted after successful setup for security reasons.";
echo "</p>";
?>
