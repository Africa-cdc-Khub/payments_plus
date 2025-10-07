<?php 
/**
 * Payment Confirmation with Registration Preview
 * Handles both registration preview and payment confirmation
 */

// Check if we have registration data (from checkout.php flow)
$registrationId = null;
$token = null;
$registration = null;

// Check for GET parameters (from direct links)
if (isset($_GET['registration_id']) && isset($_GET['token'])) {
    $registrationId = $_GET['registration_id'];
    $token = $_GET['token'];
}
// Check for POST parameters (from checkout form)
elseif (isset($_POST['registration_id']) && isset($_POST['token'])) {
    $registrationId = $_POST['registration_id'];
    $token = $_POST['token'];
}

if ($registrationId && $token) {
    require_once '../bootstrap.php';
    require_once '../functions.php';
    
    // For POST data, we don't need to verify payment_token since it's a fresh token
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get registration data directly
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name, u.email as user_email, u.address_line1, u.city, u.state, u.country, u.postal_code,
                              p.name as package_name, p.type
                              FROM registrations r 
                              JOIN users u ON r.user_id = u.id 
                              JOIN packages p ON r.package_id = p.id 
                              WHERE r.id = ?");
        $stmt->execute([$registrationId]);
        $registration = $stmt->fetch();
    } else {
        // For GET data, verify the payment token
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name, u.email as user_email, u.address_line1, u.city, u.state, u.country, u.postal_code,
                              p.name as package_name, p.type
                              FROM registrations r 
                              JOIN users u ON r.user_id = u.id 
                              JOIN packages p ON r.package_id = p.id 
                              WHERE r.id = ? AND r.payment_token = ?");
        $stmt->execute([$registrationId, $token]);
        $registration = $stmt->fetch();
    }
    
    if (!$registration) {
        die('Invalid payment link or registration not found.');
    }
    
    // Check if already paid
    if ($registration['payment_status'] === 'completed') {
        die('This registration has already been paid.');
    }
    
    $showRegistrationPreview = true;
} else {
    $showRegistrationPreview = false;
}

include_once('config.php');
include_once('security.php');

$endpoint_url = PAYMENT_URL;
if (isset($_POST['transaction_type']) && $_POST['transaction_type'] === 'create_payment_token') {
    $endpoint_url = TOKEN_CREATE_URL;
}

// If showing registration preview, prepare payment data
if ($showRegistrationPreview) {
    // Generate unique transaction ID
    $transactionUuid = uniqid();
    $sessId = session_id();
    $dfParam = 'org_id=' . DF_ORG_ID . '&session_id=' . MERCHANT_ID . $sessId;
    $responsePage = rtrim(APP_URL, '/') . '/payment/response.php';

    // Prepare payment data for sa-wm flow
    $paymentData = [
        // Signed fields (required for security) - matching sa-wm structure
        'profile_id' => PROFILE_ID,
        'access_key' => ACCESS_KEY,
        'transaction_uuid' => $transactionUuid,
        'signed_field_names' => 'profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,override_custom_cancel_page,override_custom_receipt_page',
        'unsigned_field_names' => 'signature,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,customer_ip_address,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4,line_item_count,item_0_sku,item_0_code,item_0_name,item_0_quantity,item_0_unit_price,item_0_tax_amount,item_0_amount',
        'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
        'locale' => 'en-us',
        'transaction_type' => 'sale',
        'reference_number' => 'REG-' . $registrationId . '-' . time(),
        'auth_trans_ref_no' => '',
        'amount' => $registration['total_amount'],
        'currency' => $registration['currency'] ?? 'USD',
        'merchant_descriptor' => 'CPHIA 2025 Registration',
        
        // Billing information
        'bill_to_forename' => $registration['first_name'],
        'bill_to_surname' => $registration['last_name'],
        'bill_to_email' => $registration['user_email'],
        'bill_to_phone' => '',
        'bill_to_address_line1' => $registration['address_line1'] ?? '',
        'bill_to_address_line2' => '',
        'bill_to_address_city' => $registration['city'] ?? '',
        'bill_to_address_state' => $registration['state'] ?? '',
        'bill_to_address_country' => getCountryCode($registration['country']),
        'bill_to_address_postal_code' => $registration['postal_code'] ?? '1234',
        
        // Device fingerprinting and security
        'customer_ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        
        // Response pages
        'override_custom_cancel_page' => $responsePage,
        'override_custom_receipt_page' => $responsePage,
        
        // Merchant defined data
        'merchant_defined_data1' => 'Registration ID: ' . $registrationId,
        'merchant_defined_data2' => 'Package: ' . $registration['package_name'],
        'merchant_defined_data3' => 'Conference: CPHIA 2025',
        'merchant_defined_data4' => 'Type: ' . $registration['type'],
        
        // Line items (required by CyberSource)
        'line_item_count' => '1',
        'item_0_sku' => 'PKG-' . $registration['package_id'],
        'item_0_code' => $registration['type'],
        'item_0_name' => $registration['package_name'],
        'item_0_quantity' => '1',
        'item_0_unit_price' => $registration['total_amount'],
        'item_0_tax_amount' => '0.00',
        'item_0_amount' => $registration['total_amount']
    ];
    
    //print_r(json_encode($paymentData));
    //exit();

    $signature = sign($paymentData);
}

