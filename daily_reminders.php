<?php
/**
 * Daily Reminder Generator
 * This script should be run by cron every 24 hours
 * Example cron: 0 9 * * * /usr/bin/php /path/to/daily_reminders.php
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
    error_log($logMessage, 3, __DIR__ . '/logs/daily_reminders.log');
}

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

logMessage("Starting daily reminder generation...");

try {
    $emailQueue = new EmailQueue();
    
    // Add payment reminders
    $paymentReminders = $emailQueue->addPaymentReminders();
    logMessage("Added $paymentReminders payment reminder emails to queue");
    
    // Add admin reminders
    $adminReminders = $emailQueue->addAdminReminders();
    if ($adminReminders) {
        logMessage("Added admin reminder email to queue");
    } else {
        logMessage("No admin reminders needed");
    }
    
    // Reset failed emails for retry
    $resetCount = $emailQueue->resetFailedEmails(24);
    logMessage("Reset $resetCount failed emails for retry");
    
    // Get statistics
    $stats = $emailQueue->getStats();
    logMessage("Email queue statistics:");
    foreach ($stats as $stat) {
        logMessage("- {$stat['status']} {$stat['email_type']} on {$stat['date']}: {$stat['count']}");
    }
    
} catch (Exception $e) {
    logMessage("Fatal error in daily reminders: " . $e->getMessage());
    exit(1);
}

logMessage("Daily reminder generation completed.");
