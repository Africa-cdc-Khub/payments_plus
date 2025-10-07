<?php
/**
 * Printable Invoice Page
 * Displays a professional invoice for registration
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Get registration ID from URL
$registrationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$email = isset($_GET['email']) ? trim($_GET['email']) : '';

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
$user = [
    'first_name' => $registration['first_name'],
    'last_name' => $registration['last_name'],
    'email' => $registration['user_email'],
    'organization' => $registration['organization'] ?? '',
    'organization_address' => $registration['organization_address'] ?? ''
];

// Generate invoice data
$invoiceData = generateInvoiceData(
    $user, 
    $registrationId, 
    $package, 
    $registration['total_amount'], 
    $participants, 
    $registration['registration_type']
);

// Set page title
$pageTitle = "Invoice #" . $registrationId . " - " . CONFERENCE_SHORT_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            max-width: 800px; 
            margin: 0 auto; 
            background: #f8f9fa;
            padding: 20px;
        }
        .invoice-container {
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #063218, #0a4d2e); 
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        .header h1 { 
            color: white; 
            margin: 0; 
            font-size: 28px; 
            font-weight: bold; 
        }
        .header p { 
            color: white; 
            margin: 5px 0 0 0; 
            font-size: 16px; 
            opacity: 0.9; 
        }
        .logo-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .logo { 
            max-height: 60px; 
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            padding: 30px;
            border-bottom: 2px solid #e9ecef;
        }
        .invoice-info h2 {
            color: #063218;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .invoice-meta {
            color: #666;
            font-size: 14px;
        }
        .bill-to {
            text-align: right;
        }
        .bill-to h3 {
            color: #063218;
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        .bill-to p {
            margin: 2px 0;
            color: #555;
        }
        .content { 
            padding: 30px; 
        }
        .registration-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #063218;
        }
        .participants-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .participants-table th {
            background: #063218;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        .participants-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        .participants-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .amount-cell {
            text-align: right;
            font-weight: bold;
            color: #063218;
        }
        .total-section {
            background: #063218;
            color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .payment-section {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            border: 2px solid #4caf50;
        }
        .payment-button {
            display: inline-block;
            background: #063218;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px;
            transition: background 0.3s;
        }
        .payment-button:hover {
            background: #0a4d2e;
            color: white;
        }
        .payment-instructions {
            background: #f8f9fa;
            padding: 25px;
            margin: 20px 0;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .payment-instructions h3 {
            color: #063218;
            margin: 0 0 20px 0;
            font-size: 18px;
            border-bottom: 2px solid #063218;
            padding-bottom: 10px;
        }
        .instructions-content h4 {
            color: #063218;
            margin: 20px 0 10px 0;
            font-size: 16px;
        }
        .instructions-content ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions-content li {
            margin: 8px 0;
            line-height: 1.5;
        }
        .payment-info-box {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #063218;
        }
        .payment-info-box h4 {
            color: #063218;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .payment-info-box ul {
            margin: 0;
            padding-left: 20px;
        }
        .payment-info-box li {
            margin: 5px 0;
            font-size: 13px;
        }
        .contact-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .contact-info h4 {
            color: #063218;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .contact-info p {
            margin: 5px 0;
            font-size: 13px;
        }
        .footer { 
            text-align: center; 
            padding: 30px; 
            background: #f8f9fa; 
            color: #666; 
            border-top: 1px solid #e9ecef;
        }
        .highlight { 
            color: #063218; 
            font-weight: bold; 
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            color: #063218;
        }
        .conference-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .conference-info h4 {
            color: #063218;
            margin: 0 0 10px 0;
        }
        .conference-info p {
            margin: 5px 0;
            color: #555;
        }
        .print-actions {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .print-button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 10px;
        }
        .print-button:hover {
            background: #0056b3;
        }
        @media print {
            body { background: white; padding: 0; }
            .invoice-container { margin: 0; box-shadow: none; }
            .payment-section, .print-actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()" class="print-button">🖨️ Print Invoice</button>
        <a href="<?php echo htmlspecialchars($invoiceData['payment_link']); ?>" class="payment-button">💳 Pay Now</a>
    </div>

    <div class="invoice-container">
        <div class="header">
            <div class="header-left">
                <div class="logo-container">
                    <img src="<?php echo htmlspecialchars($invoiceData['logo_url']); ?>" alt="Africa CDC" class="logo">
                    <div>
                        <h1><?php echo htmlspecialchars($invoiceData['conference_short_name']); ?></h1>
                        <p class="subtitle"><?php echo htmlspecialchars($invoiceData['conference_name']); ?></p>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#INV-<?php echo htmlspecialchars($invoiceData['registration_id']); ?></div>
                <div style="font-size: 12px; color: #b8d4c1; margin-top: 5px;">
                    Date: <?php echo htmlspecialchars($invoiceData['invoice_date']); ?>
                </div>
            </div>
        </div>
        
        <div class="invoice-details">
            <div class="bill-to">
                <h3>Bill To:</h3>
                <?php if (!empty($invoiceData['organization_name'])): ?>
                <p><strong><?php echo htmlspecialchars($invoiceData['organization_name']); ?></strong></p>
                <?php endif; ?>
                <p><?php echo htmlspecialchars($invoiceData['user_name']); ?></p>
                <p><?php echo htmlspecialchars($invoiceData['user_email']); ?></p>
                <?php if (!empty($invoiceData['organization_address'])): ?>
                <p><?php echo htmlspecialchars($invoiceData['organization_address']); ?></p>
                <?php endif; ?>
            </div>
            <div class="invoice-info">
                <div class="invoice-meta">
                    <p><strong>Due Date:</strong> <?php echo htmlspecialchars($invoiceData['due_date']); ?></p>
                    <p><strong>Registration Type:</strong> <?php echo htmlspecialchars($invoiceData['registration_type']); ?></p>
                    <?php if ($invoiceData['num_participants'] > 1): ?>
                    <p><strong>Participants:</strong> <?php echo htmlspecialchars($invoiceData['num_participants']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="content">
            <div class="conference-info">
                <h4>Conference Details</h4>
                <p><strong>Event:</strong> <?php echo htmlspecialchars($invoiceData['conference_name']); ?></p>
                <p><strong>Dates:</strong> <?php echo htmlspecialchars($invoiceData['conference_dates']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($invoiceData['conference_venue']); ?>, <?php echo htmlspecialchars($invoiceData['conference_location']); ?></p>
            </div>
            
            <div class="registration-details">
                <h3>Registration Details</h3>
                <p><strong>Registration ID:</strong> #<?php echo htmlspecialchars($invoiceData['registration_id']); ?></p>
                <p><strong>Package:</strong> <?php echo htmlspecialchars($invoiceData['package_name']); ?></p>
                <p><strong>Registration Type:</strong> <?php echo htmlspecialchars($invoiceData['registration_type']); ?></p>
                <?php if ($invoiceData['num_participants'] > 1): ?>
                <p><strong>Number of Participants:</strong> <?php echo htmlspecialchars($invoiceData['num_participants']); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($invoiceData['participants'])): ?>
            <h3>Participants</h3>
            <table class="participants-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Nationality</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoiceData['participants'] as $index => $participant): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($participant['email']); ?></td>
                        <td><?php echo htmlspecialchars($participant['nationality']); ?></td>
                        <td class="amount-cell">$<?php echo htmlspecialchars($participant['amount']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <div class="total-section">
                <h3>Total Amount Due</h3>
                <div class="total-amount">$<?php echo htmlspecialchars($invoiceData['total_amount']); ?></div>
                <p>Payment is required to complete your registration</p>
            </div>
            
            <div class="payment-section">
                <h3>Complete Your Payment</h3>
                <p>Click the button below to complete your payment securely:</p>
                <a href="<?php echo htmlspecialchars($invoiceData['payment_link']); ?>" class="payment-button">
                    Pay Now - $<?php echo htmlspecialchars($invoiceData['total_amount']); ?>
                </a>
                <p style="font-size: 12px; color: #666; margin-top: 15px;">
                    Or copy and paste this link: <?php echo htmlspecialchars($invoiceData['payment_link']); ?>
                </p>
            </div>
            
            <div class="payment-instructions">
                <h3>Payment Instructions</h3>
                <div class="instructions-content">
                    <h4>To Pay Later:</h4>
                    <ol>
                        <li><strong>Visit the Registration Portal:</strong> Go to <a href="<?php echo htmlspecialchars($invoiceData['registration_lookup_url']); ?>" style="color: #063218; font-weight: bold;"><?php echo htmlspecialchars($invoiceData['registration_lookup_url']); ?></a></li>
                        <li><strong>Enter Your Details:</strong> Provide your email address and registration phone number</li>
                        <li><strong>Retrieve Registration:</strong> Click "Lookup Registration" to access your registration details</li>
                        <li><strong>Complete Payment:</strong> Click "Pay Now" to proceed with payment for your registered package</li>
                    </ol>
                    
                    <div class="payment-info-box">
                        <h4>Payment Information:</h4>
                        <ul>
                            <li><strong>Registration ID:</strong> #<?php echo htmlspecialchars($invoiceData['registration_id']); ?></li>
                            <li><strong>Package:</strong> <?php echo htmlspecialchars($invoiceData['package_name']); ?></li>
                            <li><strong>Amount Due:</strong> $<?php echo htmlspecialchars($invoiceData['total_amount']); ?></li>
                               <li><strong>Payment Methods:</strong> Credit Card, Debit Card, Bank Transfer (contact support for details)</li>
                        </ul>
                    </div>
                    
                           <div class="contact-info">
                               <h4>Bank Transfer Payment Option</h4>
                               <p>If you prefer to pay by bank transfer instead of online payment, please contact our support team for banking details:</p>
                               <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($invoiceData['support_email']); ?>" style="color: #063218;"><?php echo htmlspecialchars($invoiceData['support_email']); ?></a></p>
                               <p><strong>Include in your email:</strong> Your Registration ID (#<?php echo htmlspecialchars($invoiceData['registration_id']); ?>) and preferred payment method</p>
                           </div>
                           
                           <div class="contact-info" style="background: #f8f9fa; margin-top: 15px;">
                               <h4>Need Help?</h4>
                               <p>If you have any questions or need assistance with your payment, please contact us:</p>
                               <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($invoiceData['support_email']); ?>" style="color: #063218;"><?php echo htmlspecialchars($invoiceData['support_email']); ?></a></p>
                               <p><strong>Phone:</strong> +251-11-552-4177 (Monday - Friday, 9:00 AM - 5:00 PM EAT)</p>
                           </div>
                </div>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
                <h4 style="color: #856404; margin: 0 0 10px 0;">Important Notes:</h4>
                <ul style="color: #856404; margin: 0; padding-left: 20px;">
                    <li>Your registration is not complete until payment is received</li>
                    <li>Payment must be completed before the conference date</li>
                    <li>Confirmation will be sent to your email upon successful payment</li>
                    <li>For questions, contact us at <a href="mailto:<?php echo htmlspecialchars($invoiceData['support_email']); ?>" style="color: #856404;"><?php echo htmlspecialchars($invoiceData['support_email']); ?></a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p><strong><?php echo htmlspecialchars($invoiceData['conference_short_name']); ?> Team</strong></p>
            <p>Moving towards self-reliance to achieve universal health coverage and health security in Africa</p>
            <p style="font-size: 12px; color: #999; margin-top: 20px;">
                This is an automated invoice. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
