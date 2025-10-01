<?php
/**
 * Cron Setup Script
 * This script helps set up the cron jobs for email processing
 */

require_once 'bootstrap.php';

echo "<h1>CPHIA 2025 Email System - Cron Setup</h1>";

// Check if we can create the email queue table
try {
    require_once 'migrations.php';
    createTables();
    echo "<p style='color: green;'>✅ Email queue table created successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating email queue table: " . $e->getMessage() . "</p>";
}

echo "<h2>Cron Job Configuration</h2>";
echo "<p>Add the following cron jobs to your server:</p>";

$projectPath = __DIR__;
$phpPath = PHP_BINARY;

echo "<h3>1. Email Processor (Every 5 minutes)</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo "*/5 * * * * $phpPath $projectPath/process_emails.php >> $projectPath/logs/cron.log 2>&1";
echo "</pre>";

echo "<h3>2. Daily Reminders (Every 24 hours at 9 AM)</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo "0 9 * * * $phpPath $projectPath/daily_reminders.php >> $projectPath/logs/cron.log 2>&1";
echo "</pre>";

echo "<h3>3. Clean Old Emails (Every Sunday at 2 AM)</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo "0 2 * * 0 $phpPath $projectPath/cleanup_emails.php >> $projectPath/logs/cron.log 2>&1";
echo "</pre>";

echo "<h2>Manual Testing</h2>";
echo "<p>You can test the email system manually:</p>";
echo "<ul>";
echo "<li><a href='test_email_queue.php'>Test Email Queue</a></li>";
echo "<li><a href='email_stats.php'>View Email Statistics</a></li>";
echo "<li><a href='process_emails.php'>Process Emails Now</a></li>";
echo "</ul>";

echo "<h2>Log Files</h2>";
echo "<p>Check the following log files for debugging:</p>";
echo "<ul>";
echo "<li><code>$projectPath/logs/email_processor.log</code> - Email processing logs</li>";
echo "<li><code>$projectPath/logs/daily_reminders.log</code> - Daily reminder logs</li>";
echo "<li><code>$projectPath/logs/cron.log</code> - General cron logs</li>";
echo "</ul>";

echo "<h2>Email Queue Status</h2>";

try {
    $emailQueue = new \Cphia2025\EmailQueue();
    $stats = $emailQueue->getStats();
    
    if (empty($stats)) {
        echo "<p>No email statistics available yet.</p>";
    } else {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>Status</th><th>Type</th><th>Date</th><th>Count</th></tr>";
        foreach ($stats as $stat) {
            echo "<tr>";
            echo "<td>{$stat['status']}</td>";
            echo "<td>{$stat['email_type']}</td>";
            echo "<td>{$stat['date']}</td>";
            echo "<td>{$stat['count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error loading email statistics: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Set up the cron jobs on your server</li>";
echo "<li>Test the email system with a registration</li>";
echo "<li>Monitor the log files for any issues</li>";
echo "<li>Configure your SMTP settings in the .env file</li>";
echo "</ol>";

echo "<p style='margin-top: 30px; padding: 15px; background: #d1ecf1; border-radius: 8px;'>";
echo "<strong>Note:</strong> Make sure your web server has write permissions to the logs directory.";
echo "</p>";
?>