?>
<?php if ($showRegistrationPreview): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CONFERENCE_SHORT_NAME; ?> - Complete Payment</title>
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
        .payment-container {
            width: 100%;
            margin: 0 auto;
            padding: var(--spacing-6);
            background: var(--light-gray);
        }
        .payment-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--light-gray);
            width: 100%;
        }
        .payment-header-section {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
            padding: var(--spacing-5);
            text-align: center;
        }
        .payment-header-section h3 {
            color: var(--white);
            font-weight: 600;
            margin-bottom: var(--spacing-1);
            font-size: var(--font-size-xl);
        }
        .payment-header-section p {
            color: rgba(255,255,255,0.9);
            margin: 0;
            font-size: var(--font-size-sm);
        }
        .payment-content {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 0;
            min-height: 700px;
        }
        .participant-info {
            background: var(--light-gray);
            padding: var(--spacing-6);
            border-right: 1px solid #e9ecef;
        }
        .participant-info h4 {
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: var(--spacing-6);
            font-size: var(--font-size-xl);
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
        }
        .participant-details {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: var(--spacing-6);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-4) 0;
            border-bottom: 1px solid #f1f3f4;
        }
        .detail-item:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: var(--font-size-lg);
            color: var(--primary-green);
            background: var(--light-green);
            margin: var(--spacing-4) -var(--spacing-6) -var(--spacing-6);
            padding: var(--spacing-4) var(--spacing-6);
            border-radius: 0 0 var(--radius-md) var(--radius-md);
        }
        .detail-label {
            font-weight: 500;
            color: var(--dark-gray);
            font-size: var(--font-size-sm);
        }
        .detail-value {
            font-weight: 600;
            color: var(--dark-gray);
            text-align: right;
        }
        .payment-form {
            padding: var(--spacing-10);
            background: var(--white);
        }
        .payment-form h4 {
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: var(--spacing-8);
            font-size: var(--font-size-xl);
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
        }
        .btn-pay {
            background: linear-gradient(135deg, var(--secondary-green) 0%, var(--primary-green) 100%);
            border: none;
            border-radius: var(--radius-md);
            padding: var(--spacing-6) var(--spacing-8);
            font-size: var(--font-size-xl);
            font-weight: 600;
            color: var(--white);
            width: 100%;
            box-shadow: 0 4px 15px rgba(26, 86, 50, 0.3);
            margin-top: var(--spacing-8);
            height: 60px;
        }
        .cybersource-branding {
            text-align: center;
            margin-top: var(--spacing-6);
            padding-top: var(--spacing-4);
            border-top: 1px solid #e9ecef;
        }
        .cybersource-logo {
            height: 24px;
            width: auto;
            opacity: 0.7;
        }
        .security-info {
            background: var(--light-green);
            padding: var(--spacing-5);
            border-radius: var(--radius-md);
            margin-top: var(--spacing-8);
            text-align: center;
            border: 1px solid #d4edda;
        }
        .security-badges {
            display: flex;
            justify-content: center;
            gap: var(--spacing-5);
            margin-top: var(--spacing-4);
        }
        .security-badge {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            color: var(--medium-gray);
            font-size: var(--font-size-sm);
        }
        .back-link {
            color: var(--medium-gray);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-2);
            margin-bottom: var(--spacing-5);
        }
        .amount-highlight {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--primary-green);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .payment-content {
                grid-template-columns: 1fr;
            }
            .participant-info {
                border-right: none;
                border-bottom: 1px solid #e9ecef;
            }
            .payment-container {
                padding: var(--spacing-4);
            }
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

    <div class="payment-container" style="padding: 0 5%;">
        <a href="../registration_lookup.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Registrations
        </a>
        
        <div class="payment-card">
                <!-- Left Side: Payment Preview -->
                <div class="payment-form">
                    <h4><i class="fas fa-check-circle me-2"></i>Payment Preview</h4>
                    
                    <!-- Payment Summary -->
                    <div class="payment-summary" style="background: var(--light-gray); padding: 25px; border-radius: 10px; margin-bottom: 30px;">
                        <h5 style="color: var(--primary-green); margin-bottom: 20px; font-weight: 600;">
                            <i class="fas fa-receipt me-2"></i>Transaction Summary
                        </h5>
                        
                        <div class="summary-item" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                            <span style="font-weight: 500; color: var(--dark-gray);">Registration ID:</span>
                            <span style="font-weight: 600; color: var(--dark-gray);">#<?php echo $registration['id']; ?></span>
                        </div>
                        
                        <div class="summary-item" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                            <span style="font-weight: 500; color: var(--dark-gray);">Package:</span>
                            <span style="font-weight: 600; color: var(--dark-gray);"><?php echo htmlspecialchars($registration['package_name']); ?></span>
                        </div>
                        
                        <div class="summary-item" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                            <span style="font-weight: 500; color: var(--dark-gray);">Participant:</span>
                            <span style="font-weight: 600; color: var(--dark-gray);"><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></span>
                        </div>
                        
                        <div class="summary-item" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                            <span style="font-weight: 500; color: var(--dark-gray);">Email:</span>
                            <span style="font-weight: 600; color: var(--dark-gray);"><?php echo htmlspecialchars($registration['user_email']); ?></span>
                        </div>
                        
                        <div class="summary-item" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                            <span style="font-weight: 500; color: var(--dark-gray);">Conference:</span>
                            <span style="font-weight: 600; color: var(--dark-gray);"><?php echo CONFERENCE_DATES; ?></span>
                        </div>
                        
                        <div class="summary-item" style="display: flex; justify-content: space-between; padding: 15px 0; background: var(--light-green); margin: 15px -25px -25px -25px; padding: 20px 25px; border-radius: 0 0 10px 10px;">
                            <span style="font-weight: 600; color: var(--primary-green); font-size: 1.1rem;">Total Amount:</span>
                            <span style="font-weight: 700; color: var(--primary-green); font-size: 1.3rem;"><?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?></span>
                        </div>
                    </div>
            
                    <form id="payment_form" action="<?php echo PAYMENT_URL; ?>" method="post">
                        <!-- Hidden signed fields -->
                        <?php foreach ($paymentData as $name => $value): ?>
                            <input type="hidden" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($value ?? ''); ?>">
                        <?php endforeach; ?>
                        
                        <input type="hidden" name="signature" value="<?php echo $signature; ?>">

                        <button type="submit" class="btn btn-pay">
                            <i class="fas fa-arrow-right me-2"></i>
                            Proceed to Secure Payment - <?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?>
                        </button>
                        
                        <!-- Security Information -->
                        <div class="security-info" style="background: var(--light-green); padding: 20px; border-radius: 10px; margin-top: 25px; text-align: center; border: 1px solid #d4edda;">
                            <h6 style="color: var(--primary-green); margin-bottom: 10px;">
                                <i class="fas fa-shield-alt me-2"></i>Secure Payment Processing
                            </h6>
                            <p style="margin-bottom: 15px; color: var(--medium-gray); font-size: 0.9rem;">
                                Your payment will be processed securely through CyberSource. No card information is stored on our servers.
                            </p>
                            <div class="security-badges" style="display: flex; justify-content: center; gap: 20px; margin-top: 15px;">
                                <div class="security-badge" style="display: flex; align-items: center; gap: 8px; color: var(--medium-gray); font-size: 0.8rem;">
                                    <i class="fas fa-lock"></i>
                                    <span>SSL Encrypted</span>
                                </div>
                                <div class="security-badge" style="display: flex; align-items: center; gap: 8px; color: var(--medium-gray); font-size: 0.8rem;">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>PCI Compliant</span>
                                </div>
                                <div class="security-badge" style="display: flex; align-items: center; gap: 8px; color: var(--medium-gray); font-size: 0.8rem;">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Secure Processing</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CyberSource Branding -->
                        <div class="cybersource-branding">
                            <img src="../img/logo-cybersource.png" alt="Powered by CyberSource" class="cybersource-logo">
                            <p class="small text-muted mb-0 mt-2">Powered by CyberSource</p>
                        </div>
                    </form>
                </div>
                
        </div>
        
        <div class="security-info">
            <h6><i class="fas fa-shield-alt me-2"></i>Your Payment is Secure</h6>
            <p class="mb-0">We use industry-standard encryption to protect your payment information. Your data is processed securely through our payment partner.</p>
            <div class="security-badges">
                <div class="security-badge">
                    <i class="fas fa-lock"></i>
                    <span>SSL Encrypted</span>
                </div>
                <div class="security-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>PCI Compliant</span>
                </div>
                <div class="security-badge">
                    <i class="fas fa-check-circle"></i>
                    <span>Secure Processing</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Fingerprinting -->
    <div style="display: none;">
        <p>device_fingerprint_param: <?php echo $dfParam; ?></p>
        <p style="background:url(https://h.online-metrix.net/fp/clear.png?<?php echo $dfParam; ?>&m=1)"></p>
        <img src="https://h.online-metrix.net/fp/clear.png?<?php echo $dfParam; ?>&m=2" width="1" height="1" />
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add loading state and confirmation to payment button
        document.getElementById('payment_form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Add loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirecting to Secure Payment...';
            submitBtn.disabled = true;
            
            // Optional: Add a small delay to show the loading state
            setTimeout(() => {
                // The form will submit naturally after this
            }, 500);
        });
    </script>
    
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

