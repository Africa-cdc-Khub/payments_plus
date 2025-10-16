<?php
require_once 'bootstrap.php';
require_once 'db_connector.php';

// Load Composer autoloader for EmailService
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Cphia2025\EmailService;
use Cphia2025\EmailQueue;

// Package functions
function getAllPackages() {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE is_active = 1 ORDER BY price DESC, type");
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
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE type = ? AND is_active = 1 ORDER BY price DESC");
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
        // Clean up temporary file if validation fails
        if (file_exists($file['tmp_name'])) {
            unlink($file['tmp_name']);
        }
        return false;
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        // Clean up temporary file if validation fails
        if (file_exists($file['tmp_name'])) {
            unlink($file['tmp_name']);
        }
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Clean up any old files in the upload directory after successful upload
        cleanupOldFiles($uploadDir, 86400); // Clean files older than 24 hours
        return $filename;
    }
    
    // Clean up temporary file if move fails
    if (file_exists($file['tmp_name'])) {
        unlink($file['tmp_name']);
    }
    
    return false;
}

// Clean up old temporary files and orphaned uploads
function cleanupOldFiles($uploadDir = 'uploads/passports/', $maxAge = 86400) {
    if (!is_dir($uploadDir)) {
        return;
    }
    
    $files = glob($uploadDir . '*');
    $currentTime = time();
    
    foreach ($files as $file) {
        if (is_file($file)) {
            $fileAge = $currentTime - filemtime($file);
            if ($fileAge > $maxAge) {
                unlink($file);
                error_log("Cleaned up old file: " . basename($file));
            }
        }
    }
}

// Clean up PHP temporary files that might be left behind
function cleanupTempFiles() {
    $tempDir = sys_get_temp_dir();
    $pattern = $tempDir . '/php*';
    $files = glob($pattern);
    
    foreach ($files as $file) {
        if (is_file($file)) {
            $fileAge = time() - filemtime($file);
            // Clean up files older than 1 hour
            if ($fileAge > 3600) {
                unlink($file);
                error_log("Cleaned up temp file: " . basename($file));
            }
        }
    }
}

// User functions
function createUser($data) {
    $pdo = getConnection();
    $sql = "INSERT INTO users (email, title, first_name, last_name, phone, nationality, passport_number, 
            passport_file, requires_visa, organization, position, institution, student_id_file, delegate_category, airport_of_origin, address_line1, address_line2, city, state, country, postal_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
        $data['position'] ?? '', $data['institution'] ?? '', $data['student_id_file'] ?? '', 
        $data['delegate_category'] ?? '', $data['airport_of_origin'] ?? '',
        $data['address_line1'], $data['address_line2'] ?? '', 
        $data['city'], $data['state'] ?? '', $data['country'], $data['postal_code'] ?? ''
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
    $sql = "INSERT INTO registration_participants (registration_id, title, first_name, last_name, email, nationality, passport_number, passport_file, requires_visa, organization, institution, student_id_file, delegate_category, airport_of_origin) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
            $requiresVisa, $participant['organization'] ?? '', $participant['institution'] ?? '', 
            $participant['student_id_file'] ?? '', $participant['delegate_category'] ?? '', 
            $participant['airport_of_origin'] ?? ''
        ]);
    }
}

function getRegistrationById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT r.*, p.name as package_name, p.price as package_price, 
                          u.first_name, u.last_name, u.email as user_email, u.nationality, 
                          u.organization, u.address_line1, u.address_line2, u.city, u.state, u.country, u.postal_code
                          FROM registrations r 
                          JOIN packages p ON r.package_id = p.id 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.id = ?");
    $stmt->execute([$id]);
    $registration = $stmt->fetch();
    
    // Fix: Remove 262145 prefix from amount if present
    if ($registration && isset($registration['total_amount'])) {
        $registration['total_amount'] = str_replace('262145', '', $registration['total_amount']);
    }
    
    return $registration;
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

// Payment functions - now handled directly in registrations table

// Utility functions
function formatCurrency($amount, $currency = 'USD') {
    // Fix: Remove 262145 prefix if present
    $cleanAmount = str_replace('262145', '', $amount);
    return '$' . number_format($cleanAmount, 2);
}

function generatePaymentToken($registrationId) {
    $token = base64_encode($registrationId . '_' . time() . '_' . rand(1000, 9999));
    
    // Store the token in the database
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE registrations SET payment_token = ? WHERE id = ?");
    $stmt->execute([$token, $registrationId]);
    
    return $token;
}

