<?php
require_once 'bootstrap.php';
require_once 'db_connector.php';

// Load Composer autoloader for EmailService
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Cphia2025\EmailService;

// Package functions
function getAllPackages() {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE is_active = 1 ORDER BY type, price");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPackageById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getPackagesByType($type) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE type = ? AND is_active = 1 ORDER BY price");
    $stmt->execute([$type]);
    return $stmt->fetchAll();
}

// File upload functions
function handleFileUpload($file, $uploadDir = 'uploads/passports/') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type
    $allowedTypes = ['application/pdf'];
    $fileType = mime_content_type($file['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        return false;
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

// User functions
function createUser($data) {
    $pdo = getConnection();
    $sql = "INSERT INTO users (email, title, first_name, last_name, phone, nationality, passport_number, 
            passport_file, requires_visa, organization, position, address_line1, city, state, country) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    // Convert requires_visa from yes/no to 1/0
    $requiresVisa = 0;
    if (isset($data['requires_visa']) && $data['requires_visa'] === 'yes') {
        $requiresVisa = 1;
    }
    
    $stmt->execute([
        $data['email'], $data['title'] ?? '', $data['first_name'], $data['last_name'], 
        $data['phone'], $data['nationality'], $data['passport_number'] ?? '', 
        $data['passport_file'] ?? '', $requiresVisa, $data['organization'],
        $data['position'] ?? '', $data['address_line1'], $data['city'], 
        $data['state'] ?? '', $data['country']
    ]);
    return $pdo->lastInsertId();
}

function getUserByEmail($email) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function getOrCreateUser($data) {
    $user = getUserByEmail($data['email']);
    if (!$user) {
        createUser($data);
        $user = getUserByEmail($data['email']);
    }
    return $user;
}

// Registration functions
function createRegistration($data) {
    $pdo = getConnection();
    $sql = "INSERT INTO registrations (user_id, package_id, registration_type, total_amount, currency, exhibition_description, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['user_id'], $data['package_id'], $data['registration_type'], 
        $data['total_amount'], $data['currency'], $data['exhibition_description'] ?? null,
        'pending' // All new registrations start with pending payment status
    ]);
    return $pdo->lastInsertId();
}

function createRegistrationParticipants($registrationId, $participants) {
    $pdo = getConnection();
    $sql = "INSERT INTO registration_participants (registration_id, title, first_name, last_name, email, nationality, passport_number, passport_file, requires_visa, organization) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($participants as $participant) {
        // Convert requires_visa from yes/no to 1/0
        $requiresVisa = 0;
        if (isset($participant['requires_visa']) && $participant['requires_visa'] === 'yes') {
            $requiresVisa = 1;
        }
        
        $stmt->execute([
            $registrationId, $participant['title'], $participant['first_name'], 
            $participant['last_name'], $participant['email'], $participant['nationality'],
            $participant['passport_number'] ?? '', $participant['passport_file'] ?? '', 
            $requiresVisa, $participant['organization'] ?? ''
        ]);
    }
}

function getRegistrationById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT r.*, p.name as package_name, p.price as package_price, 
                          u.first_name, u.last_name, u.email as user_email 
                          FROM registrations r 
                          JOIN packages p ON r.package_id = p.id 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getRegistrationParticipants($registrationId) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM registration_participants WHERE registration_id = ?");
    $stmt->execute([$registrationId]);
    return $stmt->fetchAll();
}

function updateRegistrationStatus($id, $status, $paymentReference = null) {
    $pdo = getConnection();
    $sql = "UPDATE registrations SET status = ?, payment_reference = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$status, $paymentReference, $id]);
}

// Payment functions
function createPayment($data) {
    $pdo = getConnection();
    $sql = "INSERT INTO payments (registration_id, amount, currency, transaction_uuid, payment_status) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['registration_id'], $data['amount'], $data['currency'], 
        $data['transaction_uuid'], $data['payment_status']
    ]);
}

