#!/usr/bin/env php
<?php
/**
 * Command-line script to run reminders
 * Usage: php run_reminders.php [--force]
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/functions.php';

$force = in_array('--force', $argv);

echo "Starting reminders process...\n";
echo "Force mode: " . ($force ? 'Yes' : 'No') . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('-', 50) . "\n";

try {
    // Run the daily reminders script
    include __DIR__ . '/daily_reminders.php';
    
    echo "\n" . str_repeat('-', 50) . "\n";
    echo "Reminders process completed successfully!\n";
    echo "End time: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
