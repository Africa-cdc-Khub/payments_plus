<?php
require_once 'bootstrap.php';
require_once 'db_connector.php';

function createTables() {
    $pdo = getConnection();
    
    // Packages table
    $sql = "CREATE TABLE IF NOT EXISTS packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'USD',
        type ENUM('individual', 'group', 'exhibition') NOT NULL,
        max_people INT DEFAULT 1,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255),
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        phone VARCHAR(20),
        nationality VARCHAR(100),
        organization VARCHAR(255),
        address_line1 VARCHAR(255),
        address_line2 VARCHAR(255),
        city VARCHAR(100),
        state VARCHAR(100),
        country VARCHAR(100),
        postal_code VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Registrations table
    $sql = "CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        package_id INT,
        registration_type ENUM('individual', 'group') NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'USD',
        status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
        payment_reference VARCHAR(255),
        payment_token VARCHAR(500),
        exhibition_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (package_id) REFERENCES packages(id)
    )";
    $pdo->exec($sql);
    
    // Registration participants table (for group registrations)
    $sql = "CREATE TABLE IF NOT EXISTS registration_participants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        registration_id INT,
        title VARCHAR(20),
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        nationality VARCHAR(100),
        passport_number VARCHAR(50),
        organization VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    
    // Payments table
    $sql = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        registration_id INT,
        amount DECIMAL(10,2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'USD',
        transaction_uuid VARCHAR(255),
        payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
        payment_method VARCHAR(50),
        payment_reference VARCHAR(255),
        payment_date TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (registration_id) REFERENCES registrations(id)
    )";
    $pdo->exec($sql);
    
    // Email queue table
    $sql = "CREATE TABLE IF NOT EXISTS email_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        to_email VARCHAR(255) NOT NULL,
        to_name VARCHAR(255) NOT NULL,
        subject VARCHAR(500) NOT NULL,
        template_name VARCHAR(100) NOT NULL,
        template_data TEXT,
        email_type ENUM('registration_confirmation', 'payment_link', 'payment_confirmation', 'admin_registration', 'admin_payment', 'reminder') NOT NULL,
        priority INT DEFAULT 5,
        status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
        attempts INT DEFAULT 0,
        max_attempts INT DEFAULT 3,
        scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        sent_at TIMESTAMP NULL,
        error_message TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_scheduled_at (scheduled_at),
        INDEX idx_email_type (email_type)
    )";
    $pdo->exec($sql);
    
    echo "Database tables created successfully!";
}

function insertPackages() {
    $pdo = getConnection();
    
    // Check if packages already exist
    $existingCount = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
    if ($existingCount > 0) {
        echo "Packages already exist ($existingCount found). Skipping insertion.<br>";
        return;
    }
    
    $packages = [
        ['African Nationals', 'Registration for African nationals attending CPHIA 2025', 200.00, 'USD', 'individual', 1],
        ['Non African nationals', 'Registration for non-African nationals attending CPHIA 2025', 400.00, 'USD', 'individual', 1],
        ['Side event package 1', 'Side event package for organizations', 6000.00, 'USD', 'side_event', 10],
        ['Side event package 2', 'Premium side event package for organizations', 10000.00, 'USD', 'side_event', 20],
        ['Exhibition Resilience Bronze', 'Bronze level exhibition package', 2500.00, 'USD', 'exhibition', 5],
        ['Resilience Bronze Plus', 'Bronze Plus exhibition package', 10000.00, 'USD', 'exhibition', 10],
        ['Peace Silver', 'Silver level exhibition package', 30000.00, 'USD', 'exhibition', 15],
        ['Ubuntu Gold', 'Gold level exhibition package', 50000.00, 'USD', 'exhibition', 25],
        ['Uhuru Platinum', 'Platinum level exhibition package', 75000.00, 'USD', 'exhibition', 50]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO packages (name, description, price, currency, type, max_people) VALUES (?, ?, ?, ?, ?, ?)");
    $inserted = 0;
    foreach ($packages as $package) {
        try {
            $stmt->execute($package);
            $inserted++;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                echo "Package '{$package[0]}' already exists, skipping.<br>";
            } else {
                throw $e;
            }
        }
    }
    echo "Packages inserted successfully! ($inserted packages added)<br>";
}

function addMissingColumns() {
    $pdo = getConnection();
    
    // Check if exhibition_description column exists in registrations table
    $stmt = $pdo->query("SHOW COLUMNS FROM registrations LIKE 'exhibition_description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE registrations ADD COLUMN exhibition_description TEXT AFTER payment_token");
        echo "Added exhibition_description column to registrations table.<br>";
    }
    
    // Check if passport_number column exists in registration_participants table
    $stmt = $pdo->query("SHOW COLUMNS FROM registration_participants LIKE 'passport_number'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE registration_participants ADD COLUMN passport_number VARCHAR(50) AFTER nationality");
        echo "Added passport_number column to registration_participants table.<br>";
    }
    
    // Check if organization column exists in registration_participants table
    $stmt = $pdo->query("SHOW COLUMNS FROM registration_participants LIKE 'organization'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE registration_participants ADD COLUMN organization VARCHAR(255) AFTER passport_number");
        echo "Added organization column to registration_participants table.<br>";
    }
    
    echo "Missing columns check completed!";
}

// Run migrations
if (isset($_GET['migrate'])) {
    createTables();
    echo "<br>";
    insertPackages();
    echo "<br>";
    addMissingColumns();
}
?>
