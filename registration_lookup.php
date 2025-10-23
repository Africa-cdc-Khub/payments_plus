<?php
require_once 'bootstrap.php';
require_once 'functions.php';

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

$registrations = [];
$searchPerformed = false;
$error = '';

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_registrations'])) {
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    if (empty($email)) {
        $error = "Please provide your email address to search for your registrations.";
    } elseif (!validateEmail($email)) {
        $error = "Please enter a valid email address.";
    } elseif (!empty($phone) && !validatePhoneNumber($phone)) {
        $error = "Please enter a valid phone number or leave it blank.";
    } elseif (isRecaptchaEnabled() && (empty($_POST['g-recaptcha-response']) || !validateRecaptcha($_POST['g-recaptcha-response'], RECAPTCHA_SECRET_KEY))) {
        $error = "Please complete the reCAPTCHA verification.";
        logSecurityEvent('recaptcha_failed_lookup', 'reCAPTCHA verification failed for registration lookup');
    } else {
        // Use email-only search (improved)
        $registrations = getRegistrationHistoryByEmail($email);
        $searchPerformed = true;
        
        if (empty($registrations)) {
            $error = "No registrations found for the provided email address.";
        }
    }
}

// Handle payment action
if (isset($_GET['action']) && $_GET['action'] === 'pay' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $registrationId = (int)$_GET['id'];
    $registration = getRegistrationById($registrationId);
    
    if ($registration && $registration['payment_status'] === 'pending') {
        // Generate payment token and redirect to payment page
        $paymentToken = generatePaymentToken($registrationId);
        $paymentUrl = rtrim(APP_URL, '/') . "/payment/payment_confirm.php?registration_id=" . $registrationId . "&token=" . $paymentToken;
        header("Location: " . $paymentUrl);
        exit;
    } else {
        $error = "Registration not found or payment not required.";
    }
}

