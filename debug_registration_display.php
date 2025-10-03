<?php
/**
 * Debug registration display logic
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "🔍 DEBUGGING REGISTRATION DISPLAY\n";
echo "=================================\n\n";

// Simulate the registration lookup
$email = 'agabaandre@gmail.com';
$phone = '0702449883';
$registrations = getRegistrationHistoryByEmailAndPhone($email, $phone);

echo "Found " . count($registrations) . " registrations:\n\n";

foreach ($registrations as $registration) {
    echo "Registration #" . $registration['id'] . ":\n";
    echo "  - Package: " . $registration['package_name'] . "\n";
    echo "  - Payment Status: " . ($registration['payment_status'] ?? 'NULL') . "\n";
    echo "  - Status: " . $registration['status'] . "\n";
    
    // Test the display logic
    $shouldShowPaymentButton = ($registration['payment_status'] !== 'completed');
    echo "  - Should show Complete Payment button: " . ($shouldShowPaymentButton ? 'YES' : 'NO') . "\n";
    
    if ($registration['id'] == 20) {
        echo "  *** REGISTRATION #20 DEBUG ***\n";
        echo "  - payment_status value: '" . ($registration['payment_status'] ?? 'NULL') . "'\n";
        echo "  - payment_status type: " . gettype($registration['payment_status']) . "\n";
        echo "  - payment_status === 'completed': " . (($registration['payment_status'] === 'completed') ? 'true' : 'false') . "\n";
        echo "  - payment_status !== 'completed': " . (($registration['payment_status'] !== 'completed') ? 'true' : 'false') . "\n";
        echo "  - strlen(payment_status): " . strlen($registration['payment_status'] ?? '') . "\n";
        echo "  - trim(payment_status): '" . trim($registration['payment_status'] ?? '') . "'\n";
    }
    
    echo "\n";
}

echo "🎯 SUMMARY:\n";
echo "===========\n";
echo "If registration #20 shows 'Should show Complete Payment button: YES',\n";
echo "then there's a bug in the display logic.\n";
echo "If it shows 'NO', then the issue is elsewhere.\n";
