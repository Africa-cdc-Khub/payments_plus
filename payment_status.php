<?php
require_once 'bootstrap.php';
require_once 'functions.php';

// Get registration ID from URL
$registrationId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$email = $_GET['email'] ?? '';

if (!$registrationId || !$email) {
    header('Location: index.php');
    exit;
}

// Get registration details
$registration = getRegistrationById($registrationId);
if (!$registration || $registration['user_email'] !== $email) {
    header('Location: index.php');
    exit;
}

// Get user details
$user = getUserByEmail($email);
if (!$user) {
    header('Location: index.php');
    exit;
}

// Get package details
$package = getPackageById($registration['package_id']);

// Get participants if group registration
$participants = [];
if ($registration['registration_type'] === 'group') {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM registration_participants WHERE registration_id = ?");
    $stmt->execute([$registrationId]);
    $participants = $stmt->fetchAll();
}

$paymentStatus = $registration['payment_status'] ?? 'pending';
$isPaid = $paymentStatus === 'completed';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - <?php echo CONFERENCE_SHORT_NAME; ?></title>
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
        .status-container {
            width: 100%;
            margin: 0 auto;
            padding: var(--spacing-6);
            background: var(--light-gray);
            min-height: 100vh;
        }
        .status-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid var(--light-gray);
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
        }
        .status-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
            padding: var(--spacing-6);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-header h3 {
            color: var(--white);
            font-weight: 600;
            margin: 0;
            font-size: var(--font-size-xl);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-content {
            padding: var(--spacing-8);
        }
        .info-section {
            background: var(--light-gray);
            border-radius: var(--radius-md);
            padding: var(--spacing-6);
            margin-bottom: var(--spacing-6);
        }
        .info-section h5 {
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: var(--spacing-4);
            font-size: var(--font-size-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        .info-table {
            background: var(--white);
            border-radius: var(--radius-sm);
            overflow: hidden;
        }
        .info-table .table {
            margin: 0;
        }
        .info-table .table td {
            padding: var(--spacing-3) var(--spacing-4);
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }
        .info-table .table tr:last-child td {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: var(--dark-gray);
            width: 40%;
        }
        .info-value {
            color: var(--dark-gray);
            font-weight: 500;
        }
        .amount-highlight {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--primary-green);
        }
        .status-alert {
            border-radius: var(--radius-md);
            padding: var(--spacing-5);
            margin-bottom: var(--spacing-6);
            border: none;
        }
        .status-alert h5 {
            font-weight: 600;
            margin-bottom: var(--spacing-2);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
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
            min-width: 180px;
            justify-content: center;
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
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-green);
            color: var(--primary-green);
            box-shadow: none;
        }
        .btn-outline:hover {
            background: var(--primary-green);
            color: var(--white);
        }
        .receipt-section {
            background: var(--light-gray);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            margin-top: var(--spacing-6);
        }
        .receipt-section h4 {
            color: var(--primary-green);
            font-weight: 600;
            text-align: center;
            margin-bottom: var(--spacing-6);
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
        .participants-table {
            background: var(--white);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .participants-table .table {
            margin: 0;
        }
        .participants-table th {
            background: var(--light-green);
            color: var(--primary-green);
            font-weight: 600;
            border: none;
            padding: var(--spacing-4);
        }
        .participants-table td {
            padding: var(--spacing-4);
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }
        .participants-table tr:last-child td {
            border-bottom: none;
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
    </div>

    <div class="status-container">
        <a href="registration_lookup.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Registrations
        </a>
        
        <div class="status-card">

            <div class="status-header">
                <h3><i class="fas fa-receipt me-2"></i>Registration Status</h3>
                <span class="status-badge status-<?php echo $paymentStatus; ?>">
                    <i class="fas fa-<?php echo $isPaid ? 'check-circle' : 'clock'; ?> me-1"></i>
                    <?php echo ucfirst($paymentStatus); ?>
                </span>
            </div>
            
            <div class="status-content">
                <!-- Registration Details -->
                <div class="info-section">
                    <h5><i class="fas fa-info-circle me-2"></i>Registration Information</h5>
                    <div class="info-table">
                        <table class="table">
                            <tr>
                                <td class="info-label">Registration ID:</td>
                                <td class="info-value">#<?php echo $registration['id']; ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Package:</td>
                                <td class="info-value"><?php echo htmlspecialchars($package['name']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Type:</td>
                                <td class="info-value"><?php echo ucfirst($registration['registration_type']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Amount:</td>
                                <td class="info-value amount-highlight"><?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Registered:</td>
                                <td class="info-value"><?php echo date('F j, Y \a\t g:i A', strtotime($registration['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="info-section">
                    <h5><i class="fas fa-user me-2"></i>Participant Information</h5>
                    <div class="info-table">
                        <table class="table">
                            <tr>
                                <td class="info-label">Name:</td>
                                <td class="info-value"><?php echo htmlspecialchars($user['title'] . ' ' . $user['first_name'] . ' ' . $user['last_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Email:</td>
                                <td class="info-value"><?php echo htmlspecialchars($user['email']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Phone:</td>
                                <td class="info-value"><?php echo htmlspecialchars($user['phone']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Nationality:</td>
                                <td class="info-value"><?php echo htmlspecialchars($user['nationality']); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Organization:</td>
                                <td class="info-value"><?php echo htmlspecialchars($user['organization']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Group Participants -->
                <?php if (!empty($participants)): ?>
                <div class="info-section">
                    <h5><i class="fas fa-users me-2"></i>Group Participants</h5>
                    <div class="participants-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Nationality</th>
                                    <th>Organization</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participant['title'] . ' ' . $participant['first_name'] . ' ' . $participant['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['nationality']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['organization']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payment Status Section -->
                <div class="alert status-alert alert-<?php echo $isPaid ? 'success' : 'warning'; ?>">
                    <h5>
                        <i class="fas fa-<?php echo $isPaid ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo $isPaid ? 'Payment Completed' : 'Payment Pending'; ?>
                    </h5>
                    <?php if ($isPaid): ?>
                        <p class="mb-0">Your registration has been confirmed and payment has been processed successfully. You can now print your receipt below.</p>
                    <?php else: ?>
                        <p class="mb-0">Your registration is pending payment. Please complete your payment to confirm your attendance at the conference.</p>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <?php if ($isPaid): ?>
                        <!-- Paid Registration Actions -->
                        <button onclick="printReceipt()" class="btn-action">
                            <i class="fas fa-print me-2"></i>Print Receipt
                        </button>
                        <button onclick="sendReceiptEmail(<?php echo $registrationId; ?>)" class="btn-action btn-outline">
                            <i class="fas fa-envelope me-2"></i>Send Receipt Email
                        </button>
                        <a href="registration_lookup.php" class="btn-action btn-secondary">
                            <i class="fas fa-list me-2"></i>View All Registrations
                        </a>
                    <?php else: ?>
                        <!-- Pending Payment Actions -->
                        <a href="<?php echo rtrim(APP_URL, '/') . '/checkout_payment.php?registration_id=' . $registrationId . '&token=' . $registration['payment_token']; ?>" 
                           class="btn-action">
                            <i class="fas fa-credit-card me-2"></i>Pay Now
                        </a>
                        <button onclick="sendPaymentLink(<?php echo $registrationId; ?>)" class="btn-action btn-outline">
                            <i class="fas fa-envelope me-2"></i>Send Payment Link
                        </button>
                        <a href="registration_lookup.php" class="btn-action btn-secondary">
                            <i class="fas fa-list me-2"></i>View All Registrations
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Receipt Section (for paid registrations) -->
        <?php if ($isPaid): ?>
        <div class="receipt-section" id="receipt-content">
            <h4>Payment Receipt</h4>
            <div class="row">
                <div class="col-md-6">
                    <h6 style="color: var(--primary-green); font-weight: 600; margin-bottom: var(--spacing-3);">
                        <i class="fas fa-calendar-alt me-2"></i>Conference Details
                    </h6>
                    <p style="margin-bottom: 0;"><strong><?php echo CONFERENCE_NAME; ?></strong><br>
                    <?php echo CONFERENCE_DATES; ?><br>
                    <?php echo CONFERENCE_LOCATION; ?></p>
                </div>
                <div class="col-md-6">
                    <h6 style="color: var(--primary-green); font-weight: 600; margin-bottom: var(--spacing-3);">
                        <i class="fas fa-receipt me-2"></i>Payment Details
                    </h6>
                    <p style="margin-bottom: 0;"><strong>Registration ID:</strong> #<?php echo $registration['id']; ?><br>
                    <strong>Amount Paid:</strong> <span class="amount-highlight"><?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?></span><br>
                    <strong>Payment Date:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($registration['payment_completed_at'] ?? $registration['created_at'])); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printReceipt() {
            // Get registration data for QR code generation
            const registrationData = {
                id: <?php echo $registrationId; ?>,
                name: '<?php echo addslashes($user['first_name'] . ' ' . $user['last_name']); ?>',
                email: '<?php echo addslashes($user['email']); ?>',
                phone: '<?php echo addslashes($user['phone']); ?>',
                package: '<?php echo addslashes($package['name']); ?>',
                organization: '<?php echo addslashes($user['organization']); ?>',
                institution: '<?php echo addslashes($user['institution'] ?? ''); ?>',
                nationality: '<?php echo addslashes($user['nationality']); ?>',
                amount: '<?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?>',
                paymentDate: '<?php echo date('Y-m-d H:i:s'); ?>'
            };
            
            // Generate QR code data
            const qrData = `CPHIA2025|${registrationData.name}|${registrationData.id}|${registrationData.package}|${registrationData.organization}|${registrationData.institution}|${registrationData.phone}|${registrationData.nationality}|${registrationData.paymentDate}`;
            
            // Generate QR code using PHP function (pre-generated)
            const qrCodeUrl = '<?php 
                $qrData = "CPHIA2025|" . addslashes($user["first_name"] . " " . $user["last_name"]) . "|" . $registrationId . "|" . addslashes($package["name"]) . "|" . addslashes($user["organization"]) . "|" . addslashes($user["institution"] ?? "") . "|" . addslashes($user["phone"]) . "|" . addslashes($user["nationality"]) . "|" . date("Y-m-d H:i:s");
                echo generateQRCode($qrData);
            ?>';
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Payment Receipt - <?php echo CONFERENCE_SHORT_NAME; ?></title>
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
                        
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        
                        body {
                            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                            line-height: 1.6;
                            color: #1a1a1a;
                            background: #ffffff;
                            font-size: 14px;
                        }
                        
                        .receipt-container {
                            max-width: 800px;
                            margin: 0 auto;
                            background: #ffffff;
                            box-shadow: 0 0 20px rgba(0,0,0,0.1);
                            border-radius: 12px;
                            overflow: hidden;
                        }
                        
                        .receipt-header {
                            background: linear-gradient(135deg, #1a5632 0%, #2d7d32 100%);
                            color: white;
                            padding: 40px 30px;
                            text-align: center;
                            position: relative;
                        }
                        
                        .logo-section {
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            gap: 30px;
                            margin-bottom: 25px;
                        }
                        
                        .logo {
                            height: 60px;
                            width: auto;
                            filter: brightness(0) invert(1);
                        }
                        
                        .cphia-logo {
                            height: 70px;
                            width: auto;
                        }
                        
                        .header-content h1 {
                            font-size: 32px;
                            font-weight: 700;
                            margin-bottom: 8px;
                            letter-spacing: -0.5px;
                        }
                        
                        .header-content h2 {
                            font-size: 20px;
                            font-weight: 500;
                            margin-bottom: 12px;
                            opacity: 0.9;
                        }
                        
                        .header-content p {
                            font-size: 16px;
                            opacity: 0.8;
                            font-weight: 400;
                        }
                        
                        .receipt-body {
                            padding: 40px 30px;
                        }
                        
                        .receipt-title {
                            text-align: center;
                            margin-bottom: 40px;
                            padding-bottom: 20px;
                            border-bottom: 3px solid #1a5632;
                        }
                        
                        .receipt-title h3 {
                            font-size: 28px;
                            color: #1a5632;
                            font-weight: 700;
                            margin-bottom: 8px;
                        }
                        
                        .receipt-title p {
                            font-size: 16px;
                            color: #666;
                            font-weight: 500;
                        }
                        
                        .receipt-grid {
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: 30px;
                            margin-bottom: 40px;
                        }
                        
                        .receipt-section {
                            background: #f8f9fa;
                            padding: 25px;
                            border-radius: 8px;
                            border-left: 4px solid #1a5632;
                        }
                        
                        .receipt-section h4 {
                            color: #1a5632;
                            font-size: 18px;
                            font-weight: 600;
                            margin-bottom: 20px;
                            display: flex;
                            align-items: center;
                            gap: 8px;
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
                            font-size: 14px;
                        }
                        
                        .detail-value {
                            color: #212529;
                            font-weight: 500;
                            text-align: right;
                            font-size: 14px;
                        }
                        
                        .qr-section {
                            background: #f8f9fa;
                            padding: 30px;
                            border-radius: 8px;
                            text-align: center;
                            margin: 30px 0;
                            border: 2px solid #1a5632;
                        }
                        
                        .qr-section h4 {
                            color: #1a5632;
                            font-size: 20px;
                            font-weight: 600;
                            margin-bottom: 20px;
                        }
                        
                        .qr-code {
                            max-width: 200px;
                            height: auto;
                            margin: 0 auto 20px;
                            border: 3px solid #1a5632;
                            border-radius: 8px;
                            padding: 10px;
                            background: white;
                        }
                        
                        .qr-info {
                            color: #666;
                            font-size: 14px;
                            line-height: 1.5;
                        }
                        
                        .amount-section {
                            background: linear-gradient(135deg, #1a5632 0%, #2d7d32 100%);
                            color: white;
                            padding: 30px;
                            border-radius: 8px;
                            text-align: center;
                            margin: 30px 0;
                        }
                        
                        .amount-label {
                            font-size: 18px;
                            margin-bottom: 10px;
                            opacity: 0.9;
                            font-weight: 500;
                        }
                        
                        .amount-value {
                            font-size: 36px;
                            font-weight: 700;
                            margin: 0;
                            letter-spacing: -1px;
                        }
                        
                        .footer {
                            background: #f8f9fa;
                            padding: 30px;
                            text-align: center;
                            border-top: 1px solid #e9ecef;
                        }
                        
                        .footer h4 {
                            color: #1a5632;
                            font-size: 18px;
                            font-weight: 600;
                            margin-bottom: 15px;
                        }
                        
                        .footer p {
                            color: #666;
                            font-size: 14px;
                            margin: 8px 0;
                            line-height: 1.5;
                        }
                        
                        .contact-info {
                            display: flex;
                            justify-content: center;
                            gap: 30px;
                            margin-top: 20px;
                            flex-wrap: wrap;
                        }
                        
                        .contact-item {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            color: #666;
                            font-size: 14px;
                        }
                        
                        .receipt-id {
                            background: #1a5632;
                            color: white;
                            padding: 8px 16px;
                            border-radius: 20px;
                            font-weight: 600;
                            font-size: 16px;
                            display: inline-block;
                            margin-bottom: 20px;
                        }
                        
                        @media print {
                            body {
                                background: white;
                            }
                            .receipt-container {
                                box-shadow: none;
                                border-radius: 0;
                            }
                        }
                        
                        @media (max-width: 768px) {
                            .receipt-grid {
                                grid-template-columns: 1fr;
                            }
                            .logo-section {
                                flex-direction: column;
                                gap: 15px;
                            }
                            .contact-info {
                                flex-direction: column;
                                gap: 10px;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="receipt-container">
                        <div class="receipt-header">
                            <div class="logo-section">
                                <img src="https://africacdc.org/wp-content/uploads/2020/02/AfricaCDC_Logo.png" alt="Africa CDC" class="logo">
                                <img src="https://cphia2025.com/wp-content/uploads/2025/09/CPHIA-2025-logo_reverse.png" alt="CPHIA 2025" class="cphia-logo">
                            </div>
                            <div class="header-content">
                                <h1><?php echo CONFERENCE_NAME; ?></h1>
                                <h2><?php echo CONFERENCE_SHORT_NAME; ?></h2>
                                <p><?php echo CONFERENCE_DATES; ?> • <?php echo CONFERENCE_LOCATION; ?></p>
                            </div>
                        </div>
                        
                        <div class="receipt-body">
                            <div class="receipt-title">
                                <div class="receipt-id">Receipt #${registrationData.id}</div>
                                <h3>Payment Confirmation</h3>
                                <p>Registration Receipt</p>
                            </div>
                            
                            <div class="receipt-grid">
                                <div class="receipt-section">
                                    <h4><i class="fas fa-user"></i> Participant Information</h4>
                                    <div class="detail-row">
                                        <span class="detail-label">Name:</span>
                                        <span class="detail-value">${registrationData.name}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Email:</span>
                                        <span class="detail-value">${registrationData.email}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Phone:</span>
                                        <span class="detail-value">${registrationData.phone}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Nationality:</span>
                                        <span class="detail-value">${registrationData.nationality}</span>
                                    </div>
                                </div>
                                
                                <div class="receipt-section">
                                    <h4><i class="fas fa-ticket-alt"></i> Registration Details</h4>
                                    <div class="detail-row">
                                        <span class="detail-label">Package:</span>
                                        <span class="detail-value">${registrationData.package}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Organization:</span>
                                        <span class="detail-value">${registrationData.organization}</span>
                                    </div>
                                    ${registrationData.institution ? `
                                    <div class="detail-row">
                                        <span class="detail-label">Institution:</span>
                                        <span class="detail-value">${registrationData.institution}</span>
                                    </div>
                                    ` : ''}
                                    <div class="detail-row">
                                        <span class="detail-label">Payment Date:</span>
                                        <span class="detail-value">${new Date().toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'long', 
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="qr-section">
                                <h4><i class="fas fa-qrcode"></i> Registration QR Code</h4>
                                <img src="${qrCodeUrl}" alt="Registration QR Code" class="qr-code">
                                <p class="qr-info">
                                    This QR code contains your registration details and can be used for verification at the conference.
                                    <br><strong>Scan this code at the conference for quick check-in.</strong>
                                </p>
                            </div>
                            
                            <div class="amount-section">
                                <div class="amount-label">Total Amount Paid</div>
                                <div class="amount-value">${registrationData.amount}</div>
                            </div>
                            
                            <div class="footer">
                                <h4>Important Information</h4>
                                <p><strong>Thank you for registering for <?php echo CONFERENCE_NAME; ?>!</strong></p>
                                <p>This receipt serves as confirmation of your registration and payment.</p>
                                <p>Please keep this receipt for your records and bring it to the conference.</p>
                                <p>Your QR code will be used for verification and check-in at the conference venue.</p>
                                
                                <div class="contact-info">
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo MAIL_FROM_ADDRESS; ?></span>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-globe"></i>
                                        <span>https://cphia2025.com</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        function sendPaymentLink(registrationId) {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            button.disabled = true;
            
            fetch('send_payment_link.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    registration_id: registrationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.innerHTML = '<i class="fas fa-check me-2"></i>Payment Link Sent!';
                    button.classList.remove('btn-outline');
                    button.classList.add('btn-action');
                    
                    // Show success alert
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                    alertDiv.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        Payment link sent successfully! Check your email.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    button.parentNode.appendChild(alertDiv);
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    alert('Failed to send payment link. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalText;
                button.disabled = false;
                alert('An error occurred. Please try again.');
            });
        }

        function sendReceiptEmail(registrationId) {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            button.disabled = true;
            
            fetch('send_receipt_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    registration_id: registrationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.innerHTML = '<i class="fas fa-check me-2"></i>Receipt Sent!';
                    button.classList.remove('btn-outline');
                    button.classList.add('btn-action');
                    
                    // Show success alert
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success mt-3';
                    alertDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Receipt email sent successfully!';
                    button.parentNode.appendChild(alertDiv);
                    
                    // Remove alert after 5 seconds
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                } else {
                    throw new Error(data.message || 'Failed to send receipt email');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalText;
                button.disabled = false;
                alert('Failed to send receipt email. Please try again.');
            });
        }
    </script>
</body>
</html>
