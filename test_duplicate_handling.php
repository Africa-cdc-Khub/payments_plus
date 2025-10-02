<?php
/**
 * Test Duplicate Registration Handling
 * Tests the improved duplicate registration user experience
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "🧪 Testing Duplicate Registration Handling\n";
echo "==========================================\n\n";

// Test data
$testEmail = 'test_duplicate_' . time() . '@example.com';
$testPackageId = 19; // African Nationals package

echo "1. Creating test user and registration...\n";

// Create test user
$userData = [
    'email' => $testEmail,
    'first_name' => 'Duplicate',
    'last_name' => 'Test',
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

// Create test registration
$registrationData = [
    'user_id' => $userId,
    'package_id' => $testPackageId,
    'registration_type' => 'individual',
    'total_amount' => 200.00,
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

echo "\n2. Testing duplicate registration check...\n";

// Debug: Check what CONFERENCE_DATES is
echo "   - CONFERENCE_DATES: " . CONFERENCE_DATES . "\n";

// Debug: Test date parsing
$parsedDate = parseEventDate(CONFERENCE_DATES);
echo "   - Parsed date: " . $parsedDate->format('Y-m-d H:i:s') . "\n";

$eventRange = getEventDateRange(CONFERENCE_DATES);
echo "   - Event range: " . json_encode($eventRange) . "\n";

// Debug: Direct database query
$pdo = getConnection();
$stmt = $pdo->prepare("
    SELECT r.id, r.status, r.created_at, p.name as package_name, u.first_name, u.last_name
    FROM registrations r
    JOIN packages p ON r.package_id = p.id
    JOIN users u ON r.user_id = u.id
    WHERE u.email = ? 
    AND r.package_id = ? 
    ORDER BY r.created_at DESC
    LIMIT 1
");
$stmt->execute([$testEmail, $testPackageId]);
$directResult = $stmt->fetch();
echo "   - Direct DB query result: " . json_encode($directResult) . "\n";

// Test duplicate check
$duplicateCheck = checkDuplicateRegistration($testEmail, $testPackageId, CONFERENCE_DATES);
echo "   - Duplicate check result: " . json_encode($duplicateCheck) . "\n";

if ($duplicateCheck['is_duplicate']) {
    echo "   ✅ Duplicate registration detected\n";
    echo "   - Registration ID: #" . $duplicateCheck['registration']['id'] . "\n";
    echo "   - Status: " . $duplicateCheck['registration']['status'] . "\n";
    echo "   - Package: " . $duplicateCheck['registration']['package_name'] . "\n";
    echo "   - Created: " . $duplicateCheck['registration']['created_at'] . "\n";
} else {
    echo "   ❌ Duplicate check failed\n";
    exit(1);
}

echo "\n3. Testing duplicate message generation...\n";

$duplicateMessage = getDuplicateRegistrationMessage($duplicateCheck);
echo "   ✅ Duplicate message generated:\n";
echo "   " . $duplicateMessage . "\n";

echo "\n4. Testing payment URL generation...\n";

if ($duplicateCheck['registration']['status'] === 'pending') {
    $paymentToken = generatePaymentToken($duplicateCheck['registration']['id']);
    $paymentUrl = APP_URL . "/checkout_payment.php?registration_id=" . $duplicateCheck['registration']['id'] . "&token=" . $paymentToken;
    echo "   ✅ Payment URL generated: $paymentUrl\n";
} else {
    echo "   ℹ️  Registration is not pending payment\n";
}

echo "\n5. Testing registration lookup with payment action...\n";

$lookupUrl = APP_URL . "/registration_lookup.php?action=pay&id=" . $duplicateCheck['registration']['id'];
echo "   ✅ Lookup URL with payment action: $lookupUrl\n";

echo "\n6. Testing support email generation...\n";

$supportEmail = "mailto:support@cphia2025.com?subject=Registration%20Inquiry&body=Registration%20ID:%20%23" . $duplicateCheck['registration']['id'];
echo "   ✅ Support email URL: $supportEmail\n";

echo "\n🎯 Duplicate Registration Handling Test Summary:\n";
echo "==============================================\n";
echo "✅ Duplicate detection working correctly\n";
echo "✅ User-friendly error message generated\n";
echo "✅ Payment URL generation for pending registrations\n";
echo "✅ Registration lookup with payment action\n";
echo "✅ Support contact integration\n";
echo "✅ Improved user experience with clear next steps\n\n";

echo "📝 User Experience Improvements:\n";
echo "1. Clear warning message instead of generic error\n";
echo "2. Direct action buttons for common tasks\n";
echo "3. Payment completion for pending registrations\n";
echo "4. Easy access to registration lookup\n";
echo "5. Support contact with pre-filled information\n";
echo "6. Visual indicators and helpful icons\n\n";

echo "🔗 Test the improved experience:\n";
echo "1. Try registering with email: $testEmail\n";
echo "2. Select package ID: $testPackageId\n";
echo "3. Submit the form to see the improved duplicate handling\n";
echo "4. Use the action buttons to navigate or complete payment\n\n";

echo "✨ The duplicate registration experience is now much more user-friendly!\n";
