<?php

include 'security.php';
#include '../notify/line-notify.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Response</title>
    <link rel="stylesheet" type="text/css" href="wm.css"/>
    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="logo">
                    <img src="../img/logo-cybersource.png" alt="CyberSource Logo" />
                </div>
                <div class="header-text">
                    <h1>Payment Response</h1>
                    <p>Transaction Processing Result</p>
                </div>
            </div>
        </div>

        <div class="payment-result-container">
            <?php
            $response = $_REQUEST;
            $amount = @$response['auth_amount'];
            $currency = @$response['req_currency'];
            $referenceNumber = @$response['req_reference_number'];
            $decision = @$response['decision'];
            $message = @$response['message'];
            
            // Determine status styling and icon
            $statusClass = 'status-warning';
            $statusIcon = '⚠️';
            $statusText = 'Processing';
            $statusColor = '#ff8c00';
            
            if (strtolower($decision) === 'accept') {
                $statusClass = 'status-success';
                $statusIcon = '✓';
                $statusText = 'Payment Successful';
                $statusColor = '#28a745';
            } elseif (strtolower($decision) === 'decline' || strtolower($decision) === 'error') {
                $statusClass = 'status-error';
                $statusIcon = '✗';
                $statusText = 'Payment Failed';
                $statusColor = '#dc3545';
            }
            ?>
            
            <div class="payment-status-icon <?php echo $statusClass; ?>">
                <?php echo $statusIcon; ?>
            </div>
            
            <h2 class="payment-status-title"><?php echo $statusText; ?></h2>
            
            <?php if (!empty($message)): ?>
                <p class="payment-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            
            <div class="payment-info">
                <?php if (!empty($amount) && !empty($currency)): ?>
                    <div class="payment-amount">
                        <span class="amount-label">Amount:</span>
                        <span class="amount-value"><?php echo htmlspecialchars($amount . ' ' . $currency); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($referenceNumber)): ?>
                    <div class="payment-reference">
                        <span class="reference-label">Reference:</span>
                        <span class="reference-value"><?php echo htmlspecialchars($referenceNumber); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="back-link">
            <?php
            // Extract registration ID from reference number or other parameters
            $registrationId = null;
            $email = '';
            
            // Try to get registration ID from reference number
            if (!empty($referenceNumber)) {
                // Extract registration ID from reference number (assuming format like "REG_123")
                if (preg_match('/REG_(\d+)/', $referenceNumber, $matches)) {
                    $registrationId = $matches[1];
                } else {
                    $registrationId = $referenceNumber; // Use reference number as registration ID
                }
            }
            
            // Try to get email from request parameters
            $email = @$response['req_bill_to_email'] ?: @$response['email'];
            
            if ($registrationId && $email) {
                // Redirect to payment status page with registration details
                $paymentStatusUrl = '../payment_status.php?id=' . urlencode($registrationId) . '&email=' . urlencode($email);
                echo '<a href="' . htmlspecialchars($paymentStatusUrl) . '">← View Registration Status</a>';
            } else {
                // Fallback to registration lookup if we can't determine registration details
                echo '<a href="../registration_lookup.php">← Go Back to Registration Lookup</a>';
            }
            ?>
        </div>
    </div>
</body>
</html>

