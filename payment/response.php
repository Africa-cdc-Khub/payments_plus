<?php

include 'security.php';
#include '../notify/line-notify.php';

// Load bootstrap for constants
require_once '../bootstrap.php';
require_once '../functions.php';

// Process payment response
$response = $_REQUEST;
$amount = @$response['auth_amount'];
$currency = @$response['req_currency'];
$referenceNumber = @$response['req_reference_number'];
$decision = @$response['decision'];
$message = @$response['message'];
$email = @$response['req_bill_to_email'] ?: @$response['email'];

// Extract registration ID from reference number
$registrationId = null;
if (!empty($referenceNumber)) {
    // Extract registration ID from reference number (assuming format like "REG-123")
    if (preg_match('/REG-(\d+)/', $referenceNumber, $matches)) {
        $registrationId = $matches[1];
    } else {
        $registrationId = $referenceNumber; // Use reference number as registration ID
    }
}

// Process successful payment
if (strtolower($decision) === 'accept' && $registrationId) {
    try {
        $pdo = getConnection();
        
        // Update registration with payment details
        $updateSql = "UPDATE registrations SET 
            status = 'paid',
            payment_status = 'completed',
            payment_completed_at = NOW(),
            payment_transaction_id = ?,
            payment_amount = ?,
            payment_currency = ?,
            payment_method = 'cybersource',
            payment_reference = ?
            WHERE id = ?";
        
        $stmt = $pdo->prepare($updateSql);
        $result = $stmt->execute([
            $referenceNumber,
            $amount,
            $currency,
            $referenceNumber,
            $registrationId
        ]);
        
        if ($result) {
            error_log("Payment processed successfully for registration ID: $registrationId");
            
            // Send confirmation email
            if ($email) {
                $registration = getRegistrationById($registrationId);
                if ($registration) {
                    $user = getUserById($registration['user_id']);
                    if ($user) {
                        // Send payment confirmation email
                        sendPaymentConfirmationEmail($user, $registration);
                        
                        // Send receipt email with QR codes
                        $package = getPackageById($registration['package_id']);
                        if ($package) {
                            // Get participants if group registration
                            $participants = [];
                            if ($registration['registration_type'] === 'group') {
                                $stmt = $pdo->prepare("SELECT * FROM registration_participants WHERE registration_id = ?");
                                $stmt->execute([$registrationId]);
                                $participants = $stmt->fetchAll();
                            }
                            
                            // Send receipt emails
                            $sentCount = sendReceiptEmails($registration, $package, $user, $participants);
                            error_log("Receipt emails sent: $sentCount for registration ID: $registrationId");
                        }
                    }
                }
            }
        } else {
            error_log("Failed to update registration for ID: $registrationId");
        }
        
    } catch (Exception $e) {
        error_log("Error processing payment: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CONFERENCE_SHORT_NAME; ?> - Payment Response</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: none !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .header-content {
            max-width: none !important;
            width: 100% !important;
            padding: 0 var(--spacing-6) !important;
        }
        .header-text h1 {
            font-size: var(--font-size-3xl) !important;
        }
        .header-text h2 {
            font-size: var(--font-size-5xl) !important;
        }
        .conference-dates {
            font-size: var(--font-size-xl) !important;
        }
        .response-container {
            width: 100%;
            margin: 0 auto;
            padding: var(--spacing-6);
            background: var(--light-gray);
            min-height: 100vh;
        }
        .response-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid var(--light-gray);
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        .response-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
            padding: var(--spacing-8);
            text-align: center;
        }
        .response-header h3 {
            color: var(--white);
            font-weight: 600;
            margin-bottom: var(--spacing-2);
            font-size: var(--font-size-2xl);
        }
        .response-header p {
            color: rgba(255,255,255,0.9);
            margin: 0;
            font-size: var(--font-size-lg);
        }
        .response-content {
            padding: var(--spacing-8);
        }
        .status-icon {
            font-size: 4rem;
            margin-bottom: var(--spacing-4);
            text-align: center;
        }
        .status-success {
            color: var(--primary-green);
        }
        .status-error {
            color: #dc3545;
        }
        .status-warning {
            color: #ff8c00;
        }
        .status-title {
            text-align: center;
            font-size: var(--font-size-2xl);
            font-weight: 600;
            margin-bottom: var(--spacing-4);
        }
        .status-message {
            text-align: center;
            font-size: var(--font-size-lg);
            color: var(--medium-gray);
            margin-bottom: var(--spacing-6);
        }
        .payment-info {
            background: var(--light-gray);
            border-radius: var(--radius-md);
            padding: var(--spacing-6);
            margin-bottom: var(--spacing-6);
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-3) 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-item:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: var(--font-size-lg);
            color: var(--primary-green);
            background: var(--light-green);
            margin: var(--spacing-3) -var(--spacing-6) -var(--spacing-6);
            padding: var(--spacing-4) var(--spacing-6);
            border-radius: 0 0 var(--radius-md) var(--radius-md);
        }
        .info-label {
            font-weight: 500;
            color: var(--dark-gray);
        }
        .info-value {
            font-weight: 600;
            color: var(--dark-gray);
            text-align: right;
        }
        .back-link {
            color: var(--medium-gray);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-2);
            margin-bottom: var(--spacing-5);
            font-weight: 500;
        }
        .back-link:hover {
            color: var(--primary-green);
        }
        .action-buttons {
            text-align: center;
            margin-top: var(--spacing-6);
        }
        .btn-action {
            background: linear-gradient(135deg, var(--secondary-green) 0%, var(--primary-green) 100%);
            border: none;
            border-radius: var(--radius-md);
            padding: var(--spacing-4) var(--spacing-6);
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--white);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-2);
            box-shadow: 0 4px 15px rgba(26, 86, 50, 0.3);
            margin: 0 var(--spacing-2);
        }
        .btn-action:hover {
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 86, 50, 0.4);
        }
        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark-gray);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-secondary:hover {
            background: var(--medium-gray);
            color: var(--white);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content text-center">
                <div class="header-text">
                    <div class="logo mb-2">
                        <img src="../images/logo.png" alt="CPHIA 2025" class="logo-img" style="filter: brightness(0) invert(1);">
                    </div>
                    <h1 class="mb-2"><?php echo CONFERENCE_NAME; ?></h1>
                    <h2 class="mb-2"><?php echo CONFERENCE_SHORT_NAME; ?></h2>
                    <p class="conference-dates mb-0"><?php echo CONFERENCE_DATES; ?> • <?php echo CONFERENCE_LOCATION; ?></p>
                </div>
            </div>
        </header>
    </div>

    <div class="response-container" style="padding: 0 5%;">
        <a href="../registration_lookup.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Registrations
        </a>
        
        <div class="response-card">

            <div class="response-header">
                <h3><i class="fas fa-credit-card me-2"></i>Payment Response</h3>
                <p>Transaction Processing Result</p>
        </div>

            <div class="response-content">
            <?php
            $response = $_REQUEST;

                print_r($response);
            $amount = @$response['auth_amount'];
            $currency = @$response['req_currency'];
            $referenceNumber = @$response['req_reference_number'];
            $decision = @$response['decision'];
            $message = @$response['message'];
            
            // Determine status styling and icon
            $statusClass = 'status-warning';
                $statusIcon = 'fas fa-clock';
            $statusText = 'Processing';
            $statusColor = '#ff8c00';
            
            if (strtolower($decision) === 'accept') {
                $statusClass = 'status-success';
                    $statusIcon = 'fas fa-check-circle';
                $statusText = 'Payment Successful';
                $statusColor = '#28a745';
            } elseif (strtolower($decision) === 'decline' || strtolower($decision) === 'error') {
                $statusClass = 'status-error';
                    $statusIcon = 'fas fa-times-circle';
                $statusText = 'Payment Failed';
                $statusColor = '#dc3545';
            }
            ?>
            
                <div class="status-icon <?php echo $statusClass; ?>">
                    <i class="<?php echo $statusIcon; ?>"></i>
            </div>
            
                <h2 class="status-title"><?php echo $statusText; ?></h2>
            
            <?php if (!empty($message)): ?>
                    <p class="status-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            
            <div class="payment-info">
                    <h5 style="color: var(--primary-green); margin-bottom: 20px; font-weight: 600;">
                        <i class="fas fa-receipt me-2"></i>Transaction Details
                    </h5>
                    
                <?php if (!empty($amount) && !empty($currency)): ?>
                        <div class="info-item">
                            <span class="info-label">Amount:</span>
                            <span class="info-value"><?php echo htmlspecialchars($amount . ' ' . $currency); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($referenceNumber)): ?>
                        <div class="info-item">
                            <span class="info-label">Reference Number:</span>
                            <span class="info-value"><?php echo htmlspecialchars($referenceNumber); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value" style="color: <?php echo $statusColor; ?>; font-weight: 600;"><?php echo $statusText; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Date:</span>
                        <span class="info-value"><?php echo date('F j, Y \a\t g:i A'); ?></span>
            </div>
        </div>

                <div class="action-buttons">
                    <?php
                    // Extract registration ID from reference number or other parameters
                    $registrationId = null;
                    $email = '';
                    
                    // Try to get registration ID from reference number
                    if (!empty($referenceNumber)) {
                        // Extract registration ID from reference number (assuming format like "REG_123")
                        if (preg_match('/REG-(\d+)/', $referenceNumber, $matches)) {
                            $registrationId = $matches[1];
                        } else {
                            $registrationId = $referenceNumber; // Use reference number as registration ID
                        }
                    }
                    
                    // Try to get email from request parameters
                    $email = @$response['req_bill_to_email'] ?: @$response['email'];
                    
                    if ($registrationId && $email) {
                        // Show action buttons for successful payment
                        if (strtolower($decision) === 'accept') {
                            echo '<a href="../payment_status.php?id=' . urlencode($registrationId) . '&email=' . urlencode($email) . '" class="btn-action">';
                            echo '<i class="fas fa-eye me-2"></i>View Registration Status';
                            echo '</a>';
                            echo '<a href="../registration_lookup.php" class="btn-action btn-secondary">';
                            echo '<i class="fas fa-list me-2"></i>All Registrations';
                            echo '</a>';
                        } else {
                            echo '<a href="../checkout.php?registration_id=' . urlencode($registrationId) . '" class="btn-action">';
                            echo '<i class="fas fa-redo me-2"></i>Try Payment Again';
                            echo '</a>';
                            echo '<a href="../registration_lookup.php" class="btn-action btn-secondary">';
                            echo '<i class="fas fa-list me-2"></i>All Registrations';
                            echo '</a>';
                        }
                    } else {
                        // Fallback buttons
                        echo '<a href="../registration_lookup.php" class="btn-action">';
                        echo '<i class="fas fa-list me-2"></i>View All Registrations';
                        echo '</a>';
                        echo '<a href="../index.php" class="btn-action btn-secondary">';
                        echo '<i class="fas fa-home me-2"></i>Home Page';
                        echo '</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Footer -->
    <footer class="py-3 mt-4 mx-3" style="background-color: #f8f9fa; border-top: 1px solid #e9ecef;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <img src="../images/logo.png" 
                             alt="Africa CDC" 
                             style="height: 50px; margin-right: 15px;">
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end gap-3">
                        <a href="https://africacdc.org" class="text-muted text-decoration-none small" target="_blank">Africa CDC</a>
                        <a href="https://cphia2025.com" class="text-muted text-decoration-none small" target="_blank">CPHIA 2025</a>
                        <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" class="text-muted text-decoration-none small">
                            <i class="fas fa-envelope me-1"></i>Support
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <span class="text-muted small">
                        © <?php echo date('Y'); ?> Africa CDC. All rights reserved.
                    </span>
                </div>
            </div>
    </div>
    </footer>
</body>
</html>

