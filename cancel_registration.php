<?php
/**
 * Cancel Registration Endpoint
 * Allows users to cancel their own registration
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Set content type
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $registrationId = $input['registration_id'] ?? null;
    $email = $input['email'] ?? null;
    $reason = $input['reason'] ?? 'Cancelled by User';
    
    // Validate required fields
    if (!$registrationId || !$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Registration ID and email are required']);
        exit;
    }
    
    // Get database connection
    $pdo = getConnection();
    
    // Find the registration
    $stmt = $pdo->prepare("
        SELECT r.id, r.user_id, r.payment_status, r.status, r.total_amount,
               u.email, u.first_name, u.last_name
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ? AND u.email = ?
    ");
    $stmt->execute([$registrationId, $email]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$registration) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Registration not found or email does not match']);
        exit;
    }
    
    // Check if registration can be cancelled
    if ($registration['payment_status'] === 'completed') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot cancel a completed registration. Please contact support for refund requests.']);
        exit;
    }
    
    if ($registration['payment_status'] === 'voided') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Registration is already cancelled']);
        exit;
    }
    
    // Get user ID for voided_by (we'll use the user_id from the registration)
    $userId = $registration['user_id'];
    
    // Update the registration to voided status
    $updateStmt = $pdo->prepare("
        UPDATE registrations 
        SET payment_status = 'voided',
            voided_at = NOW(),
            voided_by = ?,
            void_reason = ?
        WHERE id = ?
    ");
    
    $result = $updateStmt->execute([
        $userId,
        $reason . ' - Via Email',
        $registrationId
    ]);
    
    if ($result) {
        // Log the cancellation
        error_log("Registration #{$registrationId} cancelled by user via email: {$email}");
        
        // Send confirmation email (optional)
        // You can add email notification here if needed
        
        echo json_encode([
            'success' => true, 
            'message' => 'Registration has been successfully cancelled. Payment reminders will stop.',
            'registration_id' => $registrationId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to cancel registration. Please try again or contact support.']);
    }
    
} catch (Exception $e) {
    error_log("Cancel registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while cancelling the registration. Please try again or contact support.']);
}
?>
