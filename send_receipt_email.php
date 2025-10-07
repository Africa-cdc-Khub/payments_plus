<?php
require_once 'bootstrap.php';
require_once 'functions.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$registrationId = filter_var($input['registration_id'] ?? null, FILTER_VALIDATE_INT);

if (!$registrationId) {
    echo json_encode(['success' => false, 'message' => 'Invalid registration ID']);
    exit;
}

try {
    // Get registration details
    $registration = getRegistrationById($registrationId);
    if (!$registration) {
        echo json_encode(['success' => false, 'message' => 'Registration not found']);
        exit;
    }
    
    // Check if registration is paid
    if ($registration['payment_status'] !== 'completed') {
        echo json_encode(['success' => false, 'message' => 'Registration is not paid yet']);
        exit;
    }
    
    // Get user details
    $user = getUserByEmail($registration['user_email']);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Get package details
    $package = getPackageById($registration['package_id']);
    if (!$package) {
        echo json_encode(['success' => false, 'message' => 'Package not found']);
        exit;
    }
    
    // Get participants if group registration
    $participants = [];
    if ($registration['registration_type'] === 'group') {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM registration_participants WHERE registration_id = ?");
        $stmt->execute([$registrationId]);
        $participants = $stmt->fetchAll();
    }
    
    // Send receipt emails
    $sentCount = sendReceiptEmails($registration, $package, $user, $participants);
    
    if ($sentCount > 0) {
        echo json_encode([
            'success' => true, 
            'message' => "Receipt email sent successfully",
            'sent_count' => $sentCount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send receipt email']);
    }
    
} catch (Exception $e) {
    error_log("Receipt email error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending receipt email']);
}
?>
