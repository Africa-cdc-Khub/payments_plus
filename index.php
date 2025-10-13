<?php
require_once 'bootstrap.php';
require_once 'functions.php';

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src \'self\' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src \'self\' data: https://www.google.com; connect-src \'self\' https://www.google.com; frame-src \'self\' https://www.google.com; object-src \'none\'; base-uri \'self\';');

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Invalid request. Please try again.";
        logSecurityEvent('csrf_token_mismatch', 'Invalid CSRF token provided');
    } else {
        // Check rate limiting
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!checkRateLimit($clientIp, 'registration', 20, 3600)) {
            $errors[] = "Too many registration attempts. Please try again later.";
            logSecurityEvent('rate_limit_exceeded', 'Registration rate limit exceeded for IP: ' . $clientIp);
        } else {
    $errors = [];
    $success = false;
    
    // Enhanced validation
    if (empty($_POST['package_id']) || !validatePackageId($_POST['package_id'])) {
        $errors[] = "Please select a valid package";
    }
    
    if (empty($_POST['registration_type']) || !validateRegistrationType($_POST['registration_type'])) {
        $errors[] = "Please select a valid registration type";
    }
    
    if (empty($_POST['email']) || !validateEmail($_POST['email'])) {
        $errors[] = "Please provide a valid email address";
    }
    
    if (empty($_POST['first_name'])) {
        $errors[] = "First name is required";
    }
    
    if (empty($_POST['last_name'])) {
        $errors[] = "Last name is required";
    }
    
    // Get package details for validation
    $package = null;
    if (isset($_POST['package_id'])) {
        $package = getPackageById($_POST['package_id']);
    }
    
    // For Students, Delegates, and Non African nationals packages, allow any nationality
    $isFixedPricePackage = $package && in_array(strtolower($package['name']), ['students', 'delegates', 'non african nationals']);
    
    if (!empty($_POST['nationality']) && !$isFixedPricePackage && !validateNationality($_POST['nationality'])) {
        $errors[] = "Please select a valid nationality";
    }
    
    if (!empty($_POST['phone']) && !validatePhoneNumber($_POST['phone'])) {
        $errors[] = "Please enter a valid phone number";
    }
    
    
    if (!empty($_POST['passport_number']) && !validatePassportNumber($_POST['passport_number'])) {
        $errors[] = "Please enter a valid passport number";
    }
    
    // For Students and Delegates packages, allow any organization name
    if (!empty($_POST['organization']) && !$isFixedPricePackage && !validateOrganization($_POST['organization'])) {
        $errors[] = "Please enter a valid organization name";
    }
    
    // Validate student fields if Students package is selected
    if ($package && strtolower($package['name']) === 'students') {
            if (empty($_POST['institution'])) {
                $errors[] = "Institution/School is required for student registration";
            }
            if (empty($_FILES['student_id_file']['name'])) {
                $errors[] = "Student ID document is required for student registration";
            }
        } elseif ($package && strtolower($package['name']) === 'delegates') {
            if (empty($_POST['delegate_category'])) {
                $errors[] = "Delegate Category is required for delegate registration";
            }
            if (empty($_POST['airport_of_origin'])) {
                $errors[] = "Airport of Origin is required for delegate registration";
            }
            if (empty($_FILES['passport_file']['name'])) {
                $errors[] = "Passport Copy (PDF) is required for delegate registration";
            }
        }
    }
    
    // Exhibition description is optional - only validate if provided
    if (!empty($_POST['exhibition_description']) && !validateExhibitionDescription($_POST['exhibition_description'])) {
        $errors[] = "Please enter a valid exhibition description (5-1000 characters)";
    }
    
    // Validate reCAPTCHA if enabled
    if (isRecaptchaEnabled()) {
        if (empty($_POST['g-recaptcha-response'])) {
            $errors[] = "Please complete the reCAPTCHA verification";
        } elseif (!validateRecaptcha($_POST['g-recaptcha-response'], RECAPTCHA_SECRET_KEY)) {
            $errors[] = "reCAPTCHA verification failed. Please try again.";
            logSecurityEvent('recaptcha_failed', 'reCAPTCHA verification failed for IP: ' . $clientIp);
        }
    }
    
    if (empty($errors)) {
        // Check for duplicate registration using event date
        $duplicateCheck = checkDuplicateRegistration($_POST['email'], $_POST['package_id'], CONFERENCE_DATES);
        if ($duplicateCheck['is_duplicate']) {
            $duplicateMessage = getDuplicateRegistrationMessage($duplicateCheck);
            $duplicateRegistration = $duplicateCheck['registration'];
            
            // Set a special flag for duplicate registration
            $isDuplicateRegistration = true;
            $duplicateRegistrationId = $duplicateRegistration['id'];
            $duplicateRegistrationStatus = $duplicateRegistration['status'];
            
            // Don't add to errors array - we'll handle this specially
            logSecurityEvent('duplicate_registration_attempt', 'Duplicate registration attempt for email: ' . $_POST['email'] . ', package: ' . $_POST['package_id'] . ', event: ' . CONFERENCE_DATES);
        }
    }
    
    if (empty($errors)) {
        // Package details already retrieved above
        
        if ($package) {
            // Prepare user data
            $userData = [
                'email' => sanitizeInput($_POST['email']),
                'title' => sanitizeInput($_POST['title']),
                'first_name' => sanitizeInput($_POST['first_name']),
                'last_name' => sanitizeInput($_POST['last_name']),
                'phone' => sanitizeInput($_POST['phone']),
                'nationality' => sanitizeInput($_POST['nationality']),
                'national_id' => sanitizeInput($_POST['national_id']),
        'passport_number' => sanitizeInput($_POST['passport_number']),
        'passport_file' => handleFileUpload($_FILES['passport_file'] ?? null) ?: '',
        'requires_visa' => $_POST['requires_visa'] ?? '',
                'organization' => sanitizeInput($_POST['organization']),
        'position' => sanitizeInput($_POST['position']),
        'institution' => sanitizeInput($_POST['institution'] ?? ''),
        'student_id_file' => handleFileUpload($_FILES['student_id_file'] ?? null) ?: '',
        'delegate_category' => sanitizeInput($_POST['delegate_category'] ?? ''),
        'airport_of_origin' => sanitizeInput($_POST['airport_of_origin'] ?? ''),
                'address_line1' => substr(trim(sanitizeInput($_POST['address_line1'])), 0, 60),
                'city' => substr(trim(sanitizeInput($_POST['city'])), 0, 50),
                'state' => substr(trim(sanitizeInput($_POST['state'])), 0, 50),
                'country' => cleanCountryName($_POST['country']),
            ];
            
            // Get or create user
            $user = getOrCreateUser($userData);
            
            // Calculate total amount based on African status
            $nationality = sanitizeInput($_POST['nationality']);
            $isAfrican = isAfricanNational($nationality);
            
            // Package already retrieved above
            
             // Check if this is a side event package
             $isSideEvent = ($package['type'] === 'side_event');
             
             // Check if this is an exhibition package
             $isExhibition = ($package['type'] === 'exhibition');
             
             // Check if this is a fixed-price package (Students, Delegates, Side Events, Exhibitions)
             $isFixedPricePackage = in_array(strtolower($package['name']), ['students', 'delegates']) || 
                                   $isSideEvent || $isExhibition;
             
             if ($isFixedPricePackage) {
                 // Fixed-price packages - use exact package price, not nationality-based
                 $totalAmount = $package['price'];
             
             if ($isSideEvent) {
                     $registrationType = 'side_event';
             } else if ($isExhibition) {
                     $registrationType = 'exhibition';
                 } else {
                     $registrationType = 'individual'; // Students and Delegates are individual only
                 }
            } else if ($_POST['registration_type'] === 'individual') {
                // Regular individual registration - use African/Non-African pricing
                if ($isAfrican) {
                    $package = getPackageById(19); // African Nationals package
                } else {
                    $package = getPackageById(20); // Non-African nationals package
                }
                $totalAmount = $package['price'];
                $registrationType = 'individual';
            } else if ($_POST['registration_type'] === 'group' && isset($_POST['num_people'])) {
                // Group registration - not allowed for fixed-price packages
                if ($isFixedPricePackage) {
                    $errors[] = "Group registration is not available for " . $package['name'] . " package";
                } else {
                // Regular group registration
                $numPeople = (int)$_POST['num_people'];
                
                // Validate group size limit
                if ($numPeople < 1) {
                    $errors[] = "Group size must be at least 1 person";
                } elseif ($numPeople > 40) {
                    $errors[] = "Group size cannot exceed 40 people";
                }
                
                // Check if any participants are non-African (only for non-fixed-price packages)
                $hasNonAfricanParticipants = false;
                    if (isset($_POST['participants']) && !$isFixedPricePackage) {
                    foreach ($_POST['participants'] as $participant) {
                        if (!empty($participant['nationality']) && !isAfricanNational($participant['nationality'])) {
                            $hasNonAfricanParticipants = true;
                            break;
                        }
                    }
                }
                
                // Use non-African pricing if any participant is non-African
                if ($hasNonAfricanParticipants || !$isAfrican) {
                    $package = getPackageById(20); // Non-African nationals package
                } else {
                    $package = getPackageById(19); // African Nationals package
                }
                
                $totalAmount = $package['price'] * $numPeople;
                $registrationType = 'group';
                }
            } else {
                // Default to selected package
                $totalAmount = $package['price'];
                $registrationType = $_POST['registration_type'];
            }
            
            // Create registration
            $registrationData = [
                'user_id' => $user['id'],
                'package_id' => $package['id'],
                'registration_type' => $registrationType,
                'total_amount' => $totalAmount,
                'currency' => 'USD',
                'exhibition_description' => isset($_POST['exhibition_description']) ? sanitizeInput($_POST['exhibition_description']) : null
            ];
            
            $registrationId = createRegistration($registrationData);
            
            // Handle group participants (not for fixed-price packages)
            if ($_POST['registration_type'] === 'group' && isset($_POST['participants']) && !$isFixedPricePackage) {
                $participants = [];
                foreach ($_POST['participants'] as $participant) {
                    if (!empty($participant['first_name']) && !empty($participant['last_name']) && !empty($participant['email'])) {
                        $participants[] = [
                            'title' => sanitizeInput($participant['title']),
                            'first_name' => sanitizeInput($participant['first_name']),
                            'last_name' => sanitizeInput($participant['last_name']),
                            'email' => sanitizeInput($participant['email']),
                            'nationality' => sanitizeInput($participant['nationality']),
                            'national_id' => sanitizeInput($participant['national_id']),
                            'passport_number' => sanitizeInput($participant['passport_number']),
                            'passport_file' => handleFileUpload($participant['passport_file'] ?? null) ?: '',
                            'requires_visa' => $participant['requires_visa'] ?? '',
                            'organization' => sanitizeInput($participant['organization']),
                            'institution' => sanitizeInput($participant['institution'] ?? ''),
                            'student_id_file' => handleFileUpload($participant['student_id_file'] ?? null) ?: '',
                            'delegate_category' => sanitizeInput($participant['delegate_category'] ?? ''),
                            'airport_of_origin' => sanitizeInput($participant['airport_of_origin'] ?? '')
                        ];
                    }
                }
                createRegistrationParticipants($registrationId, $participants);
            }
            
            // Send registration emails
            $participants = [];
            if ($_POST['registration_type'] === 'group' && isset($_POST['participants']) && !$isFixedPricePackage) {
                foreach ($_POST['participants'] as $participant) {
                    if (!empty($participant['first_name']) && !empty($participant['last_name']) && !empty($participant['email'])) {
                        $participants[] = [
                            'name' => sanitizeInput($participant['title']) . ' ' . sanitizeInput($participant['first_name']) . ' ' . sanitizeInput($participant['last_name']),
                            'email' => sanitizeInput($participant['email']),
                            'nationality' => sanitizeInput($participant['nationality']),
                            'national_id' => sanitizeInput($participant['national_id']),
                            'passport_number' => sanitizeInput($participant['passport_number']),
                            'passport_file' => handleFileUpload($participant['passport_file'] ?? null) ?: '',
                            'requires_visa' => $participant['requires_visa'] ?? '',
                            'organization' => sanitizeInput($participant['organization']),
                            'institution' => sanitizeInput($participant['institution'] ?? ''),
                            'student_id_file' => handleFileUpload($participant['student_id_file'] ?? null) ?: '',
                            'delegate_category' => sanitizeInput($participant['delegate_category'] ?? ''),
                            'airport_of_origin' => sanitizeInput($participant['airport_of_origin'] ?? '')
                        ];
                    }
                }
            }
            
            if ($isSideEvent) {
                // For side events, just send confirmation email (no payment processing)
                if (sendSideEventConfirmationEmail($user, $registrationId, $package, $totalAmount)) {
                    $success = true;
                    $sideEventMessage = "Side event registration submitted successfully! You will receive a confirmation email. Payment will be processed after your event is approved.";
                } else {
                    $errors[] = "Registration created but failed to send confirmation email. Please contact support.";
                }
            } else if ($isExhibition) {
                // For exhibition packages, just send confirmation email (no payment processing)
                if (sendExhibitionConfirmationEmail($user, $registrationId, $package, $totalAmount)) {
                    $success = true;
                    $exhibitionMessage = "Exhibition registration submitted successfully! You will receive a confirmation email. Payment will be processed after your exhibition is approved.";
                } else {
                    $errors[] = "Registration created but failed to send confirmation email. Please contact support.";
                }
            } else if ($isFixedPricePackage) {
                // For fixed-price packages (Students, Delegates), send normal registration emails
                if (sendRegistrationEmails($user, $registrationId, $package, $totalAmount, $participants, $registrationType)) {
                    $success = true;
                } else {
                    $errors[] = "Registration created but failed to queue confirmation email. Please contact support.";
                }
            } else {
                // For regular registrations, send normal registration emails
                if (sendRegistrationEmails($user, $registrationId, $package, $totalAmount, $participants, $registrationType)) {
                    $success = true;
                    // Clean up old files after successful registration
                    cleanupOldFiles('uploads/passports/', 86400);
                    cleanupOldFiles('uploads/student_ids/', 86400);
                } else {
                    $errors[] = "Registration created but failed to queue confirmation email. Please contact support.";
                }
            }
        } else {
            $errors[] = "Invalid package selected";
        }
    }
        } // End rate limiting
    } // End CSRF protection

