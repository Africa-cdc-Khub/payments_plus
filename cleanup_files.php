<?php
/**
 * File Cleanup Script
 * Cleans up old temporary files and orphaned uploads
 * This script should be run periodically via cron job
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "Starting file cleanup process...\n";

// Clean up old files in upload directories
$uploadDirs = [
    'uploads/passports/',
    'uploads/student_ids/',
    'uploads/',
    'admin/storage/app/private/',
    'cphiaadmin/storage/app/private/'
];

foreach ($uploadDirs as $dir) {
    if (is_dir($dir)) {
        echo "Cleaning up directory: $dir\n";
        cleanupOldFiles($dir, 86400); // Clean files older than 24 hours
    }
}

// Clean up PHP temporary files
echo "Cleaning up PHP temporary files...\n";
cleanupTempFiles();

// Clean up any other temporary files in the project
$tempPatterns = [
    'tmp/*',
    'temp/*',
    'cache/*',
    'logs/*.tmp',
    '*.tmp'
];

foreach ($tempPatterns as $pattern) {
    $files = glob($pattern);
    foreach ($files as $file) {
        if (is_file($file)) {
            $fileAge = time() - filemtime($file);
            if ($fileAge > 3600) { // Clean files older than 1 hour
                unlink($file);
                echo "Cleaned up temp file: $file\n";
            }
        }
    }
}

// Clean up old log files (keep last 30 days)
$logFiles = glob('logs/*.log');
foreach ($logFiles as $logFile) {
    if (is_file($logFile)) {
        $fileAge = time() - filemtime($logFile);
        if ($fileAge > (30 * 86400)) { // Clean files older than 30 days
            unlink($logFile);
            echo "Cleaned up old log file: $logFile\n";
        }
    }
}

echo "File cleanup process completed.\n";