// Handle registration details request
$registrationDetails = null;
$participants = [];
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $registrationId = (int)$_GET['view'];
    $registrationDetails = getRegistrationDetails($registrationId);
    if ($registrationDetails) {
        $participants = getRegistrationParticipants($registrationId);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Lookup - <?php echo CONFERENCE_SHORT_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <img src="images/logo.png" alt="Africa CDC Logo" class="logo-img mb-3" style="filter: brightness(0) invert(1);">
            <div class="header-text">
                <h1>4th International Conference on Public Health in Africa</h1>
                <h2><?php echo CONFERENCE_SHORT_NAME; ?></h2>
                <div class="conference-dates"><?php echo CONFERENCE_DATES; ?></div>
                <div class="conference-location"><?php echo CONFERENCE_LOCATION; ?></div>
            </div>
        </div>
    </div>

    <div class="container mt-5"   style="padding: 0 5%;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Page Title -->
                <div class="text-center mb-5">
                    <h3 class="mb-3">Registration Lookup</h3>
                    <p class="lead">View your previous registrations for <?php echo CONFERENCE_DATES; ?></p>
                    <div class="alert alert-info d-inline-block">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Quick Search:</strong> Enter your email address to find all your registrations. Phone number is optional.
                    </div>
                </div>

                <!-- Search Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search Your Registrations</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-8">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required
                                       placeholder="Enter your email address">
                                <div class="form-text">We'll search for all registrations associated with this email address.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="phone" class="form-label">Phone Number <small class="text-muted">(Optional)</small></label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                       placeholder="Enter phone number (optional)">
                                <div class="form-text">Phone number is optional for searching.</div>
                            </div>
                            <!-- reCAPTCHA -->
                            <?php if (isRecaptchaEnabled()): ?>
                            <div class="col-12">
                                <div class="text-center">
                                    <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY); ?>"></div>
                                    <small class="text-muted mt-2 d-block">
                                        Please complete the reCAPTCHA verification to search for your registrations.
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-12">
                                <button type="submit" name="search_registrations" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Search Registrations
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Registration
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <!-- Registration List -->
                <?php if ($searchPerformed && !empty($registrations)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Your Registrations</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Package</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registrations as $registration): ?>
                                    <tr>
                                        <td class="fw-bold">#<?php echo $registration['id']; ?></td>
                                        <td><?php echo htmlspecialchars($registration['package_name']); ?></td>
                                        <td><?php echo ucfirst($registration['registration_type']); ?></td>
                                        <td class="fw-bold"><?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?></td>
                                        <td>
                                            <?php 
                                            $dbStatus = strtolower($registration['status'] ?? '');
                                            $paymentStatus = $registration['payment_status'] ?? '';
                                            $amount = $registration['total_amount'] ?? 0;
                                            
                                            if ($paymentStatus === 'completed'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php elseif ($amount == 0 && $dbStatus === 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif ($amount == 0 && $dbStatus === 'rejected'): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php elseif ($amount == 0): ?>
                                                <span class="badge bg-info">Awaiting Approval</span>
                                            <?php elseif ($dbStatus === 'pending payment'): ?>
                                                <span class="badge bg-warning">Pending Payment</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary"><?php echo ucfirst($registration['status']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($registration['created_at'])); ?></td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <a href="?view=<?php echo $registration['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                                <?php 
                                                $amount = $registration['total_amount'] ?? 0;
                                                $paymentStatus = $registration['payment_status'] ?? '';
                                                $dbStatus = strtolower($registration['status'] ?? '');
                                                
                                                if ($amount == 0 && $dbStatus === 'approved'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php elseif ($amount == 0 && $dbStatus === 'rejected'): ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php elseif ($amount == 0): ?>
                                                    <span class="badge bg-info">Awaiting Approval</span>
                                                <?php elseif ($paymentStatus === 'voided'): ?>
                                                    <span class="badge bg-danger">Cancelled</span>
                                                <?php elseif ($paymentStatus !== 'completed'): ?>
                                                    <a href="?action=pay&id=<?php echo $registration['id']; ?>" class="btn btn-success btn-sm">
                                                        <i class="fas fa-credit-card me-1"></i>Pay
                                                    </a>
                                                    <button onclick="requestInvoice(<?php echo $registration['id']; ?>)" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-file-invoice me-1"></i>Invoice
                                                    </button>
                                                    <button onclick="cancelRegistration(<?php echo $registration['id']; ?>, '<?php echo htmlspecialchars($registration['email']); ?>')" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($amount > 0): ?>
                                                    <a href="invoice.php?id=<?php echo $registration['id']; ?>&email=<?php echo urlencode($registration['email']); ?>" class="btn btn-outline-info btn-sm" target="_blank">
                                                        <i class="fas fa-file-invoice me-1"></i>View Invoice
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($paymentStatus === 'completed'): ?>
                                                    <a href="receipt.php?id=<?php echo $registration['id']; ?>&email=<?php echo urlencode($registration['email']); ?>" class="btn btn-outline-success btn-sm" target="_blank">
                                                        <i class="fas fa-receipt me-1"></i>View Receipt
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Bank Transfer Notice -->
                        <div class="alert alert-info mt-3">
                            <h6 class="alert-heading"><i class="fas fa-university me-2"></i>Bank Transfer Payment Option</h6>
                            <p class="mb-2">If you prefer to pay by bank transfer instead of online payment, please contact our support team:</p>
                            <p class="mb-0">
                                <strong>Email:</strong> <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" class="text-decoration-none"><?php echo SUPPORT_EMAIL; ?></a><br>
                                <strong>Include:</strong> Your Registration ID and preferred payment method in your email.
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Registration Details -->
                <?php if ($registrationDetails): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>Registration Details - #<?php echo $registrationDetails['id']; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th colspan="2" class="text-center">
                                            <h6 class="mb-0">Registration Details - #<?php echo $registrationDetails['id']; ?></h6>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" style="width: 30%;">Registration ID</td>
                                        <td>#<?php echo $registrationDetails['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Package</td>
                                        <td><?php echo htmlspecialchars($registrationDetails['package_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Registration Type</td>
                                        <td><?php echo ucfirst($registrationDetails['registration_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Status</td>
                                        <td>
                                            <span class="badge bg-<?php echo $registrationDetails['status'] == 'paid' ? 'success' : ($registrationDetails['status'] == 'pending' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($registrationDetails['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Amount</td>
                                        <td><?php echo formatCurrency($registrationDetails['total_amount'], $registrationDetails['currency']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Created Date</td>
                                        <td><?php echo date('M j, Y \a\t g:i A', strtotime($registrationDetails['created_at'])); ?></td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="fw-bold" colspan="2" style="text-align: center; padding: 15px;">
                                            <strong>Contact Information</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Full Name</td>
                                        <td><?php echo htmlspecialchars($registrationDetails['first_name'] . ' ' . $registrationDetails['last_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email Address</td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($registrationDetails['email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($registrationDetails['email']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Phone Number</td>
                                        <td>
                                            <a href="tel:<?php echo htmlspecialchars($registrationDetails['phone']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($registrationDetails['phone']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Nationality</td>
                                        <td><?php echo htmlspecialchars($registrationDetails['nationality']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Organization</td>
                                        <td><?php echo htmlspecialchars($registrationDetails['organization']); ?></td>
                                    </tr>
                                    <?php if (!empty($registrationDetails['organization_address'])): ?>
                                    <tr>
                                        <td class="fw-bold">Organization Address</td>
                                        <td><?php echo htmlspecialchars($registrationDetails['organization_address']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($registrationDetails['position'])): ?>
                                    <tr>
                                        <td class="fw-bold">Position</td>
                                        <td><?php echo htmlspecialchars($registrationDetails['position']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($participants)): ?>
                        <div class="mt-4">
                            <h6 class="fw-bold">Group Participants</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
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

                        <?php if (!empty($registrationDetails['exhibition_description'])): ?>
                        <div class="mt-4">
                            <h6 class="fw-bold">Exhibition Description</h6>
                            <div class="bg-light p-3 rounded">
                                <?php echo nl2br(htmlspecialchars($registrationDetails['exhibition_description'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Payment Section -->
                        <div class="mt-4">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th colspan="2" class="text-center">
                                                <h6 class="mb-0">Payment Information</h6>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" style="width: 30%;">Payment Status</td>
                                            <td>
                                                <?php 
                                                $dbStatus = strtolower($registrationDetails['status'] ?? '');
                                                $paymentStatus = $registrationDetails['payment_status'] ?? '';
                                                $amount = $registrationDetails['total_amount'] ?? 0;
                                                
                                                if ($paymentStatus === 'completed'): ?>
                                                    <span class="badge bg-success fs-6">Paid</span>
                                                <?php elseif ($amount == 0 && $dbStatus === 'approved'): ?>
                                                    <span class="badge bg-success fs-6">Approved</span>
                                                <?php elseif ($amount == 0 && $dbStatus === 'rejected'): ?>
                                                    <span class="badge bg-danger fs-6">Rejected</span>
                                                <?php elseif ($amount == 0): ?>
                                                    <span class="badge bg-info fs-6">Awaiting Approval</span>
                                                <?php elseif ($dbStatus === 'pending payment'): ?>
                                                    <span class="badge bg-warning fs-6">Pending Payment</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary fs-6"><?php echo ucfirst($registrationDetails['status']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Amount Due</td>
                                            <td class="fs-5 fw-bold text-success"><?php echo formatCurrency($registrationDetails['total_amount'], $registrationDetails['currency']); ?></td>
                                        </tr>
                                        <?php if ($paymentStatus === 'completed'): ?>
                                        <tr>
                                            <td class="fw-bold">Payment Date</td>
                                            <td><?php echo date('M j, Y \a\t g:i A', strtotime($registrationDetails['updated_at'])); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php 
                            $amount = $registrationDetails['total_amount'] ?? 0;
                            $paymentStatus = $registrationDetails['payment_status'] ?? '';
                            $dbStatus = strtolower($registrationDetails['status'] ?? '');
                            
                            if ($amount == 0 && $dbStatus === 'approved'): ?>
                            <div class="mt-3">
                                <span class="badge bg-success fs-6">Approved</span>
                            </div>
                            <?php elseif ($amount == 0 && $dbStatus === 'rejected'): ?>
                            <div class="mt-3">
                                <span class="badge bg-danger fs-6">Rejected</span>
                            </div>
                            <?php elseif ($amount == 0): ?>
                            <div class="mt-3">
                                <span class="badge bg-info fs-6">Awaiting Approval</span>
                            </div>
                            <?php elseif ($paymentStatus === 'voided'): ?>
                            <div class="mt-3">
                                <span class="badge bg-danger fs-6">Registration Cancelled</span>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This registration has been cancelled. Payment reminders have been stopped.
                                    </small>
                                </div>
                            </div>
                            <?php elseif ($paymentStatus !== 'completed'): ?>
                            <div class="mt-3">
                                <h6 class="fw-bold">Payment Options</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="?action=pay&id=<?php echo $registrationDetails['id']; ?>" class="btn btn-success">
                                        <i class="fas fa-credit-card me-2"></i>Pay Now
                                    </a>
                                    <button onclick="requestInvoice(<?php echo $registrationDetails['id']; ?>)" class="btn btn-outline-primary">
                                        <i class="fas fa-file-invoice me-2"></i>Request Invoice
                                    </button>
                                    <button onclick="cancelRegistration(<?php echo $registrationDetails['id']; ?>, '<?php echo htmlspecialchars($registrationDetails['email']); ?>')" class="btn btn-outline-danger">
                                        <i class="fas fa-times me-2"></i>Cancel Registration
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        You can pay immediately, request an invoice, or cancel your registration. Cancelling will stop payment reminders.
                                    </small>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="mt-3">
                                <span class="badge bg-success fs-6">Payment Completed</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($amount > 0): ?>
                            <div class="mt-3">
                                <h6 class="fw-bold">Invoice</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="invoice.php?id=<?php echo $registrationDetails['id']; ?>&email=<?php echo urlencode($registrationDetails['email']); ?>" class="btn btn-outline-info" target="_blank">
                                        <i class="fas fa-file-invoice me-2"></i>View Invoice
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($paymentStatus === 'completed'): ?>
                            <div class="mt-3">
                                <h6 class="fw-bold">Receipt</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="receipt.php?id=<?php echo $registrationDetails['id']; ?>&email=<?php echo urlencode($registrationDetails['email']); ?>" class="btn btn-outline-success" target="_blank">
                                        <i class="fas fa-receipt me-2"></i>View Receipt
                                    </a>
                                    <button onclick="printReceipt(<?php echo $registrationDetails['id']; ?>, '<?php echo urlencode($registrationDetails['email']); ?>')" class="btn btn-outline-primary">
                                        <i class="fas fa-print me-2"></i>Print Receipt
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4">
                            <a href="registration_lookup.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Search
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            const emailField = document.getElementById('email');
            const phoneField = document.getElementById('phone');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Validate email is provided
                    if (!emailField.value.trim()) {
                        e.preventDefault();
                        alert('Please enter your email address to search for registrations.');
                        emailField.focus();
                        return false;
                    }
                    
                    // Validate email format
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(emailField.value.trim())) {
                        e.preventDefault();
                        alert('Please enter a valid email address.');
                        emailField.focus();
                        return false;
                    }
                    
                    // Validate phone if provided
                    if (phoneField.value.trim()) {
                        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                        if (!phoneRegex.test(phoneField.value.replace(/[\s\-\(\)]/g, ''))) {
                            e.preventDefault();
                            alert('Please enter a valid phone number or leave it blank.');
                            phoneField.focus();
                            return false;
                        }
                    }
                    
                    // reCAPTCHA validation
                    const recaptchaResponse = document.querySelector('[name="g-recaptcha-response"]');
                    if (recaptchaResponse && !recaptchaResponse.value) {
                        e.preventDefault();
                        alert('Please complete the reCAPTCHA verification to search for your registrations.');
                        return false;
                    }
                });
            }
            
            // Auto-focus email field
            if (emailField) {
                emailField.focus();
            }
        });

        // Function to request invoice
        function requestInvoice(registrationId) {
            const button = event.target;
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            button.disabled = true;
            
            // Send AJAX request
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
                    // Show success message
                    button.innerHTML = '<i class="fas fa-check me-2"></i>Invoice Sent!';
                    button.classList.remove('btn-outline-primary');
                    button.classList.add('btn-success');
                    
                    // Show success alert
                    showAlert('Invoice sent successfully! Check your email.', 'success');
                } else {
                    // Show error message
                    button.innerHTML = originalText;
                    button.disabled = false;
                    showAlert('Failed to send invoice. Please try again.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalText;
                button.disabled = false;
                showAlert('An error occurred. Please try again.', 'danger');
            });
        }
        
        // Function to cancel registration
        function cancelRegistration(registrationId, email) {
            if (!confirm('Are you sure you want to cancel this registration? This action cannot be undone and payment reminders will stop.')) {
                return;
            }
            
            const button = event.target;
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Cancelling...';
            button.disabled = true;
            
            // Send AJAX request
            fetch('cancel_registration.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    registration_id: registrationId,
                    email: email,
                    reason: 'Cancelled by User'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    button.innerHTML = '<i class="fas fa-check me-2"></i>Cancelled';
                    button.classList.remove('btn-outline-danger');
                    button.classList.add('btn-danger');
                    button.disabled = true;
                    
                    // Show success alert
                    showAlert('Registration cancelled successfully. Payment reminders have been stopped.', 'success');
                    
                    // Reload the page after a short delay to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    // Show error message
                    button.innerHTML = originalText;
                    button.disabled = false;
                    showAlert(data.message || 'Failed to cancel registration. Please try again.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalText;
                button.disabled = false;
                showAlert('An error occurred. Please try again.', 'danger');
            });
        }
        
        // Function to show alerts
        function showAlert(message, type) {
            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert at the top of the main content
            const mainContent = document.querySelector('.container-fluid');
            if (mainContent) {
                mainContent.insertBefore(alertDiv, mainContent.firstChild);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        }
        
        // Function to print receipt
        function printReceipt(registrationId, email) {
            const receiptUrl = `receipt.php?id=${registrationId}&email=${email}&print=1`;
            const printWindow = window.open(receiptUrl, '_blank', 'width=800,height=600');
            
            if (printWindow) {
                printWindow.onload = function() {
                    printWindow.print();
                };
            } else {
                showAlert('Please allow popups to print receipts.', 'warning');
            }
        }
    </script>
    
    <!-- Footer -->
    <footer class="py-3 mt-4 mx-3" style="background-color: #f8f9fa; border-top: 1px solid #e9ecef;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <img src="images/logo.png" 
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
