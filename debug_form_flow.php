<?php
/**
 * Debug the complete form flow
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "🔍 DEBUGGING FORM FLOW\n";
echo "=====================\n\n";

// Test data
$email = 'agabaandre@gmail.com';
$phone = '0702449883';

echo "1. Testing registration lookup search...\n";
$registrations = getRegistrationHistoryByEmailAndPhone($email, $phone);
echo "   Found " . count($registrations) . " registrations\n";

$pending = array_filter($registrations, function($r) { 
    return ($r['payment_status'] ?? '') === 'pending'; 
});
$paid = array_filter($registrations, function($r) { 
    return ($r['payment_status'] ?? '') === 'completed'; 
});

echo "   - Pending: " . count($pending) . "\n";
echo "   - Paid: " . count($paid) . "\n";

if (!empty($pending)) {
    $reg = reset($pending);
    echo "   - First pending: #" . $reg['id'] . " - " . $reg['package_name'] . "\n";
    
    echo "\n2. Testing payment action simulation...\n";
    echo "   Simulating: registration_lookup.php?action=pay&id=" . $reg['id'] . "\n";
    
    // Simulate the payment action
    $_GET['action'] = 'pay';
    $_GET['id'] = $reg['id'];
    
    $registrationId = (int)$_GET['id'];
    echo "   - Registration ID: $registrationId\n";
    
    $registration = getRegistrationById($registrationId);
    if ($registration) {
        echo "   - Registration found: " . $registration['package_name'] . "\n";
        echo "   - Payment status: " . ($registration['payment_status'] ?? 'NULL') . "\n";
        echo "   - User: " . $registration['first_name'] . " " . $registration['last_name'] . "\n";
        
        if ($registration['payment_status'] === 'pending') {
            echo "   ✅ Payment action would proceed\n";
            
            echo "\n3. Testing payment token generation...\n";
            $paymentToken = generatePaymentToken($registrationId);
            echo "   - Token: " . substr($paymentToken, 0, 20) . "...\n";
            
            // Check if token was stored
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT payment_token FROM registrations WHERE id = ?");
            $stmt->execute([$registrationId]);
            $storedToken = $stmt->fetchColumn();
            
            if ($storedToken === $paymentToken) {
                echo "   ✅ Token stored in database\n";
            } else {
                echo "   ❌ Token not stored correctly\n";
                echo "   - Expected: " . substr($paymentToken, 0, 20) . "...\n";
                echo "   - Stored: " . substr($storedToken, 0, 20) . "...\n";
            }
            
            echo "\n4. Testing payment URL generation...\n";
            $paymentUrl = APP_URL . "/checkout_payment.php?registration_id=" . $registrationId . "&token=" . $paymentToken;
            echo "   - Payment URL: " . substr($paymentUrl, 0, 80) . "...\n";
            
            echo "\n5. Testing payment page access...\n";
            $_GET['registration_id'] = $registrationId;
            $_GET['token'] = $paymentToken;
            
            ob_start();
            try {
                include 'checkout_payment.php';
                $output = ob_get_clean();
                
                if (strpos($output, 'Invalid payment link') !== false) {
                    echo "   ❌ Payment page shows 'Invalid payment link' error\n";
                    echo "   - This means the token validation is failing\n";
                } elseif (strpos($output, 'This registration has already been paid') !== false) {
                    echo "   ❌ Payment page shows 'already been paid' error\n";
                } else {
                    echo "   ✅ Payment page loads successfully\n";
                    echo "   - Output length: " . strlen($output) . " characters\n";
                }
            } catch (Exception $e) {
                ob_end_clean();
                echo "   ❌ Payment page error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ❌ Registration is not pending payment\n";
        }
    } else {
        echo "   ❌ Registration not found\n";
    }
} else {
    echo "   ❌ No pending registrations found\n";
}

echo "\n6. Testing registration lookup page display...\n";
echo "   - The page should show:\n";
echo "     ✅ Payment status badges\n";
echo "     ✅ Complete Payment buttons for pending registrations\n";
echo "     ✅ View Details buttons\n";

echo "\n🎯 FORM FLOW SUMMARY:\n";
echo "====================\n";
echo "Check the above output to identify where the flow is breaking.\n";
echo "Common issues:\n";
echo "- Token not being stored in database\n";
echo "- Payment page token validation failing\n";
echo "- Registration not found or wrong payment status\n";
echo "- URL parameters not being passed correctly\n";
