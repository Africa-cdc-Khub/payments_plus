<?php
/**
 * Production Email Queue Processor
 * Enhanced version with better error handling and logging
 * Designed for production cron jobs
 */

// Set error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set time limit
set_time_limit(300); // 5 minutes

// Define script directory
$scriptDir = __DIR__;

// Check if required files exist
$requiredFiles = [
    'bootstrap.php',
    'functions.php',
    'src/EmailQueue.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($scriptDir . '/' . $file)) {
        error_log("FATAL: Required file missing: $file");
        exit(1);
    }
}

require_once 'bootstrap.php';
require_once 'functions.php';

// Load Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Cphia2025\EmailQueue;

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0755, true)) {
        error_log("FATAL: Cannot create logs directory: $logDir");
        exit(1);
    }
}

// Log function with better formatting
function logMessage($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $pid = getmypid();
    $logMessage = "[$timestamp] [$level] [PID:$pid] $message" . PHP_EOL;
    echo $logMessage;
    error_log($logMessage, 3, __DIR__ . '/logs/email_queue.log');
}

// Check for lock file to prevent multiple instances
$lockFile = '/tmp/email_processor.lock';
if (file_exists($lockFile)) {
    $lockTime = filemtime($lockFile);
    $lockAge = time() - $lockTime;
    
    // If lock is older than 10 minutes, remove it (stale lock)
    if ($lockAge > 600) {
        unlink($lockFile);
        logMessage("Removed stale lock file (age: {$lockAge}s)", 'WARN');
    } else {
        logMessage("Email processor already running (lock age: {$lockAge}s), exiting", 'INFO');
        exit(0);
    }
}

// Create lock file
file_put_contents($lockFile, getmypid());

// Cleanup function
function cleanup() {
    global $lockFile;
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}

// Register cleanup function
register_shutdown_function('cleanup');

logMessage("=== EMAIL QUEUE PROCESSOR STARTED ===", 'INFO');

try {
    // Test database connection
    $pdo = getConnection();
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }
    logMessage("Database connection successful", 'INFO');
    
    $emailQueue = new EmailQueue();
    
    // Get queue statistics before processing
    $stats = $emailQueue->getStats();
    if ($stats) {
        $totalPending = 0;
        foreach ($stats as $stat) {
            if ($stat['status'] === 'pending') {
                $totalPending += $stat['count'];
            }
        }
        logMessage("Queue status: $totalPending pending emails", 'INFO');
    }
    
    // Process pending emails in queue
    $queueResult = $emailQueue->processQueue(50); // Process up to 50 emails
    
    if ($queueResult) {
        $processed = $queueResult['processed'];
        $failed = $queueResult['failed'];
        $total = $queueResult['total'];
        
        logMessage("Processing completed: $processed sent, $failed failed, $total total", 'INFO');
        
        if ($failed > 0) {
            logMessage("Warning: $failed emails failed to send", 'WARN');
        }
        
        // If we processed emails, show success
        if ($processed > 0) {
            logMessage("Successfully processed $processed emails", 'INFO');
        } else {
            logMessage("No emails to process", 'INFO');
        }
        
    } else {
        logMessage("Error processing email queue - no result returned", 'ERROR');
        exit(1);
    }
    
} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage(), 'ERROR');
    logMessage("Stack trace: " . $e->getTraceAsString(), 'ERROR');
    exit(1);
} catch (Error $e) {
    logMessage("FATAL PHP ERROR: " . $e->getMessage(), 'ERROR');
    logMessage("Stack trace: " . $e->getTraceAsString(), 'ERROR');
    exit(1);
}

logMessage("=== EMAIL QUEUE PROCESSOR COMPLETED ===", 'INFO');
