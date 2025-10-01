<?php
/**
 * Email Queue Test Script
 * This script tests the email queue system
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Load Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Cphia2025\EmailQueue;

echo "<h1>CPHIA 2025 Email Queue Test</h1>";

try {
    $emailQueue = new EmailQueue();
    
    echo "<h2>1. Testing Email Queue Addition</h2>";
    
    // Test adding an email to queue
    $result = $emailQueue->addToQueue(
        'test@example.com',
        'Test User',
        'Test Email Subject',
        'registration_confirmation',
        [
            'user_name' => 'Test User',
            'registration_id' => 'TEST001',
            'package_name' => 'Test Package',
            'amount' => 100.00
        ],
        'registration_confirmation',
        5
    );
    
    if ($result) {
        echo "<p style='color: green;'>✅ Successfully added test email to queue</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add test email to queue</p>";
    }
    
    echo "<h2>2. Testing Payment Reminders</h2>";
    
    $reminders = $emailQueue->addPaymentReminders();
    echo "<p>Added $reminders payment reminder emails to queue</p>";
    
    echo "<h2>3. Testing Admin Reminders</h2>";
    
    $adminReminders = $emailQueue->addAdminReminders();
    if ($adminReminders) {
        echo "<p style='color: green;'>✅ Added admin reminder email to queue</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No admin reminders needed</p>";
    }
    
    echo "<h2>4. Email Queue Statistics</h2>";
    
    $stats = $emailQueue->getStats();
    
    if (empty($stats)) {
        echo "<p>No email statistics available.</p>";
    } else {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>Status</th><th>Type</th><th>Date</th><th>Count</th></tr>";
        foreach ($stats as $stat) {
            $statusColor = '';
            switch ($stat['status']) {
                case 'sent':
                    $statusColor = 'color: green;';
                    break;
                case 'failed':
                    $statusColor = 'color: red;';
                    break;
                case 'pending':
                    $statusColor = 'color: orange;';
                    break;
                case 'processing':
                    $statusColor = 'color: blue;';
                    break;
            }
            echo "<tr>";
            echo "<td style='$statusColor'>{$stat['status']}</td>";
            echo "<td>{$stat['email_type']}</td>";
            echo "<td>{$stat['date']}</td>";
            echo "<td>{$stat['count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>5. Testing Email Processing</h2>";
    echo "<p><a href='process_emails.php' target='_blank'>Click here to process emails now</a></p>";
    
    echo "<h2>6. Manual Email Queue Management</h2>";
    echo "<ul>";
    echo "<li><a href='email_stats.php'>View Detailed Email Statistics</a></li>";
    echo "<li><a href='process_emails.php'>Process Pending Emails</a></li>";
    echo "<li><a href='daily_reminders.php'>Generate Daily Reminders</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Set up cron jobs using <a href='setup_cron.php'>setup_cron.php</a></li>";
echo "<li>Configure SMTP settings in your .env file</li>";
echo "<li>Test with a real registration</li>";
echo "<li>Monitor log files for any issues</li>";
echo "</ol>";
?>
