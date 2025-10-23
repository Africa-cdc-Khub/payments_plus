<?php
/**
 * Public Attendance Verification
 * No authentication required - works with QR codes
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Set JSON response headers for API calls
if (isset($_GET['api']) || isset($_POST['api'])) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Handle QR code verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_data'])) {
    $qrData = $_POST['qr_data'];
    $verifierName = $_POST['verifier_name'] ?? 'QR Code Scanner';
    
    try {
        $pdo = getConnection();
        
        // Parse QR code data - handle different formats
        $parts = explode('|', $qrData);
        
        if (count($parts) >= 3 && $parts[0] === 'CPHIA2025') {
            // New comprehensive format
            $name = $parts[1];
            $email = $parts[2];
            $registrationId = $parts[4] ?? null;
            
            if (!$registrationId) {
                throw new Exception("Registration ID not found in QR code");
            }
            
            // Check if registration exists and is paid
            $stmt = $pdo->prepare("
                SELECT r.id, r.registration_type, r.payment_status, r.attendance_status,
                       u.first_name, u.last_name, u.email
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ? AND r.payment_status = 'completed'
            ");
            $stmt->execute([$registrationId]);
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
                    WHERE rp.registration_id = ? 
                    AND CONCAT(rp.first_name, ' ', rp.last_name) = ?
                ");
                $stmt->execute([$registrationId, $name]);
                $participant = $stmt->fetch();
                
                if ($participant) {
                    // This is a group participant
                    if ($participant['attendance_status'] === 'present') {
                        $response = [
                            'success' => true,
                            'message' => "✅ {$participant['first_name']} {$participant['last_name']} (Group Participant)} is already verified as present",
                            'already_verified' => true
                        ];
                    } else {
                        // Update participant attendance
                        $stmt = $pdo->prepare("
                            UPDATE registration_participants 
                            SET attendance_status = 'present', 
                                attendance_verified_at = NOW(), 
                                verified_by = ?
                            WHERE id = ?
                        ");
                        $result = $stmt->execute([$verifierName, $participant['id']]);
                        
                        if ($result) {
                            $response = [
                                'success' => true,
                                'message' => "✅ Attendance verified for {$participant['first_name']} {$participant['last_name']} (Group Participant)",
                                'participant_name' => $participant['first_name'] . ' ' . $participant['last_name'],
                                'registration_id' => $registrationId,
                                'verified_at' => date('Y-m-d H:i:s')
                            ];
                        } else {
                            throw new Exception("Failed to update participant attendance status");
                        }
                    }
                } else {
                    // Check if this is the focal person
                    if ($registration['first_name'] . ' ' . $registration['last_name'] === $name) {
                        if ($registration['attendance_status'] === 'present') {
                            $response = [
                                'success' => true,
                                'message' => "✅ {$registration['first_name']} {$registration['last_name']} (Focal Person)} is already verified as present",
                                'already_verified' => true
                            ];
                        } else {
                            // Update focal person attendance
                            $stmt = $pdo->prepare("
                                UPDATE registrations 
                                SET attendance_status = 'present', 
                                    attendance_verified_at = NOW(), 
                                    verified_by = ?
                                WHERE id = ?
                            ");
                            $result = $stmt->execute([$verifierName, $registrationId]);
                            
                            if ($result) {
                                $response = [
                                    'success' => true,
                                    'message' => "✅ Attendance verified for {$registration['first_name']} {$registration['last_name']} (Focal Person)",
                                    'participant_name' => $registration['first_name'] . ' ' . $registration['last_name'],
                                    'registration_id' => $registrationId,
                                    'verified_at' => date('Y-m-d H:i:s')
                                ];
                            } else {
                                throw new Exception("Failed to update focal person attendance status");
                            }
                        }
                    } else {
                        throw new Exception("Participant not found in this group registration");
                    }
                }
            } else {
                // Individual registration
                if ($registration['attendance_status'] === 'present') {
                    $response = [
                        'success' => true,
                        'message' => "✅ {$registration['first_name']} {$registration['last_name']} is already verified as present",
                        'already_verified' => true
                    ];
                } else {
                    // Update individual registration attendance
                    $stmt = $pdo->prepare("
                        UPDATE registrations 
                        SET attendance_status = 'present', 
                            attendance_verified_at = NOW(), 
                            verified_by = ?
                        WHERE id = ?
                    ");
                    $result = $stmt->execute([$verifierName, $registrationId]);
                    
                    if ($result) {
                        $response = [
                            'success' => true,
                            'message' => "✅ Attendance verified for {$registration['first_name']} {$registration['last_name']}",
                            'participant_name' => $registration['first_name'] . ' ' . $registration['last_name'],
                            'registration_id' => $registrationId,
                            'verified_at' => date('Y-m-d H:i:s')
                        ];
                    } else {
                        throw new Exception("Failed to update attendance status");
                    }
                }
            }
            
        } else {
            throw new Exception("Invalid QR code format");
        }
        
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => "❌ Error: " . $e->getMessage()
        ];
    }
    
    if (isset($_POST['api'])) {
        echo json_encode($response);
        exit;
    }
    
    $success = $response['success'] ? $response['message'] : null;
    $error = !$response['success'] ? $response['message'] : null;
    
} else {
    // Handle GET requests for direct verification via URL parameters
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
}
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
    <style>
        :root {
            --primary-green: #1a5632;
            --secondary-green: #2d7d32;
            --accent-gold: #ffd700;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #6c757d;
            --dark-gray: #343a40;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #e8f5e8 0%, #ffffff 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-green) 0%, #0d4f1c 100%);
            color: var(--white);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .verification-card {
            background: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .qr-scanner {
            background: var(--light-gray);
            border: 2px dashed var(--primary-green);
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            margin: 1.5rem 0;
        }

        .btn-verify {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            border: none;
            color: var(--white);
            padding: 0.75rem 2rem;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 1.125rem;
            box-shadow: 0 4px 15px rgba(26, 86, 50, 0.3);
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 86, 50, 0.4);
            color: var(--white);
        }

        .alert {
            border-radius: 0.375rem;
            border: none;
            font-weight: 500;
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

        .qr-input {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
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
                               placeholder="Enter your name" value="QR Code Scanner">
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
                    <h6>Method 2: Direct Link</h6>
                    <ol>
                        <li>Use the verification link from the receipt</li>
                        <li>The system will automatically verify attendance</li>
                        <li>No manual data entry required</li>
                        <li>Works with the "Verify Online" QR code</li>
                    </ol>
                </div>
            </div>
        </div>
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
