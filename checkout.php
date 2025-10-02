<?php
require_once 'bootstrap.php';
require_once 'functions.php';
require_once 'sa-sop/config.php';

// Handle payment token (for email links)
$registrationId = null;
$registration = null;

if (isset($_GET['token'])) {
    // Decode token to get registration ID
    $tokenData = base64_decode($_GET['token']);
    $parts = explode('_', $tokenData);
    if (count($parts) >= 2) {
        $registrationId = $parts[0];
        $registration = getRegistrationById($registrationId);
    }
}

// Handle direct registration ID
if (isset($_GET['registration_id'])) {
    $registrationId = $_GET['registration_id'];
    $registration = getRegistrationById($registrationId);
}

if (!$registration) {
    die('Invalid registration or payment link');
}

// Get registration participants
$participants = getRegistrationParticipants($registrationId);

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    // Create payment record
    $transactionUuid = uniqid();
    $paymentData = [
        'registration_id' => $registrationId,
        'amount' => $registration['total_amount'],
        'currency' => 'USD',
        'transaction_uuid' => $transactionUuid,
        'payment_status' => 'pending'
    ];
    createPayment($paymentData);
    
    // Redirect to payment gateway
    header('Location: sa-sop/payment_confirm.php?registration_id=' . $registrationId . '&transaction_uuid=' . $transactionUuid);
    exit;
}

