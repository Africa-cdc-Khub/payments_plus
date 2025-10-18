<?php
/**
 * Test Group Receipt Email Template
 * This script tests the group receipt email generation
 */

require_once 'bootstrap.php';
require_once 'functions.php';

echo "Testing Group Receipt Email Template\n";
echo "====================================\n\n";

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
        LIMIT 1
    ");
    $stmt->execute();
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$registration) {
        echo "❌ No completed group registrations found for testing\n";
        echo "Please complete a group registration first\n";
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
        SELECT p.first_name, p.last_name, p.email, p.phone, p.nationality, p.institution, p.position
        FROM group_participants gp
        JOIN users p ON gp.participant_id = p.id
        WHERE gp.registration_id = ?
        ORDER BY gp.id
    ");
    $participantsStmt->execute([$registration['id']]);
    $participants = $participantsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($participants)) {
        echo "❌ No participants found for this group registration\n";
        exit;
    }
    
    echo "Found " . count($participants) . " participants:\n";
    foreach ($participants as $index => $participant) {
        echo "- Participant " . ($index + 1) . ": {$participant['first_name']} {$participant['last_name']} ({$participant['email']})\n";
    }
    echo "\n";
    
    // Test the email generation
    echo "Testing group receipt email generation...\n";
    
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
    
    // Generate the email
    $emailQueue = new EmailQueue();
    
    // Create mock receipt data for testing
    $receiptData = [];
    $qrCodes = [];
    $verificationQrCodes = [];
    $navigationQrCodes = [];
    
    foreach ($participants as $participant) {
        $participantReceipt = generateReceiptData($participant, $registration, $package, $user);
        $receiptData[] = $participantReceipt['receipt_data'];
        $qrCodes[] = $participantReceipt['qr_code'];
        $verificationQrCodes[] = generateVerificationQRCode($participantReceipt['qr_string']);
        $navigationQrCodes[] = $participantReceipt['navigation_qr_code'];
    }
    
    // Generate formatted participant list
    $participantsList = '';
    foreach ($receiptData as $index => $participant) {
        $participantsList .= '<div style="border: 1px solid #e5e7eb; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f9fafb;">';
        $participantsList .= '<h4 style="margin: 0 0 10px 0; color: #374151;">Participant ' . ($index + 1) . '</h4>';
        $participantsList .= '<p style="margin: 5px 0;"><strong>Name:</strong> ' . htmlspecialchars($participant['name']) . '</p>';
        $participantsList .= '<p style="margin: 5px 0;"><strong>Email:</strong> ' . htmlspecialchars($participant['email']) . '</p>';
        $participantsList .= '<p style="margin: 5px 0;"><strong>Phone:</strong> ' . htmlspecialchars($participant['phone'] ?? 'N/A') . '</p>';
        $participantsList .= '<p style="margin: 5px 0;"><strong>Nationality:</strong> ' . htmlspecialchars($participant['nationality'] ?? 'N/A') . '</p>';
        if (!empty($participant['institution'])) {
            $participantsList .= '<p style="margin: 5px 0;"><strong>Institution:</strong> ' . htmlspecialchars($participant['institution']) . '</p>';
        }
        if (!empty($participant['position'])) {
            $participantsList .= '<p style="margin: 5px 0;"><strong>Position:</strong> ' . htmlspecialchars($participant['position']) . '</p>';
        }
        $participantsList .= '</div>';
    }
    
    // Generate QR codes display
    $qrCodesDisplay = '';
    foreach ($qrCodes as $index => $qrCode) {
        $participantName = $receiptData[$index]['name'];
        $qrCodesDisplay .= '<div style="text-align: center; margin: 20px 0; padding: 15px; border: 1px solid #e5e7eb; border-radius: 5px; background: white;">';
        $qrCodesDisplay .= '<h4 style="margin: 0 0 10px 0; color: #374151;">' . htmlspecialchars($participantName) . '</h4>';
        $qrCodesDisplay .= '<img src="data:image/png;base64,' . $qrCode . '" alt="QR Code for ' . htmlspecialchars($participantName) . '" style="max-width: 200px; height: auto; border: 1px solid #d1d5db;">';
        $qrCodesDisplay .= '<p style="margin: 10px 0 0 0; font-size: 12px; color: #6b7280;">Scan this QR code at conference check-in</p>';
        $qrCodesDisplay .= '</div>';
    }
    
    $templateData = [
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'focal_person_name' => $user['first_name'] . ' ' . $user['last_name'],
        'focal_person_email' => $user['email'],
        'registration_id' => $registration['id'],
        'package_name' => $package['name'],
        'total_amount' => formatCurrency($registration['total_amount'], $registration['currency']),
        'payment_date' => date('F j, Y \a\t g:i A'),
        'participants_count' => count($receiptData),
        'participants_list' => $participantsList,
        'qr_codes_display' => $qrCodesDisplay,
        'support_email' => SUPPORT_EMAIL
    ];
    
    // Test template rendering
    $renderedTemplate = $emailQueue->renderTemplate('group_receipt', $templateData);
    
    // Check for unresolved placeholders
    $unresolvedPlaceholders = [];
    preg_match_all('/\{\{([^}]+)\}\}/', $renderedTemplate, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $placeholder) {
            if (!isset($templateData[$placeholder])) {
                $unresolvedPlaceholders[] = $placeholder;
            }
        }
    }
    
    if (empty($unresolvedPlaceholders)) {
        echo "✅ Template rendered successfully with no unresolved placeholders\n";
    } else {
        echo "❌ Found unresolved placeholders: " . implode(', ', $unresolvedPlaceholders) . "\n";
    }
    
    // Check if participant data is properly included
    if (strpos($renderedTemplate, 'Participant 1') !== false) {
        echo "✅ Participant data is properly included in template\n";
    } else {
        echo "❌ Participant data is missing from template\n";
    }
    
    // Check if QR codes are included
    if (strpos($renderedTemplate, 'data:image/png;base64,') !== false) {
        echo "✅ QR codes are properly included in template\n";
    } else {
        echo "❌ QR codes are missing from template\n";
    }
    
    // Save rendered template to file for inspection
    $filename = 'test_group_receipt_' . time() . '.html';
    file_put_contents($filename, $renderedTemplate);
    echo "✅ Rendered template saved to: $filename\n";
    
    // Show a preview of the template
    echo "\nTemplate Preview (first 500 characters):\n";
    echo "==========================================\n";
    echo substr(strip_tags($renderedTemplate), 0, 500) . "...\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
?>
