<?php
/**
 * Send All Emails in Queue Command
 * This script processes ALL pending emails in the queue
 * Usage: php send_all_emails.php [--force] [--limit=N]
 * 
 * Options:
 *   --force    Skip confirmation prompt
 *   --limit=N  Process only N emails at a time (default: 100)
 *   --dry-run  Show what would be sent without actually sending
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Load Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Cphia2025\EmailQueue;

// Parse command line arguments
$options = getopt('', ['force', 'limit:', 'dry-run', 'help']);

if (isset($options['help'])) {
    echo "Send All Emails in Queue Command\n";
    echo "================================\n\n";
    echo "Usage: php send_all_emails.php [options]\n\n";
    echo "Options:\n";
    echo "  --force      Skip confirmation prompt\n";
    echo "  --limit=N    Process only N emails at a time (default: 100)\n";
    echo "  --dry-run    Show what would be sent without actually sending\n";
    echo "  --help       Show this help message\n\n";
    echo "Examples:\n";
    echo "  php send_all_emails.php\n";
    echo "  php send_all_emails.php --force\n";
    echo "  php send_all_emails.php --limit=50\n";
    echo "  php send_all_emails.php --dry-run\n";
    exit(0);
}

$force = isset($options['force']);
$limit = isset($options['limit']) ? (int)$options['limit'] : 100;
$dryRun = isset($options['dry-run']);

// Set time limit for long processing
set_time_limit(0); // No time limit

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    echo $logMessage;
    error_log($logMessage, 3, __DIR__ . '/logs/send_all_emails.log');
}

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

logMessage("=== SEND ALL EMAILS COMMAND STARTED ===");
logMessage("Force mode: " . ($force ? 'YES' : 'NO'));
logMessage("Dry run: " . ($dryRun ? 'YES' : 'NO'));
logMessage("Batch limit: $limit");

try {
    $emailQueue = new EmailQueue();
    
    // Get queue statistics first
    logMessage("Getting queue statistics...");
    $stats = $emailQueue->getStats();
    
    if (!$stats) {
        logMessage("ERROR: Could not retrieve queue statistics");
        exit(1);
    }
    
    // Display current queue status with detailed breakdown
    echo "\n📊 CURRENT EMAIL QUEUE STATUS:\n";
    echo "==============================\n";
    
    // Get detailed stats including attempts
    $detailedStats = $emailQueue->getDetailedStats();
    if ($detailedStats) {
        foreach ($detailedStats as $stat) {
            $status = $stat['status'];
            $attempts = $stat['attempts'];
            $count = $stat['count'];
            $date = $stat['date'];
            $attemptText = $attempts == 0 ? ' (new)' : " (attempts: $attempts)";
            echo sprintf("%-12s: %3d emails%s (%s)\n", ucfirst($status), $count, $attemptText, $date);
        }
    } else {
        // Fallback to basic stats
        foreach ($stats as $stat) {
            $status = $stat['status'];
            $count = $stat['count'];
            $date = $stat['date'];
            echo sprintf("%-12s: %3d emails (%s)\n", ucfirst($status), $count, $date);
        }
    }
    
    // Count total pending emails with 0 attempts
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM email_queue WHERE status = 'pending' AND attempts = 0");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalPending = $result['count'];
    
    echo "\n📧 TOTAL PENDING EMAILS (0 attempts): $totalPending\n";
    
    if ($totalPending === 0) {
        logMessage("No pending emails with 0 attempts to send. Queue is clean.");
        echo "✅ No pending emails with 0 attempts found. Nothing to do.\n";
        exit(0);
    }
    
    // Confirmation prompt (unless --force or --dry-run)
    if (!$force && !$dryRun) {
        echo "\n⚠️  WARNING: This will attempt to send ALL $totalPending pending emails.\n";
        echo "Are you sure you want to continue? (y/N): ";
        
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) !== 'y') {
            echo "❌ Operation cancelled by user.\n";
            exit(0);
        }
    }
    
    if ($dryRun) {
        echo "\n🔍 DRY RUN MODE - No emails will actually be sent\n";
        echo "================================================\n";
    } else {
        echo "\n🚀 STARTING EMAIL PROCESSING...\n";
        echo "===============================\n";
    }
    
    $totalProcessed = 0;
    $totalFailed = 0;
    $totalSkipped = 0;
    $batchNumber = 1;
    
    // Process emails in batches
    while (true) {
        logMessage("Processing batch #$batchNumber (limit: $limit)...");
        
        if ($dryRun) {
            // In dry run mode, just show what would be processed
            $pdo = getConnection();
            $stmt = $pdo->prepare("
                SELECT id, to_email, subject, template_name, status, created_at, attempts
                FROM email_queue 
                WHERE status = 'pending' AND attempts = 0
                ORDER BY priority DESC, created_at ASC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($emails)) {
                logMessage("No more pending emails found. Processing complete.");
                break;
            }
            
            echo "\nBatch #$batchNumber would process " . count($emails) . " emails:\n";
            foreach ($emails as $email) {
                echo sprintf("  - ID: %d | To: %s | Subject: %s | Template: %s | Attempts: %d\n", 
                    $email['id'], 
                    $email['to_email'], 
                    $email['subject'], 
                    $email['template_name'],
                    $email['attempts']
                );
            }
            
            $totalProcessed += count($emails);
            $batchNumber++;
            
            // In dry run, only show first batch
            break;
        } else {
            // Actually process the emails
            $result = $emailQueue->processQueue($limit);
            
            if (!$result) {
                logMessage("ERROR: Failed to process batch #$batchNumber");
                break;
            }
            
            $processed = $result['processed'];
            $failed = $result['failed'];
            $total = $result['total'];
            
            $totalProcessed += $processed;
            $totalFailed += $failed;
            $totalSkipped += ($total - $processed - $failed);
            
            logMessage("Batch #$batchNumber completed: $processed sent, $failed failed, " . ($total - $processed - $failed) . " skipped");
            
            // If we processed fewer emails than the limit, we're done
            if ($total < $limit) {
                logMessage("No more pending emails found. Processing complete.");
                break;
            }
            
            $batchNumber++;
            
            // Small delay between batches to avoid overwhelming the server
            sleep(1);
        }
    }
    
    // Final summary
    echo "\n📈 PROCESSING SUMMARY:\n";
    echo "=====================\n";
    echo "Total processed: $totalProcessed\n";
    echo "Total failed: $totalFailed\n";
    echo "Total skipped: $totalSkipped\n";
    echo "Batches processed: " . ($batchNumber - 1) . "\n";
    
    if ($dryRun) {
        echo "\n✅ Dry run completed. No emails were actually sent.\n";
        echo "Run without --dry-run to actually send the emails.\n";
    } else {
        if ($totalFailed > 0) {
            echo "\n⚠️  Some emails failed to send. Check the logs for details.\n";
        } else {
            echo "\n✅ All emails processed successfully!\n";
        }
        
        // Clean up old processed emails
        echo "\n🧹 CLEANING UP OLD EMAILS...\n";
        echo "============================\n";
        
        $cleanupResult = $emailQueue->cleanupQueue(7); // Keep emails for 7 days
        if ($cleanupResult) {
            $deleted = $cleanupResult['deleted'];
            $daysOld = $cleanupResult['days_old'];
            
            if ($deleted > 0) {
                echo "✅ Cleaned up $deleted old emails (older than $daysOld days)\n";
                logMessage("Queue cleanup: Deleted $deleted old emails");
            } else {
                echo "✅ No old emails to clean up\n";
                logMessage("Queue cleanup: No old emails found");
            }
        } else {
            echo "⚠️  Could not clean up old emails\n";
            logMessage("Queue cleanup: Failed to clean up old emails");
        }
    }
    
} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage());
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

logMessage("=== SEND ALL EMAILS COMMAND COMPLETED ===");
echo "\n🎉 Command completed!\n";
