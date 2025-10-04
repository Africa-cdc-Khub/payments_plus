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
                        <a href="registration_lookup.php" class="btn-action btn-outline">
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
            const receiptContent = document.getElementById('receipt-content');
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Payment Receipt - <?php echo CONFERENCE_SHORT_NAME; ?></title>
                        <style>
                            body { 
                                font-family: 'Inter', Arial, sans-serif; 
                                margin: 20px; 
                                background: #f8f9fa;
                            }
                            .header { 
                                text-align: center; 
                                margin-bottom: 30px; 
                                background: linear-gradient(135deg, #1a5632 0%, #2d7a4a 100%);
                                color: white;
                                padding: 30px;
                                border-radius: 10px;
                            }
                            .receipt-details { 
                                background: white;
                                padding: 30px;
                                border-radius: 10px;
                                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                                margin: 20px 0; 
                            }
                            .amount { 
                                font-size: 1.2em; 
                                font-weight: bold; 
                                color: #1a5632; 
                            }
                            h4 { color: #1a5632; }
                            h6 { color: #1a5632; font-weight: 600; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h2><?php echo CONFERENCE_NAME; ?></h2>
                            <h3><?php echo CONFERENCE_SHORT_NAME; ?></h3>
                            <p><?php echo CONFERENCE_DATES; ?> • <?php echo CONFERENCE_LOCATION; ?></p>
                        </div>
                        <div class="receipt-details">
                            ${receiptContent.innerHTML}
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
    </script>
</body>
</html>
