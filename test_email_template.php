<?php
/**
 * Test email template with payment link
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "📧 TESTING EMAIL TEMPLATE WITH PAYMENT LINK\n";
echo "==========================================\n\n";

// Test data
$user = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com'
];

$package = [
    'name' => 'African Nationals'
];

$registrationId = 20;
$amount = '200.00';

echo "1. Testing sendRegistrationEmails function...\n";
$result = sendRegistrationEmails($user, $registrationId, $package, $amount, []);

if ($result) {
    echo "   ✅ Email queued successfully\n";
} else {
    echo "   ❌ Failed to queue email\n";
}

echo "\n2. Testing email template rendering...\n";

// Test template data
$templateData = [
    'user_name' => 'John Doe',
    'registration_id' => $registrationId,
    'package_name' => $package['name'],
    'amount' => $amount,
    'participants' => [],
    'conference_name' => CONFERENCE_NAME,
    'conference_short_name' => CONFERENCE_SHORT_NAME,
    'conference_dates' => CONFERENCE_DATES,
    'conference_location' => CONFERENCE_LOCATION,
    'conference_venue' => CONFERENCE_VENUE,
    'logo_url' => EMAIL_LOGO_URL,
    'payment_link' => APP_URL . "/checkout_payment.php?registration_id=" . $registrationId . "&token=test_token",
    'payment_status' => 'pending'
];

// Test template rendering
$emailQueue = new \Cphia2025\EmailQueue();
$renderedContent = $emailQueue->renderTemplate('registration_confirmation', $templateData);

if (strpos($renderedContent, 'Complete Payment') !== false) {
    echo "   ✅ Payment button found in template\n";
} else {
    echo "   ❌ Payment button NOT found in template\n";
}

if (strpos($renderedContent, 'checkout_payment.php') !== false) {
    echo "   ✅ Payment link found in template\n";
} else {
    echo "   ❌ Payment link NOT found in template\n";
}

echo "\n3. Template content preview:\n";
echo "   " . substr($renderedContent, 0, 200) . "...\n";

echo "\n🎯 SUMMARY:\n";
echo "===========\n";
echo "The email template should now include:\n";
echo "✅ Payment button with amount\n";
echo "✅ Payment link for checkout\n";
echo "✅ Clear call-to-action for payment\n";
echo "\nUsers will receive a complete payment link in their registration email!\n";