function updatePaymentStatus($transactionUuid, $status, $paymentReference = null) {
    $pdo = getConnection();
    $sql = "UPDATE payments SET payment_status = ?, payment_reference = ?, payment_date = NOW() 
            WHERE transaction_uuid = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$status, $paymentReference, $transactionUuid]);
}

// Utility functions
function formatCurrency($amount, $currency = 'USD') {
    return '$' . number_format($amount, 2);
}

function generatePaymentToken($registrationId) {
    $token = base64_encode($registrationId . '_' . time() . '_' . rand(1000, 9999));
    
    // Store the token in the database
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE registrations SET payment_token = ? WHERE id = ?");
    $stmt->execute([$token, $registrationId]);
    
    return $token;
}

// Email notification functions using EmailQueue
function sendRegistrationEmails($user, $registrationId, $package, $amount, $participants = []) {
    $emailQueue = new \Cphia2025\EmailQueue();
    $success = true;

    // Queue registration confirmation to user
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    
    // Generate payment status link (no payment link in registration email)
    $paymentStatusLink = rtrim(APP_URL, '/') . "/payment_status.php?id=" . $registrationId . "&email=" . urlencode($user['email']);
    $paymentStatus = 'pending'; // All new registrations start as pending
    
    $templateData = [
        'user_name' => $userName,
        'registration_id' => $registrationId,
        'package_name' => $package['name'],
        'amount' => $amount,
        'participants' => $participants,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL,
        'payment_status_link' => $paymentStatusLink,
        'payment_status' => $paymentStatus
    ];

    $result = $emailQueue->addToQueue(
        $user['email'],
        $userName,
        CONFERENCE_SHORT_NAME . " - Registration Confirmation #" . $registrationId,
        'registration_confirmation',
        $templateData,
        'registration_confirmation',
        5
    );

    if (!$result) {
        $success = false;
        error_log("Failed to queue registration confirmation to: " . $user['email']);
    }

    // Queue admin notification
    $adminTemplateData = [
        'registration_id' => $registrationId,
        'user_name' => $userName,
        'user_email' => $user['email'],
        'package_name' => $package['name'],
        'amount' => $amount,
        'registration_type' => 'individual',
        'participants' => $participants,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'admin_name' => ADMIN_NAME,
        'logo_url' => EMAIL_LOGO_URL
    ];

    $result = $emailQueue->addToQueue(
        ADMIN_EMAIL,
        ADMIN_NAME,
        "New Registration: #" . $registrationId . " - " . $userName,
        'admin_registration_notification',
        $adminTemplateData,
        'admin_registration',
        3
    );

    if (!$result) {
        $success = false;
        error_log("Failed to queue admin registration notification");
    }

    return $success;
}

function sendPaymentLinkEmail($user, $registrationId, $amount) {
    $emailQueue = new \Cphia2025\EmailQueue();
    $paymentToken = generatePaymentToken($registrationId);
    $baseUrl = APP_URL . dirname($_SERVER['PHP_SELF']);
    $paymentLink = $baseUrl . "/sa-wm/payment_confirm.php?registration_id=" . $registrationId . "&token=" . $paymentToken;

    $userName = $user['first_name'] . ' ' . $user['last_name'];
    
    $templateData = [
        'user_name' => $userName,
        'registration_id' => $registrationId,
        'amount' => $amount,
        'payment_link' => $paymentLink,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL
    ];

    return $emailQueue->addToQueue(
        $user['email'],
        $userName,
        CONFERENCE_SHORT_NAME . " - Payment Required #" . $registrationId,
        'payment_link',
        $templateData,
        'payment_link',
        4
    );
}