// Generate reference number
$referenceNumber = generateReferenceNumber();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPHIA 2025 - Checkout</title>
    <link rel="stylesheet" href="css/style.css">
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

        <div class="checkout-container">
            <div class="checkout-content">
                <!-- Registration Summary -->
                <div class="summary-section">
                    <h2>Registration Summary</h2>
                    <div class="summary-card">
                        <div class="summary-item">
                            <span>Registration ID:</span>
                            <span>#<?php echo $registration['id']; ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Package:</span>
                            <span><?php echo htmlspecialchars($registration['package_name']); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Registration Type:</span>
                            <span><?php echo ucfirst($registration['registration_type']); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Registrant:</span>
                            <span><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Email:</span>
                            <span><?php echo htmlspecialchars($registration['user_email']); ?></span>
                        </div>
                        <?php if (!empty($participants)): ?>
                        <div class="summary-item">
                            <span>Participants:</span>
                            <span><?php echo count($participants); ?> people</span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-item total">
                            <span>Total Amount:</span>
                            <span><?php echo formatCurrency($registration['total_amount']); ?></span>
                        </div>
                    </div>

                    <!-- Participants List -->
                    <?php if (!empty($participants)): ?>
                    <div class="participants-list">
                        <h3>Group Participants</h3>
                        <div class="participants-grid">
                            <?php foreach ($participants as $index => $participant): ?>
                            <div class="participant-item">
                                <div class="participant-number"><?php echo $index + 1; ?></div>
                                <div class="participant-details">
                                    <div class="participant-name">
                                        <?php echo htmlspecialchars($participant['title'] . ' ' . $participant['first_name'] . ' ' . $participant['last_name']); ?>
                                    </div>
                                    <div class="participant-email"><?php echo htmlspecialchars($participant['email']); ?></div>
                                    <?php if ($participant['nationality']): ?>
                                    <div class="participant-nationality"><?php echo htmlspecialchars($participant['nationality']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Payment Form -->
                <div class="payment-section">
                    <h2>Payment Information</h2>
                    <form id="paymentForm" method="POST" action="sa-sop/payment_confirm.php">
                        <!-- Hidden fields for payment processing -->
                        <input type="hidden" name="profile_id" value="<?php echo PROFILE_ID ?>">
                        <input type="hidden" name="access_key" value="<?php echo ACCESS_KEY ?>">
                        <input type="hidden" name="transaction_uuid" value="<?php echo uniqid() ?>">
                        <input type="hidden" name="signed_date_time" value="<?php echo gmdate('Y-m-d\TH:i:s\Z') ?>">
                        <input type="hidden" name="signed_field_names" value="profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,payment_method,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,override_custom_receipt_page">
                        <input type="hidden" name="unsigned_field_names" value="device_fingerprint_id,card_type,card_number,card_expiry_date,card_cvn,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,customer_ip_address,line_item_count,item_0_code,item_0_sku,item_0_name,item_0_quantity,item_0_unit_price,item_1_code,item_1_sku,item_1_name,item_1_quantity,item_1_unit_price,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4">
                        
                        <!-- Payment details -->
                        <input type="hidden" name="payment_method" value="card">
                        <input type="hidden" name="transaction_type" value="sale">
                        <input type="hidden" name="reference_number" value="<?php echo $referenceNumber ?>">
                        <input type="hidden" name="auth_trans_ref_no" value="">
                        <input type="hidden" name="amount" value="<?php echo $registration['total_amount'] ?>">
                        <input type="hidden" name="currency" value="USD">
                        <input type="hidden" name="locale" value="en-us">
                        <input type="hidden" name="merchant_descriptor" value="CPHIA 2025 Registration">
                        
                        <!-- Billing information -->
                        <input type="hidden" name="bill_to_forename" value="<?php echo htmlspecialchars($registration['first_name']) ?>">
                        <input type="hidden" name="bill_to_surname" value="<?php echo htmlspecialchars($registration['last_name']) ?>">
                        <input type="hidden" name="bill_to_email" value="<?php echo htmlspecialchars($registration['user_email']) ?>">
                        <input type="hidden" name="bill_to_phone" value="">
                        <input type="hidden" name="bill_to_address_line1" value="">
                        <input type="hidden" name="bill_to_address_line2" value="">
                        <input type="hidden" name="bill_to_address_city" value="">
                        <input type="hidden" name="bill_to_address_state" value="">
                        <input type="hidden" name="bill_to_address_country" value="">
                        <input type="hidden" name="bill_to_address_postal_code" value="">
                        <input type="hidden" name="customer_ip_address" value="<?php echo $_SERVER['REMOTE_ADDR'] ?>">
                        
                        <!-- Line items -->
                        <input type="hidden" name="line_item_count" value="1">
                        <input type="hidden" name="item_0_sku" value="CPHIA2025-REG">
                        <input type="hidden" name="item_0_code" value="CPHIA2025-REG">
                        <input type="hidden" name="item_0_name" value="<?php echo htmlspecialchars($registration['package_name']) ?>">
                        <input type="hidden" name="item_0_quantity" value="1">
                        <input type="hidden" name="item_0_unit_price" value="<?php echo $registration['total_amount'] ?>">
                        
                        <!-- Merchant defined data -->
                        <input type="hidden" name="merchant_defined_data1" value="Registration ID: <?php echo $registration['id'] ?>">
                        <input type="hidden" name="merchant_defined_data2" value="Type: <?php echo ucfirst($registration['registration_type']) ?>">
                        <input type="hidden" name="merchant_defined_data3" value="CPHIA 2025">
                        <input type="hidden" name="merchant_defined_data4" value="Durban, South Africa">
                        
                        <!-- Response page -->
                        <input type="hidden" name="override_custom_receipt_page" value="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/response.php' ?>">
                        
                        <!-- Device fingerprint -->
                        <input type="hidden" name="device_fingerprint_id" value="<?php echo session_id() ?>" />
                        
                        <div class="payment-options">
                            <h3>Payment Method</h3>
                            <div class="payment-method">
                                <div class="payment-card">
                                    <div class="card-icon">💳</div>
                                    <div class="card-info">
                                        <h4>Credit/Debit Card</h4>
                                        <p>Secure payment processing by CyberSource</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="payment-amount">
                                <div class="amount-label">Amount to Pay</div>
                                <div class="amount-value"><?php echo formatCurrency($registration['total_amount']); ?></div>
                            </div>
                            
                            <div class="payment-actions">
                                <button type="submit" class="btn btn-primary btn-large">
                                    <span class="btn-icon">🔒</span>
                                    Proceed to Secure Payment
                                </button>
                                <a href="index.php" class="btn btn-secondary">Back to Registration</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('paymentForm');
            
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<span class="btn-icon">⏳</span>Processing...';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html>
