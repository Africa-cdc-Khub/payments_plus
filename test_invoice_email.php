<?php
/**
 * Test Invoice Email Template
 * This script tests the invoice email generation for group registrations
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "Testing Invoice Email Template for Group Registrations\n";
echo "====================================================\n\n";

try {
    // Get database connection
    $pdo = getConnection();
    
    // Find a group registration with participants
    $stmt = $pdo->prepare("
        SELECT r.id, r.user_id, r.registration_type, r.total_amount, r.currency, r.payment_status,
               u.first_name, u.last_name, u.email, u.phone, u.nationality,
               p.name as package_name, p.id as package_id
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        JOIN packages p ON r.package_id = p.id
        WHERE r.registration_type = 'group'
        ORDER BY r.id DESC
        LIMIT 1
    ");
    $stmt->execute();
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$registration) {
        echo "❌ No group registrations found for testing\n";
        exit;
    }
    
    echo "Found group registration:\n";
    echo "- ID: {$registration['id']}\n";
    echo "- Focal Person: {$registration['first_name']} {$registration['last_name']}\n";
    echo "- Email: {$registration['email']}\n";
    echo "- Package: {$registration['package_name']}\n";
    echo "- Amount: \${$registration['total_amount']}\n\n";
    
    // Get participants for this registration
    $participantsStmt = $pdo->prepare("
        SELECT p.first_name, p.last_name, p.email, p.nationality, p.organization, p.institution
        FROM registration_participants p
        WHERE p.registration_id = ?
        ORDER BY p.id
    ");
    $participantsStmt->execute([$registration['id']]);
    $participants = $participantsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($participants) . " participants:\n";
    foreach ($participants as $index => $participant) {
        echo "- Participant " . ($index + 1) . ": {$participant['first_name']} {$participant['last_name']} ({$participant['email']})\n";
    }
    echo "\n";
    
    // Test the invoice data generation
    echo "Testing invoice data generation...\n";
    
    $package = [
        'id' => $registration['package_id'],
        'name' => $registration['package_name']
    ];
    
    $user = [
        'first_name' => $registration['first_name'],
        'last_name' => $registration['last_name'],
        'email' => $registration['email'],
        'phone' => $registration['phone'],
        'nationality' => $registration['nationality']
    ];
    
    // Generate invoice data
    $invoiceData = generateInvoiceData($user, $registration['id'], $package, $registration['total_amount'], $participants, 'group');
    
    echo "Generated invoice data:\n";
    echo "- User Name: {$invoiceData['user_name']}\n";
    echo "- Registration ID: {$invoiceData['registration_id']}\n";
    echo "- Package: {$invoiceData['package_name']}\n";
    echo "- Total Amount: \${$invoiceData['total_amount']}\n";
    echo "- Number of Participants: {$invoiceData['num_participants']}\n";
    echo "- Participants HTML Length: " . strlen($invoiceData['participants_html']) . " characters\n";
    
    // Check if participants HTML contains actual names
    if (strpos($invoiceData['participants_html'], $user['first_name']) !== false) {
        echo "✅ Main registrant name found in participants HTML\n";
    } else {
        echo "❌ Main registrant name NOT found in participants HTML\n";
    }
    
    // Check for each participant
    foreach ($participants as $index => $participant) {
        $fullName = $participant['first_name'] . ' ' . $participant['last_name'];
        if (strpos($invoiceData['participants_html'], $fullName) !== false) {
            echo "✅ Participant " . ($index + 1) . " name found in participants HTML\n";
        } else {
            echo "❌ Participant " . ($index + 1) . " name NOT found in participants HTML\n";
        }
    }
    
    // Test template rendering
    echo "\nTesting template rendering...\n";
    
    $emailQueue = new EmailQueue();
    $renderedTemplate = $emailQueue->renderTemplate('invoice', $invoiceData);
    
    // Check for unresolved placeholders
    $unresolvedPlaceholders = [];
    preg_match_all('/\{\{([^}]+)\}\}/', $renderedTemplate, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $placeholder) {
            if (!isset($invoiceData[$placeholder]) && !in_array($placeholder, ['#if', 'participants', '/if', 'each', '/each', '@index'])) {
                $unresolvedPlaceholders[] = $placeholder;
            }
        }
    }
    
    if (empty($unresolvedPlaceholders)) {
        echo "✅ Template rendered successfully with no unresolved placeholders\n";
    } else {
        echo "❌ Found unresolved placeholders: " . implode(', ', $unresolvedPlaceholders) . "\n";
    }
    
    // Check if participant names are properly displayed
    if (strpos($renderedTemplate, $user['first_name']) !== false && strpos($renderedTemplate, $user['last_name']) !== false) {
        echo "✅ Main registrant name properly displayed in rendered template\n";
    } else {
        echo "❌ Main registrant name NOT properly displayed in rendered template\n";
    }
    
    // Check for each participant
    foreach ($participants as $index => $participant) {
        if (strpos($renderedTemplate, $participant['first_name']) !== false && strpos($renderedTemplate, $participant['last_name']) !== false) {
            echo "✅ Participant " . ($index + 1) . " name properly displayed in rendered template\n";
        } else {
            echo "❌ Participant " . ($index + 1) . " name NOT properly displayed in rendered template\n";
        }
    }
    
    // Check for {{first_name}} and {{last_name}} placeholders (should not exist)
    if (strpos($renderedTemplate, '{{first_name}}') !== false || strpos($renderedTemplate, '{{last_name}}') !== false) {
        echo "❌ Found unresolved {{first_name}} or {{last_name}} placeholders in template\n";
    } else {
        echo "✅ No unresolved {{first_name}} or {{last_name}} placeholders found\n";
    }
    
    // Save rendered template to file for inspection
    $filename = 'test_invoice_email_' . time() . '.html';
    file_put_contents($filename, $renderedTemplate);
    echo "✅ Rendered template saved to: $filename\n";
    
    // Show a preview of the participants section
    echo "\nParticipants section preview:\n";
    echo "============================\n";
    if (preg_match('/<h3>Participants<\/h3>(.*?)<\/table>/s', $renderedTemplate, $matches)) {
        echo $matches[0] . "\n";
    } else {
        echo "Could not extract participants section\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
?>
