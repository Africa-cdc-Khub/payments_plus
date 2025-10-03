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
            <a href="./">← Go Back</a>
        </div>
    </div>
</body>
</html>

    </div>
</body>
</html>

