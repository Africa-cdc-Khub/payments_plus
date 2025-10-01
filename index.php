<?php
require_once 'bootstrap.php';
require_once 'functions.php';

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src \'self\' https://fonts.gstatic.com; img-src \'self\' data: https://www.google.com; connect-src \'self\' https://www.google.com; frame-src \'self\' https://www.google.com;');

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
        if (!checkRateLimit($clientIp, 'registration', 5, 3600)) {
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
    
    if (!empty($_POST['nationality']) && !validateNationality($_POST['nationality'])) {
        $errors[] = "Please select a valid nationality";
    }
    
    if (!empty($_POST['phone']) && !validatePhoneNumber($_POST['phone'])) {
        $errors[] = "Please enter a valid phone number";
    }
    
    if (!empty($_POST['postal_code']) && !validatePostalCode($_POST['postal_code'])) {
        $errors[] = "Please enter a valid postal code";
    }
    
    if (!empty($_POST['passport_number']) && !validatePassportNumber($_POST['passport_number'])) {
        $errors[] = "Please enter a valid passport number";
    }
    
    if (!empty($_POST['organization']) && !validateOrganization($_POST['organization'])) {
        $errors[] = "Please enter a valid organization name";
    }
    
    if (!empty($_POST['exhibition_description']) && !validateExhibitionDescription($_POST['exhibition_description'])) {
        $errors[] = "Please enter a valid exhibition description (10-1000 characters)";
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
            $errors[] = getDuplicateRegistrationMessage($duplicateCheck);
            logSecurityEvent('duplicate_registration_attempt', 'Duplicate registration attempt for email: ' . $_POST['email'] . ', package: ' . $_POST['package_id'] . ', event: ' . CONFERENCE_DATES);
        }
    }
    
    if (empty($errors)) {
        // Get package details
        $package = getPackageById($_POST['package_id']);
        
        if ($package) {
            // Prepare user data
            $userData = [
                'email' => sanitizeInput($_POST['email']),
                'first_name' => sanitizeInput($_POST['first_name']),
                'last_name' => sanitizeInput($_POST['last_name']),
                'phone' => sanitizeInput($_POST['phone']),
                'nationality' => sanitizeInput($_POST['nationality']),
                'organization' => sanitizeInput($_POST['organization']),
                'address_line1' => sanitizeInput($_POST['address_line1']),
                'address_line2' => sanitizeInput($_POST['address_line2']),
                'city' => sanitizeInput($_POST['city']),
                'state' => sanitizeInput($_POST['state']),
                'country' => sanitizeInput($_POST['country']),
                'postal_code' => sanitizeInput($_POST['postal_code'])
            ];
            
            // Get or create user
            $user = getOrCreateUser($userData);
            
            // Calculate total amount based on African status
            $nationality = sanitizeInput($_POST['nationality']);
            $isAfrican = isAfricanNational($nationality);
            
            // Get the selected package
            $package = getPackageById($_POST['package_id']);
            
            // Check if this is a side event package (group type but for side events)
            $isSideEvent = ($package['type'] === 'group' && in_array($package['id'], [3, 4])); // Side event package IDs
            
            // Check if this is an exhibition package
            $isExhibition = ($package['type'] === 'exhibition');
            
            if ($isSideEvent) {
                // Side event - individual registration only, use exact package price
                $totalAmount = $package['price']; // Use exact package price, not nationality-based
                $registrationType = 'individual'; // Force individual for side events
            } else if ($isExhibition) {
                // Exhibition package - individual registration only, use exact package price
                $totalAmount = $package['price']; // Use exact package price, not nationality-based
                $registrationType = 'individual'; // Force individual for exhibition packages
            } else if ($_POST['registration_type'] === 'individual') {
                // Regular individual registration - use African/Non-African pricing
                if ($isAfrican) {
                    $package = getPackageById(1); // African Nationals package
                } else {
                    $package = getPackageById(2); // Non-African nationals package
                }
                $totalAmount = $package['price'];
                $registrationType = 'individual';
            } else if ($_POST['registration_type'] === 'group' && isset($_POST['num_people'])) {
                // Regular group registration
                $numPeople = (int)$_POST['num_people'];
                
                // Check if any participants are non-African
                $hasNonAfricanParticipants = false;
                if (isset($_POST['participants'])) {
                    foreach ($_POST['participants'] as $participant) {
                        if (!empty($participant['nationality']) && !isAfricanNational($participant['nationality'])) {
                            $hasNonAfricanParticipants = true;
                            break;
                        }
                    }
                }
                
                // Use non-African pricing if any participant is non-African
                if ($hasNonAfricanParticipants || !$isAfrican) {
                    $package = getPackageById(2); // Non-African nationals package
                } else {
                    $package = getPackageById(1); // African Nationals package
                }
                
                $totalAmount = $package['price'] * $numPeople;
                $registrationType = 'group';
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
            
            // Handle group participants (not for side events or exhibition packages)
            if ($_POST['registration_type'] === 'group' && isset($_POST['participants']) && !$isSideEvent && !$isExhibition) {
                $participants = [];
                foreach ($_POST['participants'] as $participant) {
                    if (!empty($participant['first_name']) && !empty($participant['last_name']) && !empty($participant['email'])) {
                        $participants[] = [
                            'title' => sanitizeInput($participant['title']),
                            'first_name' => sanitizeInput($participant['first_name']),
                            'last_name' => sanitizeInput($participant['last_name']),
                            'email' => sanitizeInput($participant['email']),
                            'nationality' => sanitizeInput($participant['nationality']),
                            'passport_number' => sanitizeInput($participant['passport_number']),
                            'organization' => sanitizeInput($participant['organization'])
                        ];
                    }
                }
                createRegistrationParticipants($registrationId, $participants);
            }
            
            // Send registration emails
            $participants = [];
            if ($_POST['registration_type'] === 'group' && isset($_POST['participants']) && !$isSideEvent && !$isExhibition) {
                foreach ($_POST['participants'] as $participant) {
                    if (!empty($participant['first_name']) && !empty($participant['last_name']) && !empty($participant['email'])) {
                        $participants[] = [
                            'name' => sanitizeInput($participant['title']) . ' ' . sanitizeInput($participant['first_name']) . ' ' . sanitizeInput($participant['last_name']),
                            'email' => sanitizeInput($participant['email']),
                            'nationality' => sanitizeInput($participant['nationality']),
                            'passport_number' => sanitizeInput($participant['passport_number']),
                            'organization' => sanitizeInput($participant['organization'])
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
            } else {
                // For regular registrations, send normal registration emails
                if (sendRegistrationEmails($user, $registrationId, $package, $totalAmount, $participants)) {
                    $success = true;
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
}

// Get all packages
$packages = getAllPackages();
$individualPackages = getPackagesByType('individual');
$groupPackages = getPackagesByType('group');
$exhibitionPackages = getPackagesByType('exhibition');

// Check if user has existing registrations (for display purposes)
$userEmail = '';
$registrationHistory = [];
if (isset($_GET['email']) && validateEmail($_GET['email'])) {
    $userEmail = sanitizeInput($_GET['email']);
    $registrationHistory = getRegistrationHistory($userEmail, CONFERENCE_DATES);
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

        <!-- Success/Error Messages -->
        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success">
                <h3>Registration Successful!</h3>
                <p>Thank you for registering for CPHIA 2025. A payment link has been sent to your email address.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h3>Please correct the following errors:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Package Selection (Initial View) -->
        <div class="package-selection-container" id="packageSelection">
            <h2>Select Your Registration Package</h2>
            <p>Choose the package that best fits your needs for the 4th International Conference on Public Health in Africa</p>
            
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
                                    <span class="badge bg-<?php echo $registration['status'] == 'paid' ? 'success' : ($registration['status'] == 'pending' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst($registration['status']); ?>
                                    </span>
                                </div>
                                <div class="text-muted small">
                                    <div>Registration ID: #<?php echo $registration['id']; ?></div>
                                    <div>Date: <?php echo date('M j, Y', strtotime($registration['created_at'])); ?></div>
                                    <div>Amount: <?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        If you try to register for the same package again, you will receive a duplicate registration warning.
                    </small>
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
                        <div class="col-6 col-md-6">
                            <div class="card package-card h-100" data-package-id="<?php echo $package['id']; ?>" data-type="individual">
                                <div class="card-body d-flex flex-column p-3 text-center">
                                    <h5 class="card-title mb-3"><?php echo htmlspecialchars($package['name']); ?></h5>
                                    <div class="package-price h4 text-success mb-3"><?php echo formatCurrency($package['price']); ?></div>
                                    <div class="badge bg-info mb-3"><?php echo $package['id'] == 1 ? 'African Nationals' : 'Non-African Nationals'; ?></div>
                                    <button type="button" class="btn btn-primary btn-lg select-package mt-auto">Select Package</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- side event Registration Row -->
                <div class="row g-3">
                    <div class="col-12">
                        <h4 class="text-center mb-3">Side Events Registration</h4>
                    </div>
                    <?php foreach ($groupPackages as $package): ?>
                        <div class="col-6 col-md-6">
                            <div class="card package-card h-100" data-package-id="<?php echo $package['id']; ?>" data-type="group">
                                <div class="card-body d-flex flex-column p-3 text-center">
                                    <h5 class="card-title mb-3"><?php echo htmlspecialchars($package['name']); ?></h5>
                                    <div class="package-price h4 text-success mb-3"><?php echo formatCurrency($package['price']); ?></div>
                                    <button type="button" class="btn btn-primary btn-lg select-package mt-auto">Select Package</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Exhibition Packages -->
            <div class="package-category">
                <div class="row g-3">
                    <div class="col-12">
                        <h3 class="text-center">Exhibition Packages</h3>
                    </div>
                    <?php foreach ($exhibitionPackages as $package): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="card package-card h-100" data-package-id="<?php echo $package['id']; ?>" data-type="exhibition">
                                <div class="card-body d-flex flex-column p-3 text-center">
                                    <h5 class="card-title mb-3"><?php echo htmlspecialchars($package['name']); ?></h5>
                                    <div class="package-price h4 text-success mb-3"><?php echo formatCurrency($package['price']); ?></div>
                                    <button type="button" class="btn btn-primary btn-lg select-package mt-auto">Select Package</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Registration Form (Hidden Initially) -->
        <div class="registration-container" id="registrationForm" style="display: none;">
            <div class="form-header">
                <div class="selected-package-info" id="selectedPackageInfo"></div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-8 text-center">
                    <h2 class="mb-0">Complete Your Registration</h2>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-secondary" id="changePackage">
                        <i class="fas fa-arrow-left me-2"></i>Change Package
                    </button>
                </div>
            </div>
            
            <form id="registrationFormData" method="POST" class="registration-form">
                <input type="hidden" name="package_id" id="selectedPackageId" required>
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
                                    <input class="form-check-input" type="radio" name="registration_type" value="individual" id="individual" required>
                                    <label class="form-check-label" for="individual">
                                        Individual Registration
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="registration_type" value="group" id="group" required>
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
                                <input type="number" class="form-control form-control-lg" name="num_people" id="numPeople" min="1" placeholder="Enter number of people" style="font-size: 1.5rem; font-weight: bold;">
                                <div class="form-text">This will automatically add/remove participant fields below for easy cost estimation.</div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Cost Estimation</h6>
                                    <p class="mb-0" id="costEstimation">Enter number of people to see estimated cost</p>
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
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" id="last_name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" id="phone">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nationality" class="form-label">Nationality *</label>
                                <select name="nationality" id="nationality" class="form-select" required>
                                    <option value="">Select Nationality</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="passport_number" class="form-label">Passport Number</label>
                                <input type="text" class="form-control" name="passport_number" id="passport_number">
                                <div class="form-text">Optional - for international participants</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="organization" class="form-label">Organization *</label>
                                <input type="text" class="form-control" name="organization" id="organization" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">Position/Title</label>
                                <input type="text" class="form-control" name="position" id="position">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Address Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" name="address_line1" id="address_line1">
                        </div>
                        <div class="mb-3">
                            <label for="address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" name="address_line2" id="address_line2">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" name="city" id="city">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" name="state" id="state">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" name="country" id="country">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" name="postal_code" id="postal_code">
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
                            <label for="exhibition_description" class="form-label">Description of What You Will Exhibit *</label>
                            <textarea class="form-control" name="exhibition_description" id="exhibition_description" rows="4" placeholder="Please describe what you plan to exhibit at the conference..."></textarea>
                            <div class="form-text">Provide a detailed description of your exhibition, including products, services, or information you will showcase.</div>
                        </div>
                    </div>
                </div>

                <!-- Group Participants (for group registration) -->
                <div class="card mb-4" id="participantsSection" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Group Participants (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="addParticipantsNow">
                                <label class="form-check-label" for="addParticipantsNow">
                                    Add participant details now (optional)
                                </label>
                            </div>
                            <div class="form-text">You can skip this step and provide participant details later via email. You will receive an email with your allocated slots.</div>
                        </div>
                        
                        <div id="participantsDetails" style="display: none;">
                            <p class="text-muted">Please provide details for each participant in your group</p>
                            <div id="participantsContainer">
                                <!-- Participants will be added automatically based on number of people -->
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Lobibox JS -->
    <script src="https://cdn.jsdelivr.net/npm/lobibox@1.2.7/dist/js/lobibox.min.js"></script>
    <!-- reCAPTCHA JS -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <!-- Custom JS -->
    <script src="js/registration.js"></script>
</body>
</html>
