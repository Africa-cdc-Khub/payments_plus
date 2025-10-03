<?php
/**
 * Test the payment button fix
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "🔧 TESTING PAYMENT BUTTON FIX\n";
echo "=============================\n\n";

// Test data
$email = 'agabaandre@gmail.com';
$phone = '0702449883';

echo "1. Getting registration data...\n";
$registrations = getRegistrationHistoryByEmailAndPhone($email, $phone);

echo "2. Testing payment button logic for each registration...\n";
foreach ($registrations as $registration) {
    $paymentStatus = $registration['payment_status'] ?? '';
    $shouldShowButton = ($paymentStatus !== 'completed');
    
    echo "   Registration #" . $registration['id'] . ":\n";
    echo "     - Payment Status: '$paymentStatus'\n";
    echo "     - Should show button: " . ($shouldShowButton ? 'YES' : 'NO') . "\n";
    
    if ($registration['id'] == 20) {
        echo "     *** REGISTRATION #20 ***\n";
        echo "     - This should NOT show a Complete Payment button\n";
        if ($shouldShowButton) {
            echo "     ❌ BUG: Button would be shown (incorrect)\n";
        } else {
            echo "     ✅ CORRECT: Button would NOT be shown\n";
        }
    }
    echo "\n";
}

echo "3. Testing the actual display logic...\n";
// Simulate the display logic
foreach ($registrations as $registration) {
    if ($registration['id'] == 20) {
        echo "   Registration #20 display logic test:\n";
        $paymentStatus = $registration['payment_status'] ?? '';
        echo "     - payment_status: '$paymentStatus'\n";
        echo "     - payment_status !== 'completed': " . (($paymentStatus !== 'completed') ? 'true' : 'false') . "\n";
        
        if (($paymentStatus ?? '') !== 'completed') {
            echo "     ❌ BUG: Complete Payment button would be shown\n";
        } else {
            echo "     ✅ CORRECT: Complete Payment button would NOT be shown\n";
        }
        break;
    }
}

echo "\n🎯 SUMMARY:\n";
echo "===========\n";
echo "If registration #20 shows 'Button would be shown', there's a bug.\n";
echo "If it shows 'Button would NOT be shown', the logic is correct.\n";
echo "\nIf you're still seeing the button in the browser, try:\n";
echo "1. Clear browser cache\n";
echo "2. Hard refresh (Ctrl+F5)\n";
echo "3. Check if you're looking at a different registration\n";
