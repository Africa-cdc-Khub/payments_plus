<?php
/**
 * Test Attendance Confirmation Email
 * 
 * This script sends a test attendance confirmation email to agabaandre@gmail.com
 * using sample data to test the email template and functionality.
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "=== CPHIA 2025 Attendance Confirmation Email Test ===\n";
echo "Sending test email to: agabaandre@gmail.com\n\n";

try {
    $emailQueue = new \Cphia2025\EmailQueue();
    
    // Test data
    $testData = [
        'user_name' => 'Andrew Agaba',
        'registration_id' => '1234',
        'package_name' => 'Delegates',
        'registration_type' => 'Individual',
        'amount' => '$0.00',
        'payment_status' => 'pending', // This will show as "Approved Delegate"
        'confirmation_url' => rtrim(APP_URL, '/') . "/confirm_attendance.php",
        'user_email' => 'agabaandre@gmail.com',
        'support_email' => SUPPORT_EMAIL,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'logo_url' => EMAIL_LOGO_URL
    ];
    
    // Add email to queue
    $result = $emailQueue->addToQueue(
        'agabaandre@gmail.com',
        'Andrew Agaba',
        CONFERENCE_SHORT_NAME . " - Confirm Your Attendance #1234 (TEST)",
        'attendance_confirmation',
        $testData,
        'attendance_confirmation',
        1 // High priority for test
    );
    
    if ($result) {
        echo "✓ Test email queued successfully!\n";
        echo "Check agabaandre@gmail.com inbox for the test message.\n";
        echo "\nEmail details:\n";
        echo "- Recipient: agabaandre@gmail.com\n";
        echo "- Subject: " . CONFERENCE_SHORT_NAME . " - Confirm Your Attendance #1234 (TEST)\n";
        echo "- Template: attendance_confirmation\n";
        echo "- Priority: High (1)\n";
        
        // Log the test
        logSecurityEvent('attendance_confirmation_test', "Sent test attendance confirmation email to agabaandre@gmail.com");
        
    } else {
        echo "✗ Failed to queue test email!\n";
        echo "Check the error logs for more details.\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Test attendance confirmation email error: " . $e->getMessage());
    exit(1);
}

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
?>
