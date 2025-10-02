<?php
/**
 * Payment Response Handler
 * Processes CyberSource payment responses and updates registration status
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Load CyberSource configuration
require_once 'sa-sop/config.php';
require_once 'sa-sop/security.php';

// Get response data
$response = $_REQUEST;
$amount = @$response['auth_amount'];
if (!empty($amount)) {
    $amount = ', ' . $amount;
    $amount .= ' ' . $response['req_currency'];
}

$message = 'req_reference_number: ' . $response['req_reference_number'] . $amount;
$message .= ' => ' . $response['decision'] . ' ' . @$response['reason_code'] . ' - ' . $response['message'];

// Verify signature
$params = array();
foreach($_POST as $name => $value) {
    $params[$name] = $value;
}

$signed = (strcmp($params["signature"], sign($params)) == 0 ? "true" : "false");

// Extract registration ID from reference number
$registrationId = null;
if (isset($response['req_reference_number']) && strpos($response['req_reference_number'], 'REG-') === 0) {
    $registrationId = str_replace('REG-', '', $response['req_reference_number']);
}

// Process payment result
$paymentSuccess = false;
$paymentMessage = '';

if ($response['decision'] === 'ACCEPT') {
    $paymentSuccess = true;
    $paymentMessage = 'Payment successful!';
} else {
    $paymentMessage = 'Payment failed: ' . $response['message'];
}

// Update database if we have a registration ID
if ($registrationId && $paymentSuccess) {
    try {
        $pdo = getConnection();
        
        // Update registration status
        $stmt = $pdo->prepare("UPDATE registrations SET 
                              payment_status = 'completed',
                              payment_transaction_id = ?,
                              payment_amount = ?,
                              payment_currency = ?,
                              payment_method = ?,
                              payment_completed_at = NOW()
                              WHERE id = ?");
        $stmt->execute([
            $response['transaction_id'] ?? $response['req_transaction_uuid'],
            $response['auth_amount'] ?? $response['req_amount'],
            $response['req_currency'] ?? 'USD',
            'card',
            $registrationId
        ]);
        
        // Get registration details for email
        $stmt = $pdo->prepare("SELECT r.*, u.*, p.* FROM registrations r 
                              JOIN users u ON r.user_id = u.id 
                              JOIN packages p ON r.package_id = p.id 
                              WHERE r.id = ?");
        $stmt->execute([$registrationId]);
        $registration = $stmt->fetch();
        
        if ($registration) {
            // Send payment confirmation email
            $user = [
                'first_name' => $registration['first_name'],
                'last_name' => $registration['last_name'],
                'email' => $registration['email']
            ];
            
            sendPaymentConfirmationEmails(
                $user,
                $registrationId,
                $registration['total_amount'],
                $response['transaction_id'] ?? $response['req_transaction_uuid'],
                []
            );
        }
        
    } catch (Exception $e) {
        error_log("Payment response processing error: " . $e->getMessage());
    }
}

// Log the payment response
error_log("Payment Response: " . $message . " | Signed: " . $signed);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo CONFERENCE_SHORT_NAME; ?> - Payment Result</title>
    <link rel="stylesheet" type="text/css" href="css/payment.css"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .result-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .result-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .result-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .result-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .result-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .btn-home {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        .btn-home:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="result-header">
            <h1><?php echo CONFERENCE_SHORT_NAME; ?></h1>
            <h2>Payment Result</h2>
        </div>

        <?php if ($paymentSuccess): ?>
            <div class="result-success">
                <h3>✅ Payment Successful!</h3>
                <p>Your registration has been confirmed and payment processed successfully.</p>
                <?php if ($registrationId): ?>
                    <p><strong>Registration ID:</strong> #<?php echo $registrationId; ?></p>
                <?php endif; ?>
                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($response['transaction_id'] ?? $response['req_transaction_uuid']); ?></p>
                <p><strong>Amount:</strong> <?php echo $response['req_currency'] ?? 'USD'; ?> <?php echo $response['auth_amount'] ?? $response['req_amount']; ?></p>
            </div>
            
            <div class="result-details">
                <h4>What's Next?</h4>
                <ul>
                    <li>You will receive a confirmation email shortly</li>
                    <li>Conference materials will be sent closer to the event date</li>
                    <li>Check your email for regular updates</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="result-error">
                <h3>❌ Payment Failed</h3>
                <p><?php echo htmlspecialchars($paymentMessage); ?></p>
                <p><strong>Reason Code:</strong> <?php echo htmlspecialchars($response['reason_code'] ?? 'Unknown'); ?></p>
            </div>
            
            <div class="result-details">
                <h4>What to do next?</h4>
                <ul>
                    <li>Check your card details and try again</li>
                    <li>Contact your bank if the issue persists</li>
                    <li>Contact us for assistance</li>
                </ul>
            </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="index.php" class="btn-home">Return to Home</a>
        </div>

        <?php if (APP_DEBUG): ?>
            <div class="result-details">
                <h4>Debug Information</h4>
                <p><strong>Signed:</strong> <?php echo $signed; ?></p>
                <p><strong>Message:</strong> <?php echo htmlspecialchars($message); ?></p>
                <details>
                    <summary>Full Response Data</summary>
                    <pre><?php print_r($response); ?></pre>
                </details>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
