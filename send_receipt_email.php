<?php
/**
 * Send Receipt Email Endpoint
 * Handles sending receipt emails to participants
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['registration_id']) || !isset($input['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$registrationId = (int)$input['registration_id'];
$email = trim($input['email']);

// Validate email
if (!validateEmail($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Get registration details
$registration = getRegistrationById($registrationId);
if (!$registration) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Registration not found']);
    exit;
}

// Verify email matches registration
if ($registration['user_email'] !== $email) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Email does not match registration']);
    exit;
}

// Check if registration is paid
$paymentStatus = $registration['payment_status'] ?? '';
if ($paymentStatus !== 'completed') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Receipt not available for unpaid registrations']);
    exit;
}

try {
    // Generate receipt email using the existing email queue system
    $emailQueue = new \Cphia2025\EmailQueue();
    
    // Prepare email data
    $emailData = [
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'registration_id' => $registration['id'],
        'participant_name' => $registration['first_name'] . ' ' . $registration['last_name'],
        'participant_email' => $registration['user_email'],
        'phone' => $registration['phone'] ?? 'N/A',
        'package_name' => $registration['package_name'],
        'organization' => $registration['organization'],
        'institution' => $registration['institution'] ?? '',
        'nationality' => $registration['nationality'],
        'payment_date' => date('F j, Y \a\t g:i A', strtotime($registration['created_at'] ?? 'now')),
        'total_amount' => formatCurrency($registration['total_amount'], $registration['currency']),
        'payment_method' => 'Online Payment',
        'mail_from_address' => SUPPORT_EMAIL,
        'support_email' => SUPPORT_EMAIL
    ];
    
    // Add receipt URL for easy access
    $receiptUrl = rtrim(APP_URL, '/') . "/receipt.php?id=" . $registrationId . "&email=" . urlencode($email);
    $emailData['receipt_url'] = $receiptUrl;
    
    // Queue the email using the correct method signature
    $result = $emailQueue->addToQueue(
        $email,
        $emailData['participant_name'],
        'Registration Receipt - ' . CONFERENCE_SHORT_NAME,
        'individual_receipt',
        $emailData,
        'receipt_email',
        5
    );
    
    if ($result) {
        // Log the email send
        logSecurityEvent('receipt_email_sent', "Receipt email queued for registration #{$registrationId} to {$email}");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Receipt email has been queued and will be sent shortly'
        ]);
    } else {
        // Check if it's a duplicate email issue
        $pdo = getConnection();
        $duplicateCheck = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM email_queue
            WHERE to_email = ? 
            AND subject = ?
            AND template_name = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND status IN ('pending', 'sent', 'processing')
        ");
        $duplicateCheck->execute([$email, 'Registration Receipt - ' . CONFERENCE_SHORT_NAME, 'individual_receipt']);
        $duplicateCount = $duplicateCheck->fetch(\PDO::FETCH_ASSOC)['count'];
        
        if ($duplicateCount > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'A receipt email was already sent recently. Please check your email or try again later.'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to queue receipt email. Please try again.'
            ]);
        }
    }
    
} catch (Exception $e) {
    // Log the error
    error_log("Failed to send receipt email for registration #{$registrationId}: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send email. Please try again later.'
    ]);
}
?>