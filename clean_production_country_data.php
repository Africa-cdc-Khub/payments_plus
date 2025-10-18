<?php
/**
 * Production script to clean existing country data in the database
 * This will fix HTML-encoded country names and remove extra text
 * 
 * Usage: php clean_production_country_data.php
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "Production Country Data Cleanup Script\n";
echo "======================================\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Database: " . DB_NAME . "\n\n";

$pdo = getConnection();

// Step 1: Find all records with problematic country names
echo "Step 1: Scanning for problematic country names...\n";
$stmt = $pdo->query("
    SELECT id, country 
    FROM users 
    WHERE country LIKE '%&gt;%' 
       OR country LIKE '%&lt;%' 
       OR country LIKE '%&amp;%' 
       OR country LIKE '%&quot;%' 
       OR country LIKE '%&#%'
       OR country LIKE '%>%'
       OR country LIKE '%selected%'
       OR country REGEXP '\\s{2,}'
");
$problematicRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($problematicRecords) . " records with problematic country names.\n\n";

if (count($problematicRecords) == 0) {
    echo "✅ No problematic records found. Database is clean!\n";
    exit(0);
}

// Step 2: Show preview of changes
echo "Step 2: Preview of changes to be made:\n";
echo "=====================================\n";
$changesPreview = [];
foreach ($problematicRecords as $record) {
    $originalCountry = $record['country'];
    $cleanedCountry = cleanCountryName($originalCountry);
    
    if ($originalCountry !== $cleanedCountry) {
        $changesPreview[] = [
            'id' => $record['id'],
            'original' => $originalCountry,
            'cleaned' => $cleanedCountry
        ];
        echo "ID {$record['id']}: '$originalCountry' → '$cleanedCountry'\n";
    }
}

echo "\nTotal changes to be made: " . count($changesPreview) . "\n\n";

// Step 3: Create backup log
$logFile = 'logs/country_cleanup_' . date('Y-m-d_H-i-s') . '.log';
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

$logContent = "Country Data Cleanup Log\n";
$logContent .= "======================\n";
$logContent .= "Date: " . date('Y-m-d H:i:s') . "\n";
$logContent .= "Database: " . DB_NAME . "\n";
$logContent .= "Records found: " . count($problematicRecords) . "\n";
$logContent .= "Changes to make: " . count($changesPreview) . "\n\n";

foreach ($changesPreview as $change) {
    $logContent .= "ID {$change['id']}: '{$change['original']}' → '{$change['cleaned']}'\n";
}

file_put_contents($logFile, $logContent);
echo "Backup log created: $logFile\n\n";

// Step 4: Perform the cleanup
echo "Step 3: Performing cleanup...\n";
echo "============================\n";

$fixedCount = 0;
$errorCount = 0;
$startTime = microtime(true);

foreach ($problematicRecords as $record) {
    $originalCountry = $record['country'];
    $cleanedCountry = cleanCountryName($originalCountry);
    
    if ($originalCountry !== $cleanedCountry) {
        try {
            $updateStmt = $pdo->prepare("UPDATE users SET country = ? WHERE id = ?");
            $result = $updateStmt->execute([$cleanedCountry, $record['id']]);
            
            if ($result) {
                echo "✅ ID {$record['id']}: '$originalCountry' → '$cleanedCountry'\n";
                $fixedCount++;
            } else {
                echo "❌ ID {$record['id']}: Failed to update\n";
                $errorCount++;
            }
        } catch (Exception $e) {
            echo "❌ ID {$record['id']}: Error - " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
}

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

// Step 5: Verification
echo "\nStep 4: Verification\n";
echo "===================\n";
$verifyStmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM users 
    WHERE country LIKE '%&gt;%' 
       OR country LIKE '%&lt;%' 
       OR country LIKE '%&amp;%' 
       OR country LIKE '%&quot;%' 
       OR country LIKE '%&#%'
       OR country LIKE '%>%'
       OR country LIKE '%selected%'
");
$remainingProblems = $verifyStmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($remainingProblems == 0) {
    echo "✅ All country data has been cleaned successfully!\n";
} else {
    echo "⚠️  $remainingProblems records still have problematic country names.\n";
}

// Step 6: Final summary
echo "\nStep 5: Final Summary\n";
echo "====================\n";
echo "Total records scanned: " . count($problematicRecords) . "\n";
echo "Records successfully fixed: $fixedCount\n";
echo "Errors encountered: $errorCount\n";
echo "Remaining problems: $remainingProblems\n";
echo "Execution time: {$executionTime} seconds\n";
echo "Log file: $logFile\n";

// Update log file with results
$logContent .= "\nResults:\n";
$logContent .= "========\n";
$logContent .= "Records successfully fixed: $fixedCount\n";
$logContent .= "Errors encountered: $errorCount\n";
$logContent .= "Remaining problems: $remainingProblems\n";
$logContent .= "Execution time: {$executionTime} seconds\n";
$logContent .= "Completed: " . date('Y-m-d H:i:s') . "\n";

file_put_contents($logFile, $logContent);

if ($errorCount == 0 && $remainingProblems == 0) {
    echo "\n🎉 Country data cleanup completed successfully!\n";
    exit(0);
} else {
    echo "\n⚠️  Cleanup completed with some issues. Check the log file for details.\n";
    exit(1);
}
