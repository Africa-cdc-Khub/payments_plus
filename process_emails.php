<?php
/*
 Email Queue Processor
  This script should be run by cron every 5-10 minutes
  Example cron: 5 * * * * /usr/bin/php /path/to/process_emails.php
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Load Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Cphia2025\EmailQueue;
use Cphia2025\EmailService;

// Set time limit for long-running process
set_time_limit(300); // 5 minutes

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    echo $logMessage;
    error_log($logMessage, 3, __DIR__ . '/logs/email_processor.log');
}

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

logMessage("Starting email processing...");

try {
    $emailQueue = new EmailQueue();
    $emailService = new EmailService();
    
    // Get pending emails
    $pendingEmails = $emailQueue->getPendingEmails(50);
    
    if (empty($pendingEmails)) {
        logMessage("No pending emails to process.");
        exit(0);
    }
    
    logMessage("Processing " . count($pendingEmails) . " emails...");
    
    $processed = 0;
    $sent = 0;
    $failed = 0;
    
    foreach ($pendingEmails as $email) {
        try {
            // Mark as processing
            $emailQueue->markAsProcessing($email['id']);
            
            // Decode template data
            $templateData = json_decode($email['template_data'], true) ?: [];
            
            // Send email
            $result = $emailService->sendEmail(
                $email['to_email'],
                $email['to_name'],
                $email['subject'],
                $email['template_name'],
                $templateData
            );
            
            if ($result) {
                $emailQueue->markAsSent($email['id']);
                $sent++;
                logMessage("Email sent successfully to: " . $email['to_email']);
            } else {
                $emailQueue->markAsFailed($email['id'], "Email service returned false");
                $failed++;
                logMessage("Failed to send email to: " . $email['to_email']);
            }
            
        } catch (Exception $e) {
            $emailQueue->markAsFailed($email['id'], $e->getMessage());
            $failed++;
            logMessage("Exception sending email to " . $email['to_email'] . ": " . $e->getMessage());
        }
        
        $processed++;
        
        // Small delay to prevent overwhelming the email server
        usleep(100000); // 0.1 second
    }
    
    logMessage("Email processing completed. Processed: $processed, Sent: $sent, Failed: $failed");
    
    // Clean up old emails
    $emailQueue->cleanOldEmails(30);
    
} catch (Exception $e) {
    logMessage("Fatal error in email processor: " . $e->getMessage());
    exit(1);
}

logMessage("Email processing finished.");
