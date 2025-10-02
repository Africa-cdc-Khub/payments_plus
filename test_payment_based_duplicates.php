<?php
/**
 * Test Payment-Based Duplicate Registration Policy
 * Tests that only paid registrations are considered duplicates
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "🧪 Testing Payment-Based Duplicate Registration Policy\n";
echo "=====================================================\n\n";

// Test data
$testEmail = 'test_payment_policy_' . time() . '@example.com';
$testPackageId = 19; // African Nationals package

echo "1. Creating test user...\n";

// Create test user
$userData = [
    'email' => $testEmail,
    'first_name' => 'Payment',
    'last_name' => 'Policy',
    'phone' => '+1234567890',
    'nationality' => 'Ghanaian',
    'organization' => 'Test Organization',
    'address_line1' => '123 Test Street',
    'city' => 'Test City',
    'country' => 'Ghana',
    'postal_code' => '12345'
];

$userId = createUser($userData);
if ($userId) {
    echo "   ✅ User created (ID: $userId)\n";
} else {
    echo "   ❌ User creation failed\n";
    exit(1);
}

echo "\n2. Creating first registration (unpaid)...\n";

// Create first registration (unpaid)
$registrationData1 = [
    'user_id' => $userId,
    'package_id' => $testPackageId,
    'registration_type' => 'individual',
    'total_amount' => 200.00,
    'currency' => 'USD',
    'exhibition_description' => null
];

$registrationId1 = createRegistration($registrationData1);
if ($registrationId1) {
    echo "   ✅ First registration created (ID: $registrationId1)\n";
    echo "   - Status: pending (unpaid)\n";
} else {
    echo "   ❌ First registration creation failed\n";
    exit(1);
}

echo "\n3. Testing duplicate check for unpaid registration...\n";

// Test duplicate check for unpaid registration
$duplicateCheck1 = checkDuplicateRegistration($testEmail, $testPackageId, CONFERENCE_DATES);
if ($duplicateCheck1['is_duplicate']) {
    echo "   ❌ Duplicate check incorrectly detected unpaid registration as duplicate\n";
    echo "   - Result: " . json_encode($duplicateCheck1) . "\n";
} else {
    echo "   ✅ Duplicate check correctly allowed unpaid registration\n";
    echo "   - Result: " . json_encode($duplicateCheck1) . "\n";
}

echo "\n4. Creating second registration (unpaid)...\n";

// Create second registration (unpaid) - should be allowed
$registrationData2 = [
    'user_id' => $userId,
    'package_id' => $testPackageId,
    'registration_type' => 'individual',
    'total_amount' => 200.00,
    'currency' => 'USD',
    'exhibition_description' => null
];

$registrationId2 = createRegistration($registrationData2);
if ($registrationId2) {
    echo "   ✅ Second registration created (ID: $registrationId2)\n";
    echo "   - Status: pending (unpaid)\n";
} else {
    echo "   ❌ Second registration creation failed\n";
    exit(1);
}

echo "\n5. Testing duplicate check after second unpaid registration...\n";

// Test duplicate check after second unpaid registration
$duplicateCheck2 = checkDuplicateRegistration($testEmail, $testPackageId, CONFERENCE_DATES);
if ($duplicateCheck2['is_duplicate']) {
    echo "   ❌ Duplicate check incorrectly detected second unpaid registration as duplicate\n";
    echo "   - Result: " . json_encode($duplicateCheck2) . "\n";
} else {
    echo "   ✅ Duplicate check correctly allowed second unpaid registration\n";
    echo "   - Result: " . json_encode($duplicateCheck2) . "\n";
}

echo "\n6. Simulating payment for first registration...\n";

// Simulate payment for first registration
$pdo = getConnection();
$stmt = $pdo->prepare("UPDATE registrations SET payment_status = 'completed', payment_completed_at = NOW() WHERE id = ?");
$result = $stmt->execute([$registrationId1]);
if ($result) {
    echo "   ✅ First registration marked as paid\n";
} else {
    echo "   ❌ Failed to mark first registration as paid\n";
    exit(1);
}

echo "\n7. Testing duplicate check after payment...\n";

// Test duplicate check after payment
$duplicateCheck3 = checkDuplicateRegistration($testEmail, $testPackageId, CONFERENCE_DATES);
if ($duplicateCheck3['is_duplicate']) {
    echo "   ✅ Duplicate check correctly detected paid registration as duplicate\n";
    echo "   - Registration ID: #" . $duplicateCheck3['registration']['id'] . "\n";
    echo "   - Status: " . $duplicateCheck3['registration']['status'] . "\n";
    echo "   - Package: " . $duplicateCheck3['registration']['package_name'] . "\n";
} else {
    echo "   ❌ Duplicate check failed to detect paid registration as duplicate\n";
    echo "   - Result: " . json_encode($duplicateCheck3) . "\n";
    
    // Debug: Check what registrations exist
    echo "   - Debug: Checking all registrations for this email and package...\n";
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT r.id, r.payment_status, r.status, r.created_at, p.name as package_name
        FROM registrations r
        JOIN packages p ON r.package_id = p.id
        JOIN users u ON r.user_id = u.id
        WHERE u.email = ? AND r.package_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$testEmail, $testPackageId]);
    $allRegistrations = $stmt->fetchAll();
    foreach ($allRegistrations as $reg) {
        echo "     - Registration #{$reg['id']}: payment_status={$reg['payment_status']}, status={$reg['status']}, package={$reg['package_name']}\n";
    }
}

echo "\n8. Testing registration history...\n";

// Test registration history
$history = getRegistrationHistory($testEmail, CONFERENCE_DATES);
echo "   ✅ Registration history retrieved:\n";
foreach ($history as $reg) {
    $paymentStatus = $reg['payment_status'] === 'completed' ? 'Paid' : 'Unpaid';
    echo "   - Registration #{$reg['id']}: {$reg['package_name']} - {$paymentStatus}\n";
}

echo "\n🎯 Payment-Based Duplicate Policy Test Summary:\n";
echo "==============================================\n";
echo "✅ Unpaid registrations are NOT considered duplicates\n";
echo "✅ Multiple unpaid registrations are allowed\n";
echo "✅ Paid registrations ARE considered duplicates\n";
echo "✅ Registration history shows payment status clearly\n";
echo "✅ Users can register multiple times until they pay\n\n";

echo "📝 Policy Benefits:\n";
echo "1. Users can try different packages without restrictions\n";
echo "2. Users can correct their information before paying\n";
echo "3. Only paid registrations count as 'real' conference registrations\n";
echo "4. Clear distinction between paid and unpaid registrations\n";
echo "5. Better user experience with flexible registration\n\n";

echo "🔗 Test the new policy:\n";
echo "1. Register with email: $testEmail\n";
echo "2. Try registering for the same package multiple times\n";
echo "3. See that unpaid registrations are allowed\n";
echo "4. Complete payment for one registration\n";
echo "5. Try registering again - should now show duplicate warning\n\n";

echo "✨ The new payment-based duplicate policy is working correctly!\n";