// Generate invoice data for registration
function generateInvoiceData($user, $registrationId, $package, $amount, $participants = [], $registrationType = 'individual') {
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    
    // Generate payment link
    $paymentLink = rtrim(APP_URL, '/') . "/registration_lookup.php?action=pay&id=" . $registrationId;
    
    // Generate registration lookup URL
    $registrationLookupUrl = rtrim(APP_URL, '/') . "/registration_lookup.php";
    
    // For group registrations, include the main registrant in the participants list
    $allParticipants = [];
    if ($registrationType === 'group') {
        // Add main registrant as first participant
        $mainRegistrant = [
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'nationality' => $user['nationality'] ?? 'Not specified'
        ];
        $allParticipants[] = $mainRegistrant;
        
        // Add other participants
        foreach ($participants as $participant) {
            $allParticipants[] = $participant;
        }
    } else {
        // For individual registrations, just use the main registrant
        $allParticipants = [
            [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'nationality' => $user['nationality'] ?? 'Not specified'
            ]
        ];
    }
    
    // Calculate per-participant amount
    $numParticipants = count($allParticipants);
    $perParticipantAmount = $numParticipants > 1 ? $amount / $numParticipants : $amount;
    
    // Add amount to each participant
    $participantsWithAmount = [];
    foreach ($allParticipants as $participant) {
        $participant['amount'] = number_format($perParticipantAmount, 2);
        $participantsWithAmount[] = $participant;
    }
    
    // Generate participants HTML table for invoice template
    $participantsHtml = '';
    if (!empty($participantsWithAmount)) {
        $participantsHtml .= '<table class="participants-table">';
        $participantsHtml .= '<thead><tr>';
        $participantsHtml .= '<th>#</th>';
        $participantsHtml .= '<th>Full Name</th>';
        $participantsHtml .= '<th>Email</th>';
        $participantsHtml .= '<th>Nationality</th>';
        $participantsHtml .= '<th>Amount</th>';
        $participantsHtml .= '</tr></thead>';
        $participantsHtml .= '<tbody>';
        
        foreach ($participantsWithAmount as $index => $participant) {
            $participantsHtml .= '<tr>';
            $participantsHtml .= '<td>' . ($index + 1) . '</td>';
            $participantsHtml .= '<td>' . htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) . '</td>';
            $participantsHtml .= '<td>' . htmlspecialchars($participant['email']) . '</td>';
            $participantsHtml .= '<td>' . htmlspecialchars($participant['nationality']) . '</td>';
            $participantsHtml .= '<td class="amount-cell">$' . $participant['amount'] . '</td>';
            $participantsHtml .= '</tr>';
        }
        
        $participantsHtml .= '</tbody></table>';
    }
    
    // Format dates
    $invoiceDate = date('F j, Y');
    $dueDate = date('F j, Y', strtotime('+30 days')); // 30 days from now
    
    return [
        'user_name' => $userName,
        'user_email' => $user['email'],
        'organization_name' => $user['organization'] ?? '',
        'organization_address' => $user['organization_address'] ?? '',
        'registration_id' => $registrationId,
        'package_name' => $package['name'],
        'registration_type' => ucfirst($registrationType),
        'num_participants' => $numParticipants,
        'total_amount' => number_format($amount, 2),
        'participants' => $participantsWithAmount,
        'participants_html' => $participantsHtml,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL,
        'payment_link' => $paymentLink,
        'registration_lookup_url' => $registrationLookupUrl,
        'invoice_date' => $invoiceDate,
        'due_date' => $dueDate,
        'support_email' => SUPPORT_EMAIL
    ];
}

