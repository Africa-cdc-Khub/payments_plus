<?php
/**
 * Test Cancel Registration Functionality
 * This script tests the cancel registration endpoint
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "Testing Cancel Registration Functionality\n";
echo "==========================================\n\n";

try {
    // Get database connection
    $pdo = getConnection();
    
    // Find a test registration with pending payment status
    $stmt = $pdo->prepare("
        SELECT r.id, r.user_id, r.payment_status, r.status, r.total_amount,
               u.email, u.first_name, u.last_name
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        WHERE r.payment_status = 'pending'
        AND r.status != 'cancelled'
        AND r.total_amount > 0
        LIMIT 1
    ");
    $stmt->execute();
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$registration) {
        echo "❌ No pending registrations found for testing\n";
        echo "Please create a test registration first\n";
        exit;
    }
    
    echo "Found test registration:\n";
    echo "- ID: {$registration['id']}\n";
    echo "- Email: {$registration['email']}\n";
    echo "- Name: {$registration['first_name']} {$registration['last_name']}\n";
    echo "- Payment Status: {$registration['payment_status']}\n";
    echo "- Amount: \${$registration['total_amount']}\n\n";
    
    // Test the cancel registration endpoint
    echo "Testing cancel registration endpoint...\n";
    
    $url = 'http://localhost/payments_plus/cancel_registration.php';
    $data = [
        'registration_id' => $registration['id'],
        'email' => $registration['email'],
        'reason' => 'Test Cancellation'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        echo "❌ Failed to call cancel registration endpoint\n";
        exit;
    }
    
    $response = json_decode($result, true);
    
    if ($response['success']) {
        echo "✅ Cancel registration successful!\n";
        echo "Response: {$response['message']}\n\n";
        
        // Verify the registration was actually cancelled
        $verifyStmt = $pdo->prepare("
            SELECT payment_status, voided_at, voided_by, void_reason
            FROM registrations
            WHERE id = ?
        ");
        $verifyStmt->execute([$registration['id']]);
        $updatedRegistration = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Verification:\n";
        echo "- Payment Status: {$updatedRegistration['payment_status']}\n";
        echo "- Voided At: {$updatedRegistration['voided_at']}\n";
        echo "- Voided By: {$updatedRegistration['voided_by']}\n";
        echo "- Void Reason: {$updatedRegistration['void_reason']}\n\n";
        
        if ($updatedRegistration['payment_status'] === 'voided') {
            echo "✅ Registration successfully voided!\n";
        } else {
            echo "❌ Registration was not voided properly\n";
        }
        
    } else {
        echo "❌ Cancel registration failed!\n";
        echo "Error: {$response['message']}\n";
    }
    
    // Test payment reminders query (should exclude voided registrations, delegates, and approved)
    echo "\nTesting payment reminders query...\n";
    
    $reminderStmt = $pdo->prepare("
        SELECT r.id, r.payment_status, r.status, u.email, p.name as package_name
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        JOIN packages p ON r.package_id = p.id
        WHERE r.payment_status = 'pending'
        AND r.status != 'cancelled'
        AND r.status != 'approved'
        AND r.total_amount > 0
        AND r.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        AND r.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND r.payment_status != 'voided'
        AND LOWER(p.name) NOT LIKE '%delegate%'
        ORDER BY r.created_at ASC
        LIMIT 5
    ");
    $reminderStmt->execute();
    $pendingForReminders = $reminderStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Registrations eligible for payment reminders:\n";
    foreach ($pendingForReminders as $reg) {
        echo "- ID: {$reg['id']}, Payment Status: {$reg['payment_status']}, Status: {$reg['status']}, Package: {$reg['package_name']}, Email: {$reg['email']}\n";
    }
    
    // Check if our cancelled registration is excluded
    $cancelledInReminders = false;
    foreach ($pendingForReminders as $reg) {
        if ($reg['id'] == $registration['id']) {
            $cancelledInReminders = true;
            break;
        }
    }
    
    if (!$cancelledInReminders) {
        echo "✅ Cancelled registration is properly excluded from payment reminders\n";
    } else {
        echo "❌ Cancelled registration is still included in payment reminders\n";
    }
    
    // Test that delegates and approved registrations are excluded
    echo "\nTesting exclusion of delegates and approved registrations...\n";
    
    $exclusionStmt = $pdo->prepare("
        SELECT r.id, r.payment_status, r.status, u.email, p.name as package_name
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        JOIN packages p ON r.package_id = p.id
        WHERE r.payment_status = 'pending'
        AND (r.status = 'approved' OR LOWER(p.name) LIKE '%delegate%')
        AND r.total_amount > 0
        ORDER BY r.created_at ASC
        LIMIT 5
    ");
    $exclusionStmt->execute();
    $excludedRegistrations = $exclusionStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Registrations that should be excluded from reminders (delegates/approved):\n";
    foreach ($excludedRegistrations as $reg) {
        echo "- ID: {$reg['id']}, Payment Status: {$reg['payment_status']}, Status: {$reg['status']}, Package: {$reg['package_name']}, Email: {$reg['email']}\n";
    }
    
    // Check if any of these excluded registrations appear in the reminders list
    $excludedInReminders = false;
    foreach ($excludedRegistrations as $excludedReg) {
        foreach ($pendingForReminders as $reminderReg) {
            if ($excludedReg['id'] == $reminderReg['id']) {
                $excludedInReminders = true;
                echo "❌ Excluded registration ID {$excludedReg['id']} is still in reminders list\n";
                break;
            }
        }
        if ($excludedInReminders) break;
    }
    
    if (!$excludedInReminders) {
        echo "✅ Delegates and approved registrations are properly excluded from payment reminders\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>
