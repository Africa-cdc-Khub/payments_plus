<?php
/**
 * Simple test script for Invitation API
 * Run this from command line: php test_api.php {registration_id}
 */

// Get registration ID from command line argument
$registrationId = $argv[1] ?? 57;

echo "Testing Invitation API\n";
echo "=====================\n\n";
echo "Registration ID: {$registrationId}\n";
echo "Endpoint: http://localhost:8000/api/invitation/send\n\n";

// Prepare request
$url = 'http://localhost:8000/api/invitation/send';
$data = ['registration_id' => (int)$registrationId];

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

echo "Sending request...\n";

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Check for cURL errors
if ($error) {
    echo "\n❌ cURL Error: {$error}\n";
    echo "Make sure the dev server is running: php artisan serve\n";
    exit(1);
}

// Parse response
$result = json_decode($response, true);

echo "\nHTTP Status: {$httpCode}\n";
echo "Response:\n";
echo str_repeat('-', 50) . "\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
echo str_repeat('-', 50) . "\n\n";

// Display result
if ($httpCode === 200 && isset($result['success']) && $result['success']) {
    echo "✅ SUCCESS!\n";
    echo "   Invitation queued successfully\n";
    echo "   User: {$result['user_name']}\n";
    echo "   Email: {$result['user_email']}\n";
    echo "   Package: {$result['package']}\n";
    echo "   Queued at: {$result['queued_at']}\n\n";
    echo "📝 Next step: Process the queue\n";
    echo "   php artisan queue:work --once\n";
} else {
    echo "❌ FAILED!\n";
    echo "   Error: " . ($result['error'] ?? 'Unknown error') . "\n";
    if (isset($result['payment_status'])) {
        echo "   Payment Status: {$result['payment_status']}\n";
    }
    if (isset($result['status'])) {
        echo "   Registration Status: {$result['status']}\n";
    }
}

echo "\n";

