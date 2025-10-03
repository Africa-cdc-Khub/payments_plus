<?php
/**
 * Email Queue Manager
 * This script provides management functions for the email queue
 */

require_once 'bootstrap.php';
require_once 'functions.php';

use Cphia2025\EmailQueue;

function showHelp() {
    echo "Email Queue Manager\n";
    echo "==================\n";
    echo "Usage: php manage_email_queue.php [command]\n\n";
    echo "Commands:\n";
    echo "  process    - Process pending emails\n";
    echo "  stats      - Show queue statistics\n";
    echo "  reset      - Reset failed emails for retry\n";
    echo "  clear      - Clear old sent emails (older than 30 days)\n";
    echo "  test       - Test email sending\n";
    echo "  help       - Show this help\n";
}

function processQueue() {
    echo "Processing email queue...\n";
    $emailQueue = new EmailQueue();
    $result = $emailQueue->processQueue(50);
    
    if ($result) {
        echo "✅ Processed {$result['processed']} emails successfully\n";
        if ($result['failed'] > 0) {
            echo "❌ Failed to send {$result['failed']} emails\n";
        }
        echo "Total processed: {$result['total']}\n";
    } else {
        echo "❌ Error processing email queue\n";
    }
}

function showStats() {
    $pdo = getConnection();
    
    echo "Email Queue Statistics\n";
    echo "=====================\n";
    
    // Status counts
    $statuses = ['pending', 'processing', 'sent', 'failed'];
    foreach ($statuses as $status) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM email_queue WHERE status = ?");
        $stmt->execute([$status]);
        $result = $stmt->fetch();
        echo ucfirst($status) . ": " . $result['count'] . "\n";
    }
    
    // Recent activity
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            status,
            COUNT(*) as count
        FROM email_queue 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
        GROUP BY DATE(created_at), status
        ORDER BY date DESC, status
    ");
    $activities = $stmt->fetchAll();
    
    if (!empty($activities)) {
        echo "\nRecent Activity (Last 7 Days):\n";
        foreach ($activities as $activity) {
            echo "  " . $activity['date'] . " - " . $activity['status'] . ": " . $activity['count'] . "\n";
        }
    }
}

function resetFailedEmails() {
    echo "Resetting failed emails for retry...\n";
    $emailQueue = new EmailQueue();
    $count = $emailQueue->resetFailedEmails(24);
    echo "✅ Reset $count failed emails\n";
}

function clearOldEmails() {
    echo "Clearing old sent emails (older than 30 days)...\n";
    $pdo = getConnection();
    $stmt = $pdo->prepare("DELETE FROM email_queue WHERE status = 'sent' AND sent_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "✅ Cleared $count old emails\n";
}

function testEmail() {
    echo "Testing email sending...\n";
    $emailQueue = new EmailQueue();
    
    $result = $emailQueue->addToQueue(
        'test@example.com',
        'Test User',
        'Test Email - ' . date('Y-m-d H:i:s'),
        'registration_confirmation',
        ['user_name' => 'Test User', 'registration_id' => 'TEST'],
        'registration_confirmation',
        1
    );
    
    if ($result) {
        echo "✅ Test email queued successfully (ID: $result)\n";
        echo "Run 'php manage_email_queue.php process' to send it\n";
    } else {
        echo "❌ Failed to queue test email\n";
    }
}

// Main execution
$command = $argv[1] ?? 'help';

switch ($command) {
    case 'process':
        processQueue();
        break;
    case 'stats':
        showStats();
        break;
    case 'reset':
        resetFailedEmails();
        break;
    case 'clear':
        clearOldEmails();
        break;
    case 'test':
        testEmail();
        break;
    case 'help':
    default:
        showHelp();
        break;
}
