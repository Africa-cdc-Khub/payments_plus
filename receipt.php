<?php
/**
 * Printable Receipt Page
 * Displays a professional receipt for paid registrations
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Get registration ID from URL
$registrationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$isPrint = isset($_GET['print']) && $_GET['print'] == '1';

if (!$registrationId) {
    http_response_code(404);
    die('Registration not found');
}

// Get registration details
$registration = getRegistrationById($registrationId);
if (!$registration) {
    http_response_code(404);
    die('Registration not found');
}

// Verify email if provided
if ($email && $registration['user_email'] !== $email) {
    http_response_code(403);
    die('Access denied');
}

// Check if registration is paid
$paymentStatus = $registration['payment_status'] ?? '';

if ($paymentStatus !== 'completed') {
    http_response_code(403);
    die('Receipt not available for this registration. Only completed payments can generate receipts.');
}

// Get package details
$package = getPackageById($registration['package_id']);
if (!$package) {
    http_response_code(404);
    die('Package not found');
}

// Get participants if group registration
$participants = [];
if ($registration['registration_type'] === 'group') {
    $participants = getRegistrationParticipants($registrationId);
}

// Prepare user data
$organizationAddress = '';
if (!empty($registration['address_line1'])) {
    $organizationAddress = $registration['address_line1'];
    if (!empty($registration['address_line2'])) {
        $organizationAddress .= ', ' . $registration['address_line2'];
    }
    if (!empty($registration['city'])) {
        $organizationAddress .= ', ' . $registration['city'];
    }
    if (!empty($registration['state'])) {
        $organizationAddress .= ', ' . $registration['state'];
    }
    if (!empty($registration['postal_code'])) {
        $organizationAddress .= ' ' . $registration['postal_code'];
    }
    if (!empty($registration['country'])) {
        $organizationAddress .= ', ' . $registration['country'];
    }
}

// Generate QR codes for receipt
function generateReceiptQRCodes($registration, $userEmail) {
    $qrCodes = [];
    
    // Main registration QR code
    $mainQrData = json_encode([
        'type' => 'registration',
        'registration_id' => $registration['id'],
        'participant_name' => $registration['first_name'] . ' ' . $registration['last_name'],
        'email' => $userEmail,
        'package' => $registration['package_name'],
        'amount' => $registration['total_amount'],
        'timestamp' => date('c')
    ]);
    
    // Verification QR code
    $verificationQrData = json_encode([
        'type' => 'verification',
        'registration_id' => $registration['id'],
        'email' => $userEmail
    ]);
    
    // Navigation QR code (link to verification page)
    $verificationUrl = rtrim(APP_URL, '/') . "/verify_attendance.php?email=" . urlencode($userEmail) . "&reg_id=" . $registration['id'];
    
    // Generate QR codes using Google Charts API
    $qrCodes['main'] = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($mainQrData);
    $qrCodes['verification'] = "https://chart.googleapis.com/chart?chs=120x120&cht=qr&chl=" . urlencode($verificationQrData);
    $qrCodes['navigation'] = "https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl=" . urlencode($verificationUrl);
    
    return $qrCodes;
}

$qrCodes = generateReceiptQRCodes($registration, $registration['user_email']);

// Set print-friendly headers if printing
if ($isPrint) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Receipt - <?php echo CONFERENCE_SHORT_NAME; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .receipt-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a5632 0%, #2d7d32 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            width: 100%;
            gap: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .africa-cdc-logo {
            max-width: 120px;
        }
        .cphia-logo {
            max-width: 180px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: white;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
            color: white;
        }
        .receipt-content {
            padding: 30px;
        }
        .receipt-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .receipt-title h2 {
            color: #1a5632;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        .receipt-title p {
            color: #666;
            font-size: 16px;
            margin: 0;
        }
        .receipt-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 120px;
        }
        .detail-value {
            color: #212529;
            text-align: right;
            flex: 1;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .qr-codes-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }
        .qr-code-main, .qr-code-verification, .qr-code-navigation {
            text-align: center;
        }
        .qr-code {
            max-width: 200px;
            height: auto;
            margin: 15px 0;
            border: 3px solid #1a5632;
            border-radius: 8px;
        }
        .qr-code-small {
            max-width: 120px;
            height: auto;
            margin: 10px 0;
            border: 2px solid #1a5632;
            border-radius: 6px;
        }
        .qr-code-navigation {
            max-width: 100px;
            height: auto;
            margin: 8px 0;
            border: 2px solid #ff8c00;
            border-radius: 6px;
        }
        .qr-info {
            color: #666;
            font-size: 14px;
            margin-top: 15px;
        }
        .qr-info-small {
            color: #666;
            font-size: 12px;
            margin-top: 10px;
        }
        .qr-info-navigation {
            color: #ff8c00;
            font-size: 11px;
            margin-top: 8px;
            font-weight: 600;
        }
        .amount-section {
            background: linear-gradient(135deg, #1a5632 0%, #2d7d32 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
        }
        .amount-label {
            font-size: 16px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        .amount-value {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 25px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .contact-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .contact-info h4 {
            color: #1a5632;
            margin-bottom: 15px;
        }
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .contact-item i {
            width: 20px;
            color: #1a5632;
            margin-right: 10px;
        }
        .participants-section {
            margin: 30px 0;
        }
        .participants-title {
            color: #1a5632;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .participant-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #1a5632;
        }
        .participant-name {
            font-weight: 600;
            color: #1a5632;
            margin-bottom: 8px;
        }
        .participant-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            font-size: 14px;
        }
        .participant-detail {
            display: flex;
            flex-direction: column;
        }
        .participant-detail-label {
            font-weight: 500;
            color: #6c757d;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .participant-detail-value {
            color: #333;
            margin-top: 2px;
        }
        
        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
            }
            .header {
                background: #1a5632 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .amount-section {
                background: #1a5632 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .qr-section {
                page-break-inside: avoid;
            }
            .participants-section {
                page-break-inside: avoid;
            }
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .receipt-content {
                padding: 20px;
            }
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .detail-value {
                text-align: left;
                margin-top: 5px;
            }
            .participant-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="logo-container">
                <img src="https://africacdc.org/wp-content/uploads/2020/02/AfricaCDC_Logo.png" alt="Africa CDC" class="logo africa-cdc-logo">
                <img src="https://cphia2025.com/wp-content/uploads/2025/09/CPHIA-2025-logo_reverse.png" alt="CPHIA 2025" class="logo cphia-logo">
            </div>
            <h1><?php echo CONFERENCE_SHORT_NAME; ?></h1>
            <p><?php echo CONFERENCE_DATES; ?> • <?php echo CONFERENCE_LOCATION; ?></p>
        </div>
        
        <div class="receipt-content">
            <div class="receipt-title">
                <h2><?php echo $registration['registration_type'] === 'group' ? 'Group Registration Receipt' : 'Registration Receipt'; ?></h2>
                <p>Payment Confirmation</p>
            </div>
            
            <div class="receipt-details">
                <div class="detail-row">
                    <span class="detail-label">Registration ID:</span>
                    <span class="detail-value">#<?php echo $registration['id']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Participant Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($registration['user_email']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($registration['phone'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Package:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($registration['package_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Organization:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($registration['organization']); ?></span>
                </div>
                <?php if (!empty($registration['institution'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Institution:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($registration['institution']); ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label">Nationality:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($registration['nationality']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y \a\t g:i A', strtotime($registration['updated_at'])); ?></span>
                </div>
            </div>
            
            <?php if (!empty($participants)): ?>
            <div class="participants-section">
                <h3 class="participants-title">Group Participants</h3>
                <?php foreach($participants as $participant): ?>
                <div class="participant-card">
                    <div class="participant-name"><?php echo htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']); ?></div>
                    <div class="participant-details">
                        <div class="participant-detail">
                            <div class="participant-detail-label">Email</div>
                            <div class="participant-detail-value"><?php echo htmlspecialchars($participant['email']); ?></div>
                        </div>
                        <div class="participant-detail">
                            <div class="participant-detail-label">Phone</div>
                            <div class="participant-detail-value"><?php echo htmlspecialchars($participant['phone'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="participant-detail">
                            <div class="participant-detail-label">Organization</div>
                            <div class="participant-detail-value"><?php echo htmlspecialchars($participant['organization']); ?></div>
                        </div>
                        <div class="participant-detail">
                            <div class="participant-detail-label">Nationality</div>
                            <div class="participant-detail-value"><?php echo htmlspecialchars($participant['nationality']); ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="qr-section">
                <h4 style="color: #1a5632; margin-bottom: 15px;">Registration QR Codes</h4>
                <div class="qr-codes-container">
                    <div class="qr-code-main">
                        <img src="<?php echo $qrCodes['main']; ?>" alt="Full Registration QR Code" class="qr-code">
                        <p class="qr-info">
                            <strong>Complete Receipt</strong><br>
                            All registration details
                        </p>
                    </div>
                    <div class="qr-code-verification">
                        <img src="<?php echo $qrCodes['verification']; ?>" alt="Verification QR Code" class="qr-code-small">
                        <p class="qr-info-small">
                            <strong>Quick Check-in</strong><br>
                            Fast attendance scanning
                        </p>
                    </div>
                    <div class="qr-code-navigation">
                        <img src="<?php echo $qrCodes['navigation']; ?>" alt="Verification Link QR Code" class="qr-code-navigation">
                        <p class="qr-info-navigation">
                            <strong>Verify Online</strong><br>
                            Scan to verify attendance
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="amount-section">
                <div class="amount-label">Total Amount Paid</div>
                <div class="amount-value"><?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?></div>
            </div>
            
            <div class="contact-info">
                <h4>Contact Information</h4>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo SUPPORT_EMAIL; ?></span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-globe"></i>
                    <span>https://cphia2025.com</span>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Thank you for registering for <?php echo CONFERENCE_NAME; ?>!</strong></p>
            <p>This receipt serves as confirmation of your registration and payment.</p>
            <p>Please keep this receipt for your records and bring it to the conference.</p>
        </div>
    </div>
    
    <?php if ($isPrint): ?>
    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
    <?php endif; ?>
</body>
</html>
