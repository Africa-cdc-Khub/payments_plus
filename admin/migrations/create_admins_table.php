<?php
/**
 * Admin Users Table Migration
 * Creates the admins table for admin panel authentication
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../db_connector.php';

function createAdminsTable() {
    try {
        $pdo = getConnection();
        
        // Create admins table
        $sql = "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(200) NOT NULL,
            role ENUM('super_admin', 'admin') DEFAULT 'admin',
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        
        // Create default admin user (username: admin, password: admin123)
        // NOTE: Change this password immediately after first login!
        $defaultPassword = password_hash('admin123', PASSWORD_BCRYPT);
        
        $checkAdmin = $pdo->query("SELECT COUNT(*) FROM admins WHERE username = 'admin'")->fetchColumn();
        
        if ($checkAdmin == 0) {
            $insertSql = "INSERT INTO admins (username, email, password, full_name, role) 
                         VALUES ('admin', 'admin@cphia2025.com', ?, 'System Administrator', 'super_admin')";
            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([$defaultPassword]);
            
            echo "✅ Admins table created successfully!\n";
            echo "✅ Default admin user created:\n";
            echo "   Username: admin\n";
            echo "   Password: admin123\n";
            echo "   ⚠️  IMPORTANT: Please change this password after first login!\n";
        } else {
            echo "✅ Admins table created successfully!\n";
            echo "ℹ️  Admin user already exists.\n";
        }
        
        return true;
    } catch (PDOException $e) {
        echo "❌ Error creating admins table: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli') {
    echo "Running admins table migration...\n";
    createAdminsTable();
}


