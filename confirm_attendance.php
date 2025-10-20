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

$message = '';
$messageType = '';
$attendanceConfirmed = false;

// Handle attendance confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_attendance'])) {
    $email = sanitizeInput($_POST['email'] ?? '');
    $registrationId = sanitizeInput($_POST['registration_id'] ?? '');
    
    if (empty($email) || !validateEmail($email)) {
        $message = "Please provide a valid email address.";
        $messageType = "error";
    } elseif (empty($registrationId) || !is_numeric($registrationId)) {
        $message = "Please provide a valid registration ID.";
        $messageType = "error";
    } elseif (isRecaptchaEnabled() && (empty($_POST['g-recaptcha-response']) || !validateRecaptcha($_POST['g-recaptcha-response'], RECAPTCHA_SECRET_KEY))) {
        $message = "Please complete the reCAPTCHA verification.";
        $messageType = "error";
        logSecurityEvent('recaptcha_failed_attendance', 'reCAPTCHA verification failed for attendance confirmation');
    } else {
        try {
            $pdo = getConnection();
            
            // Check if registration exists and belongs to the email
            $stmt = $pdo->prepare("
                SELECT r.id, r.status, r.payment_status, r.total_amount, r.registration_type,
                       u.first_name, u.last_name, u.email, u.attendance_status, u.attendance_verified_at
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ? AND u.email = ?
            ");
            $stmt->execute([$registrationId, $email]);
            $registration = $stmt->fetch();
            
            if (!$registration) {
                $message = "Registration not found or email does not match. Please check your details and try again.";
                $messageType = "error";
            } elseif ($registration['attendance_status'] === 'present') {
                $message = "Your attendance has already been confirmed for this registration.";
                $messageType = "info";
                $attendanceConfirmed = true;
            } elseif ($registration['status'] === 'rejected') {
                $message = "This registration has been rejected and cannot be used for attendance confirmation.";
                $messageType = "error";
            } elseif ($registration['total_amount'] > 0 && $registration['payment_status'] !== 'completed') {
                $message = "Please complete your payment before confirming attendance. You can pay through the registration lookup page.";
                $messageType = "warning";
            } elseif ($registration['total_amount'] == 0 && $registration['status'] !== 'approved') {
                $message = "Your delegate registration is still pending approval. Only approved delegates can confirm attendance.";
                $messageType = "warning";
            } else {
                // Update attendance status
                $stmt = $pdo->prepare("
                    UPDATE users u
                    JOIN registrations r ON u.id = r.user_id
                    SET u.attendance_status = 'present',
                        u.attendance_verified_at = NOW(),
                        u.verified_by = 'Self-Confirmed'
                    WHERE r.id = ? AND u.email = ?
                ");
                
                if ($stmt->execute([$registrationId, $email])) {
                    $message = "✅ Your attendance has been successfully confirmed! Welcome to CPHIA 2025.";
                    $messageType = "success";
                    $attendanceConfirmed = true;
                    
                    // Log the confirmation
                    logSecurityEvent('attendance_confirmed', "Self-confirmed attendance for registration #{$registrationId}, email: {$email}");
                } else {
                    $message = "Failed to confirm attendance. Please try again or contact support.";
                    $messageType = "error";
                }
            }
        } catch (Exception $e) {
            $message = "An error occurred. Please try again or contact support.";
            $messageType = "error";
            error_log("Attendance confirmation error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Attendance - <?php echo CONFERENCE_SHORT_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>
    <style>
        #qr-reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        #qr-reader video {
            width: 100%;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        
        .qr-scanner-container {
            position: relative;
            margin: 20px 0;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        
        .scan-instructions {
            text-align: center;
            margin: 15px 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .camera-controls {
            text-align: center;
            margin: 15px 0;
        }
        
        .camera-controls button {
            margin: 0 5px;
        }
        
        .qr-result {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        
        .manual-entry-toggle {
            text-align: center;
            margin: 20px 0;
        }
        
        /* Responsive adjustments for mobile */
        @media (max-width: 768px) {
            .qr-scanner-container {
                max-width: 300px;
            }
        }
        
        @media (max-width: 480px) {
            .qr-scanner-container {
                max-width: 250px;
            }
        }
    </style>
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

    <div class="container mt-5" style="padding: 0 5%;">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Page Title -->
                <div class="text-center mb-5">
                    <h3 class="mb-3">Confirm Your Attendance</h3>
                    <p class="lead">Confirm your attendance for <?php echo CONFERENCE_DATES; ?></p>
                    <div class="alert alert-info d-inline-block">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Eligibility:</strong> Only paid participants and approved delegates can confirm attendance.
                    </div>
                </div>

                <!-- Confirmation Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Attendance Confirmation</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : ($messageType === 'warning' ? 'warning' : ($messageType === 'success' ? 'success' : 'info')); ?>">
                                <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-triangle' : ($messageType === 'warning' ? 'exclamation-triangle' : ($messageType === 'success' ? 'check-circle' : 'info-circle')); ?> me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$attendanceConfirmed): ?>
                        <!-- QR Code Scanner Section -->
                        <div id="qr-scanner-section" class="mb-4">
                            <div class="text-center mb-3">
                                <h6><i class="fas fa-qrcode me-2"></i>Scan Your QR Code</h6>
                                <p class="text-muted small">Use your device camera to scan the QR code from your registration confirmation</p>
                            </div>
                            
                            <div class="qr-scanner-container">
                                <div id="qr-reader"></div>
                            </div>
                            
                            <div class="scan-instructions">
                                <i class="fas fa-camera me-1"></i>
                                Point your camera at the QR code to scan it
                            </div>
                            
                            <div class="camera-controls">
                                <button type="button" id="start-camera" class="btn btn-success btn-sm">
                                    <i class="fas fa-qrcode me-1"></i>Scan QR Code
                                </button>
                                <button type="button" id="stop-camera" class="btn btn-warning btn-sm" style="display: none;">
                                    <i class="fas fa-stop me-1"></i>Stop Camera
                                </button>
                                <button type="button" id="switch-camera" class="btn btn-info btn-sm" style="display: none;">
                                    <i class="fas fa-sync-alt me-1"></i>Switch Camera
                                </button>
                            </div>
                            
                            <div id="qr-result" class="qr-result" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span id="qr-result-text">QR Code scanned successfully!</span>
                                </div>
                            </div>
                            
                            <div class="manual-entry-toggle">
                                <button type="button" id="toggle-manual-entry" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-keyboard me-1"></i>Enter Details Manually Instead
                                </button>
                            </div>
                        </div>

                        <!-- Manual Entry Section (Initially Hidden) -->
                        <div id="manual-entry-section" style="display: none;">
                            <div class="text-center mb-3">
                                <h6><i class="fas fa-keyboard me-2"></i>Manual Entry</h6>
                                <p class="text-muted small">Enter your details manually if QR scanning is not available</p>
                            </div>
                        </div>

                        <form method="POST" class="row g-3" id="attendance-form">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required
                                       placeholder="Enter your email address">
                                <div class="form-text">The email address used for registration</div>
                            </div>
                            <div class="col-md-6">
                                <label for="registration_id" class="form-label">Registration ID *</label>
                                <input type="text" class="form-control" id="registration_id" name="registration_id" 
                                       value="<?php echo htmlspecialchars($_POST['registration_id'] ?? ''); ?>" required
                                       placeholder="Enter your registration ID (e.g., 1234)">
                                <div class="form-text">Your registration ID from the confirmation email</div>
                            </div>
                            
                            <!-- reCAPTCHA -->
                            <?php if (isRecaptchaEnabled()): ?>
                            <div class="col-12">
                                <div class="text-center">
                                    <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY); ?>"></div>
                                    <small class="text-muted mt-2 d-block">
                                        Please complete the reCAPTCHA verification to confirm your attendance.
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-12">
                                <button type="submit" name="confirm_attendance" class="btn btn-success btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Confirm My Attendance
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Registration
                                </a>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                                <h4 class="text-success">Attendance Confirmed!</h4>
                                <p class="lead">Thank you for confirming your attendance. We look forward to seeing you at the conference!</p>
                            </div>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Back to Home
                                </a>
                                <a href="registration_lookup.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list-alt me-2"></i>View My Registrations
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-check-circle me-2"></i>Who Can Confirm Attendance?</h6>
                                <p class="small text-muted">
                                    • <strong>Paid Participants:</strong> Those who have completed payment<br>
                                    • <strong>Approved Delegates:</strong> Delegates with approved status (no payment required)
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-envelope me-2"></i>Need Help?</h6>
                                <p class="small text-muted">
                                    Can't find your Registration ID? Check your confirmation email or use the 
                                    <a href="registration_lookup.php" class="text-decoration-none">"View My Registrations"</a> link.
                                    <br><br>
                                    Contact us at <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" class="text-decoration-none"><?php echo SUPPORT_EMAIL; ?></a> 
                                    for assistance.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let qrScanner = null;
        let currentCamera = 'environment'; // Start with back camera
        
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('attendance-form');
            const emailField = document.getElementById('email');
            const registrationIdField = document.getElementById('registration_id');
            const startCameraBtn = document.getElementById('start-camera');
            const stopCameraBtn = document.getElementById('stop-camera');
            const switchCameraBtn = document.getElementById('switch-camera');
            const toggleManualBtn = document.getElementById('toggle-manual-entry');
            const qrScannerSection = document.getElementById('qr-scanner-section');
            const manualEntrySection = document.getElementById('manual-entry-section');
            const qrResult = document.getElementById('qr-result');
            const qrResultText = document.getElementById('qr-result-text');
            
            // Camera controls
            startCameraBtn.addEventListener('click', startCamera);
            stopCameraBtn.addEventListener('click', stopCamera);
            switchCameraBtn.addEventListener('click', switchCamera);
            toggleManualBtn.addEventListener('click', toggleManualEntry);
            
            // Form validation
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Validate email
                    if (!emailField.value.trim()) {
                        e.preventDefault();
                        alert('Please enter your email address.');
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
                    
                    // Validate registration ID
                    if (!registrationIdField.value.trim()) {
                        e.preventDefault();
                        alert('Please enter your registration ID.');
                        registrationIdField.focus();
                        return false;
                    }
                    
                    if (!/^\d+$/.test(registrationIdField.value.trim())) {
                        e.preventDefault();
                        alert('Please enter a valid registration ID (numbers only).');
                        registrationIdField.focus();
                        return false;
                    }
                    
                    // reCAPTCHA validation
                    const recaptchaResponse = document.querySelector('[name="g-recaptcha-response"]');
                    if (recaptchaResponse && !recaptchaResponse.value) {
                        e.preventDefault();
                        alert('Please complete the reCAPTCHA verification.');
                        return false;
                    }
                });
            }
            
            // Auto-focus email field
            if (emailField) {
                emailField.focus();
            }
        });
        
        function startCamera() {
            if (qrScanner) {
                qrScanner.start();
                return;
            }
            
            const video = document.getElementById('qr-reader');
            if (!video) return;
            
            qrScanner = new QrScanner(video, result => {
                handleQRResult(result);
            }, {
                highlightScanRegion: true,
                highlightCodeOutline: true,
                preferredCamera: currentCamera
            });
            
            qrScanner.start().then(() => {
                document.getElementById('start-camera').style.display = 'none';
                document.getElementById('stop-camera').style.display = 'inline-block';
                document.getElementById('switch-camera').style.display = 'inline-block';
            }).catch(err => {
                console.error('Camera start failed:', err);
                alert('Unable to start camera. Please check permissions and try again.');
            });
        }
        
        function stopCamera() {
            if (qrScanner) {
                qrScanner.stop();
                document.getElementById('start-camera').style.display = 'inline-block';
                document.getElementById('stop-camera').style.display = 'none';
                document.getElementById('switch-camera').style.display = 'none';
            }
        }
        
        function switchCamera() {
            if (qrScanner) {
                currentCamera = currentCamera === 'environment' ? 'user' : 'environment';
                qrScanner.setCamera(currentCamera);
            }
        }
        
        function handleQRResult(result) {
            try {
                const qrData = result.data;
                console.log('QR Code scanned:', qrData);
                
                // Parse QR code data - handle both old and new formats
                const parts = qrData.split('|');
                if (parts.length >= 9 && parts[0] === 'CPHIA2025') {
                    // New comprehensive format
                    let email, registrationId;
                    
                    if (parts.length >= 16) {
                        email = parts[2];
                        registrationId = parts[4];
                    } else {
                        // Old format for backward compatibility
                        registrationId = parts[2];
                        // For old format, we need to get email from user input
                        email = document.getElementById('email').value;
                    }
                    
                    if (registrationId) {
                        // Fill in the registration ID
                        document.getElementById('registration_id').value = registrationId;
                        
                        // If we have email from QR code, fill it too
                        if (email) {
                            document.getElementById('email').value = email;
                        }
                        
                        // Show success message
                        qrResultText.textContent = `QR Code scanned successfully! Registration ID: ${registrationId}`;
                        qrResult.style.display = 'block';
                        
                        // Stop camera after successful scan
                        setTimeout(() => {
                            stopCamera();
                        }, 2000);
                        
                        // Focus on email field if not filled
                        if (!email) {
                            document.getElementById('email').focus();
                        }
                    } else {
                        throw new Error('Invalid QR code format');
                    }
                } else {
                    // Try to extract just the registration ID if it's a simple QR code
                    const regIdMatch = qrData.match(/(\d+)/);
                    if (regIdMatch) {
                        document.getElementById('registration_id').value = regIdMatch[1];
                        qrResultText.textContent = `Registration ID found: ${regIdMatch[1]}`;
                        qrResult.style.display = 'block';
                        
                        setTimeout(() => {
                            stopCamera();
                        }, 2000);
                        
                        document.getElementById('email').focus();
                    } else {
                        throw new Error('No registration ID found in QR code');
                    }
                }
            } catch (error) {
                console.error('QR Code parsing error:', error);
                qrResultText.textContent = 'Invalid QR code format. Please try again or enter details manually.';
                qrResult.style.display = 'block';
                qrResult.style.borderLeftColor = '#dc3545';
                qrResult.style.backgroundColor = '#f8d7da';
            }
        }
        
        function toggleManualEntry() {
            if (qrScannerSection.style.display === 'none') {
                qrScannerSection.style.display = 'block';
                manualEntrySection.style.display = 'none';
                toggleManualBtn.innerHTML = '<i class="fas fa-keyboard me-1"></i>Enter Details Manually Instead';
            } else {
                qrScannerSection.style.display = 'none';
                manualEntrySection.style.display = 'block';
                toggleManualBtn.innerHTML = '<i class="fas fa-qrcode me-1"></i>Scan QR Code Instead';
                stopCamera();
            }
        }
        
        // Clean up camera on page unload
        window.addEventListener('beforeunload', function() {
            if (qrScanner) {
                qrScanner.stop();
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