// Email notification functions using EmailQueue
function sendRegistrationEmails($user, $registrationId, $package, $amount, $participants = [], $registrationType = 'individual') {
    $emailQueue = new \Cphia2025\EmailQueue();
    $success = true;

    // Generate invoice data
    $invoiceData = generateInvoiceData($user, $registrationId, $package, $amount, $participants, $registrationType);
    
    // Queue registration confirmation email to user
    $confirmationData = [
        'user_name' => $invoiceData['user_name'],
        'registration_id' => $registrationId,
        'package_name' => $package['name'],
        'package_id' => $package['id'],
        'amount' => $amount,
        'is_delegate_package' => ($package['id'] == DELEGATE_PACKAGE_ID),
        'is_not_delegate_package' => ($package['id'] != DELEGATE_PACKAGE_ID),
        'payment_status_link' => $invoiceData['payment_link'],
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL,
        'support_email' => SUPPORT_EMAIL
    ];

    $result = $emailQueue->addToQueue(
        $user['email'],
        $invoiceData['user_name'],
        CONFERENCE_SHORT_NAME . " - Registration Confirmation #" . $registrationId,
        'registration_confirmation',
        $confirmationData,
        'registration_confirmation',
        5
    );

    if (!$result) {
        $success = false;
        error_log("Failed to queue registration confirmation to: " . $user['email']);
    }

    // Queue invoice email to user
    $result = $emailQueue->addToQueue(
        $user['email'],
        $invoiceData['user_name'],
        CONFERENCE_SHORT_NAME . " - Registration Invoice #" . $registrationId,
        'invoice',
        $invoiceData,
        'invoice',
        5
    );

    if (!$result) {
        $success = false;
        error_log("Failed to queue invoice to: " . $user['email']);
    }

    // Queue admin notification
    $adminTemplateData = [
        'registration_id' => $registrationId,
        'user_name' => $invoiceData['user_name'],
        'user_email' => $user['email'],
        'package_name' => $package['name'],
        'amount' => $amount,
        'registration_type' => $registrationType,
        'participants' => $participants,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'admin_name' => ADMIN_NAME,
        'logo_url' => EMAIL_LOGO_URL,
        'mail_from_address' => MAIL_FROM_ADDRESS,
        'support_email' => SUPPORT_EMAIL
    ];

    $result = $emailQueue->addToQueue(
        ADMIN_EMAIL,
        ADMIN_NAME,
        "New Registration: #" . $registrationId . " - " . $invoiceData['user_name'],
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

function sendPaymentLinkEmail($user, $registrationId, $amount, $packageName = null) {
    $emailQueue = new \Cphia2025\EmailQueue();
    $paymentLink = rtrim(APP_URL, '/') . "/registration_lookup.php?action=pay&id=" . $registrationId;

    $userName = $user['first_name'] . ' ' . $user['last_name'];
    
    // Get package name if not provided
    if (!$packageName) {
        $registration = getRegistrationById($registrationId);
        $packageName = $registration ? $registration['package_name'] : 'Unknown Package';
    }
    
    $templateData = [
        'user_name' => $userName,
        'registration_id' => $registrationId,
        'package_name' => $packageName,
        'amount' => $amount,
        'payment_link' => $paymentLink,
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL,
        'mail_from_address' => MAIL_FROM_ADDRESS,
        'support_email' => SUPPORT_EMAIL
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
        'payment_date' => date('F j, Y \a\t g:i A'),
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
    // Trim whitespace and normalize the nationality
    $nationality = trim($nationality);
    
    if (empty($nationality)) {
        return false;
    }
    
    // Get all nationalities from database
    $nationalities = getAllNationalities();
    $allowedNationalities = array_column($nationalities, 'nationality');
    
    // Check if nationality exists in database
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
        SELECT r.id, r.status, r.payment_status, r.created_at, r.total_amount, r.currency, r.registration_type,
               p.name as package_name, p.type as package_type,
               u.first_name, u.last_name, u.email, u.phone, u.nationality, u.organization
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
    $registrations = $stmt->fetchAll();
    
    // Fix: Remove 262145 prefix from amounts if present
    foreach ($registrations as &$registration) {
        if (isset($registration['total_amount'])) {
            $registration['total_amount'] = str_replace('262145', '', $registration['total_amount']);
        }
    }
    
    return $registrations;
}

/**
 * Get registration history by email only (improved lookup)
 * 
 * @param string $email User's email address
 * @param string $eventDate Event date (optional)
 * @return array Array of registration records
 */
function getRegistrationHistoryByEmail($email, $eventDate = null) {
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
        AND r.created_at >= ? 
        AND r.created_at <= ?
        ORDER BY r.payment_status DESC, r.created_at DESC
    ");
    
    $stmt->execute([$email, $eventRange['start'], $eventRange['end']]);
    $registrations = $stmt->fetchAll();
    
    // Fix: Remove 262145 prefix from amounts if present
    foreach ($registrations as &$registration) {
        if (isset($registration['total_amount'])) {
            $registration['total_amount'] = str_replace('262145', '', $registration['total_amount']);
        }
    }
    
    return $registrations;
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
    $registration = $stmt->fetch();
    
    // Fix: Remove 262145 prefix from amount if present
    if ($registration && isset($registration['total_amount'])) {
        $registration['total_amount'] = str_replace('262145', '', $registration['total_amount']);
    }
    
    return $registration;
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

// Function to get all countries from database
function getAllCountries() {
    static $countries = null;
    
    if ($countries === null) {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT code, name, nationality, continent, iso2_code, iso3_code FROM countries ORDER BY name");
        $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $countries;
}
function getCountryCodeByName($countryName) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT code FROM countries WHERE name = ?");
    $stmt->execute([$countryName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['code'] : 'US'; // Default to US if not found
}

function cleanCountryName($countryName) {
    if (empty($countryName)) {
        return '';
    }
    
    // Decode HTML entities
    $cleaned = html_entity_decode($countryName, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Remove any extra text that might be appended (like "Country >Country")
    if (strpos($cleaned, '>') !== false) {
        $parts = explode('>', $cleaned);
        $cleaned = trim($parts[0]);
    }
    
    // Remove any "selected" text that might be appended
    $cleaned = str_replace('selected', '', $cleaned);
    
    // Clean up extra whitespace
    $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));
    
    return $cleaned;
}

// Function to get all nationalities from database
function getAllNationalities() {
    static $nationalities = null;
    
    if ($nationalities === null) {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT DISTINCT nationality, name as country_name, code, continent FROM countries WHERE nationality IS NOT NULL AND nationality != '' ORDER BY name");
        $nationalities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $nationalities;
}

// Function to get countries by continent
function getCountriesByContinent($continent) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT code, name, nationality, continent, iso2_code, iso3_code FROM countries WHERE continent = ? ORDER BY name");
    $stmt->execute([$continent]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get African countries
function getAfricanCountries() {
    return getCountriesByContinent('Africa');
}

// Function to get country code from country name
function getCountryCode($countryName) {
    static $countryCodes = null;
    
    if ($countryCodes === null) {
        $countries = getAllCountries();
        $countryCodes = [];
        foreach ($countries as $country) {
            $countryCodes[$country['name']] = $country['code'];
        }
    }
    
    return $countryCodes[$countryName] ?? $countryName; // Return country name as fallback
}

// Function to get country by name
function getCountryByName($countryName) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM countries WHERE name = ?");
    $stmt->execute([$countryName]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get country by code
function getCountryByCode($countryCode) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM countries WHERE code = ?");
    $stmt->execute([$countryCode]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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

function getUserById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function sendPaymentConfirmationEmail($user, $registration) {
    $emailQueue = new \Cphia2025\EmailQueue();
    
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    $templateData = [
        'user_name' => $userName,
        'registration_id' => $registration['id'],
        'package_name' => $registration['package_name'],
        'amount' => $registration['total_amount'],
        'currency' => $registration['currency'],
        'payment_reference' => $registration['payment_reference'],
        'payment_date' => date('F j, Y \a\t g:i A', strtotime($registration['payment_completed_at'] ?? 'now')),
        'conference_name' => CONFERENCE_NAME,
        'conference_short_name' => CONFERENCE_SHORT_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION,
        'conference_venue' => CONFERENCE_VENUE,
        'logo_url' => EMAIL_LOGO_URL,
        'mail_from_address' => MAIL_FROM_ADDRESS,
        'support_email' => SUPPORT_EMAIL
    ];
    
    return $emailQueue->addToQueue(
        $user['email'],
        $userName,
        CONFERENCE_SHORT_NAME . " - Payment Confirmed #" . $registration['id'],
        'payment_confirmation',
        $templateData,
        'payment_confirmation',
        3
    );
}

function generateQRCode($data, $size = 200) {
    // Use QR Server API which is more reliable
    $qrData = urlencode($data);
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$qrData}";
    
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'CPHIA2025/1.0',
                'follow_location' => true,
                'max_redirects' => 3
            ]
        ]);
        
        $qrImage = file_get_contents($qrUrl, false, $context);
        if ($qrImage !== false && strlen($qrImage) > 0) {
            // Return as base64 embedded image for better email compatibility
            return 'data:image/png;base64,' . base64_encode($qrImage);
        }
    } catch (Exception $e) {
        error_log("QR Code generation error: " . $e->getMessage());
    }
    
    // Fallback: create a simple text-based QR representation
    $fallbackText = "QR Code\n" . substr($data, 0, 30) . "...";
    return '<div style="border: 2px solid #333; padding: 10px; text-align: center; font-family: monospace; background: #f0f0f0; width: ' . $size . 'px; height: ' . $size . 'px; display: flex; align-items: center; justify-content: center;">' . htmlspecialchars($fallbackText) . '</div>';
}

function generateVerificationQRCode($data) {
    // Generate a smaller QR code for verification purposes
    return generateQRCode($data, 120);
}

function generateReceiptData($participant, $registration, $package, $user) {
    $receiptData = [
        'name' => $participant['first_name'] . ' ' . $participant['last_name'],
        'email' => $participant['email'] ?? $user['email'],
        'phone' => $user['phone'],
        'registration_id' => $registration['id'],
        'package' => $package['name'],
        'organization' => $participant['organization'] ?? $user['organization'],
        'institution' => $participant['institution'] ?? '',
        'nationality' => $participant['nationality'] ?? $user['nationality'],
        'payment_date' => date('Y-m-d H:i:s'),
        'amount' => formatCurrency($registration['total_amount'], $registration['currency']),
        'currency' => $registration['currency'],
        'registration_type' => $registration['registration_type'],
        'conference_name' => CONFERENCE_NAME,
        'conference_dates' => CONFERENCE_DATES,
        'conference_location' => CONFERENCE_LOCATION
    ];
    
    // Create comprehensive QR code data string with all receipt information
    $qrString = "CPHIA2025|{$receiptData['name']}|{$receiptData['email']}|{$receiptData['phone']}|{$receiptData['registration_id']}|{$receiptData['package']}|{$receiptData['organization']}|{$receiptData['institution']}|{$receiptData['nationality']}|{$receiptData['amount']}|{$receiptData['currency']}|{$receiptData['registration_type']}|{$receiptData['payment_date']}|{$receiptData['conference_name']}|{$receiptData['conference_dates']}|{$receiptData['conference_location']}";
    
    // Create navigation QR code for verification link
    $verificationUrl = APP_URL . "/verify_attendance.php";
    $navigationQrString = "VERIFY|{$verificationUrl}|{$receiptData['registration_id']}|{$receiptData['name']}";
    
    return [
        'receipt_data' => $receiptData,
        'qr_string' => $qrString,
        'qr_code' => generateQRCode($qrString),
        'navigation_qr_string' => $navigationQrString,
        'navigation_qr_code' => generateVerificationQRCode($navigationQrString)
    ];
}

function sendReceiptEmails($registration, $package, $user, $participants = []) {
    $emailQueue = new EmailQueue();
    $sentCount = 0;
    
    if ($registration['registration_type'] === 'group' && !empty($participants)) {
        // For group registrations, send receipts to focal person with all participants
        $receiptData = [];
        $qrCodes = [];
        $verificationQrCodes = [];
        $navigationQrCodes = [];
        
        foreach ($participants as $participant) {
            $participantReceipt = generateReceiptData($participant, $registration, $package, $user);
            $receiptData[] = $participantReceipt['receipt_data'];
            $qrCodes[] = $participantReceipt['qr_code'];
            $verificationQrCodes[] = generateVerificationQRCode($participantReceipt['qr_string']);
            $navigationQrCodes[] = $participantReceipt['navigation_qr_code'];
        }
        
        // Generate formatted participant list
        $participantsList = '';
        foreach ($receiptData as $index => $participant) {
            $participantsList .= '<div style="border: 1px solid #e5e7eb; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f9fafb;">';
            $participantsList .= '<h4 style="margin: 0 0 10px 0; color: #374151;">Participant ' . ($index + 1) . '</h4>';
            $participantsList .= '<p style="margin: 5px 0;"><strong>Name:</strong> ' . htmlspecialchars($participant['name']) . '</p>';
            $participantsList .= '<p style="margin: 5px 0;"><strong>Email:</strong> ' . htmlspecialchars($participant['email']) . '</p>';
            $participantsList .= '<p style="margin: 5px 0;"><strong>Phone:</strong> ' . htmlspecialchars($participant['phone'] ?? 'N/A') . '</p>';
            $participantsList .= '<p style="margin: 5px 0;"><strong>Nationality:</strong> ' . htmlspecialchars($participant['nationality'] ?? 'N/A') . '</p>';
            if (!empty($participant['institution'])) {
                $participantsList .= '<p style="margin: 5px 0;"><strong>Institution:</strong> ' . htmlspecialchars($participant['institution']) . '</p>';
            }
            if (!empty($participant['position'])) {
                $participantsList .= '<p style="margin: 5px 0;"><strong>Position:</strong> ' . htmlspecialchars($participant['position']) . '</p>';
            }
            $participantsList .= '</div>';
        }
        
        // Generate QR codes display
        $qrCodesDisplay = '';
        foreach ($qrCodes as $index => $qrCode) {
            $participantName = $receiptData[$index]['name'];
            $qrCodesDisplay .= '<div style="text-align: center; margin: 20px 0; padding: 15px; border: 1px solid #e5e7eb; border-radius: 5px; background: white;">';
            $qrCodesDisplay .= '<h4 style="margin: 0 0 10px 0; color: #374151;">' . htmlspecialchars($participantName) . '</h4>';
            $qrCodesDisplay .= '<img src="data:image/png;base64,' . $qrCode . '" alt="QR Code for ' . htmlspecialchars($participantName) . '" style="max-width: 200px; height: auto; border: 1px solid #d1d5db;">';
            $qrCodesDisplay .= '<p style="margin: 10px 0 0 0; font-size: 12px; color: #6b7280;">Scan this QR code at conference check-in</p>';
            $qrCodesDisplay .= '</div>';
        }
        
        $templateData = [
            'conference_name' => CONFERENCE_NAME,
            'conference_short_name' => CONFERENCE_SHORT_NAME,
            'conference_dates' => CONFERENCE_DATES,
            'conference_location' => CONFERENCE_LOCATION,
            'focal_person_name' => $user['first_name'] . ' ' . $user['last_name'],
            'focal_person_email' => $user['email'],
            'registration_id' => $registration['id'],
            'package_name' => $package['name'],
            'total_amount' => formatCurrency($registration['total_amount'], $registration['currency']),
            'payment_date' => date('F j, Y \a\t g:i A'),
            'participants_count' => count($receiptData),
            'participants_list' => $participantsList,
            'qr_codes_display' => $qrCodesDisplay,
            'participants' => $receiptData,
            'qr_codes' => $qrCodes,
            'verification_qr_codes' => $verificationQrCodes,
            'navigation_qr_codes' => $navigationQrCodes,
            'logo_url' => EMAIL_LOGO_URL,
            'mail_from_address' => MAIL_FROM_ADDRESS,
            'support_email' => SUPPORT_EMAIL
        ];
        
        $success = $emailQueue->addToQueue(
            $user['email'],
            $user['first_name'] . ' ' . $user['last_name'],
            'Group Registration Receipts - ' . CONFERENCE_SHORT_NAME,
            'group_receipt',
            $templateData,
            'registration_receipt',
            3
        );
        
        if ($success) $sentCount++;
        
    } else {
        // For individual registrations, send receipt to the participant
        $receiptInfo = generateReceiptData($user, $registration, $package, $user);
        
        $templateData = [
            'conference_name' => CONFERENCE_NAME,
            'conference_short_name' => CONFERENCE_SHORT_NAME,
            'conference_dates' => CONFERENCE_DATES,
            'conference_location' => CONFERENCE_LOCATION,
            'participant_name' => $user['first_name'] . ' ' . $user['last_name'],
            'participant_email' => $user['email'],
            'registration_id' => $registration['id'],
            'package_name' => $package['name'],
            'total_amount' => formatCurrency($registration['total_amount'], $registration['currency']),
            'payment_date' => date('F j, Y \a\t g:i A'),
            'organization' => $user['organization'],
            'institution' => $user['institution'] ?? '',
            'nationality' => $user['nationality'],
            'phone' => $user['phone'],
            'qr_code' => $receiptInfo['qr_code'],
            'verification_qr_code' => generateVerificationQRCode($receiptInfo['qr_string']),
            'navigation_qr_code' => $receiptInfo['navigation_qr_code'],
            'logo_url' => EMAIL_LOGO_URL,
            'mail_from_address' => MAIL_FROM_ADDRESS,
        'support_email' => SUPPORT_EMAIL
        ];
        
        $success = $emailQueue->addToQueue(
            $user['email'],
            $user['first_name'] . ' ' . $user['last_name'],
            'Registration Receipt - ' . CONFERENCE_SHORT_NAME,
            'individual_receipt',
            $templateData,
            'registration_receipt',
            3
        );
        
        if ($success) $sentCount++;
    }
    
    return $sentCount;
}
// test function
function testFunction() {
    return 'test';
}
?>
