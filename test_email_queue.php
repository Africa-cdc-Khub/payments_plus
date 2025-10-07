<?php
/**
 * Test Email Queue System
 * This script tests the email queue by simulating a registration
 */

require_once 'bootstrap.php';
require_once 'functions.php';

use Cphia2025\EmailQueue;

echo "Testing Email Queue System...\n";

try {
    // Test data
    $user = [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com'
    ];
    
    $package = [
        'name' => 'Test Package',
        'id' => 1
    ];
    
    $registrationId = 999;
    $amount = 200.00;
    $participants = [];
    
    echo "1. Testing sendRegistrationEmails function...\n";
    
    // Test registration emails
    $result = sendRegistrationEmails($user, $registrationId, $package, $amount, $participants);
    
    if ($result) {
        echo "✓ Registration emails queued successfully\n";
    } else {
        echo "✗ Failed to queue registration emails\n";
    }
    
    // Check queue status
    $pdo = getConnection();
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM email_queue WHERE status = "pending"');
    $result = $stmt->fetch();
    echo "2. Pending emails in queue: " . $result['count'] . "\n";
    
    // Process the queue
    echo "3. Processing email queue...\n";
    $emailQueue = new EmailQueue();
    $queueResult = $emailQueue->processQueue(10);
    
    if ($queueResult) {
        echo "✓ Processed {$queueResult['processed']} emails successfully\n";
        if ($queueResult['failed'] > 0) {
            echo "✗ Failed to send {$queueResult['failed']} emails\n";
        }
    } else {
        echo "✗ Error processing email queue\n";
    }
    
    // Check final status
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM email_queue WHERE status = "sent"');
    $result = $stmt->fetch();
    echo "4. Sent emails: " . $result['count'] . "\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM email_queue WHERE status = "pending"');
    $result = $stmt->fetch();
    echo "5. Remaining pending emails: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
