<?php
/**
 * Send payment link via email
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['registration_id']) || !is_numeric($input['registration_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid registration ID']);
        exit;
    }
    
    $registrationId = (int)$input['registration_id'];
    
    // Get registration details
    $registration = getRegistrationById($registrationId);
    if (!$registration) {
        echo json_encode(['success' => false, 'message' => 'Registration not found']);
        exit;
    }
    
    // Check if payment is already completed
    if ($registration['payment_status'] === 'completed') {
        echo json_encode(['success' => false, 'message' => 'Payment already completed']);
        exit;
    }
    
    // Generate payment link
    $paymentLink = rtrim(APP_URL, '/') . "/registration_lookup.php?action=pay&id=" . $registrationId;
    
    // Prepare email data
    $user = [
        'first_name' => $registration['first_name'],
        'last_name' => $registration['last_name'],
        'email' => $registration['user_email']
    ];
    
    $package = [
        'name' => $registration['package_name']
    ];
    
    $amount = $registration['total_amount'];
    
    // Get participants if group registration
    $participants = [];
    if ($registration['registration_type'] === 'group') {
        $participants = getRegistrationParticipants($registrationId);
    }
    
    // Generate invoice data
    $invoiceData = generateInvoiceData(
        $user, 
        $registrationId, 
        $package, 
        $amount, 
        $participants, 
        $registration['registration_type']
    );
    
    // Send invoice email
    $emailQueue = new \Cphia2025\EmailQueue();
    $result = $emailQueue->addToQueue(
        $user['email'],
        $invoiceData['user_name'],
        CONFERENCE_SHORT_NAME . " - Registration Invoice #" . $registrationId,
        'invoice',
        $invoiceData,
        'invoice',
        5
    );
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Invoice sent successfully',
            'payment_link' => $invoiceData['payment_link']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send invoice']);
    }
    
} catch (Exception $e) {
    error_log("Send payment link error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending the payment link']);
}
