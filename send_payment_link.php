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
    
    // Generate payment token
    $paymentToken = generatePaymentToken($registrationId);
    $paymentLink = rtrim(APP_URL, '/') . "/checkout_payment.php?registration_id=" . $registrationId . "&token=" . $paymentToken;
    
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
    
    // Send payment link email
    $emailQueue = new \Cphia2025\EmailQueue();
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    
    $templateData = [
        'user_name' => $userName,
        'registration_id' => $registrationId,
        'package_name' => $package['name'],
        'amount' => $amount,
        'participants' => [],
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL,
        'payment_link' => $paymentLink,
        'payment_status' => 'pending'
    ];
    
    $result = $emailQueue->addToQueue(
        $user['email'],
        $userName,
        CONFERENCE_SHORT_NAME . " - Payment Link for Registration #" . $registrationId,
        'payment_link',
        $templateData,
        'payment_link',
        5
    );
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Payment link sent successfully',
            'payment_link' => $paymentLink
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send payment link']);
    }
    
} catch (Exception $e) {
    error_log("Send payment link error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending the payment link']);
}
