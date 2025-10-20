<?php
/**
 * Send Attendance Confirmation Emails
 * 
 * This script sends attendance confirmation emails to all approved delegates
 * and paid participants who haven't already confirmed their attendance.
 * 
 * Usage: php send_attendance_confirmation_emails.php [--test-email=email@example.com]
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Command line argument parsing
$testEmail = null;
$dryRun = false;

foreach ($argv as $arg) {
    if (strpos($arg, '--test-email=') === 0) {
        $testEmail = substr($arg, 13);
    } elseif ($arg === '--dry-run') {
        $dryRun = true;
    } elseif ($arg === '--help' || $arg === '-h') {
        echo "Usage: php send_attendance_confirmation_emails.php [options]\n";
        echo "Options:\n";
        echo "  --test-email=email@example.com  Send test email to specific address\n";
        echo "  --dry-run                       Show what would be sent without actually sending\n";
        echo "  --help, -h                      Show this help message\n";
        exit(0);
    }
}

echo "=== CPHIA 2025 Attendance Confirmation Email Sender ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $pdo = getConnection();
    
    // Query to get all eligible participants
    $query = "
        SELECT 
            r.id as registration_id,
            r.status,
            r.payment_status,
            r.total_amount,
            r.currency,
            r.registration_type,
            r.created_at as registration_date,
            p.name as package_name,
            u.id as user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.attendance_status,
            u.attendance_verified_at
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        JOIN packages p ON r.package_id = p.id
        WHERE r.voided_at IS NULL
        AND (
            (r.payment_status = 'completed' AND r.total_amount > 0) OR
            (r.total_amount = 0 AND r.status = 'approved')
        )
        AND u.attendance_status IS NULL
        ORDER BY r.created_at ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($participants) . " eligible participants for attendance confirmation emails.\n\n";
    
    if (empty($participants)) {
        echo "No eligible participants found. All approved delegates and paid participants may have already confirmed attendance.\n";
        exit(0);
    }
    
    // If test email is specified, only send to that email
    if ($testEmail) {
        echo "TEST MODE: Sending only to {$testEmail}\n";
        $participants = array_filter($participants, function($participant) use ($testEmail) {
            return $participant['email'] === $testEmail;
        });
        
        if (empty($participants)) {
            echo "No participant found with email: {$testEmail}\n";
            exit(1);
        }
    }
    
    $emailQueue = new \Cphia2025\EmailQueue();
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($participants as $participant) {
        $userName = $participant['first_name'] . ' ' . $participant['last_name'];
        $registrationId = $participant['registration_id'];
        $email = $participant['email'];
        
        // Generate confirmation URL
        $confirmationUrl = rtrim(APP_URL, '/') . "/confirm_attendance.php";
        
        // Determine status for display
        $statusDisplay = '';
        if ($participant['payment_status'] === 'completed') {
            $statusDisplay = 'Paid';
        } else {
            $statusDisplay = 'Approved Delegate';
        }
        
        // Prepare template data
        $templateData = [
            'user_name' => $userName,
            'registration_id' => $registrationId,
            'package_name' => $participant['package_name'],
            'registration_type' => ucfirst($participant['registration_type']),
            'amount' => formatCurrency($participant['total_amount'], $participant['currency']),
            'payment_status' => $participant['payment_status'],
            'confirmation_url' => $confirmationUrl,
            'user_email' => $email,
            'support_email' => SUPPORT_EMAIL,
            'conference_name' => CONFERENCE_NAME,
            'conference_short_name' => CONFERENCE_SHORT_NAME,
            'conference_dates' => CONFERENCE_DATES,
            'conference_location' => CONFERENCE_LOCATION,
            'logo_url' => EMAIL_LOGO_URL
        ];
        
        if ($dryRun) {
            echo "DRY RUN: Would send email to {$userName} ({$email}) - Registration #{$registrationId}\n";
            $successCount++;
            continue;
        }
        
        // Add email to queue
        $result = $emailQueue->addToQueue(
            $email,
            $userName,
            CONFERENCE_SHORT_NAME . " - Confirm Your Attendance #" . $registrationId,
            'attendance_confirmation',
            $templateData,
            'attendance_confirmation',
            3 // Priority 3 (normal)
        );
        
        if ($result) {
            echo "✓ Queued email for {$userName} ({$email}) - Registration #{$registrationId}\n";
            $successCount++;
        } else {
            echo "✗ Failed to queue email for {$userName} ({$email}) - Registration #{$registrationId}\n";
            $errorCount++;
            $errors[] = "Failed to queue email for {$userName} ({$email})";
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Total participants processed: " . count($participants) . "\n";
    echo "Emails queued successfully: {$successCount}\n";
    echo "Errors: {$errorCount}\n";
    
    if (!empty($errors)) {
        echo "\nErrors:\n";
        foreach ($errors as $error) {
            echo "- {$error}\n";
        }
    }
    
    if ($testEmail) {
        echo "\nTEST MODE: Email queued for {$testEmail}\n";
        echo "Check your email inbox for the test message.\n";
    } else {
        echo "\nAll emails have been queued for sending.\n";
        echo "The email queue will process these emails automatically.\n";
    }
    
    // Log the batch sending
    logSecurityEvent('attendance_confirmation_batch', "Sent attendance confirmation emails to {$successCount} participants" . ($testEmail ? " (test mode: {$testEmail})" : ""));
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Attendance confirmation email batch error: " . $e->getMessage());
    exit(1);
}

echo "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";
?>
