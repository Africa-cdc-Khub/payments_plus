<?php
require_once 'bootstrap.php';
require_once 'functions.php';

// Handle payment response from CyberSource
$paymentStatus = 'failed';
$message = 'Payment processing failed';
$registrationId = null;
$transactionDetails = [];

// Process CyberSource response
if (!empty($_POST) || !empty($_GET)) {
    $response = array_merge($_POST, $_GET);
    
    // Extract transaction details
    $transactionUuid = $response['req_transaction_uuid'] ?? null;
    $decision = $response['decision'] ?? 'ERROR';
    $reasonCode = $response['reason_code'] ?? '';
    $authAmount = $response['auth_amount'] ?? '';
    $currency = $response['req_currency'] ?? 'USD';
    $referenceNumber = $response['req_reference_number'] ?? '';
    $paymentToken = $response['payment_token'] ?? '';
    
    // Get registration ID from reference number or transaction UUID
    if ($referenceNumber && preg_match('/REG-(\d+)-/', $referenceNumber, $matches)) {
        $registrationId = $matches[1];
    } elseif ($transactionUuid) {
        // Try to find registration by transaction UUID
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT registration_id FROM payments WHERE transaction_uuid = ?");
        $stmt->execute([$transactionUuid]);
        $result = $stmt->fetch();
        if ($result) {
            $registrationId = $result['registration_id'];
        }
    }
    
    // Determine payment status
    if (strtoupper($decision) === 'ACCEPT') {
        $paymentStatus = 'completed';
        $message = 'Payment completed successfully!';
        
        // Update payment status in database
        if ($registrationId) {
            // Update registration payment status
            $pdo = getConnection();
            $stmt = $pdo->prepare("UPDATE registrations SET payment_status = 'completed', payment_reference = ? WHERE id = ?");
            $stmt->execute([$referenceNumber, $registrationId]);
            
            // Create or update payment record
            $stmt = $pdo->prepare("INSERT INTO payments (registration_id, transaction_uuid, amount, currency, payment_status, reference_number, payment_token, response_data) 
                                  VALUES (?, ?, ?, ?, 'completed', ?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE 
                                  status = 'completed', reference_number = VALUES(reference_number), 
                                  payment_token = VALUES(payment_token), response_data = VALUES(response_data)");
            $stmt->execute([
                $registrationId, 
                $transactionUuid, 
                $authAmount, 
                $currency, 
                $referenceNumber, 
                $paymentToken, 
                json_encode($response)
            ]);
        }
        
    } else {
        $paymentStatus = 'failed';
        $errorMessage = $response['message'] ?? 'Payment was declined';
        $message = "Payment failed: " . $errorMessage . " (Code: " . $reasonCode . ")";
        
        // Update payment status in database
        if ($registrationId) {
            $pdo = getConnection();
            $stmt = $pdo->prepare("INSERT INTO payments (registration_id, transaction_uuid, amount, currency, payment_status, reference_number, response_data) 
                                  VALUES (?, ?, ?, ?, 'failed', ?, ?) 
                                  ON DUPLICATE KEY UPDATE 
                                  status = 'failed', response_data = VALUES(response_data)");
            $stmt->execute([
                $registrationId, 
                $transactionUuid, 
                $authAmount, 
                $currency, 
                $referenceNumber, 
                json_encode($response)
            ]);
        }
    }
    
    // Store transaction details for display
    $transactionDetails = [
        'transaction_uuid' => $transactionUuid,
        'reference_number' => $referenceNumber,
        'amount' => $authAmount,
        'currency' => $currency,
        'decision' => $decision,
        'reason_code' => $reasonCode,
        'payment_token' => $paymentToken,
        'message' => $response['message'] ?? '',
        'signature_valid' => verifySignature($response)
    ];
}

// Get registration details
$registration = null;
$participants = [];
if ($registrationId) {
    $registration = getRegistrationById($registrationId);
    if ($registration) {
        $participants = getRegistrationParticipants($registrationId);
    }
}

// Function to verify CyberSource signature
function verifySignature($response) {
    if (!isset($response['signature'])) {
        return false;
    }
    
    // Include security functions
    require_once 'payment/security.php';
    
    // Verify signature
    $receivedSignature = $response['signature'];
    unset($response['signature']); // Remove signature for verification
    
    $calculatedSignature = sign($response);
    return hash_equals($receivedSignature, $calculatedSignature);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPHIA 2025 - Payment Response</title>
    <link rel="stylesheet" href="payment/wm.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <img src="images/logo.png" alt="CPHIA 2025" class="logo-img">
                </div>
                       <div class="header-text">
                           <div class="au-branding">
                               <img src="images/au-logo.svg" alt="African Union" class="au-logo">
                               <span class="au-text">African Union</span>
                           </div>
                           <h1><?php echo CONFERENCE_NAME; ?></h1>
                           <h2><?php echo CONFERENCE_SHORT_NAME; ?></h2>
                           <p class="conference-dates"><?php echo CONFERENCE_DATES; ?> • <?php echo CONFERENCE_LOCATION; ?></p>
                       </div>
            </div>
        </header>

        <div class="payment-result-container">
            <?php if ($paymentStatus === 'completed'): ?>
                <div class="payment-status-icon status-success">✓</div>
                <h2 class="payment-status-title">Payment Successful!</h2>
                <p class="payment-message">Your registration for CPHIA 2025 has been confirmed.</p>
            <?php else: ?>
                <div class="payment-status-icon status-error">✗</div>
                <h2 class="payment-status-title">Payment Failed</h2>
                <p class="payment-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            
            <div class="payment-info">
                <?php if (!empty($transactionDetails['amount']) && !empty($transactionDetails['currency'])): ?>
                    <div class="payment-amount">
                        <span class="amount-label">Amount:</span>
                        <span class="amount-value"><?php echo htmlspecialchars($transactionDetails['amount'] . ' ' . $transactionDetails['currency']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($transactionDetails['reference_number'])): ?>
                    <div class="payment-reference">
                        <span class="reference-label">Reference:</span>
                        <span class="reference-value"><?php echo htmlspecialchars($transactionDetails['reference_number']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($transactionDetails['signature_valid'])): ?>
                    <div class="payment-reference">
                        <span class="reference-label">Security:</span>
                        <span class="reference-value" style="color: <?php echo $transactionDetails['signature_valid'] ? '#28a745' : '#dc3545'; ?>">
                            <?php echo $transactionDetails['signature_valid'] ? '✓ Verified' : '✗ Invalid'; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="back-link">
            <a href="./">← Go Back</a>
        </div>
            </div>
        </div>
    </div>

</body>
</html>
