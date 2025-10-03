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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
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
        .receipt-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-top: 2rem;
        }
        .action-buttons {
            margin-top: 2rem;
        }
        .btn-action {
            margin: 0.5rem;
            min-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <img src="images/logo.png" alt="<?php echo CONFERENCE_SHORT_NAME; ?>" class="mb-3" style="height: 80px;">
                <h1 class="h3 mb-2"><?php echo CONFERENCE_NAME; ?></h1>
                <h2 class="h4 text-muted mb-0"><?php echo CONFERENCE_SHORT_NAME; ?></h2>
                <p class="text-muted"><?php echo CONFERENCE_DATES; ?> • <?php echo CONFERENCE_LOCATION; ?></p>
            </div>
        </div>

        <!-- Registration Status Card -->
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Registration Status</h4>
                    </div>
                    <div class="col-auto">
                        <span class="status-badge status-<?php echo $paymentStatus; ?>">
                            <i class="fas fa-<?php echo $isPaid ? 'check-circle' : 'clock'; ?> me-1"></i>
                            <?php echo ucfirst($paymentStatus); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Registration Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="text-primary">Registration Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Registration ID:</strong></td>
                                <td>#<?php echo $registration['id']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Package:</strong></td>
                                <td><?php echo htmlspecialchars($package['name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td><?php echo ucfirst($registration['registration_type']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Amount:</strong></td>
                                <td><strong class="text-success"><?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong>Registered:</strong></td>
                                <td><?php echo date('F j, Y \a\t g:i A', strtotime($registration['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="text-primary">Participant Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td><?php echo htmlspecialchars($user['title'] . ' ' . $user['first_name'] . ' ' . $user['last_name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nationality:</strong></td>
                                <td><?php echo htmlspecialchars($user['nationality']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Organization:</strong></td>
                                <td><?php echo htmlspecialchars($user['organization']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Group Participants -->
                <?php if (!empty($participants)): ?>
                <div class="mb-4">
                    <h5 class="text-primary">Group Participants</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
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
                <div class="alert alert-<?php echo $isPaid ? 'success' : 'warning'; ?>">
                    <h5 class="alert-heading">
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
                <div class="action-buttons text-center">
                    <?php if ($isPaid): ?>
                        <!-- Paid Registration Actions -->
                        <button onclick="printReceipt()" class="btn btn-success btn-action">
                            <i class="fas fa-print me-2"></i>Print Receipt
                        </button>
                        <a href="registration_lookup.php" class="btn btn-outline-primary btn-action">
                            <i class="fas fa-list me-2"></i>View All Registrations
                        </a>
                    <?php else: ?>
                        <!-- Pending Payment Actions -->
                        <a href="<?php echo rtrim(APP_URL, '/') . '/checkout_payment.php?registration_id=' . $registrationId . '&token=' . $registration['payment_token']; ?>" 
                           class="btn btn-success btn-action">
                            <i class="fas fa-credit-card me-2"></i>Pay Now
                        </a>
                        <button onclick="sendPaymentLink(<?php echo $registrationId; ?>)" class="btn btn-outline-primary btn-action">
                            <i class="fas fa-envelope me-2"></i>Send Payment Link
                        </button>
                        <a href="registration_lookup.php" class="btn btn-outline-secondary btn-action">
                            <i class="fas fa-list me-2"></i>View All Registrations
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Receipt Section (for paid registrations) -->
        <?php if ($isPaid): ?>
        <div class="receipt-section" id="receipt-content">
            <h4 class="text-center mb-4">Payment Receipt</h4>
            <div class="row">
                <div class="col-md-6">
                    <h6>Conference Details</h6>
                    <p><strong><?php echo CONFERENCE_NAME; ?></strong><br>
                    <?php echo CONFERENCE_DATES; ?><br>
                    <?php echo CONFERENCE_LOCATION; ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Payment Details</h6>
                    <p><strong>Registration ID:</strong> #<?php echo $registration['id']; ?><br>
                    <strong>Amount Paid:</strong> <?php echo CURRENCY_SYMBOL . number_format($registration['total_amount'], 2); ?><br>
                    <strong>Payment Date:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($registration['payment_completed_at'] ?? $registration['created_at'])); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

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
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .header { text-align: center; margin-bottom: 30px; }
                            .receipt-details { margin: 20px 0; }
                            .amount { font-size: 1.2em; font-weight: bold; color: #28a745; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h2><?php echo CONFERENCE_NAME; ?></h2>
                            <h3><?php echo CONFERENCE_SHORT_NAME; ?></h3>
                            <p><?php echo CONFERENCE_DATES; ?> • <?php echo CONFERENCE_LOCATION; ?></p>
                        </div>
                        ${receiptContent.innerHTML}
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
                    button.classList.remove('btn-outline-primary');
                    button.classList.add('btn-success');
                    
                    // Show success alert
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                    alertDiv.innerHTML = `
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
