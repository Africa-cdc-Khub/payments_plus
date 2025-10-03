<?php
/**
 * Migration: Add payment_status column to registrations table
 * This column will track whether a registration has been paid for
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "🔧 Adding payment_status column to registrations table\n";
echo "=====================================================\n\n";

try {
    $pdo = getConnection();
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM registrations LIKE 'payment_status'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✅ payment_status column already exists\n";
    } else {
        // Add the payment_status column
        $sql = "ALTER TABLE registrations ADD COLUMN payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending' AFTER status";
        $pdo->exec($sql);
        echo "✅ Added payment_status column to registrations table\n";
    }
    
    // Check if payment_completed_at column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM registrations LIKE 'payment_completed_at'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✅ payment_completed_at column already exists\n";
    } else {
        // Add the payment_completed_at column
        $sql = "ALTER TABLE registrations ADD COLUMN payment_completed_at TIMESTAMP NULL AFTER payment_status";
        $pdo->exec($sql);
        echo "✅ Added payment_completed_at column to registrations table\n";
    }
    
    // Check if payment_transaction_id column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM registrations LIKE 'payment_transaction_id'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✅ payment_transaction_id column already exists\n";
    } else {
        // Add the payment_transaction_id column
        $sql = "ALTER TABLE registrations ADD COLUMN payment_transaction_id VARCHAR(255) NULL AFTER payment_completed_at";
        $pdo->exec($sql);
        echo "✅ Added payment_transaction_id column to registrations table\n";
    }
    
    // Check if payment_amount column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM registrations LIKE 'payment_amount'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✅ payment_amount column already exists\n";
    } else {
        // Add the payment_amount column
        $sql = "ALTER TABLE registrations ADD COLUMN payment_amount DECIMAL(10,2) NULL AFTER payment_transaction_id";
        $pdo->exec($sql);
        echo "✅ Added payment_amount column to registrations table\n";
    }
    
    // Check if payment_currency column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM registrations LIKE 'payment_currency'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✅ payment_currency column already exists\n";
    } else {
        // Add the payment_currency column
        $sql = "ALTER TABLE registrations ADD COLUMN payment_currency VARCHAR(3) NULL AFTER payment_amount";
        $pdo->exec($sql);
        echo "✅ Added payment_currency column to registrations table\n";
    }
    
    // Check if payment_method column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM registrations LIKE 'payment_method'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✅ payment_method column already exists\n";
    } else {
        // Add the payment_method column
        $sql = "ALTER TABLE registrations ADD COLUMN payment_method VARCHAR(50) NULL AFTER payment_currency";
        $pdo->exec($sql);
        echo "✅ Added payment_method column to registrations table\n";
    }
    
    // Update existing registrations to have 'pending' payment status
    $stmt = $pdo->prepare("UPDATE registrations SET payment_status = 'pending' WHERE payment_status IS NULL");
    $result = $stmt->execute();
    $affectedRows = $stmt->rowCount();
    echo "✅ Updated $affectedRows existing registrations to 'pending' payment status\n";
    
    // Show the updated table structure
    echo "\n📋 Updated registrations table structure:\n";
    $stmt = $pdo->query("DESCRIBE registrations");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "   - {$column['Field']}: {$column['Type']} " . ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column['Default'] ? " DEFAULT '{$column['Default']}'" : '') . "\n";
    }
    
    echo "\n🎉 Migration completed successfully!\n";
    echo "The registrations table now supports payment tracking.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
