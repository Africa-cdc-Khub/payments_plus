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
    
    if (empty($email) || empty($phone)) {
        $error = "Please provide both email and phone number to search for your registrations.";
    } elseif (!validateEmail($email)) {
        $error = "Please enter a valid email address.";
    } elseif (!validatePhoneNumber($phone)) {
        $error = "Please enter a valid phone number.";
    } elseif (isRecaptchaEnabled() && (empty($_POST['g-recaptcha-response']) || !validateRecaptcha($_POST['g-recaptcha-response'], RECAPTCHA_SECRET_KEY))) {
        $error = "Please complete the reCAPTCHA verification.";
        logSecurityEvent('recaptcha_failed_lookup', 'reCAPTCHA verification failed for registration lookup');
    } else {
        $registrations = getRegistrationHistoryByEmailAndPhone($email, $phone);
        $searchPerformed = true;
        
        if (empty($registrations)) {
            $error = "No registrations found for the provided email and phone number.";
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

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Page Title -->
                <div class="text-center mb-5">
                    <p class="lead">View your previous registrations for <?php echo CONFERENCE_DATES; ?></p>
                </div>

                <!-- Search Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search Your Registrations</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
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
                        <div class="row">
                            <?php foreach ($registrations as $registration): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0"><?php echo htmlspecialchars($registration['package_name']); ?></h6>
                                            <span class="badge bg-<?php echo $registration['status'] == 'paid' ? 'success' : ($registration['status'] == 'pending' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($registration['status']); ?>
                                            </span>
                                        </div>
                                        <div class="text-muted small mb-3">
                                            <div><strong>Registration ID:</strong> #<?php echo $registration['id']; ?></div>
                                            <div><strong>Type:</strong> <?php echo ucfirst($registration['registration_type']); ?></div>
                                            <div><strong>Date:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($registration['created_at'])); ?></div>
                                            <div><strong>Amount:</strong> <?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?></div>
                                            <div><strong>Payment Status:</strong> 
                                                <?php 
                                                $paymentStatus = $registration['payment_status'] ?? '';
                                                if ($paymentStatus === 'completed'): ?>
                                                    <span class="badge bg-success">Paid</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending Payment</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="?view=<?php echo $registration['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
                                            <?php 
                                            // Debug: Log payment status for troubleshooting
                                            if ($registration['id'] == 20) {
                                                error_log("Registration #20 payment_status: " . ($registration['payment_status'] ?? 'NULL') . " (type: " . gettype($registration['payment_status']) . ")");
                                            }
                                            
                                            if (($registration['payment_status'] ?? '') !== 'completed' && $registration['total_amount'] > 0): ?>
                                                <a href="?action=pay&id=<?php echo $registration['id']; ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-credit-card me-1"></i>Complete Payment
                                                </a>
                                            <?php elseif ($registration['total_amount'] == 0): ?>
                                                <span class="badge bg-success">No Payment Required</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
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
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Registration Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Registration ID:</strong></td>
                                        <td>#<?php echo $registrationDetails['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Package:</strong></td>
                                        <td><?php echo htmlspecialchars($registrationDetails['package_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type:</strong></td>
                                        <td><?php echo ucfirst($registrationDetails['registration_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $registrationDetails['status'] == 'paid' ? 'success' : ($registrationDetails['status'] == 'pending' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($registrationDetails['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Amount:</strong></td>
                                        <td><?php echo formatCurrency($registrationDetails['total_amount'], $registrationDetails['currency']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created:</strong></td>
                                        <td><?php echo date('M j, Y \a\t g:i A', strtotime($registrationDetails['created_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Contact Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td><?php echo htmlspecialchars($registrationDetails['first_name'] . ' ' . $registrationDetails['last_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?php echo htmlspecialchars($registrationDetails['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td><?php echo htmlspecialchars($registrationDetails['phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nationality:</strong></td>
                                        <td><?php echo htmlspecialchars($registrationDetails['nationality']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Organization:</strong></td>
                                        <td><?php echo htmlspecialchars($registrationDetails['organization']); ?></td>
                                    </tr>
                                </table>
                            </div>
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
        // reCAPTCHA validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const recaptchaResponse = document.querySelector('[name="g-recaptcha-response"]');
                    if (recaptchaResponse && !recaptchaResponse.value) {
                        e.preventDefault();
                        alert('Please complete the reCAPTCHA verification to search for your registrations.');
                        return false;
                    }
                });
            }
        });
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
