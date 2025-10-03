<?php
/**
 * Cron Setup Script
 * This script helps set up cron jobs for the email queue system
 */

$currentDir = __DIR__;
$phpPath = '/usr/bin/php'; // Default PHP path, adjust if needed

echo "=== CPHIA 2025 Email Queue Cron Setup ===\n\n";

// Check if PHP is available
if (!file_exists($phpPath)) {
    // Try to find PHP
    $phpPath = trim(shell_exec('which php'));
    if (empty($phpPath)) {
        echo "❌ PHP not found. Please install PHP or update the path in this script.\n";
        exit(1);
    }
}

echo "✅ PHP found: $phpPath\n";
echo "✅ Project directory: $currentDir\n\n";

// Create cron entries
$cronEntries = [
    "# CPHIA 2025 Email Queue System",
    "# Process email queue every 5 minutes",
    "*/5 * * * * cd $currentDir && $phpPath process_email_queue.php >> logs/email_queue.log 2>&1",
    "",
    "# Daily reminders at 9 AM",
    "0 9 * * * cd $currentDir && $phpPath daily_reminders.php >> logs/daily_reminders.log 2>&1",
    "",
    "# Clean up old logs weekly (keep last 30 days)",
    "0 2 * * 0 find $currentDir/logs -name \"*.log\" -mtime +30 -delete"
];

echo "Cron jobs to install:\n";
echo "====================\n";
foreach ($cronEntries as $entry) {
    echo $entry . "\n";
}
echo "\n";

// Create logs directory
if (!is_dir($currentDir . '/logs')) {
    mkdir($currentDir . '/logs', 0755, true);
    echo "✅ Created logs directory\n";
} else {
    echo "✅ Logs directory exists\n";
}

echo "\nTo install these cron jobs:\n";
echo "1. Run: crontab -e\n";
echo "2. Add the above cron entries\n";
echo "3. Save and exit\n\n";

echo "Or run this command to add them automatically:\n";
echo "(crontab -l 2>/dev/null; echo \"" . implode('\n', $cronEntries) . "\") | crontab -\n\n";

echo "Manual testing commands:\n";
echo "=======================\n";
echo "Test email queue:     php manage_email_queue.php test\n";
echo "Process emails:       php manage_email_queue.php process\n";
echo "Show statistics:      php manage_email_queue.php stats\n";
echo "Monitor queue:        php monitor_email_queue.php\n";
echo "Reset failed emails:  php manage_email_queue.php reset\n";
echo "Clear old emails:     php manage_email_queue.php clear\n\n";

echo "Log files:\n";
echo "==========\n";
echo "Email queue log:      logs/email_queue.log\n";
echo "Daily reminders log:  logs/daily_reminders.log\n";
echo "Process queue log:    logs/process_email_queue.log\n\n";

echo "Setup complete! 🎉\n";