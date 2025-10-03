#!/bin/bash

# Email Queue Setup Script
# This script sets up the email queue system with cron jobs and monitoring

echo "=== CPHIA 2025 Email Queue Setup ==="
echo ""

# Get the current directory
CURRENT_DIR=$(pwd)
echo "Current directory: $CURRENT_DIR"

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed or not in PATH"
    exit 1
fi

echo "✅ PHP found: $(php -v | head -n 1)"

# Check if the required files exist
REQUIRED_FILES=(
    "process_email_queue.php"
    "daily_reminders.php"
    "src/EmailQueue.php"
    "bootstrap.php"
    "functions.php"
)

echo ""
echo "Checking required files..."
for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file (missing)"
        exit 1
    fi
done

# Create logs directory if it doesn't exist
echo ""
echo "Setting up logs directory..."
mkdir -p logs
chmod 755 logs
echo "✅ Logs directory created"

# Test the email queue system
echo ""
echo "Testing email queue system..."
php -r "
require_once 'bootstrap.php';
require_once 'functions.php';
use Cphia2025\EmailQueue;
try {
    \$emailQueue = new EmailQueue();
    echo '✅ EmailQueue class loaded successfully' . PHP_EOL;
} catch (Exception \$e) {
    echo '❌ Error loading EmailQueue: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

if [ $? -ne 0 ]; then
    echo "❌ Email queue test failed"
    exit 1
fi

# Create cron job setup
echo ""
echo "Setting up cron jobs..."

# Create a temporary cron file
CRON_FILE="/tmp/cphia_email_queue_cron"
cat > "$CRON_FILE" << EOF
# CPHIA 2025 Email Queue System
# Process email queue every 5 minutes
*/5 * * * * cd $CURRENT_DIR && /usr/bin/php process_email_queue.php >> logs/email_queue.log 2>&1

# Daily reminders at 9 AM
0 9 * * * cd $CURRENT_DIR && /usr/bin/php daily_reminders.php >> logs/daily_reminders.log 2>&1

# Clean up old logs weekly (keep last 30 days)
0 2 * * 0 find $CURRENT_DIR/logs -name "*.log" -mtime +30 -delete
EOF

echo "Cron jobs configuration:"
echo "========================"
cat "$CRON_FILE"
echo ""

# Ask user if they want to install the cron jobs
read -p "Do you want to install these cron jobs? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Install cron jobs
    (crontab -l 2>/dev/null; cat "$CRON_FILE") | crontab -
    echo "✅ Cron jobs installed successfully"
else
    echo "⚠️  Cron jobs not installed. You can install them manually later."
    echo "   To install manually, run: crontab -e"
    echo "   Then add the contents of: $CRON_FILE"
fi

# Create monitoring script
echo ""
echo "Creating monitoring script..."
cat > "monitor_email_queue.php" << 'EOF'
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
EOF

chmod +x monitor_email_queue.php
echo "✅ Monitoring script created: monitor_email_queue.php"

# Create queue management script
echo ""
echo "Creating queue management script..."
cat > "manage_email_queue.php" << 'EOF'
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
EOF

chmod +x manage_email_queue.php
echo "✅ Management script created: manage_email_queue.php"

# Create README for email queue
echo ""
echo "Creating documentation..."
cat > "EMAIL_QUEUE_README.md" << EOF
# CPHIA 2025 Email Queue System

## Overview
The email queue system processes registration and notification emails asynchronously, making the registration process faster and more reliable.

## Files
- \`process_email_queue.php\` - Processes pending emails (run every 5 minutes)
- \`daily_reminders.php\` - Generates daily reminders (run once daily)
- \`monitor_email_queue.php\` - Monitor queue status
- \`manage_email_queue.php\` - Manage queue operations
- \`src/EmailQueue.php\` - Core queue functionality

## Setup
1. Run: \`bash setup_email_queue.sh\`
2. This will install cron jobs and create monitoring scripts

## Manual Commands
\`\`\`bash
# Process pending emails
php manage_email_queue.php process

# Show queue statistics
php manage_email_queue.php stats

# Monitor queue status
php monitor_email_queue.php

# Test email sending
php manage_email_queue.php test

# Reset failed emails
php manage_email_queue.php reset

# Clear old emails
php manage_email_queue.php clear
\`\`\`

## Cron Jobs
- **Queue Processor**: Every 5 minutes
- **Daily Reminders**: 9 AM daily
- **Log Cleanup**: Weekly

## Monitoring
- Check logs in \`logs/\` directory
- Use \`monitor_email_queue.php\` for real-time status
- Monitor failed emails and retry as needed

## Troubleshooting
1. Check PHP error logs
2. Verify Exchange email service configuration
3. Check database connectivity
4. Review queue statistics for patterns
EOF

echo "✅ Documentation created: EMAIL_QUEUE_README.md"

# Final summary
echo ""
echo "=== Setup Complete ==="
echo "✅ Email queue system configured"
echo "✅ Monitoring scripts created"
echo "✅ Management tools ready"
echo "✅ Documentation provided"
echo ""
echo "Next steps:"
echo "1. Test the system: php manage_email_queue.php test"
echo "2. Process emails: php manage_email_queue.php process"
echo "3. Monitor status: php monitor_email_queue.php"
echo "4. Check logs: tail -f logs/email_queue.log"
echo ""
echo "For help: php manage_email_queue.php help"
