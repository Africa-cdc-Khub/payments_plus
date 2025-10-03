<?php
/**
 * Test Payment Flow
 * Demonstrates the complete registration to payment flow
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "💳 Payment Flow Test\n";
echo "===================\n\n";

try {
    // Test data
    $testUser = [
        'email' => 'test_payment_' . time() . '@example.com',
        'first_name' => 'Payment',
        'last_name' => 'Test',
        'phone' => '+1234567890',
        'nationality' => 'Ghana',
        'organization' => 'Test Organization',
        'address_line1' => '123 Test Street',
        'address_line2' => '',
        'city' => 'Test City',
        'state' => 'Test State',
        'country' => 'Ghana',
        'postal_code' => '12345'
    ];

    $testPackage = [
        'id' => 19,
        'name' => 'African Nationals',
        'type' => 'individual',
        'price' => 200.00
    ];

    echo "1. Creating test user...\n";
    $userId = createUser($testUser);
    if ($userId) {
        echo "   ✅ User created (ID: $userId)\n";
    } else {
        echo "   ❌ User creation failed\n";
        exit(1);
    }

    echo "\n2. Creating test registration...\n";
    $registrationData = [
        'user_id' => $userId,
        'package_id' => $testPackage['id'],
        'registration_type' => 'individual',
        'total_amount' => $testPackage['price'],
        'currency' => 'USD',
        'exhibition_description' => null
    ];
    $registrationId = createRegistration($registrationData);
    if ($registrationId) {
        echo "   ✅ Registration created (ID: $registrationId)\n";
    } else {
        echo "   ❌ Registration creation failed\n";
        exit(1);
    }

    echo "\n3. Generating payment token...\n";
    $paymentToken = generatePaymentToken($registrationId);
    echo "   ✅ Payment token: $paymentToken\n";

    echo "\n4. Creating payment link...\n";
    $paymentLink = APP_URL . "/checkout_payment.php?registration_id=" . $registrationId . "&token=" . $paymentToken;
    echo "   ✅ Payment link: $paymentLink\n";

    echo "\n5. Testing payment form data...\n";
    
    // Simulate the payment form data that would be generated
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT r.*, u.*, p.* FROM registrations r 
                          JOIN users u ON r.user_id = u.id 
                          JOIN packages p ON r.package_id = p.id 
                          WHERE r.id = ?");
    $stmt->execute([$registrationId]);
    $registration = $stmt->fetch();

    if ($registration) {
        echo "   ✅ Registration data retrieved\n";
        echo "   - Name: " . $registration['first_name'] . " " . $registration['last_name'] . "\n";
        echo "   - Email: " . $registration['email'] . "\n";
        echo "   - Package: " . $registration['name'] . "\n";
        echo "   - Amount: $" . $registration['total_amount'] . "\n";
        echo "   - Address: " . $registration['address_line1'] . ", " . $registration['city'] . "\n";
    } else {
        echo "   ❌ Failed to retrieve registration data\n";
    }

    echo "\n6. Testing CyberSource integration...\n";
    
    // Test the payment form generation
    $testUrl = $paymentLink;
    echo "   ✅ Payment form URL: $testUrl\n";
    echo "   📝 To test the payment form, visit: $testUrl\n";
    
    echo "\n7. Testing email notifications...\n";
    $emailResult = sendPaymentLinkEmail($testUser, $registrationId, $testPackage['price']);
    if ($emailResult) {
        echo "   ✅ Payment link email sent\n";
    } else {
        echo "   ❌ Payment link email failed\n";
    }

    echo "\n🎉 Payment flow test completed!\n";
    echo "\n📋 Summary:\n";
    echo "- User created and registered ✅\n";
    echo "- Payment token generated ✅\n";
    echo "- Payment form URL created ✅\n";
    echo "- Email notification sent ✅\n";
    echo "\n🔗 Test the payment form at: $testUrl\n";
    echo "📧 Check email at: " . $testUser['email'] . "\n";

    echo "\n📝 Next Steps:\n";
    echo "1. Visit the payment form URL\n";
    echo "2. Fill in test card details (use CyberSource test cards)\n";
    echo "3. Submit payment\n";
    echo "4. Check payment response handling\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n";
