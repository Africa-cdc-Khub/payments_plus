<?php
require_once 'bootstrap.php';
require_once 'functions.php';

// Security password
define('VERIFICATION_PASSWORD', 'cphia@2025');

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['verified']);
    header('Location: verify_attendance.php');
    exit;
}

// Handle direct verification via GET parameters (for backward compatibility)
if (isset($_GET['email']) && isset($_GET['reg_id'])) {
    $email = $_GET['email'];
    $regId = $_GET['reg_id'];
    
    try {
        $pdo = getConnection();
        
        // Check if registration exists and is paid
        $stmt = $pdo->prepare("
            SELECT r.id, r.registration_type, r.payment_status, r.attendance_status,
                   u.first_name, u.last_name, u.email
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ? AND r.payment_status = 'completed'
        ");
        $stmt->execute([$regId]);
        $registration = $stmt->fetch();
        
        if (!$registration) {
            throw new Exception("Registration not found or not paid");
        }
        
        // Check if it's a group registration
        if ($registration['registration_type'] === 'group') {
            // For group registrations, check if this is the focal person or a participant
            $stmt = $pdo->prepare("
                SELECT rp.id, rp.first_name, rp.last_name, rp.email, rp.attendance_status
                FROM registration_participants rp
                WHERE rp.registration_id = ? AND rp.email = ?
            ");
            $stmt->execute([$regId, $email]);
            $participant = $stmt->fetch();
            
            if ($participant) {
                // This is a group participant
                if ($participant['attendance_status'] === 'present') {
                    $success = "✅ {$participant['first_name']} {$participant['last_name']} (Group Participant) is already verified as present";
                    $already_verified = true;
                } else {
                    // Update participant attendance
                    $stmt = $pdo->prepare("
                        UPDATE registration_participants 
                        SET attendance_status = 'present', 
                            attendance_verified_at = NOW(), 
                            verified_by = 'Online Verification'
                        WHERE id = ?
                    ");
                    $result = $stmt->execute([$participant['id']]);
                    
                    if ($result) {
                        $success = "✅ Attendance verified for {$participant['first_name']} {$participant['last_name']} (Group Participant)";
                        $participant_name = $participant['first_name'] . ' ' . $participant['last_name'];
                    } else {
                        throw new Exception("Failed to update participant attendance status");
                    }
                }
            } else {
                // Check if this is the focal person
                if ($registration['email'] === $email) {
                    if ($registration['attendance_status'] === 'present') {
                        $success = "✅ {$registration['first_name']} {$registration['last_name']} (Focal Person) is already verified as present";
                        $already_verified = true;
                    } else {
                        // Update focal person attendance
                        $stmt = $pdo->prepare("
                            UPDATE registrations 
                            SET attendance_status = 'present', 
                                attendance_verified_at = NOW(), 
                                verified_by = 'Online Verification'
                            WHERE id = ?
                        ");
                        $result = $stmt->execute([$regId]);
                        
                        if ($result) {
                            $success = "✅ Attendance verified for {$registration['first_name']} {$registration['last_name']} (Focal Person)";
                            $participant_name = $registration['first_name'] . ' ' . $registration['last_name'];
                        } else {
                            throw new Exception("Failed to update focal person attendance status");
                        }
                    }
                } else {
                    throw new Exception("Email not found in this group registration");
                }
            }
        } else {
            // Individual registration
            if ($registration['email'] !== $email) {
                throw new Exception("Email doesn't match this registration");
            }
            
            if ($registration['attendance_status'] === 'present') {
                $success = "✅ {$registration['first_name']} {$registration['last_name']} is already verified as present";
                $already_verified = true;
            } else {
                // Update individual registration attendance
                $stmt = $pdo->prepare("
                    UPDATE registrations 
                    SET attendance_status = 'present', 
                        attendance_verified_at = NOW(), 
                        verified_by = 'Online Verification'
                    WHERE id = ?
                ");
                $result = $stmt->execute([$regId]);
                
                if ($result) {
                    $success = "✅ Attendance verified for {$registration['first_name']} {$registration['last_name']}";
                    $participant_name = $registration['first_name'] . ' ' . $registration['last_name'];
                } else {
                    throw new Exception("Failed to update attendance status");
                }
            }
        }
        
    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Handle authentication
$authenticated = false;
$auth_error = '';

if (isset($_POST['verify_password'])) {
    // Verify reCAPTCHA
    $recaptcha_secret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'; // Test secret key
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    if (empty($recaptcha_response)) {
        $auth_error = "❌ Please complete the reCAPTCHA verification.";
    } else {
        // Verify reCAPTCHA with Google
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $verify_data = [
            'secret' => $recaptcha_secret,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($verify_data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($verify_url, false, $context);
        $recaptcha_result = json_decode($result, true);
        
        if ($recaptcha_result['success'] && $_POST['verify_password'] === VERIFICATION_PASSWORD) {
            $_SESSION['verified'] = true;
            $authenticated = true;
        } else {
            $auth_error = "❌ Invalid password or reCAPTCHA verification failed. Access denied.";
        }
    }
} elseif (isset($_SESSION['verified']) && $_SESSION['verified'] === true) {
    $authenticated = true;
}

// Handle QR code verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_data']) && $authenticated) {
    $qrData = $_POST['qr_data'];
    $verifierName = $_POST['verifier_name'] ?? 'System';
    
    // Parse QR code data - handle both old and new formats
    $parts = explode('|', $qrData);
    if (count($parts) >= 9 && $parts[0] === 'CPHIA2025') {
        // New comprehensive format
        if (count($parts) >= 16) {
            $name = $parts[1];
            $email = $parts[2];
            $phone = $parts[3];
            $registrationId = $parts[4];
            $package = $parts[5];
            $organization = $parts[6];
            $institution = $parts[7];
            $nationality = $parts[8];
            $amount = $parts[9];
            $currency = $parts[10];
            $registrationType = $parts[11];
            $paymentDate = $parts[12];
            $conferenceName = $parts[13];
            $conferenceDates = $parts[14];
            $conferenceLocation = $parts[15];
        } else {
            // Old format for backward compatibility
            $name = $parts[1];
            $registrationId = $parts[2];
            $package = $parts[3];
            $organization = $parts[4];
            $institution = $parts[5];
            $phone = $parts[6];
            $nationality = $parts[7];
            $paymentDate = $parts[8];
        }
        
        try {
            $pdo = getConnection();
            
            // Check if it's a group registration
            $stmt = $pdo->prepare("SELECT registration_type FROM registrations WHERE id = ?");
            $stmt->execute([$registrationId]);
            $registration = $stmt->fetch();
            
            if ($registration) {
                if ($registration['registration_type'] === 'group') {
                    // Update participant attendance
                    $stmt = $pdo->prepare("UPDATE registration_participants 
                                         SET attendance_status = 'present', 
                                             attendance_verified_at = NOW(), 
                                             verified_by = ?
                                         WHERE registration_id = ? 
                                         AND CONCAT(first_name, ' ', last_name) = ?");
                    $result = $stmt->execute([$verifierName, $registrationId, $name]);
                    
                    if ($result && $stmt->rowCount() > 0) {
                        $success = "✅ Attendance verified for {$name} (Group Participant)";
                    } else {
                        $error = "❌ Participant not found in group registration";
                    }
                } else {
                    // Update individual registration
                    $stmt = $pdo->prepare("UPDATE users u
                                         JOIN registrations r ON u.id = r.user_id
                                         SET u.attendance_status = 'present', 
                                             u.attendance_verified_at = NOW(), 
                                             u.verified_by = ?
                                         WHERE r.id = ? 
                                         AND CONCAT(u.first_name, ' ', u.last_name) = ?");
                    $result = $stmt->execute([$verifierName, $registrationId, $name]);
                    
                    if ($result && $stmt->rowCount() > 0) {
                        $success = "✅ Attendance verified for {$name} (Individual Registration)";
                    } else {
                        $error = "❌ Participant not found in individual registration";
                    }
                }
            } else {
                $error = "❌ Registration not found";
            }
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $error = "❌ Invalid QR code format";
    }
}

// Get attendance statistics
$pdo = getConnection();
$stats = [];

// Individual registrations
$stmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN u.attendance_status = 'present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN u.attendance_status = 'absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN u.attendance_status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM users u 
    JOIN registrations r ON u.id = r.user_id 
    WHERE r.registration_type = 'individual'");
$stats['individual'] = $stmt->fetch();

// Group participants
$stmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN attendance_status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM registration_participants");
$stats['participants'] = $stmt->fetch();

$totalAttendees = $stats['individual']['total'] + $stats['participants']['total'];
$totalPresent = $stats['individual']['present'] + $stats['participants']['present'];
$totalAbsent = $stats['individual']['absent'] + $stats['participants']['absent'];
$totalPending = $stats['individual']['pending'] + $stats['participants']['pending'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Attendance - <?php echo CONFERENCE_SHORT_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        :root {
            --primary-green: #1a5632;
            --secondary-green: #2d7d32;
            --accent-gold: #ffd700;
            --accent-orange: #ff8c00;
            --accent-red: #dc2626;
            --dark-green: #0d4f1c;
            --light-green: #e8f5e8;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #6c757d;
            --dark-gray: #343a40;
            --spacing-1: 0.25rem;
            --spacing-2: 0.5rem;
            --spacing-3: 0.75rem;
            --spacing-4: 1rem;
            --spacing-5: 1.25rem;
            --spacing-6: 1.5rem;
            --spacing-8: 2rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;
            --radius-sm: 0.25rem;
            --radius-md: 0.375rem;
            --radius-lg: 0.5rem;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--light-green) 0%, var(--white) 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--white);
            padding: var(--spacing-8) 0;
            margin-bottom: var(--spacing-8);
        }

        .header h1 {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            margin-bottom: var(--spacing-2);
        }

        .header p {
            font-size: var(--font-size-lg);
            opacity: 0.9;
        }

        .verification-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: var(--spacing-8);
            margin-bottom: var(--spacing-6);
        }

        .qr-scanner {
            background: var(--light-gray);
            border: 2px dashed var(--primary-green);
            border-radius: var(--radius-lg);
            padding: var(--spacing-8);
            text-align: center;
            margin: var(--spacing-6) 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-8);
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-6);
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-green);
        }

        .stat-number {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: var(--spacing-2);
        }

        .stat-label {
            color: var(--medium-gray);
            font-weight: 500;
        }

        .btn-verify {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            border: none;
            color: var(--white);
            padding: var(--spacing-4) var(--spacing-8);
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: var(--font-size-lg);
            box-shadow: 0 4px 15px rgba(26, 86, 50, 0.3);
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 86, 50, 0.4);
            color: var(--white);
        }

        .alert {
            border-radius: var(--radius-md);
            border: none;
            font-weight: 500;
        }

        .form-control {
            border-radius: var(--radius-md);
            border: 2px solid #e9ecef;
            padding: var(--spacing-3) var(--spacing-4);
            font-size: var(--font-size-base);
        }

        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(26, 86, 50, 0.25);
        }

        .qr-input {
            font-family: 'Courier New', monospace;
            font-size: var(--font-size-sm);
        }

        .verification-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .verification-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-qrcode me-3"></i>Attendance Verification</h1>
                    <p><?php echo CONFERENCE_SHORT_NAME; ?> • <?php echo CONFERENCE_DATES; ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <img src="https://africacdc.org/wp-content/uploads/2020/02/AfricaCDC_Logo.png" alt="Africa CDC" style="height: 50px; filter: brightness(0) invert(1);">
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['email']) && isset($_GET['reg_id'])): ?>
        <!-- Direct Verification Result -->
        <div class="verification-card">
            <h3 class="mb-4"><i class="fas fa-check-circle me-2"></i>Attendance Verification Result</h3>
            
            <?php if (isset($success)): ?>
                <div class="alert verification-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert verification-error">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="verify_attendance.php" class="btn btn-verify">
                    <i class="fas fa-qrcode me-2"></i>Back to Admin Verification
                </a>
            </div>
        </div>
        <?php elseif (!$authenticated): ?>
        <!-- Authentication Form -->
        <div class="verification-card">
            <h3 class="mb-4"><i class="fas fa-lock me-2"></i>Access Verification Required</h3>
            
            <?php if ($auth_error): ?>
                <div class="alert verification-error">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $auth_error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <label for="verify_password" class="form-label">Verification Password</label>
                        <input type="password" class="form-control" id="verify_password" name="verify_password" 
                               placeholder="Enter verification password" required>
                        <div class="form-text">Enter the verification password to access attendance verification</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Security Verification</label>
                        <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                        <div class="form-text">Complete the reCAPTCHA verification</div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-verify">
                        <i class="fas fa-key me-2"></i>Verify Access
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalAttendees; ?></div>
                <div class="stat-label">Total Registrations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo $totalPresent; ?></div>
                <div class="stat-label">Present</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning"><?php echo $totalPending; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-danger"><?php echo $totalAbsent; ?></div>
                <div class="stat-label">Absent</div>
            </div>
        </div>

        <!-- Verification Form -->
        <div class="verification-card">
            <h3 class="mb-4"><i class="fas fa-qrcode me-2"></i>QR Code Verification</h3>
            
            <?php if (isset($success)): ?>
                <div class="alert verification-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert verification-error">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-8">
                        <label for="qr_data" class="form-label">QR Code Data</label>
                        <textarea class="form-control qr-input" id="qr_data" name="qr_data" rows="3" 
                                  placeholder="Paste QR code data here or scan QR code..." required></textarea>
                        <div class="form-text">Scan the participant's QR code or paste the data manually</div>
                    </div>
                    <div class="col-md-4">
                        <label for="verifier_name" class="form-label">Verifier Name</label>
                        <input type="text" class="form-control" id="verifier_name" name="verifier_name" 
                               placeholder="Enter your name" required>
                        <div class="form-text">Your name for verification records</div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-verify">
                        <i class="fas fa-check-circle me-2"></i>Verify Attendance
                    </button>
                </div>
            </form>
        </div>

        <!-- QR Code Display -->
        <div class="verification-card">
            <h4><i class="fas fa-qrcode me-2"></i>Sample QR Codes</h4>
            <div class="row text-center">
                <div class="col-md-4">
                    <img src="<?php echo generateQRCode('CPHIA2025|Sample User|sample@example.com|1234567890|123|Test Package|Test Organization|Test Institution|Test Nationality|$100.00|USD|individual|2025-01-01 12:00:00|CPHIA 2025|22-25 October 2025|Cape Town, South Africa'); ?>" 
                         alt="Complete Receipt QR Code" style="max-width: 150px; height: auto; border: 2px solid var(--primary-green); border-radius: 8px; padding: 10px;">
                    <p class="mt-2 text-muted"><strong>Complete Receipt</strong><br>All registration details</p>
                </div>
                <div class="col-md-4">
                    <img src="<?php echo generateVerificationQRCode('CPHIA2025|Sample User|sample@example.com|1234567890|123|Test Package|Test Organization|Test Institution|Test Nationality|$100.00|USD|individual|2025-01-01 12:00:00|CPHIA 2025|22-25 October 2025|Cape Town, South Africa'); ?>" 
                         alt="Verification QR Code" style="max-width: 120px; height: auto; border: 2px solid var(--primary-green); border-radius: 8px; padding: 10px;">
                    <p class="mt-2 text-muted"><strong>Quick Check-in</strong><br>Fast attendance scanning</p>
                </div>
                <div class="col-md-4">
                    <img src="<?php echo generateVerificationQRCode('VERIFY|' . APP_URL . '/verify_attendance.php|123|Sample User'); ?>" 
                         alt="Navigation QR Code" style="max-width: 100px; height: auto; border: 2px solid #ff8c00; border-radius: 8px; padding: 10px;">
                    <p class="mt-2 text-muted"><strong>Verify Online</strong><br>Scan to verify attendance</p>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="verification-card">
            <h4><i class="fas fa-info-circle me-2"></i>How to Use</h4>
            <div class="row">
                <div class="col-md-6">
                    <h6>Method 1: QR Code Scanner</h6>
                    <ol>
                        <li>Use a QR code scanner app on your phone</li>
                        <li>Scan the participant's QR code from their receipt</li>
                        <li>Copy the scanned data and paste it above</li>
                        <li>Enter your name as the verifier</li>
                        <li>Click "Verify Attendance"</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6>Method 2: Manual Entry</h6>
                    <ol>
                        <li>Ask the participant to show their receipt</li>
                        <li>Look for the QR code data string</li>
                        <li>Copy and paste the entire data string</li>
                        <li>Enter your name as the verifier</li>
                        <li>Click "Verify Attendance"</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <!-- Logout Option -->
        <div class="text-center mt-4">
            <a href="?logout=1" class="btn btn-outline-danger">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Auto-focus on QR data input
        document.getElementById('qr_data').focus();
    </script>
</body>
</html>