<?php else: ?>
<!-- Original sa-wm payment confirmation (hidden when showing registration preview) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <link rel="stylesheet" type="text/css" href="wm.css"/>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="logo">
                    <img src="../img/logo-cybersource.png" alt="CyberSource Logo" />
                </div>
                <div class="header-text">
                    <h1>Payment Confirmation</h1>
                    <p>Review & Confirm Transaction Details</p>
                </div>
            </div>
        </div>
        <div class="confirmation-container">
            <div class="confirmation-header">
                <h2>Review Transaction Details</h2>
            </div>
            <div class="confirmation-content">
                <form id="payment_confirmation" action="<?php echo $endpoint_url ?>" method="post">
<?php
    foreach($_POST as $name => $value) {
        $params[$name] = $value;
    }
?>
                    
                    <div class="confirmation-details">
                        <h3 style="color: var(--primary-green); margin-bottom: 20px; font-size: 1.3rem;">Transaction Information</h3>
        <?php
            foreach($params as $name => $value) {
                                if (!empty($value) && !in_array($name, ['profile_id', 'access_key', 'signed_field_names', 'unsigned_field_names', 'signature'])) {
                                    echo "<div style='margin-bottom: 12px; padding: 8px 0; border-bottom: 1px solid var(--border-color);'>";
                                    echo "<span class=\"fieldName\">" . ucwords(str_replace('_', ' ', $name)) . ":</span> ";
                                    echo "<span class=\"fieldValue\">" . htmlspecialchars($value) . "</span>";
                echo "</div>\n";
                                }
            }
        ?>
    </div>
    
    <?php
        foreach($params as $name => $value) {
                            echo "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . htmlspecialchars($value) . "\"/>\n";
        }
    ?>

    <input type="hidden" name="signature" value="<?php echo sign($params) ?>" />

                    <div style="text-align: center; margin-top: 30px;">
                        <button type="submit" class="btn" id="btn_submit">Confirm & Process Payment</button>
                    </div>
</form>
            </div>
        </div>

        <div class="back-link">
            <a href="javascript:history.back()">← Back to Form</a>
        </div>
    </div>

    <script type="text/javascript">
        // Add loading state to submit button
        document.getElementById('payment_confirmation').addEventListener('submit', function() {
            var submitBtn = document.getElementById('btn_submit');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
<?php endif; ?>
