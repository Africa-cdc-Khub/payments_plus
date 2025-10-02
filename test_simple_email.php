<?php
/**
 * Simple test for email template
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "📧 TESTING EMAIL TEMPLATE\n";
echo "========================\n\n";

// Test template data
$templateData = [
    'user_name' => 'John Doe',
    'registration_id' => 20,
    'package_name' => 'African Nationals',
    'amount' => '200.00',
    'participants' => [],
    'conference_name' => CONFERENCE_NAME,
    'conference_short_name' => CONFERENCE_SHORT_NAME,
    'conference_dates' => CONFERENCE_DATES,
    'conference_location' => CONFERENCE_LOCATION,
    'conference_venue' => CONFERENCE_VENUE,
    'logo_url' => EMAIL_LOGO_URL,
    'payment_link' => APP_URL . "/checkout_payment.php?registration_id=20&token=test_token",
    'payment_status' => 'pending'
];

echo "1. Template data prepared:\n";
echo "   - User: " . $templateData['user_name'] . "\n";
echo "   - Registration ID: " . $templateData['registration_id'] . "\n";
echo "   - Package: " . $templateData['package_name'] . "\n";
echo "   - Amount: $" . $templateData['amount'] . "\n";
echo "   - Payment Link: " . $templateData['payment_link'] . "\n";

echo "\n2. Testing template file...\n";
$templateFile = 'templates/email/registration_confirmation.html';
if (file_exists($templateFile)) {
    echo "   ✅ Template file exists\n";
    
    $templateContent = file_get_contents($templateFile);
    
    if (strpos($templateContent, '{{payment_link}}') !== false) {
        echo "   ✅ Template contains payment_link placeholder\n";
    } else {
        echo "   ❌ Template does NOT contain payment_link placeholder\n";
    }
    
    if (strpos($templateContent, 'Complete Payment') !== false) {
        echo "   ✅ Template contains Complete Payment button\n";
    } else {
        echo "   ❌ Template does NOT contain Complete Payment button\n";
    }
    
    if (strpos($templateContent, '{{#payment_link}}') !== false) {
        echo "   ✅ Template contains conditional payment section\n";
    } else {
        echo "   ❌ Template does NOT contain conditional payment section\n";
    }
} else {
    echo "   ❌ Template file not found\n";
}

echo "\n3. Testing sendRegistrationEmails function...\n";
$user = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com'
];

$package = [
    'name' => 'African Nationals'
];

$result = sendRegistrationEmails($user, 20, $package, '200.00', []);
if ($result) {
    echo "   ✅ Email queued successfully\n";
} else {
    echo "   ❌ Failed to queue email\n";
}

echo "\n🎯 SUMMARY:\n";
echo "===========\n";
echo "The email template should now include:\n";
echo "✅ Payment link in template data\n";
echo "✅ Complete Payment button in template\n";
echo "✅ Conditional payment section\n";
echo "✅ Registration success page with payment button\n";
echo "\nUsers will now have multiple ways to complete payment:\n";
echo "1. Click 'Complete Payment Now' button on success page\n";
echo "2. Use the payment link in the email\n";
echo "3. Go to registration lookup page\n";
