<?php
/**
 * Email Queue Monitor
 * This script provides real-time monitoring of the email queue
 */

require_once 'bootstrap.php';
require_once 'functions.php';

use Cphia2025\EmailQueue;

function getQueueStats() {
    $pdo = getConnection();
    
    $stats = [];
    
    // Get counts by status
    $statuses = ['pending', 'processing', 'sent', 'failed'];
    foreach ($statuses as $status) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM email_queue WHERE status = ?");
        $stmt->execute([$status]);
        $result = $stmt->fetch();
        $stats[$status] = $result['count'];
    }
    
    // Get recent activity (last 24 hours)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            status,
            COUNT(*) as count
        FROM email_queue 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY DATE(created_at), status
        ORDER BY date DESC, status
    ");
    $stmt->execute();
    $stats['recent_activity'] = $stmt->fetchAll();
    
    return $stats;
}

function displayStats($stats) {
    echo "=== Email Queue Status ===\n";
    echo "Pending:   " . $stats['pending'] . "\n";
    echo "Processing: " . $stats['processing'] . "\n";
    echo "Sent:      " . $stats['sent'] . "\n";
    echo "Failed:    " . $stats['failed'] . "\n";
    echo "\n";
    
    if (!empty($stats['recent_activity'])) {
        echo "=== Recent Activity (Last 24 Hours) ===\n";
        foreach ($stats['recent_activity'] as $activity) {
            echo $activity['date'] . " - " . $activity['status'] . ": " . $activity['count'] . "\n";
        }
    }
}

// Main execution
try {
    $stats = getQueueStats();
    displayStats($stats);
    
    // If there are pending emails, suggest processing
    if ($stats['pending'] > 0) {
        echo "\n⚠️  There are " . $stats['pending'] . " pending emails.\n";
        echo "Run: php process_email_queue.php\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
