<?php
/**
 * Cleanup Duplicate Emails Script
 * This script removes duplicate emails from the queue to prevent spam
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "Cleaning up duplicate emails from queue\n";
echo "=====================================\n\n";

try {
    // Get database connection
    $pdo = getConnection();
    
    // Find duplicate emails (same recipient, subject, and template within 24 hours)
    $duplicatesStmt = $pdo->prepare("
        SELECT 
            to_email, 
            subject, 
            template_name, 
            COUNT(*) as count,
            GROUP_CONCAT(id ORDER BY created_at ASC) as email_ids
        FROM email_queue 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        AND status IN ('pending', 'sent', 'processing')
        GROUP BY to_email, subject, template_name
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ");
    
    $duplicatesStmt->execute();
    $duplicates = $duplicatesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "✅ No duplicate emails found in the queue\n";
        exit;
    }
    
    echo "Found " . count($duplicates) . " groups of duplicate emails:\n\n";
    
    $totalRemoved = 0;
    
    foreach ($duplicates as $duplicate) {
        echo "Duplicate group:\n";
        echo "- Email: {$duplicate['to_email']}\n";
        echo "- Subject: {$duplicate['subject']}\n";
        echo "- Template: {$duplicate['template_name']}\n";
        echo "- Count: {$duplicate['count']}\n";
        
        // Get all email IDs for this duplicate group
        $emailIds = explode(',', $duplicate['email_ids']);
        $keepId = $emailIds[0]; // Keep the first (oldest) email
        $removeIds = array_slice($emailIds, 1); // Remove the rest
        
        echo "- Keeping email ID: $keepId\n";
        echo "- Removing email IDs: " . implode(', ', $removeIds) . "\n";
        
        // Remove duplicate emails (keep the oldest one)
        $removeStmt = $pdo->prepare("
            DELETE FROM email_queue 
            WHERE id IN (" . implode(',', array_fill(0, count($removeIds), '?')) . ")
        ");
        $removeStmt->execute($removeIds);
        $removedCount = $removeStmt->rowCount();
        
        echo "- Removed: $removedCount duplicate emails\n";
        $totalRemoved += $removedCount;
        echo "---\n";
    }
    
    echo "\n✅ Cleanup completed!\n";
    echo "Total duplicate emails removed: $totalRemoved\n";
    
    // Show current queue status
    echo "\nCurrent queue status:\n";
    echo "====================\n";
    
    $statusStmt = $pdo->prepare("
        SELECT status, COUNT(*) as count
        FROM email_queue
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY status
        ORDER BY status
    ");
    $statusStmt->execute();
    $statuses = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($statuses as $status) {
        echo "- {$status['status']}: {$status['count']} emails\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nCleanup completed.\n";
?>
