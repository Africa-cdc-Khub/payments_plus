<?php
/**
 * Integrated Checkout Payment System
 * Automatically populates CyberSource payment form with registration data
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Check if we have registration data
if (!isset($_GET['registration_id']) || !isset($_GET['token'])) {
    header('Location: index.php');
    exit;
}

$registrationId = $_GET['registration_id'];
$token = $_GET['token'];

// Verify the payment token
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT r.*, u.*, p.* FROM registrations r 
                      JOIN users u ON r.user_id = u.id 
                      JOIN packages p ON r.package_id = p.id 
                      WHERE r.id = ? AND r.payment_token = ?");
$stmt->execute([$registrationId, $token]);
$registration = $stmt->fetch();

if (!$registration) {
    die('Invalid payment link or registration not found.');
}

// Check if already paid
if ($registration['payment_status'] === 'completed') {
    die('This registration has already been paid.');
}

// Load CyberSource configuration
require_once 'sa-sop/config.php';
require_once 'sa-sop/security.php';

// Generate unique transaction ID
$transactionUuid = uniqid();
$sessId = session_id();
$dfParam = 'org_id=' . DF_ORG_ID . '&session_id=' . MERCHANT_ID . $sessId;
$responsePage = APP_URL . '/response.php';

// Prepare payment data
$paymentData = [
    // Signed fields (required for security)
    'profile_id' => PROFILE_ID,
    'access_key' => ACCESS_KEY,
    'transaction_uuid' => $transactionUuid,
    'signed_date_time' => gmdate('Y-m-d\TH:i:s\Z'),
    'signed_field_names' => 'profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,payment_method,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,override_custom_receipt_page',
    'unsigned_field_names' => 'device_fingerprint_id,card_type,card_number,card_expiry_date,card_cvn,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,customer_ip_address,line_item_count,item_0_code,item_0_sku,item_0_name,item_0_quantity,item_0_unit_price,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4',
    
    // Payment details
    'payment_method' => 'card',
    'transaction_type' => 'sale',
    'reference_number' => 'REG-' . $registrationId,
    'auth_trans_ref_no' => '',
    'amount' => number_format($registration['total_amount'], 2, '.', ''),
    'currency' => $registration['currency'] ?: 'USD',
    'locale' => 'en-us',
    'merchant_descriptor' => CONFERENCE_SHORT_NAME,
    'override_custom_receipt_page' => $responsePage,
    
    // Billing information (from registration)
    'bill_to_forename' => $registration['first_name'],
    'bill_to_surname' => $registration['last_name'],
    'bill_to_email' => $registration['email'],
    'bill_to_phone' => $registration['phone'],
    'bill_to_address_line1' => $registration['address_line1'],
    'bill_to_address_line2' => $registration['address_line2'],
    'bill_to_address_city' => $registration['city'],
    'bill_to_address_state' => $registration['state'],
    'bill_to_address_country' => $registration['country'],
    'bill_to_address_postal_code' => $registration['postal_code'],
    
    // Device fingerprinting
    'device_fingerprint_id' => $sessId,
    'customer_ip_address' => $_SERVER['REMOTE_ADDR'],
    
    // Line items
    'line_item_count' => '1',
    'item_0_sku' => 'PKG-' . $registration['package_id'],
    'item_0_code' => $registration['type'],
    'item_0_name' => $registration['name'],
    'item_0_quantity' => '1',
    'item_0_unit_price' => number_format($registration['total_amount'], 2, '.', ''),
    
    // Merchant defined data
    'merchant_defined_data1' => 'registration_id:' . $registrationId,
    'merchant_defined_data2' => 'user_id:' . $registration['user_id'],
    'merchant_defined_data3' => 'package_type:' . $registration['type'],
    'merchant_defined_data4' => 'conference:' . CONFERENCE_SHORT_NAME
];

// Generate signature
$signature = sign($paymentData);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo CONFERENCE_SHORT_NAME; ?> - Payment</title>
    <link rel="stylesheet" type="text/css" href="css/payment.css"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .registration-summary {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .payment-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
        }
        .btn-submit {
            background: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
        }
        .btn-submit:hover {
            background: #0056b3;
        }
        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="header">
            <h1><?php echo CONFERENCE_SHORT_NAME; ?></h1>
            <h2>Secure Payment</h2>
            <p>Complete your registration payment</p>
        </div>

        <div class="registration-summary">
            <h3>Registration Summary</h3>
            <p><strong>Registration ID:</strong> #<?php echo $registrationId; ?></p>
            <p><strong>Package:</strong> <?php echo htmlspecialchars($registration['name']); ?></p>
            <p><strong>Amount:</strong> <?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?></p>
            <p><strong>Registrant:</strong> <?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></p>
        </div>

        <div class="security-notice">
            <strong>🔒 Secure Payment:</strong> Your payment information is processed securely by CyberSource. 
            We do not store your card details.
        </div>

        <form id="payment_form" action="sa-sop/payment_confirm.php" method="post">
            <!-- Hidden signed fields -->
            <?php foreach ($paymentData as $name => $value): ?>
                <input type="hidden" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($value); ?>">
            <?php endforeach; ?>
            
            <input type="hidden" name="signature" value="<?php echo $signature; ?>">

            <div class="payment-form">
                <h3>Payment Information</h3>
                
                <div class="form-group">
                    <label for="card_type">Card Type</label>
                    <select name="card_type" id="card_type" required>
                        <option value="">Select Card Type</option>
                        <option value="001">Visa</option>
                        <option value="002">Mastercard</option>
                        <option value="003">American Express</option>
                        <option value="004">Discover</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" name="card_number" id="card_number" maxlength="19" placeholder="1234 5678 9012 3456" required>
                </div>

                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="card_expiry_date">Expiry Date</label>
                        <input type="text" name="card_expiry_date" id="card_expiry_date" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="card_cvn">CVV</label>
                        <input type="text" name="card_cvn" id="card_cvn" maxlength="4" placeholder="123" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Pay <?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?> - Complete Registration
                </button>
            </div>
        </form>
    </div>

    <!-- Device Fingerprinting -->
    <div style="display: none;">
        <p>device_fingerprint_param: <?php echo $dfParam; ?></p>
        <p style="background:url(https://h.online-metrix.net/fp/clear.png?<?php echo $dfParam; ?>&m=1)"></p>
        <img src="https://h.online-metrix.net/fp/clear.png?<?php echo $dfParam; ?>&m=2" width="1" height="1" />
    </div>

    <script src="js/jquery-1.7.min.js"></script>
    <script src="js/payment_form.js"></script>
    <script src="js/jquery.maskedinput-1.3.js"></script>
    <script>
        $(document).ready(function() {
            // Format card number
            $("#card_number").mask("9999 9999 9999 9999");
            
            // Format expiry date
            $("#card_expiry_date").mask("99/99");
            
            // Auto-detect card type
            $("#card_number").on('input', function() {
                var cardNumber = $(this).val().replace(/\s/g, '');
                var cardType = '';
                
                if (cardNumber.match(/^4/)) {
                    cardType = '001'; // Visa
                } else if (cardNumber.match(/^5[1-5]/)) {
                    cardType = '002'; // Mastercard
                } else if (cardNumber.match(/^3[47]/)) {
                    cardType = '003'; // American Express
                } else if (cardNumber.match(/^6/)) {
                    cardType = '004'; // Discover
                }
                
                if (cardType) {
                    $("#card_type").val(cardType);
                }
            });
            
            // Form validation
            $("#payment_form").on('submit', function(e) {
                var cardNumber = $("#card_number").val().replace(/\s/g, '');
                var expiryDate = $("#card_expiry_date").val();
                var cvv = $("#card_cvn").val();
                
                if (cardNumber.length < 13 || cardNumber.length > 19) {
                    alert('Please enter a valid card number');
                    e.preventDefault();
                    return false;
                }
                
                if (!expiryDate.match(/^\d{2}\/\d{2}$/)) {
                    alert('Please enter expiry date in MM/YY format');
                    e.preventDefault();
                    return false;
                }
                
                if (cvv.length < 3 || cvv.length > 4) {
                    alert('Please enter a valid CVV');
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>
