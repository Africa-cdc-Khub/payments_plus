<?php
/**
 * Email Cleanup Script
 * This script cleans up old sent emails and failed emails
 * Should be run weekly via cron
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Load Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Cphia2025\EmailQueue;

// Set time limit
set_time_limit(300); // 5 minutes

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    echo $logMessage;
    error_log($logMessage, 3, __DIR__ . '/logs/cleanup.log');
}

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

logMessage("Starting email cleanup...");

try {
    $emailQueue = new EmailQueue();
    
    // Clean old sent emails (older than 30 days)
    $cleaned = $emailQueue->cleanOldEmails(30);
    logMessage("Cleaned $cleaned old sent emails");
    
    // Reset failed emails for retry (older than 7 days)
    $resetCount = $emailQueue->resetFailedEmails(168); // 7 days in hours
    logMessage("Reset $resetCount failed emails for retry");
    
    // Get current statistics
    $stats = $emailQueue->getStats();
    $totalEmails = array_sum(array_column($stats, 'count'));
    logMessage("Total emails in queue: $totalEmails");
    
} catch (Exception $e) {
    logMessage("Fatal error in cleanup: " . $e->getMessage());
    exit(1);
}

logMessage("Email cleanup completed.");
