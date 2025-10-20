<?php
/**
 * Test Attendance Confirmation Email - Second Test
 * 
 * This script sends a test attendance confirmation email to kibsden@gmail.com
 * using sample data to test the email template and functionality.
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "=== CPHIA 2025 Attendance Confirmation Email Test #2 ===\n";
echo "Sending test email to: kibsden@gmail.com\n\n";

try {
    $emailQueue = new \Cphia2025\EmailQueue();
    
    // Test data for a paid participant
    $testData = [
        'user_name' => 'Kibson Den',
        'registration_id' => '5678',
        'package_name' => 'Non African nationals',
        'registration_type' => 'Individual',
        'amount' => '$400.00',
        'payment_status' => 'completed', // This will show as "Paid"
        'confirmation_url' => rtrim(APP_URL, '/') . "/confirm_attendance.php",
        'user_email' => 'kibsden@gmail.com',
        'support_email' => SUPPORT_EMAIL,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'logo_url' => EMAIL_LOGO_URL
    ];
    
    // Add email to queue
    $result = $emailQueue->addToQueue(
        'kibsden@gmail.com',
        'Kibson Den',
        CONFERENCE_SHORT_NAME . " - Confirm Your Attendance #5678 (TEST)",
        'attendance_confirmation',
        $testData,
        'attendance_confirmation',
        1 // High priority for test
    );
    
    if ($result) {
        echo "✓ Test email queued successfully!\n";
        echo "Check kibsden@gmail.com inbox for the test message.\n";
        echo "\nEmail details:\n";
        echo "- Recipient: kibsden@gmail.com\n";
        echo "- Subject: " . CONFERENCE_SHORT_NAME . " - Confirm Your Attendance #5678 (TEST)\n";
        echo "- Template: attendance_confirmation\n";
        echo "- Priority: High (1)\n";
        echo "- Registration Type: Paid Participant (\$400.00)\n";
        
        // Log the test
        logSecurityEvent('attendance_confirmation_test2', "Sent test attendance confirmation email to kibsden@gmail.com");
        
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
