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
$isDownload = isset($_GET['download']) && $_GET['download'] == '1';

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
    
    // Generate QR codes using multiple fallback methods
    $qrCodes['main'] = generateQRCodeImage($mainQrData, 150);
    $qrCodes['verification'] = generateQRCodeImage($verificationQrData, 150);
    $qrCodes['navigation'] = generateQRCodeImage($verificationUrl, 150);
    
    return $qrCodes;
}

// Generate QR code image with fallbacks
function generateQRCodeImage($data, $size) {
    // Try QR Server API first (more reliable)
    $qrServerUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
    
    // Test if QR Server is accessible
    $headers = @get_headers($qrServerUrl);
    if ($headers && strpos($headers[0], '200') !== false) {
        return $qrServerUrl;
    }
    
    // Fallback to Google Charts API
    $googleUrl = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . urlencode($data);
    
    // Test if Google Charts is accessible
    $headers = @get_headers($googleUrl);
    if ($headers && strpos($headers[0], '200') !== false) {
        return $googleUrl;
    }
    
    // Final fallback - return a placeholder with data
    return "data:image/svg+xml;base64," . base64_encode(
        '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">' .
        '<rect width="' . $size . '" height="' . $size . '" fill="#f0f0f0" stroke="#ccc" stroke-width="2"/>' .
        '<text x="' . ($size/2) . '" y="' . ($size/2) . '" text-anchor="middle" fill="#666" font-size="12">QR Code</text>' .
        '<text x="' . ($size/2) . '" y="' . ($size/2 + 15) . '" text-anchor="middle" fill="#999" font-size="8">' . substr($data, 0, 20) . '...</text>' .
        '</svg>'
    );
}

$qrCodes = generateReceiptQRCodes($registration, $registration['user_email']);

// Debug QR codes (remove in production)
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<!-- QR Code Debug Info:\n";
    echo "Main QR URL: " . $qrCodes['main'] . "\n";
    echo "Verification QR URL: " . $qrCodes['verification'] . "\n";
    echo "Navigation QR URL: " . $qrCodes['navigation'] . "\n";
    echo "-->\n";
}

