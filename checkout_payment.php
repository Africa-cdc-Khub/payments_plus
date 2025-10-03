<?php
/**
 * Improved Checkout Payment System
 * Modern, responsive payment page with better UX
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
$stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name, u.email as user_email, u.address_line1, u.city, u.state, u.country, u.postal_code,
                      p.name as package_name, p.type
                      FROM registrations r 
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
$responsePage = rtrim(APP_URL, '/') . '/response.php';

// Prepare payment data
$paymentData = [
    // Signed fields (required for security)
    'profile_id' => PROFILE_ID,
    'access_key' => ACCESS_KEY,
    'transaction_uuid' => $transactionUuid,
    'signed_field_names' => 'access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,payment_method,bill_to_forename,bill_to_surname,bill_to_email,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,device_fingerprint_id,customer_ip_address,line_item_count,item_0_sku,item_0_code,item_0_name,item_0_quantity,item_0_unit_price,item_0_tax_amount,item_0_amount',
    'unsigned_field_names' => 'card_type,card_number,card_expiry_date,card_cvn',
    'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
    'locale' => 'en',
    'transaction_type' => 'sale',
    'reference_number' => 'REG-' . $registrationId . '-' . time(),
    'amount' => $registration['total_amount'],
    'currency' => $registration['currency'] ?? 'USD',
    'payment_method' => 'card',
    
    // Billing information
    'bill_to_forename' => $registration['first_name'],
    'bill_to_surname' => $registration['last_name'],
    'bill_to_email' => $registration['user_email'],
    'bill_to_address_line1' => $registration['address_line1'] ?? '',
    'bill_to_address_city' => $registration['city'] ?? '',
    'bill_to_address_state' => $registration['state'] ?? '',
    'bill_to_address_country' => $registration['country'] ?? '',
    'bill_to_address_postal_code' => $registration['postal_code'] ?? '',
    
    // Device fingerprinting
    'device_fingerprint_id' => $sessId,
    'customer_ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    
    // Line items
    'line_item_count' => '1',
    'item_0_sku' => 'PKG-' . $registration['package_id'],
    'item_0_code' => $registration['type'],
    'item_0_name' => $registration['package_name'],
    'item_0_quantity' => '1',
    'item_0_unit_price' => $registration['total_amount'],
    'item_0_tax_amount' => '0.00',
    'item_0_amount' => $registration['total_amount']
];

$signature = sign($paymentData);
?>

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
    <link rel="stylesheet" href="css/style.css">
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
            margin: 0;
            padding: var(--spacing-6);
            background: var(--light-gray);
        }
        .payment-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid var(--light-gray);
            width: 100%;
            margin: 0;
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
            min-height: 800px;
            width: 100%;
            display: flex;
        }
        .payment-form {
            padding: var(--spacing-10);
            background: var(--white);
            height: 100%;
            width: 50%;
            display: flex;
            flex-direction: column;
        }
        .participant-info {
            background: var(--light-gray);
            padding: var(--spacing-10);
            border-left: 1px solid #e9ecef;
            height: 100%;
            width: 50%;
            display: flex;
            flex-direction: column;
        }
        @media (max-width: 991.98px) {
            .payment-content {
                flex-direction: column;
            }
            .payment-form {
                width: 100%;
            }
            .participant-info {
                width: 100%;
                border-left: none;
                border-top: 1px solid #e9ecef;
                margin-top: var(--spacing-4);
            }
        }
        .participant-info h4 {
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: var(--spacing-8);
            font-size: var(--font-size-2xl);
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
            width: 100%;
        }
        .participant-details {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: var(--spacing-6);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            width: 100%;
            flex: 1;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-6) 0;
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
            font-size: var(--font-size-base);
        }
        .detail-value {
            font-weight: 600;
            color: var(--dark-gray);
            text-align: right;
            font-size: var(--font-size-lg);
        }
        .payment-form {
            padding: var(--spacing-10);
            background: var(--white);
            flex: 1;
        }
        .payment-form h4 {
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: var(--spacing-10);
            font-size: var(--font-size-2xl);
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
            width: 100%;
        }
        .form-label {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: var(--spacing-2);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-8);
            margin-bottom: var(--spacing-8);
            width: 100%;
        }
        .form-row.single {
            grid-template-columns: 1fr;
        }
        .form-group {
            margin-bottom: var(--spacing-8);
            width: 100%;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: var(--radius-md);
            padding: var(--spacing-6) var(--spacing-6);
            font-size: var(--font-size-xl);
            height: 64px;
        }
        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(26, 86, 50, 0.25);
            outline: none;
        }
        .form-control::placeholder {
            color: #adb5bd;
            font-size: var(--font-size-sm);
        }
        .card-icons {
            display: flex;
            gap: var(--spacing-6);
            margin-top: var(--spacing-6);
            justify-content: center;
        }
        .card-icon {
            width: 80px;
            height: 50px;
            background: var(--white);
            border: 1px solid #dee2e6;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-base);
            color: var(--medium-gray);
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-icon.visa {
            background: #1a1f71;
            color: white;
        }
        .card-icon.mastercard {
            background: #eb001b;
            color: white;
        }
        .card-icon.amex {
            background: #006fcf;
            color: white;
        }
        .btn-pay {
            background: linear-gradient(135deg, var(--secondary-green) 0%, var(--primary-green) 100%);
            border: none;
            border-radius: var(--radius-md);
            padding: var(--spacing-8) var(--spacing-10);
            font-size: var(--font-size-2xl);
            font-weight: 600;
            color: var(--white);
            width: 100%;
            box-shadow: 0 4px 15px rgba(26, 86, 50, 0.3);
            margin-top: var(--spacing-10);
            height: 72px;
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
        
        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            .payment-container {
                padding: var(--spacing-3);
            }
            .payment-form {
                padding: var(--spacing-4);
            }
            .participant-info {
                padding: var(--spacing-4);
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: var(--spacing-4);
            }
            .form-control {
                height: 48px;
                font-size: var(--font-size-base);
            }
            .btn-pay {
                height: 52px;
                font-size: var(--font-size-lg);
                padding: var(--spacing-4) var(--spacing-6);
            }
            .card-icons {
                gap: var(--spacing-2);
            }
            .card-icon {
                width: 45px;
                height: 28px;
                font-size: var(--font-size-xs);
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
                        <img src="images/logo.png" alt="CPHIA 2025" class="logo-img" style="filter: brightness(0) invert(1);">
                    </div>
                    <h1 class="mb-2"><?php echo CONFERENCE_NAME; ?></h1>
                    <h2 class="mb-2"><?php echo CONFERENCE_SHORT_NAME; ?></h2>
                    <p class="conference-dates mb-0"><?php echo CONFERENCE_DATES; ?> • <?php echo CONFERENCE_LOCATION; ?></p>
                </div>
            </div>
        </header>
    </div> <!-- Close container div -->

    <div class="payment-container">
        <a href="registration_lookup.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Registrations
        </a>
        
        <div class="payment-card">
                
                <div class="payment-content">
                    <!-- Left Side: Payment Form -->
                    <div class="payment-form">
                        <h4><i class="fas fa-lock me-2"></i>Payment Information</h4>
                
                        <form id="payment_form" action="sa-sop/payment_confirm.php" method="post">
                            <!-- Hidden signed fields -->
                            <?php foreach ($paymentData as $name => $value): ?>
                                <input type="hidden" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($value ?? ''); ?>">
                            <?php endforeach; ?>
                            
                            <input type="hidden" name="signature" value="<?php echo $signature; ?>">

                            <!-- Card Type and Number Row -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="card_type" class="form-label">Card Type</label>
                                    <select name="card_type" id="card_type" class="form-control" required>
                                        <option value="">Select Card Type</option>
                                        <option value="001">Visa</option>
                                        <option value="002">Mastercard</option>
                                        <option value="003">American Express</option>
                                        <option value="004">Discover</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" name="card_number" id="card_number" class="form-control" 
                                           maxlength="19" placeholder="1234 5678 9012 3456" required>
                                </div>
                            </div>

                            <!-- Card Icons -->
                            <div class="card-icons">
                                <div class="card-icon visa">VISA</div>
                                <div class="card-icon mastercard">MC</div>
                                <div class="card-icon amex">AMEX</div>
                                <div class="card-icon">DISC</div>
                            </div>

                            <!-- Expiry and CVV Row -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="card_expiry_date" class="form-label">Expiry Date</label>
                                    <input type="text" name="card_expiry_date" id="card_expiry_date" class="form-control" 
                                           placeholder="MM/YY" maxlength="5" required>
                                </div>
                                <div class="form-group">
                                    <label for="card_cvn" class="form-label">CVV</label>
                                    <input type="text" name="card_cvn" id="card_cvn" class="form-control" 
                                           maxlength="4" placeholder="123" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-pay">
                                <i class="fas fa-lock me-2"></i>
                                Pay <?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?> - Complete Registration
                            </button>
                            
                            <!-- CyberSource Branding -->
                            <div class="cybersource-branding">
                                <img src="img/logo-cybersource.png" alt="Powered by CyberSource" class="cybersource-logo">
                                <p class="small text-muted mb-0 mt-2">Powered by CyberSource</p>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Right Side: Participant Information -->
                    <div class="participant-info">
                        <h4><i class="fas fa-user me-2"></i>Registration Details</h4>
                        <div class="participant-details">
                            <div class="detail-item">
                                <span class="detail-label">Registration ID</span>
                                <span class="detail-value">#<?php echo $registration['id']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Package</span>
                                <span class="detail-value"><?php echo htmlspecialchars($registration['package_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Participant</span>
                                <span class="detail-value"><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo htmlspecialchars($registration['user_email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Conference</span>
                                <span class="detail-value"><?php echo CONFERENCE_DATES; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total Amount</span>
                                <span class="detail-value amount-highlight"><?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
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
        // Format card number input and detect card type
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
            
            // Auto-detect card type and update dropdown
            detectCardType(value);
        });

        // Auto-detect card type based on number
        function detectCardType(cardNumber) {
            const cardTypeSelect = document.getElementById('card_type');
            const firstDigit = cardNumber.charAt(0);
            const firstTwoDigits = cardNumber.substring(0, 2);
            
            if (firstDigit === '4') {
                cardTypeSelect.value = '001'; // Visa
            } else if (firstTwoDigits >= '51' && firstTwoDigits <= '55') {
                cardTypeSelect.value = '002'; // Mastercard
            } else if (firstTwoDigits === '34' || firstTwoDigits === '37') {
                cardTypeSelect.value = '003'; // American Express
            } else if (firstTwoDigits === '65' || firstTwoDigits === '64' || firstTwoDigits === '60') {
                cardTypeSelect.value = '004'; // Discover
            }
        }

        // Format expiry date input
        document.getElementById('card_expiry_date').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        // Format CVV input
        document.getElementById('card_cvn').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // Add visual feedback for form validation
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = '#28a745';
                } else {
                    this.style.borderColor = '#dc3545';
                }
            });
            
            input.addEventListener('focus', function() {
                this.style.borderColor = 'var(--primary-green)';
            });
        });

        // Form submission
        document.getElementById('payment_form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
