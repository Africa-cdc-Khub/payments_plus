<?php
/**
 * Debug script for payment action
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "🔍 Debug Payment Action\n";
echo "======================\n\n";

// Test with a known registration ID
$testRegistrationId = 24; // Use an existing registration

echo "1. Testing getRegistrationById for ID: $testRegistrationId\n";
$registration = getRegistrationById($testRegistrationId);

if ($registration) {
    echo "✅ Registration found:\n";
    echo "   - ID: " . $registration['id'] . "\n";
    echo "   - Payment Status: " . ($registration['payment_status'] ?? 'NULL') . "\n";
    echo "   - Status: " . $registration['status'] . "\n";
    echo "   - Package: " . $registration['package_name'] . "\n";
    echo "   - User: " . $registration['first_name'] . " " . $registration['last_name'] . "\n";
    echo "   - Email: " . $registration['user_email'] . "\n";
    
    if ($registration['payment_status'] === 'pending') {
        echo "\n2. Testing payment token generation...\n";
        $paymentToken = generatePaymentToken($testRegistrationId);
        echo "✅ Payment token generated: " . substr($paymentToken, 0, 20) . "...\n";
        
        echo "\n3. Testing payment URL...\n";
        $paymentUrl = APP_URL . "/checkout_payment.php?registration_id=" . $testRegistrationId . "&token=" . $paymentToken;
        echo "✅ Payment URL: $paymentUrl\n";
        
        echo "\n4. Testing payment page access...\n";
        $_GET['registration_id'] = $testRegistrationId;
        $_GET['token'] = $paymentToken;
        
        ob_start();
        try {
            include 'checkout_payment.php';
            $output = ob_get_clean();
            
            if (strpos($output, 'Invalid payment link') === false) {
                echo "✅ Payment page loads successfully\n";
            } else {
                echo "❌ Payment page shows 'Invalid payment link' error\n";
            }
        } catch (Exception $e) {
            ob_end_clean();
            echo "❌ Payment page error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Registration payment status is not 'pending': " . ($registration['payment_status'] ?? 'NULL') . "\n";
    }
} else {
    echo "❌ Registration not found\n";
}

echo "\n5. Testing URL parameters simulation...\n";
echo "Simulating: registration_lookup.php?action=pay&id=$testRegistrationId\n";

// Simulate the payment action
$_GET['action'] = 'pay';
$_GET['id'] = $testRegistrationId;

$registrationId = (int)$_GET['id'];
$registration = getRegistrationById($registrationId);

if ($registration && $registration['payment_status'] === 'pending') {
    echo "✅ Payment action would succeed\n";
    $paymentToken = generatePaymentToken($registrationId);
    $paymentUrl = APP_URL . "/checkout_payment.php?registration_id=" . $registrationId . "&token=" . $paymentToken;
    echo "✅ Would redirect to: $paymentUrl\n";
} else {
    echo "❌ Payment action would fail\n";
    if (!$registration) {
        echo "   - Reason: Registration not found\n";
    } else {
        echo "   - Reason: Payment status is not 'pending' (" . ($registration['payment_status'] ?? 'NULL') . ")\n";
    }
}

echo "\n🎯 Debug Summary:\n";
echo "================\n";
echo "Check the above output to see where the payment action is failing.\n";