function sendPaymentConfirmationEmails($user, $registrationId, $amount, $transactionId, $participants = []) {
    $emailQueue = new \Cphia2025\EmailQueue();
    $success = true;

    // Queue confirmation to user
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    $templateData = [
        'user_name' => $userName,
        'registration_id' => $registrationId,
        'amount' => $amount,
        'transaction_id' => $transactionId,
        'participants' => $participants,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL
    ];

    $result = $emailQueue->addToQueue(
        $user['email'],
        $userName,
        CONFERENCE_SHORT_NAME . " - Payment Confirmed #" . $registrationId,
        'payment_confirmation',
        $templateData,
        'payment_confirmation',
        5
    );

    if (!$result) {
        $success = false;
        error_log("Failed to queue payment confirmation to: " . $user['email']);
    }

    // Queue admin notification
    $adminTemplateData = [
        'registration_id' => $registrationId,
        'user_name' => $userName,
        'user_email' => $user['email'],
        'amount' => $amount,
        'transaction_id' => $transactionId,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'admin_name' => ADMIN_NAME,
        'logo_url' => EMAIL_LOGO_URL
    ];

    $result = $emailQueue->addToQueue(
        ADMIN_EMAIL,
        ADMIN_NAME,
        "Payment Confirmed: #" . $registrationId . " - " . $userName,
        'admin_payment_notification',
        $adminTemplateData,
        'admin_payment',
        3
    );

    if (!$result) {
        $success = false;
        error_log("Failed to queue admin payment notification");
    }

    return $success;
}

// Legacy function for backward compatibility
function sendPaymentEmail($email, $registrationId, $amount) {
    // Get user data
    $user = getUserByEmail($email);
    if (!$user) {
        return false;
    }
    
    return sendPaymentLinkEmail($user, $registrationId, $amount);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateReferenceNumber() {
    return 'CPHIA' . date('Y') . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

// Enhanced input validation functions
function validatePackageId($id) {
    return is_numeric($id) && $id > 0;
}

function validateRegistrationType($type) {
    return in_array($type, ['individual', 'group']);
}

function validateNationality($nationality) {
    // Check if nationality is in our allowed list
    $allowedNationalities = [
        'Algerian', 'Angolan', 'Beninese', 'Botswanan', 'Burkinabe', 'Burundian',
        'Cameroonian', 'Cape Verdian', 'Central African', 'Chadian', 'Comoran',
        'Congolese', 'Ivorian', 'Djibouti', 'Egyptian', 'Equatorial Guinean',
        'Eritrean', 'Ethiopian', 'Gabonese', 'Gambian', 'Ghanaian', 'Guinean',
        'Guinea-Bissauan', 'Kenyan', 'Lesotho', 'Liberian', 'Libyan', 'Malagasy',
        'Malawian', 'Malian', 'Mauritanian', 'Mauritian', 'Moroccan', 'Mozambican',
        'Namibian', 'Nigerien', 'Nigerian', 'Rwandan', 'Sao Tomean', 'Senegalese',
        'Seychellois', 'Sierra Leonean', 'Somali', 'South African', 'South Sudanese',
        'Sudanese', 'Swazi', 'Tanzanian', 'Togolese', 'Tunisian', 'Ugandan',
        'Zambian', 'Zimbabwean', 'Motswana', 'Mosotho', 'American', 'British',
        'Canadian', 'French', 'German', 'Italian', 'Spanish', 'Chinese', 'Japanese',
        'Indian', 'Brazilian', 'Australian', 'Other'
    ];
    return in_array($nationality, $allowedNationalities);
}

function validatePhoneNumber($phone) {
    // Allow international phone numbers with +, digits, spaces, hyphens, and parentheses
    return preg_match('/^[\+]?[0-9\s\-\(\)]{7,20}$/', $phone);
}

function validatePostalCode($postalCode) {
    // Allow alphanumeric postal codes with spaces and hyphens
    return preg_match('/^[A-Za-z0-9\s\-]{3,10}$/', $postalCode);
}

function validatePassportNumber($passportNumber) {
    // Allow alphanumeric passport numbers
    return preg_match('/^[A-Za-z0-9]{6,20}$/', $passportNumber);
}

function validateOrganization($organization) {
    // Allow letters, numbers, spaces, hyphens, and common punctuation
    return preg_match('/^[A-Za-z0-9\s\-\.\,\&\(\)]{2,100}$/', $organization);
}

function validateExhibitionDescription($description) {
    // Allow letters, numbers, spaces, and common punctuation
    // More lenient validation for optional field - allow shorter descriptions
    return preg_match('/^[A-Za-z0-9\s\-\.\,\!\?\:\;\(\)]{5,1000}$/', $description);
}

// Rate limiting functions
function checkRateLimit($ip, $action = 'registration', $maxAttempts = 5, $timeWindow = 3600) {
    // Skip rate limiting in development mode
    if (defined('APP_DEBUG') && APP_DEBUG && $ip === '::1') {
        return true;
    }
    $pdo = getConnection();
    
    // Create rate_limits table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        action VARCHAR(50) NOT NULL,
        attempts INT DEFAULT 1,
        first_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_ip_action (ip_address, action),
        INDEX idx_last_attempt (last_attempt)
    )");
    
    // Clean old records (older than time window)
    $pdo->prepare("DELETE FROM rate_limits WHERE last_attempt < DATE_SUB(NOW(), INTERVAL ? SECOND)")
         ->execute([$timeWindow]);
    
    // Check current attempts
    $stmt = $pdo->prepare("SELECT attempts FROM rate_limits WHERE ip_address = ? AND action = ?");
    $stmt->execute([$ip, $action]);
    $result = $stmt->fetch();
    
    if ($result) {
        if ($result['attempts'] >= $maxAttempts) {
            return false; // Rate limit exceeded
        }
        // Increment attempts
        $pdo->prepare("UPDATE rate_limits SET attempts = attempts + 1, last_attempt = NOW() WHERE ip_address = ? AND action = ?")
             ->execute([$ip, $action]);
    } else {
        // First attempt
        $pdo->prepare("INSERT INTO rate_limits (ip_address, action) VALUES (?, ?)")
             ->execute([$ip, $action]);
    }
    
    return true; // Within rate limit
}

function logSecurityEvent($event, $details = '') {
    $pdo = getConnection();
    
    // Create security_logs table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS security_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_event (event),
        INDEX idx_ip (ip_address),
        INDEX idx_created_at (created_at)
    )");
    
    $stmt = $pdo->prepare("INSERT INTO security_logs (event, ip_address, user_agent, details) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $event,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        $details
    ]);
}

