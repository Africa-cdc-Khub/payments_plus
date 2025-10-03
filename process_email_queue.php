<?php
/**
 * Email Queue Processor
 * This script processes pending emails in the queue
 * Can be run manually or via cron every few minutes
 * Example cron: 0,5,10,15,20,25,30,35,40,45,50,55 * * * * /usr/bin/php /path/to/process_email_queue.php
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
    error_log($logMessage, 3, __DIR__ . '/logs/email_queue.log');
}

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

logMessage("Starting email queue processing...");

try {
    $emailQueue = new EmailQueue();
    
    // Process pending emails in queue
    $queueResult = $emailQueue->processQueue(50); // Process up to 50 emails
    if ($queueResult) {
        logMessage("Processed {$queueResult['processed']} emails successfully");
        if ($queueResult['failed'] > 0) {
            logMessage("Failed to send {$queueResult['failed']} emails");
        }
        logMessage("Total emails processed: {$queueResult['total']}");
    } else {
        logMessage("Error processing email queue");
    }
    
} catch (Exception $e) {
    logMessage("Fatal error in email queue processing: " . $e->getMessage());
    exit(1);
}

logMessage("Email queue processing completed.");
