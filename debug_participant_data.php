<?php
/**
 * Debug Participant Data
 * This script helps debug participant data issues in group registrations
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "Debugging Participant Data in Group Registrations\n";
echo "================================================\n\n";

try {
    // Get database connection
    $pdo = getConnection();
    
    // Find a group registration with participants
    $stmt = $pdo->prepare("
        SELECT r.id, r.user_id, r.registration_type, r.total_amount, r.currency, r.payment_status,
               u.first_name, u.last_name, u.email, u.phone,
               p.name as package_name, p.id as package_id
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        JOIN packages p ON r.package_id = p.id
        WHERE r.registration_type = 'group'
        AND r.payment_status = 'completed'
        ORDER BY r.id DESC
        LIMIT 1
    ");
    $stmt->execute();
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$registration) {
        echo "❌ No completed group registrations found\n";
        exit;
    }
    
    echo "Found group registration ID: {$registration['id']}\n";
    echo "Focal Person: {$registration['first_name']} {$registration['last_name']}\n\n";
    
    // Get participants from registration_participants table
    $stmt = $pdo->prepare("SELECT * FROM registration_participants WHERE registration_id = ? ORDER BY id");
    $stmt->execute([$registration['id']]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Participants from registration_participants table:\n";
    echo "================================================\n";
    
    if (empty($participants)) {
        echo "❌ No participants found in registration_participants table\n";
    } else {
        foreach ($participants as $index => $participant) {
            echo "Participant " . ($index + 1) . ":\n";
            echo "- ID: " . ($participant['id'] ?? 'N/A') . "\n";
            echo "- First Name: '" . ($participant['first_name'] ?? 'NULL') . "'\n";
            echo "- Last Name: '" . ($participant['last_name'] ?? 'NULL') . "'\n";
            echo "- Email: '" . ($participant['email'] ?? 'NULL') . "'\n";
            echo "- Nationality: '" . ($participant['nationality'] ?? 'NULL') . "'\n";
            echo "- Organization: '" . ($participant['organization'] ?? 'NULL') . "'\n";
            echo "- Institution: '" . ($participant['institution'] ?? 'NULL') . "'\n";
            echo "- All fields: " . print_r($participant, true) . "\n";
            echo "---\n";
        }
    }
    
    // Test generateReceiptData function
    if (!empty($participants)) {
        echo "\nTesting generateReceiptData function:\n";
        echo "===================================\n";
        
        $package = [
            'id' => $registration['package_id'],
            'name' => $registration['package_name']
        ];
        
        $user = [
            'first_name' => $registration['first_name'],
            'last_name' => $registration['last_name'],
            'email' => $registration['email'],
            'phone' => $registration['phone']
        ];
        
        foreach ($participants as $index => $participant) {
            echo "Processing participant " . ($index + 1) . ":\n";
            echo "Input participant data: " . print_r($participant, true) . "\n";
            
            $receiptData = generateReceiptData($participant, $registration, $package, $user);
            echo "Generated receipt data:\n";
            echo "- Name: '" . $receiptData['receipt_data']['name'] . "'\n";
            echo "- Email: '" . $receiptData['receipt_data']['email'] . "'\n";
            echo "- Nationality: '" . $receiptData['receipt_data']['nationality'] . "'\n";
            echo "---\n";
        }
    }
    
    // Check if there are any issues with the data
    echo "\nChecking for data issues:\n";
    echo "========================\n";
    
    if (!empty($participants)) {
        foreach ($participants as $index => $participant) {
            $issues = [];
            
            if (empty($participant['first_name'])) {
                $issues[] = "first_name is empty";
            }
            if (empty($participant['last_name'])) {
                $issues[] = "last_name is empty";
            }
            if (empty($participant['email'])) {
                $issues[] = "email is empty";
            }
            
            if (!empty($issues)) {
                echo "❌ Participant " . ($index + 1) . " has issues: " . implode(', ', $issues) . "\n";
            } else {
                echo "✅ Participant " . ($index + 1) . " data looks good\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nDebug completed.\n";
?>