// reCAPTCHA validation functions
function validateRecaptcha($response, $secretKey) {
    if (empty($response)) {
        return false;
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secretKey,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        return false;
    }
    
    $resultJson = json_decode($result, true);
    return isset($resultJson['success']) && $resultJson['success'] === true;
}

function isRecaptchaEnabled() {
    return RECAPTCHA_ENABLED && !empty(RECAPTCHA_SITE_KEY) && !empty(RECAPTCHA_SECRET_KEY);
}

// Event date utility functions
function parseEventDate($eventDate) {
    // Handle different date formats
    $formats = [
        'd-M Y',           // 22-Oct 2025
        'd-M-Y',           // 22-Oct-2025
        'd M Y',           // 22 October 2025
        'd-M-Y H:i:s',     // 22-Oct-2025 00:00:00
        'Y-m-d',           // 2025-10-22
        'd/m/Y',           // 22/10/2025
        'm/d/Y',           // 10/22/2025
    ];
    
    // Handle range format like "22-25 October 2025" by taking the first date
    if (preg_match('/^(\d+)-(\d+) (\w+) (\d+)$/', $eventDate, $matches)) {
        $day = $matches[1];
        $month = $matches[3];
        $year = $matches[4];
        $eventDate = "$day $month $year"; // Convert to "22 October 2025"
    }
    
    foreach ($formats as $format) {
        $parsed = DateTime::createFromFormat($format, $eventDate);
        if ($parsed !== false) {
            return $parsed;
        }
    }
    
    // Fallback to strtotime
    $timestamp = strtotime($eventDate);
    if ($timestamp !== false) {
        return new DateTime('@' . $timestamp);
    }
    
    // If all else fails, return current date
    return new DateTime();
}

function getEventYear($eventDate) {
    $parsedDate = parseEventDate($eventDate);
    return $parsedDate->format('Y');
}

