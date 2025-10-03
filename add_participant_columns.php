<?php
/**
 * Add missing columns to participants table
 * This migration adds passport and visa-related columns to participants
 */

require_once 'bootstrap.php';
require_once 'functions.php';

function addParticipantColumns() {
    $pdo = getConnection();
    
    echo "Adding missing columns to participants table...\n";
    
    $columns = [
        'title' => 'VARCHAR(50) DEFAULT NULL',
        'passport_number' => 'VARCHAR(50) DEFAULT NULL',
        'passport_file' => 'VARCHAR(255) DEFAULT NULL',
        'requires_visa' => 'ENUM("yes", "no") DEFAULT NULL'
    ];
    
    foreach ($columns as $column => $definition) {
        try {
            // Check if column exists by trying to describe the table
            $stmt = $pdo->query("DESCRIBE participants");
            $columns_exist = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!in_array($column, $columns_exist)) {
                $sql = "ALTER TABLE participants ADD COLUMN `$column` $definition";
                $pdo->exec($sql);
                echo "✅ Added column: $column\n";
            } else {
                echo "⚠️  Column already exists: $column\n";
            }
        } catch (Exception $e) {
            echo "❌ Error adding column $column: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nParticipant table columns update completed!\n";
}

// Run the migration
addParticipantColumns();