// Set headers based on action
if ($isDownload) {
    // Redirect to print version which can be saved as PDF
    header('Location: receipt.php?id=' . $registrationId . '&email=' . urlencode($userEmail) . '&print=1');
    exit;
} elseif ($isPrint) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Receipt - <?php echo CONFERENCE_SHORT_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            margin: 0 -30px 15px -30px;
            width: calc(100% + 60px);
            gap: 20px;
            background: white;
            padding: 15px 30px;
            border-radius: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .logo {
            max-width: 150px;
            width: 150px;
            height: auto;
        }
        .africa-cdc-logo {
            max-width: 150px;
            width: 150px;
            height: auto;
        }
        .cphia-logo {
            max-width: 150px;
            width: 150px;
            height: auto;
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
            max-width: 150px;
            width: 150px;
            height: 150px;
            margin: 15px 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .qr-code-small {
            max-width: 150px;
            width: 150px;
            height: 150px;
            margin: 15px 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .qr-code-navigation {
            max-width: 150px;
            width: 150px;
            height: 150px;
            margin: 15px 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .qr-code img {
            max-width: 100%;
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 4px;
        }
        .qr-fallback {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }
        .receipt-actions {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .receipt-actions h4 {
            color: #1a5632;
            margin-bottom: 15px;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .print-btn {
            background: #1a5632;
            color: white;
        }
        .print-btn:hover {
            background: #2d7d32;
            color: white;
        }
        .email-btn {
            background: #007bff;
            color: white;
        }
        .email-btn:hover {
            background: #0056b3;
            color: white;
        }
        .download-btn {
            background: #28a745;
            color: white;
        }
        .download-btn:hover {
            background: #1e7e34;
            color: white;
        }
        @media print {
            .receipt-actions {
                display: none;
            }
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
            color: #666;
            font-size: 14px;
            margin-top: 15px;
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
                font-size: 12px;
                line-height: 1.4;
            }
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
                width: 100%;
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
            .receipt-actions {
                display: none;
            }
            .print-instructions {
                display: none;
            }
            .logo {
                max-width: 120px !important;
                width: 120px !important;
            }
            .qr-code {
                max-width: 120px !important;
                width: 120px !important;
                height: 120px !important;
            }
            .receipt-content {
                padding: 20px;
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
    <?php if ($isPrint): ?>
    <div class="print-instructions" style="background: #e3f2fd; border: 1px solid #2196f3; padding: 15px; margin: 20px; border-radius: 8px; text-align: center;">
        <h3 style="color: #1976d2; margin: 0 0 10px 0;">📄 Save as PDF Instructions</h3>
        <p style="margin: 0; color: #1976d2;">
            <strong>To save this receipt as a PDF:</strong><br>
            1. In the print dialog, select "Save as PDF" as the destination<br>
            2. Choose your preferred settings and click "Save"<br>
            3. Select a location and filename for your PDF receipt
        </p>
    </div>
    <?php endif; ?>
    
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
                        <div class="qr-code">
                            <img src="<?php echo $qrCodes['main']; ?>" 
                                 alt="Full Registration QR Code" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="qr-fallback" style="display: none;">
                                <i class="fas fa-qrcode" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                                <strong>Registration QR Code</strong><br>
                                <small>Registration #<?php echo $registration['id']; ?></small>
                            </div>
                        </div>
                        <p class="qr-info">
                            <strong>Complete Receipt</strong><br>
                            All registration details
                        </p>
                    </div>
                    <div class="qr-code-verification">
                        <div class="qr-code">
                            <img src="<?php echo $qrCodes['verification']; ?>" 
                                 alt="Verification QR Code" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="qr-fallback" style="display: none;">
                                <i class="fas fa-qrcode" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                                <strong>Verification</strong><br>
                                <small>Check-in</small>
                            </div>
                        </div>
                        <p class="qr-info">
                            <strong>Quick Check-in</strong><br>
                            Fast attendance scanning
                        </p>
                    </div>
                    <div class="qr-code-navigation">
                        <div class="qr-code">
                            <img src="<?php echo $qrCodes['navigation']; ?>" 
                                 alt="Verification Link QR Code" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="qr-fallback" style="display: none;">
                                <i class="fas fa-qrcode" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                                <strong>Online</strong><br>
                                <small>Verify</small>
                            </div>
                        </div>
                        <p class="qr-info">
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
            
            <div class="receipt-actions">
                <h4>Receipt Actions</h4>
                <div class="action-buttons">
                    <button onclick="printReceipt()" class="action-btn print-btn">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                    <button onclick="emailReceipt()" class="action-btn email-btn">
                        <i class="fas fa-envelope me-2"></i>Email Receipt
                    </button>
                    <button onclick="downloadReceipt()" class="action-btn download-btn">
                        <i class="fas fa-download me-2"></i>Download PDF
                    </button>
                </div>
                <p class="text-muted mt-3 mb-0">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        You can print this receipt, email it to yourself, or download it as a PDF for your records.
                    </small>
                </p>
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
    
    <script>
        // Handle QR code loading errors
        document.addEventListener('DOMContentLoaded', function() {
            const qrImages = document.querySelectorAll('.qr-code img');
            qrImages.forEach(function(img) {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const fallback = this.nextElementSibling;
                    if (fallback) {
                        fallback.style.display = 'block';
                    }
                });
                
                // Test if image loads
                img.addEventListener('load', function() {
                    console.log('QR code loaded successfully:', this.src);
                });
            });
        });
        
        // Print receipt function
        function printReceipt() {
            window.print();
        }
        
        // Email receipt function
        function emailReceipt() {
            const registrationId = <?php echo $registration['id']; ?>;
            const email = '<?php echo $registration['user_email']; ?>';
            
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            button.disabled = true;
            
            // Send email request
            console.log('Sending email request:', { registration_id: registrationId, email: email });
            fetch('send_receipt_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    registration_id: registrationId,
                    email: email
                })
            })
            .then(response => {
                console.log('Email response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Email response data:', data);
                if (data.success) {
                    button.innerHTML = '<i class="fas fa-check me-2"></i>Email Sent!';
                    button.classList.remove('email-btn');
                    button.classList.add('download-btn');
                    showMessage('Receipt email sent successfully!', 'success');
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    showMessage(data.message || 'Failed to send email. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Email sending error:', error);
                button.innerHTML = originalText;
                button.disabled = false;
                showMessage(`Error: ${error.message}. Please try again.`, 'error');
            });
        }
        
        // Download PDF function
        function downloadReceipt() {
            const registrationId = <?php echo $registration['id']; ?>;
            const email = '<?php echo $registration['user_email']; ?>';
            
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Opening...';
            button.disabled = true;
            
            // Open print version in new window for PDF generation
            const printUrl = `receipt.php?id=${registrationId}&email=${encodeURIComponent(email)}&print=1`;
            const printWindow = window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
            
            if (printWindow) {
                printWindow.onload = function() {
                    // Auto-trigger print dialog for PDF generation
                    setTimeout(() => {
                        printWindow.print();
                    }, 1500);
                };
                
                // Reset button after a delay
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 3000);
            } else {
                // Fallback: show message
                button.innerHTML = originalText;
                button.disabled = false;
                showMessage('Please allow popups to download PDF receipts.', 'warning');
            }
        }
        
        // Show message function
        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            messageDiv.style.position = 'fixed';
            messageDiv.style.top = '20px';
            messageDiv.style.right = '20px';
            messageDiv.style.zIndex = '9999';
            messageDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(messageDiv);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 5000);
        }
        
        <?php if ($isPrint): ?>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
        <?php endif; ?>
    </script>
</body>
</html>