function getEventDateRange($eventDate) {
    $parsedDate = parseEventDate($eventDate);
    $year = $parsedDate->format('Y');
    
    // Return a date range for the event (1 year before to 1 year after)
    return [
        'start' => $parsedDate->modify('-1 year')->format('Y-m-d'),
        'end' => $parsedDate->modify('+2 years')->format('Y-m-d'),
        'year' => $year
    ];
}

// Duplicate registration check functions
function checkDuplicateRegistration($email, $packageId, $eventDate = null) {
    if ($eventDate === null) {
        // Use the conference event date from configuration
        $eventDate = CONFERENCE_DATES; // e.g., "22-25 October 2025"
    }
    
    $pdo = getConnection();
    
    // Get event date range for more accurate checking
    $eventRange = getEventDateRange($eventDate);
    $eventYear = $eventRange['year'];
    
    $stmt = $pdo->prepare("
        SELECT r.id, r.status, r.payment_status, r.created_at, p.name as package_name, u.first_name, u.last_name
        FROM registrations r
        JOIN packages p ON r.package_id = p.id
        JOIN users u ON r.user_id = u.id
        WHERE u.email = ? 
        AND r.package_id = ? 
        AND r.created_at >= ? 
        AND r.created_at <= ?
        ORDER BY r.created_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$email, $packageId, $eventRange['start'], $eventRange['end']]);
    $existingRegistration = $stmt->fetch();
    
    if ($existingRegistration) {
        return [
            'is_duplicate' => true,
            'registration' => $existingRegistration,
            'event_date' => $eventDate,
            'event_year' => $eventYear,
            'payment_status' => $existingRegistration['payment_status']
        ];
    }
    
    return ['is_duplicate' => false];
}

