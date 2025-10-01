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
    $stmt = $pdo->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY type, price");
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

// User functions
function createUser($data) {
    $pdo = getConnection();
    $sql = "INSERT INTO users (email, first_name, last_name, phone, nationality, organization, 
            address_line1, address_line2, city, state, country, postal_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['email'], $data['first_name'], $data['last_name'], 
        $data['phone'], $data['nationality'], $data['organization'],
        $data['address_line1'], $data['address_line2'], $data['city'], 
        $data['state'], $data['country'], $data['postal_code']
    ]);
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
    $sql = "INSERT INTO registrations (user_id, package_id, registration_type, total_amount, currency, exhibition_description) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['user_id'], $data['package_id'], $data['registration_type'], 
        $data['total_amount'], $data['currency'], $data['exhibition_description'] ?? null
    ]);
    return $pdo->lastInsertId();
}

function createRegistrationParticipants($registrationId, $participants) {
    $pdo = getConnection();
    $sql = "INSERT INTO registration_participants (registration_id, title, first_name, last_name, email, nationality, passport_number, organization) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($participants as $participant) {
        $stmt->execute([
            $registrationId, $participant['title'], $participant['first_name'], 
            $participant['last_name'], $participant['email'], $participant['nationality'],
            $participant['passport_number'] ?? '', $participant['organization'] ?? ''
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
    return base64_encode($registrationId . '_' . time() . '_' . rand(1000, 9999));
}

// Email notification functions using EmailQueue
function sendRegistrationEmails($user, $registrationId, $package, $amount, $participants = []) {
    $emailQueue = new \Cphia2025\EmailQueue();
    $success = true;

    // Queue registration confirmation to user
    $userName = $user['first_name'] . ' ' . $user['last_name'];
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
        'logo_url' => EMAIL_LOGO_URL
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
    $paymentLink = $baseUrl . "/checkout.php?token=" . $paymentToken;

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
?>