// Get all packages
$packages = getAllPackages();
$individualPackages = getPackagesByType('individual');
$sideEventPackages = getPackagesByType('side_event');
$exhibitionPackages = getPackagesByType('exhibition');

// Check if user has existing registrations (for display purposes)
$userEmail = '';
$registrationHistory = [];
if (isset($_GET['email']) && validateEmail($_GET['email'])) {
    $userEmail = sanitizeInput($_GET['email']);
    $registrationHistory = getRegistrationHistory($userEmail, CONFERENCE_DATES);
}

// Preserve form data on errors
$formData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)) {
    $formData = [
        'package_id' => $_POST['package_id'] ?? '',
        'registration_type' => $_POST['registration_type'] ?? '',
        'email' => $_POST['email'] ?? '',
        'title' => $_POST['title'] ?? '',
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'nationality' => $_POST['nationality'] ?? '',
        'national_id' => $_POST['national_id'] ?? '',
        'passport_number' => $_POST['passport_number'] ?? '',
        'passport_file' => $_FILES['passport_file']['name'] ?? '',
        'requires_visa' => $_POST['requires_visa'] ?? '',
        'organization' => $_POST['organization'] ?? '',
        'position' => $_POST['position'] ?? '',
        'institution' => $_POST['institution'] ?? '',
        'student_id_file' => $_FILES['student_id_file']['name'] ?? '',
        'delegate_category' => $_POST['delegate_category'] ?? '',
        'airport_of_origin' => $_POST['airport_of_origin'] ?? '',
        'address_line1' => substr(trim($_POST['address_line1'] ?? ''), 0, 60),
        'city' => substr(trim($_POST['city'] ?? ''), 0, 50),
        'state' => substr(trim($_POST['state'] ?? ''), 0, 50),
        'country' => cleanCountryName($_POST['country'] ?? ''),
        'num_people' => $_POST['num_people'] ?? '',
        'exhibition_description' => $_POST['exhibition_description'] ?? '',
        'participants' => $_POST['participants'] ?? []
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPHIA 2025 - Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Lobibox CSS -->
    <link href="https://cdn.jsdelivr.net/npm/lobibox@1.2.7/dist/css/lobibox.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Full width overrides */
        body {
            margin: 0 !important;
            padding: 0 !important;
        }
        .container {
            max-width: none !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .header {
            margin: 0 !important;
            padding: var(--spacing-8) 0 !important;
            width: 100% !important;
        }
        .header-content {
            max-width: none !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
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
        .package-selection-container,
        .registration-container {
            max-width: none !important;
            width: 100% !important;
            padding: var(--spacing-6) !important;
        }
        
        /* Package icon styles */
        .package-icon {
            transition: transform 0.3s ease;
        }
        
        .package-card:hover .package-icon {
            transform: scale(1.1);
        }
        
        .package-icon i {
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .selected-package-card .package-icon {
            text-align: center;
        }
        
        .selected-package-card .package-icon i {
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.15));
        }
        
        /* View Registrations Button Styles */
        .view-registrations-btn {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%) !important;
            border: none !important;
            font-weight: 600 !important;
            padding: 12px 24px !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 15px rgba(26, 86, 50, 0.3) !important;
            position: relative !important;
            overflow: hidden !important;
            color: var(--white) !important;
        }
        
        .view-registrations-btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(26, 86, 50, 0.4) !important;
            background: linear-gradient(135deg, var(--dark-green) 0%, #0d4f1c 100%) !important;
            color: var(--white) !important;
        }
        
        .view-registrations-btn:active {
            transform: translateY(0) !important;
        }
        
        .view-registrations-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .view-registrations-btn:hover::before {
            left: 100%;
        }
        
        /* Make only asterisks red and bold */
        .form-label span.asterisk,
        .form-label .required-asterisk,
        span.asterisk {
            color: #dc3545 !important;
            font-weight: bold;
        }
        
        /* Ensure asterisks are red regardless of parent */
        .asterisk {
            color: #dc3545 !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <?php include __DIR__ . '/home/header.php'; ?>

        <div style="padding: 0 5%;">

        <!-- Success/Error Messages -->
        <?php if (isset($success) && $success): ?>
            <?php 
            // Get user's registration history for display
            $userEmail = $userData['email'] ?? '';
            $userRegistrationHistory = [];
            if ($userEmail) {
                $userRegistrationHistory = getRegistrationHistory($userEmail, CONFERENCE_DATES);
            }
            ?>
            <div class="alert alert-success">
                <h3>Registration Successful!</h3>
                <p>Thank you for registering for CPHIA 2025. <?php if ($package['price'] > 0): ?>A payment link has been sent to your email address.<?php else: ?>Your registration is complete - no payment required.<?php endif; ?></p>
                
                <?php if (isset($registrationId) && !$isSideEvent && !$isExhibition && $package['price'] > 0): ?>
                    <div class="mt-4">
                        <h5>Complete Your Registration</h5>
                        <p class="mb-3">Choose how you'd like to complete your payment:</p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-credit-card fa-3x text-success mb-3"></i>
                                        <h6 class="card-title">Pay Now</h6>
                                        <p class="card-text small">Complete your payment immediately to finalize your registration.</p>
                                        <a href="registration_lookup.php?action=pay&id=<?php echo $registrationId; ?>" class="btn btn-success">
                                            <i class="fas fa-credit-card me-2"></i>Pay Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                                        <h6 class="card-title">Pay Later</h6>
                                        <p class="card-text small">We'll send you a payment link via email so you can pay when convenient.</p>
                                        <button onclick="sendPaymentLink(<?php echo $registrationId; ?>)" class="btn btn-outline-primary">
                                            <i class="fas fa-envelope me-2"></i>Send Payment Link
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <p class="small text-muted mb-2">
                                Registration ID: #<?php echo $registrationId; ?>
                            </p>
                        </div>
            </div>
        <?php endif; ?>
            </div>
            
            <!-- Duplicate Registration Alert -->
            <?php if (isset($isDuplicateRegistration) && $isDuplicateRegistration): ?>
                <div class="alert alert-warning">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="alert-heading">Duplicate Registration Detected</h4>
                            <p class="mb-3"><?php echo $duplicateMessage; ?></p>
                            
                            <div class="d-flex flex-wrap gap-2">
                                <a href="registration_lookup.php" class="btn btn-lg" style="background: var(--primary-green); border-color: var(--primary-green); color: white;">
                                    <i class="fas fa-eye me-2"></i>View My Registrations
                                </a>
                                
                                <?php if ($duplicateRegistrationStatus === 'pending'): ?>
                                    <a href="registration_lookup.php?action=pay&id=<?php echo $duplicateRegistrationId; ?>" class="btn btn-success">
                                        <i class="fas fa-credit-card me-2"></i>Complete Payment
                                    </a>
                                <?php endif; ?>
                                
                                <a href="mailto:support@cphia2025.com?subject=Registration%20Inquiry&body=Registration%20ID:%20%23<?php echo $duplicateRegistrationId; ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-envelope me-2"></i>Contact Support
                                </a>
                            </div>
                            
                            <hr class="my-3">
                            <small class="text-muted">
                                <strong>Need help?</strong> If you believe this is an error or need to make changes to your registration, 
                                please contact our support team with your registration ID: <strong>#<?php echo $duplicateRegistrationId; ?></strong>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Display User's Registration History -->
            <?php if (!empty($userRegistrationHistory)): ?>
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>Your Registration History for <?php echo CONFERENCE_SHORT_NAME; ?> (<?php echo CONFERENCE_DATES; ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($userRegistrationHistory as $registration): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">
                                                    <?php echo htmlspecialchars($registration['package_name']); ?>
                                                    <?php if ($registration['registration_type'] === 'group'): ?>
                                                        <span class="badge bg-info ms-2">
                                                            <i class="fas fa-users me-1"></i>Group
                                                        </span>
                                                    <?php endif; ?>
                                                </h6>
                                                <div class="d-flex flex-column align-items-end">
                                                    <?php 
                                                    $dbStatus = strtolower($registration['status'] ?? '');
                                                    $paymentStatus = $registration['payment_status'] ?? '';
                                                    $amount = $registration['total_amount'] ?? 0;
                                                    
                                                    if ($paymentStatus === 'completed'): ?>
                                                        <span class="badge bg-success mb-1">
                                                            <i class="fas fa-check-circle me-1"></i>Paid
                                                        </span>
                                                    <?php elseif ($amount == 0): ?>
                                                        <span class="badge bg-info mb-1">
                                                            <i class="fas fa-hourglass-half me-1"></i>Awaiting Approval
                                                        </span>
                                                    <?php elseif ($dbStatus === 'pending payment'): ?>
                                                        <span class="badge bg-warning mb-1">
                                                            <i class="fas fa-clock me-1"></i>Pending Payment
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary mb-1">
                                                            <i class="fas fa-info-circle me-1"></i><?php echo ucfirst($registration['status']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <small class="text-muted"><?php echo ucfirst($registration['status']); ?></small>
                                                </div>
                                            </div>
                                            
                                            <!-- Contact Information -->
                                            <div class="mb-2">
                                                <div class="text-muted small">
                                                    <div><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></div>
                                                    <div><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($registration['email']); ?></div>
                                                    <div><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($registration['phone']); ?></div>
                                                    <?php if (!empty($registration['organization'])): ?>
                                                        <div><i class="fas fa-building me-1"></i><?php echo htmlspecialchars($registration['organization']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Registration Details -->
                                            <div class="text-muted small mb-2">
                                                <div><strong>Registration ID:</strong> #<?php echo $registration['id']; ?></div>
                                                <div><strong>Date:</strong> <?php echo date('M j, Y', strtotime($registration['created_at'])); ?></div>
                                                <div><strong>Amount:</strong> <?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?></div>
                                                <?php if ($registration['registration_type'] === 'group'): ?>
                                                    <div class="text-info small">
                                                        <i class="fas fa-info-circle me-1"></i>Group registration - payment made by focal person
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <?php if ($registration['payment_status'] !== 'completed' && $registration['total_amount'] > 0): ?>
                                                <div class="mt-2">
                                                    <a href="registration_lookup.php?action=pay&id=<?php echo $registration['id']; ?>" class="btn btn-success btn-sm">
                                                        <i class="fas fa-credit-card me-1"></i>Complete Payment
                                                    </a>
                                                </div>
                                            <?php elseif ($registration['payment_status'] === 'completed'): ?>
                                                <div class="mt-2">
                                                    <a href="registration_lookup.php?action=receipt&id=<?php echo $registration['id']; ?>" class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-receipt me-1"></i>View Receipt
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <div class="mt-2">
                                                    <span class="badge bg-success">No Payment Required</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <a href="registration_lookup.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-search me-1"></i>Search All My Registrations
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>


        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h3>Please correct the following errors:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Package Selection (Initial View) -->
        <div class="package-selection-container" id="packageSelection" <?php echo (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST') ? 'style="display: none;"' : ''; ?>>
            <div class="text-center mb-4">
                <!-- Important Disclaimer -->
                <div class="alert alert-warning mb-4" style="text-align: left;">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h5>
                    <p class="mb-2"><strong>Please ensure you select the correct package for your registration.</strong></p>
                    <p class="mb-0">Selecting the wrong package may result in disqualification or additional fees. If you are unsure about which package to choose, please contact our support team at <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" class="text-decoration-none"><strong><?php echo SUPPORT_EMAIL; ?></strong></a> before proceeding.</p>
                </div>
                
                <a href="registration_lookup.php" class="btn btn-primary btn-lg view-registrations-btn">
                    <i class="fas fa-list-alt me-2"></i>View My Registrations
                </a>
            </div>
            
            <!-- Registration History Check -->
            <?php if (!empty($registrationHistory)): ?>
            <div class="alert alert-info mb-4">
                <h5><i class="fas fa-history me-2"></i>Your Registration History for <?php echo CONFERENCE_SHORT_NAME; ?> (<?php echo CONFERENCE_DATES; ?>)</h5>
                <p class="mb-3">You have previously registered for the following packages for this event:</p>
                <div class="row">
                    <?php foreach ($registrationHistory as $registration): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($registration['package_name']); ?></h6>
                                    <div class="d-flex flex-column align-items-end">
                                        <?php 
                                        $dbStatus = strtolower($registration['status'] ?? '');
                                        $paymentStatus = $registration['payment_status'] ?? '';
                                        $amount = $registration['total_amount'] ?? 0;
                                        
                                        if ($paymentStatus === 'completed'): ?>
                                            <span class="badge bg-success mb-1">
                                                <i class="fas fa-check-circle me-1"></i>Paid
                                    </span>
                                        <?php elseif ($amount == 0): ?>
                                            <span class="badge bg-info mb-1">
                                                <i class="fas fa-hourglass-half me-1"></i>Awaiting Approval
                                            </span>
                                        <?php elseif ($dbStatus === 'pending payment'): ?>
                                            <span class="badge bg-warning mb-1">
                                                <i class="fas fa-clock me-1"></i>Pending Payment
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary mb-1">
                                                <i class="fas fa-info-circle me-1"></i><?php echo ucfirst($registration['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <small class="text-muted"><?php echo ucfirst($registration['status']); ?></small>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    <div>Registration ID: #<?php echo $registration['id']; ?></div>
                                    <div>Date: <?php echo date('M j, Y', strtotime($registration['created_at'])); ?></div>
                                    <div>Amount: <?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?></div>
                                </div>
                                
                                <?php if ($registration['payment_status'] !== 'completed'): ?>
                                    <div class="mt-3">
                                        <a href="registration_lookup.php?action=pay&id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-credit-card me-1"></i>Complete Payment
                                        </a>
                                        <a href="registration_lookup.php?view=<?php echo $registration['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Registration Policy:</strong> You can register multiple times for different packages. 
                        Only <strong>paid registrations</strong> are considered confirmed for the conference. 
                        Unpaid registrations can be modified or cancelled.
                    </small>
                    <div class="mt-2">
                        <a href="registration_lookup.php" class="btn btn-sm" style="background: var(--primary-green); border-color: var(--primary-green); color: white;">
                            <i class="fas fa-eye me-1"></i>View Detailed Registration History
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Individual & Group Packages -->
            <div class="package-category">
                <h3>Registration Packages</h3>
               
                <!-- Individual Registration Row -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h4 class="text-center mb-3">Individual Registration</h4>
                    </div>
                    <?php foreach ($individualPackages as $package): ?>
                        <div class="col-6 col-md-3">
                            <div class="card package-card h-100" data-package-id="<?php echo $package['id']; ?>" data-type="<?php echo $package['type']; ?>" data-package-name="<?php echo htmlspecialchars($package['name']); ?>" data-continent="<?php echo htmlspecialchars($package['continent'] ?? 'all'); ?>">
                                <div class="card-body d-flex flex-column p-3 text-center">
                                    <div class="package-icon mb-3">
                                        <i class="<?php echo htmlspecialchars($package['icon'] ?? 'fas fa-ticket-alt'); ?> <?php echo htmlspecialchars($package['color'] ?? 'text-primary'); ?> fa-3x"></i>
                                    </div>
                                    <h5 class="card-title mb-3"><?php echo htmlspecialchars($package['name']); ?></h5>
                                    <?php if ($package['price'] > 0): ?>
                                    <div class="package-price h4 text-success mb-3"><?php echo formatCurrency($package['price']); ?></div>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-primary btn-lg select-package mt-auto">Register</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
         
            </div>

        </div>

        <!-- Registration Form (Hidden Initially) -->
        <div class="registration-container" id="registrationForm" <?php echo (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST') ? 'style="display: block;"' : 'style="display: none;"'; ?>>
            <div class="form-header">
                <div class="selected-package-info" id="selectedPackageInfo">
                    <?php if (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($formData['package_id'])): ?>
                        <?php 
                        $selectedPackage = getPackageById($formData['package_id']);
                        if ($selectedPackage): 
                        ?>
                            <div class="selected-package-card">
                                <div class="package-icon mb-2">
                                    <i class="<?php echo htmlspecialchars($selectedPackage['icon'] ?? 'fas fa-ticket-alt'); ?> <?php echo htmlspecialchars($selectedPackage['color'] ?? 'text-primary'); ?> fa-2x"></i>
                                </div>
                                <h4><?php echo htmlspecialchars($selectedPackage['name']); ?></h4>
                                <?php if ($selectedPackage['price'] > 0): ?>
                                    <div class="package-price"><?php echo formatCurrency($selectedPackage['price']); ?></div>
                                <?php endif; ?>
                                <div class="package-description mt-3" id="packageDescription" style="display: none;">
                                    <small class="text-muted" id="packageDescriptionText">Loading description...</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="mb-0">Complete Your Registration</h2>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-secondary" id="changePackage">
                        <i class="fas fa-arrow-left me-2"></i>Change Package
                    </button>
                </div>
            </div>
            
            <form id="registrationFormData" method="POST" class="registration-form" enctype="multipart/form-data">
                <input type="hidden" name="package_id" id="selectedPackageId" value="<?php echo htmlspecialchars($formData['package_id'] ?? ''); ?>" required>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <!-- Registration Type -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Registration Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="registration_type" value="individual" id="individual" <?php echo (($formData['registration_type'] ?? '') === 'individual') ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="individual">
                                        Individual Registration
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="registration_type" value="group" id="group" <?php echo (($formData['registration_type'] ?? '') === 'group') ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="group">
                                        Group Registration
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group Size (for group registration) -->
                <div class="card mb-4" id="groupSizeSection" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Number of People</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="numPeople" class="form-label">How many additional people are you registering (inlcuding yourself)?</label>
                                <input type="number" class="form-control form-control-lg" name="num_people" id="numPeople" min="1" max="40" placeholder="Enter number of people (Maximum 40)" value="<?php echo htmlspecialchars($formData['num_people'] ?? ''); ?>" style="font-size: 1.5rem; font-weight: bold;">
                                <div class="form-text">This will automatically add/remove participant fields below for easy cost estimation.</div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Cost Estimation</h6>
                                    <p class="mb-0" id="costEstimation">Enter number of people to see estimated cost (Maximum 40)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="title" class="form-label">Title</label>
                                <select name="title" id="title" class="form-select">
                                    <option value="">Select</option>
                                    <option value="Dr." <?php echo (($formData['title'] ?? '') === 'Dr.') ? 'selected' : ''; ?>>Dr.</option>
                                    <option value="Prof." <?php echo (($formData['title'] ?? '') === 'Prof.') ? 'selected' : ''; ?>>Prof.</option>
                                    <option value="Mr." <?php echo (($formData['title'] ?? '') === 'Mr.') ? 'selected' : ''; ?>>Mr.</option>
                                    <option value="Mrs." <?php echo (($formData['title'] ?? '') === 'Mrs.') ? 'selected' : ''; ?>>Mrs.</option>
                                    <option value="Ms." <?php echo (($formData['title'] ?? '') === 'Ms.') ? 'selected' : ''; ?>>Ms.</option>
                                </select>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="asterisk">*</span></label>
                                <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" id="phone" value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nationality" class="form-label">Nationality <span class="asterisk">*</span></label>
                                <select name="nationality" id="nationality" class="form-select">
                                    <option value="">Select Nationality</option>
                                    <?php
                                    // Load nationalities from database
                                    $nationalitiesData = getAllNationalities();
                                    if ($nationalitiesData) {
                                        foreach ($nationalitiesData as $nationality) {
                                            $selected = (isset($formData['nationality']) && $formData['nationality'] === $nationality['nationality']) ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($nationality['nationality']) . '" data-continent="' . htmlspecialchars($nationality['continent']) . '" ' . $selected . '>' . htmlspecialchars($nationality['country_name']) . ' (' . htmlspecialchars($nationality['nationality']) . ')</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="national_id" class="form-label">National ID</label>
                                <input type="text" class="form-control" name="national_id" id="national_id" value="<?php echo htmlspecialchars($formData['national_id'] ?? ''); ?>">
                                <div class="form-text">Optional - your national identification number</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="passport_number" class="form-label">Passport Number</label>
                                <input type="text" class="form-control" name="passport_number" id="passport_number" value="<?php echo htmlspecialchars($formData['passport_number'] ?? ''); ?>">
                                <div class="form-text">Optional - for international participants</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="passport_file" class="form-label">Passport Copy (PDF) <span class="asterisk">*</span></label>
                                <input type="file" class="form-control" name="passport_file" id="passport_file" accept=".pdf" value="<?php echo htmlspecialchars($formData['passport_file'] ?? ''); ?>">
                                <div class="form-text">Required for delegate registration - upload a clear copy of your passport (PDF format, max 5MB)</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Do you require a visa to enter South Africa? <span class="asterisk">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="requires_visa" id="visa_yes" value="1" <?php echo (($formData['requires_visa'] ?? '') === '1') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="visa_yes">
                                        Yes
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="requires_visa" id="visa_no" value="0" <?php echo (($formData['requires_visa'] ?? '') === '0') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="visa_no">
                                        No
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!-- Organization and Position fields (hidden for Students) -->
                        <div id="organizationFields" class="row">
                            <div class="col-md-6 mb-3">
                                <label for="organization" class="form-label">Organization <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="organization" id="organization" value="<?php echo htmlspecialchars($formData['organization'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">Position/Title</label>
                                <input type="text" class="form-control" name="position" id="position" value="<?php echo htmlspecialchars($formData['position'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- 1and Airport fields (only for Delegates package) - Right column -->
                        <div id="delegateFields" class="row" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <!-- Empty left column for delegates -->
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="delegate_category" class="form-label">Delegate Category <span class="asterisk">*</span></label>
                                <select class="form-select" name="delegate_category" id="delegate_category">
                                    <option value="">Select Category</option>
                                    <option value="Oral abstract presenter" <?php echo (($formData['delegate_category'] ?? '') === 'Oral abstract presenter') ? 'selected' : ''; ?>>Oral abstract presenter</option>
                                    <option value="Invited speaker/Moderator" <?php echo (($formData['delegate_category'] ?? '') === 'Invited speaker/Moderator') ? 'selected' : ''; ?>>Invited speaker/Moderator</option>
                                    <option value="Scientific Program Committee Member" <?php echo (($formData['delegate_category'] ?? '') === 'Scientific Program Committee Member') ? 'selected' : ''; ?>>Scientific Program Committee Member</option>
                                    <option value="Secretariat" <?php echo (($formData['delegate_category'] ?? '') === 'Secretariat') ? 'selected' : ''; ?>>Secretariat</option>
                                    <option value="Media Partner" <?php echo (($formData['delegate_category'] ?? '') === 'Media Partner') ? 'selected' : ''; ?>>Media Partner</option>
                                    <option value="Side event focal person" <?php echo (($formData['delegate_category'] ?? '') === 'Side event focal person') ? 'selected' : ''; ?>>Side event focal person</option>
                                    <option value="Youth Program Participant" <?php echo (($formData['delegate_category'] ?? '') === 'Youth Program Participant') ? 'selected' : ''; ?>>Youth Program Participant</option>
                                    <option value="Exhibition Focal Person (Bronze+)" <?php echo (($formData['delegate_category'] ?? '') === 'Exhibition Focal Person') ? 'selected' : ''; ?>>Exhibition Focal Person (Bronze+)</option>
                                    <option value="Journalist" <?php echo (($formData['delegate_category'] ?? '') === 'Journalist') ? 'selected' : ''; ?>>Journalist</option>
                                    <option value="Interpreter/Translator" <?php echo (($formData['delegate_category'] ?? '') === 'Interpreter/Translator') ? 'selected' : ''; ?>>Interpreter/Translator</option>
                                    
                                </select>
                                <div class="form-text">Required for delegate registration</div>
                            </div>
                        </div>
                        
                        <!-- Airport of Origin field (only for Delegates package) - Right column -->
                        <div id="airportFields" class="row" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <!-- Empty left column for delegates -->
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="airport_of_origin" class="form-label">Airport of Origin <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="airport_of_origin" id="airport_of_origin" value="<?php echo htmlspecialchars($formData['airport_of_origin'] ?? ''); ?>" placeholder="Enter your departure airport">
                                <div class="form-text">Required for delegate registration - for travel planning purposes</div>
                            </div>
                        </div>
                        
                        <!-- Student Fields (only for Students package) -->
                        <div id="studentFields" class="row" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="institution" class="form-label">Institution/School <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="institution" id="institution" value="<?php echo htmlspecialchars($formData['institution'] ?? ''); ?>" placeholder="Enter your institution or school name">
                                <div class="form-text">Required for student registration</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="student_id_file" class="form-label">Student ID Document <span class="asterisk">*</span></label>
                                <input type="file" class="form-control" name="student_id_file" id="student_id_file" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Required for student registration (PDF, JPG, PNG - max 5MB)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Address Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Billing Address Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address_line1" class="form-label">Address <span class="asterisk">*</span></label>
                            <input type="text" class="form-control" name="address_line1" id="address_line1" value="<?php echo htmlspecialchars($formData['address_line1'] ?? ''); ?>" maxlength="60" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="city" id="city" value="<?php echo htmlspecialchars($formData['city'] ?? ''); ?>" maxlength="50" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State/Province <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="state" id="state" value="<?php echo htmlspecialchars($formData['state'] ?? ''); ?>" maxlength="50" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country <span class="asterisk">*</span></label>
                                <select class="form-control" name="country" id="country" required>
                                    <option value="">Select Country</option>
                                    <?php
                                    // Load countries from database
                                    $countriesData = getAllCountries();
                                    if ($countriesData) {
                                        foreach ($countriesData as $country) {
                                            $selected = (isset($formData['country']) && $formData['country'] === $country['name']) ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($country['name']) . '" ' . $selected . '>' . htmlspecialchars($country['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exhibition Description (for exhibition packages) -->
                <div class="card mb-4" id="exhibitionDescriptionSection" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Exhibition Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="exhibition_description" class="form-label">Description of What You Will Exhibit (Optional)</label>
                            <textarea class="form-control" name="exhibition_description" id="exhibition_description" rows="4" placeholder="Please describe what you plan to exhibit at the conference... (optional)"><?php echo htmlspecialchars($formData['exhibition_description'] ?? ''); ?></textarea>
                            <div class="form-text">Provide a detailed description of your exhibition, including products, services, or information you will showcase. This field is optional.</div>
                        </div>
                    </div>
                </div>

                <!-- Group Participants (for group registration) -->
                <div class="card mb-4" id="participantsSection" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Additional Group Participants (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="addParticipantsNow" style="display: none !important;">
                                <label class="form-check-label" for="addParticipantsNow" style="display: none !important;">
                                    <!-- Add participant details now (optional) -->
                                </label>
                            </div>
                            <!-- <div class="form-text">You can skip this step and provide participant details later via email. You will receive an email with your allocated slots.</div> -->
                        </div>
                        
                        <div id="participantsDetails" style="display: none;">
                            <p class="text-muted">Please provide details for additional participants in your group (excluding yourself)</p>
                            <div id="participantsContainer">
                                <!-- Additional participants will be added automatically based on number of people -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card mb-4" id="summarySection" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Registration Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Package:</span>
                                    <span id="summaryPackage">-</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Registration Type:</span>
                                    <span id="summaryType">-</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Number of People:</span>
                                    <span id="summaryPeople">-</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2 fw-bold fs-5 text-success">
                                    <span>Total Amount:</span>
                                    <span id="summaryTotal">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Protection Disclaimer -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Data Protection & Privacy</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="dataProtectionConsent" required>
                            <label class="form-check-label" for="dataProtectionConsent">
                                I consent to the processing of my personal data in accordance with the 
                                <strong>Africa CDC and African Union Data Protection Policy</strong>. 
                                I understand that my personal information will be used solely for the purpose of 
                                conference registration and related communications, and will be protected according to 
                                established data protection standards.
                            </label>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <strong>Data Protection Notice:</strong> Your personal data is collected and processed 
                                in accordance with the Africa CDC and African Union Data Protection Policy. 
                                We are committed to protecting your privacy and ensuring the security of your personal information.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- reCAPTCHA -->
                <?php if (isRecaptchaEnabled()): ?>
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY); ?>"></div>
                        <small class="text-muted mt-2 d-block">
                            Please complete the reCAPTCHA verification to proceed with registration.
                        </small>
                    </div>
                </div>
                <?php endif; ?>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-lg">Register & Continue to Payment</button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <?php include __DIR__ . '/home/scripts.php'; ?>
    
    <!-- Footer -->
    <?php include __DIR__ . '/home/footer.php'; ?>
</body>
</html>