function getRegistrationHistory($email, $eventDate = null) {
    if ($eventDate === null) {
        // Use the conference event date from configuration
        $eventDate = CONFERENCE_DATES; // e.g., "22-25 October 2025"
    }
    
    $pdo = getConnection();
    
    // Get event date range for more accurate checking
    $eventRange = getEventDateRange($eventDate);
    
    $stmt = $pdo->prepare("
        SELECT r.id, r.status, r.payment_status, r.created_at, r.total_amount, r.currency, 
               p.name as package_name, p.type as package_type,
               u.first_name, u.last_name
        FROM registrations r
        JOIN packages p ON r.package_id = p.id
        JOIN users u ON r.user_id = u.id
        WHERE u.email = ? 
        AND r.created_at >= ? 
        AND r.created_at <= ?
        ORDER BY r.payment_status DESC, r.created_at DESC
    ");
    
    $stmt->execute([$email, $eventRange['start'], $eventRange['end']]);
    return $stmt->fetchAll();
}

function getRegistrationHistoryByEmailAndPhone($email, $phone, $eventDate = null) {
    if ($eventDate === null) {
        $eventDate = CONFERENCE_DATES;
    }
    
    $pdo = getConnection();
    $eventRange = getEventDateRange($eventDate);
    
    $stmt = $pdo->prepare("
        SELECT r.id, r.status, r.payment_status, r.created_at, r.total_amount, r.currency, r.registration_type,
               p.name as package_name, p.type as package_type,
               u.first_name, u.last_name, u.email, u.phone, u.nationality, u.organization
        FROM registrations r
        JOIN packages p ON r.package_id = p.id
        JOIN users u ON r.user_id = u.id
        WHERE u.email = ? 
        AND u.phone = ?
        AND r.created_at >= ? 
        AND r.created_at <= ?
        ORDER BY r.payment_status DESC, r.created_at DESC
    ");
    
    $stmt->execute([$email, $phone, $eventRange['start'], $eventRange['end']]);
    return $stmt->fetchAll();
}

function getRegistrationDetails($registrationId) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT r.*, p.name as package_name, p.type as package_type, p.price as package_price,
               u.first_name, u.last_name, u.email, u.phone, u.nationality, u.organization,
               u.address_line1, u.address_line2, u.city, u.state, u.country, u.postal_code
        FROM registrations r
        JOIN packages p ON r.package_id = p.id
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    
    $stmt->execute([$registrationId]);
    return $stmt->fetch();
}


function getDuplicateRegistrationMessage($duplicateData) {
    $registration = $duplicateData['registration'];
    $packageName = $registration['package_name'];
    $paymentStatus = $duplicateData['payment_status'] ?? $registration['payment_status'];
    $createdAt = date('F j, Y \a\t g:i A', strtotime($registration['created_at']));
    $name = $registration['first_name'] . ' ' . $registration['last_name'];
    $eventDate = $duplicateData['event_date'] ?? CONFERENCE_DATES;
    
    $statusMessages = [
        'pending' => 'is pending payment',
        'completed' => 'has been completed and paid',
        'failed' => 'failed payment',
        'cancelled' => 'was cancelled'
    ];
    
    $statusText = $statusMessages[$paymentStatus] ?? 'exists';
    
    return "You have already registered for the <strong>{$packageName}</strong> package for <strong>{$eventDate}</strong>. " .
           "Your previous registration (ID: #{$registration['id']}) {$statusText} and was created on {$createdAt}. " .
           "Each email address can only register once per package.";
}

// Function to check if a nationality is African
function isAfricanNational($nationality) {
    $africanNationalities = [
        'Algerian', 'Angolan', 'Beninese', 'Botswanan', 'Burkinabe', 'Burundian',
        'Cameroonian', 'Cape Verdian', 'Central African', 'Chadian', 'Comoran',
        'Congolese', 'Ivorian', 'Djibouti', 'Egyptian', 'Equatorial Guinean',
        'Eritrean', 'Ethiopian', 'Gabonese', 'Gambian', 'Ghanaian', 'Guinean',
        'Guinea-Bissauan', 'Kenyan', 'Lesotho', 'Liberian', 'Libyan', 'Malagasy',
        'Malawian', 'Malian', 'Mauritanian', 'Mauritian', 'Moroccan', 'Mozambican',
        'Namibian', 'Nigerien', 'Nigerian', 'Rwandan', 'Sao Tomean', 'Senegalese',
        'Seychellois', 'Sierra Leonean', 'Somali', 'South African', 'South Sudanese',
        'Sudanese', 'Swazi', 'Tanzanian', 'Togolese', 'Tunisian', 'Ugandan',
        'Zambian', 'Zimbabwean', 'Motswana', 'Mosotho'
    ];
    
    return in_array($nationality, $africanNationalities);
}

// Function to get African status for a user
function getAfricanStatus($nationality) {
    return isAfricanNational($nationality) ? 'African' : 'Non-African';
}

// Function to send side event confirmation email
function sendSideEventConfirmationEmail($user, $registrationId, $package, $amount) {
    $emailQueue = new \Cphia2025\EmailQueue();
    $userName = $user['first_name'] . ' ' . $user['last_name'];

    $templateData = [
        'user_name' => $userName,
        'registration_id' => $registrationId,
        'package_name' => $package['name'],
        'amount' => $amount,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL
    ];

    return $emailQueue->addToQueue(
        $user['email'],
        $userName,
        CONFERENCE_SHORT_NAME . " - Side Event Registration Confirmation #" . $registrationId,
        'side_event_confirmation',
        $templateData,
        'registration_confirmation',
        5
    );
}

// Function to send exhibition confirmation email
function sendExhibitionConfirmationEmail($user, $registrationId, $package, $amount) {
    $emailQueue = new \Cphia2025\EmailQueue();
    $userName = $user['first_name'] . ' ' . $user['last_name'];

    $templateData = [
        'user_name' => $userName,
        'registration_id' => $registrationId,
        'package_name' => $package['name'],
        'amount' => $amount,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL
    ];

    return $emailQueue->addToQueue(
        $user['email'],
        $userName,
        CONFERENCE_SHORT_NAME . " - Exhibition Registration Confirmation #" . $registrationId,
        'exhibition_confirmation',
        $templateData,
        'registration_confirmation',
        5
    );
}
?>
