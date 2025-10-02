<?php
/**
 * Bootstrap file for CPHIA 2025 Registration System
 * Loads environment variables and sets up autoloading
 */

// Load Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback: Load dotenv manually if composer is not installed
    if (file_exists(__DIR__ . '/vendor/vlucas/phpdotenv/src/Dotenv.php')) {
        require_once __DIR__ . '/vendor/vlucas/phpdotenv/src/Dotenv.php';
    }
}

// Load environment variables
try {
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
} catch (Exception $e) {
    // If .env file doesn't exist, continue with defaults
    error_log('Environment file not found: ' . $e->getMessage());
}

// Define constants from environment variables
define('APP_NAME', $_ENV['APP_NAME'] ?? 'CPHIA 2025 Registration System');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'cphia_payments');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'password');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8');

// Payment Gateway - CyberSource
define('CYBERSOURCE_MERCHANT_ID', $_ENV['CYBERSOURCE_MERCHANT_ID'] ?? 'ECOEGH0002');
define('CYBERSOURCE_PROFILE_ID', $_ENV['CYBERSOURCE_PROFILE_ID'] ?? 'B001FD3F-4723-48D6-B139-87B8552DE9B1');
define('CYBERSOURCE_ACCESS_KEY', $_ENV['CYBERSOURCE_ACCESS_KEY'] ?? '8955d2ab178337a88eba9c5044c16c1d');
define('CYBERSOURCE_SECRET_KEY', $_ENV['CYBERSOURCE_SECRET_KEY'] ?? 'caa42c9a602b41e0a661f4bda2b042ae568a1ca8d7b343d78f148a959cbf1cade402a438de294e1795457092b54e056c20f8e19d6f4444d59c5d365d2dad3fb17ce885074c3248608a2e6cf2f100d0bbb31264bbfc89454aaf6f6c730bc04563ca49a64fac1b44b2aad0e9461210b0e1830de4b3ebce470c8ff084e7cf96d053');
define('CYBERSOURCE_DF_ORG_ID', $_ENV['CYBERSOURCE_DF_ORG_ID'] ?? '1snn5n9w');
define('CYBERSOURCE_BASE_URL', $_ENV['CYBERSOURCE_BASE_URL'] ?? 'https://testsecureacceptance.cybersource.com/silent');

// Email configuration - MS Exchange OAuth
define('MAIL_DRIVER', $_ENV['MAIL_DRIVER'] ?? 'exchange_oauth');
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.office365.com');
define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? '587');
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'PHPMailer::ENCRYPTION_STARTTLS');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@cphia2025.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'CPHIA 2025');

// MS Exchange OAuth Configuration
define('EXCHANGE_TENANT_ID', $_ENV['EXCHANGE_TENANT_ID'] ?? '');
define('EXCHANGE_CLIENT_ID', $_ENV['EXCHANGE_CLIENT_ID'] ?? '');
define('EXCHANGE_CLIENT_SECRET', $_ENV['EXCHANGE_CLIENT_SECRET'] ?? '');
define('EXCHANGE_REDIRECT_URI', $_ENV['EXCHANGE_REDIRECT_URI'] ?? 'http://localhost:8000/oauth/callback');
define('EXCHANGE_SCOPE', $_ENV['EXCHANGE_SCOPE'] ?? 'https://graph.microsoft.com/Mail.Send');

// Admin email configuration
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'admin@cphia2025.com');
define('ADMIN_NAME', $_ENV['ADMIN_NAME'] ?? 'CPHIA 2025 Admin');
define('ADMIN_NOTIFICATIONS', filter_var($_ENV['ADMIN_NOTIFICATIONS'] ?? 'true', FILTER_VALIDATE_BOOLEAN));

// Email templates
define('EMAIL_TEMPLATE_PATH', $_ENV['EMAIL_TEMPLATE_PATH'] ?? 'templates/email/');
define('EMAIL_LOGO_URL', $_ENV['EMAIL_LOGO_URL'] ?? 'https://cphia2025.com/images/logo.png');

// Security
define('APP_KEY', $_ENV['APP_KEY'] ?? 'base64:your-32-character-secret-key-here');
define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? '120');
define('SESSION_ENCRYPT', filter_var($_ENV['SESSION_ENCRYPT'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('SESSION_SECURE', filter_var($_ENV['SESSION_SECURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('SESSION_HTTP_ONLY', filter_var($_ENV['SESSION_HTTP_ONLY'] ?? 'true', FILTER_VALIDATE_BOOLEAN));

// Conference Information
define('CONFERENCE_NAME', $_ENV['CONFERENCE_NAME'] ?? '4th International Conference on Public Health in Africa');
define('CONFERENCE_SHORT_NAME', $_ENV['CONFERENCE_SHORT_NAME'] ?? 'CPHIA 2025');
define('CONFERENCE_DATES', $_ENV['CONFERENCE_DATES'] ?? '22-25 October 2025');
define('CONFERENCE_LOCATION', $_ENV['CONFERENCE_LOCATION'] ?? 'Durban, South Africa');
define('CONFERENCE_VENUE', $_ENV['CONFERENCE_VENUE'] ?? 'Durban International Convention Centre');

// Currency
define('DEFAULT_CURRENCY', $_ENV['DEFAULT_CURRENCY'] ?? 'USD');
define('CURRENCY_SYMBOL', $_ENV['CURRENCY_SYMBOL'] ?? '$');

// Features
define('ENABLE_EMAIL_NOTIFICATIONS', filter_var($_ENV['ENABLE_EMAIL_NOTIFICATIONS'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('ENABLE_PAYMENT_EMAILS', filter_var($_ENV['ENABLE_PAYMENT_EMAILS'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('ENABLE_DEBUG_MODE', filter_var($_ENV['ENABLE_DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('ENABLE_MAINTENANCE_MODE', filter_var($_ENV['ENABLE_MAINTENANCE_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN));

// File Uploads
define('MAX_FILE_SIZE', $_ENV['MAX_FILE_SIZE'] ?? '5242880');
define('ALLOWED_FILE_TYPES', $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,pdf,doc,docx');

// Rate Limiting
define('RATE_LIMIT_REQUESTS', $_ENV['RATE_LIMIT_REQUESTS'] ?? '100');
define('RATE_LIMIT_WINDOW', $_ENV['RATE_LIMIT_WINDOW'] ?? '60');

// reCAPTCHA Configuration
define('RECAPTCHA_SITE_KEY', $_ENV['RECAPTCHA_SITE_KEY'] ?? '');
define('RECAPTCHA_SECRET_KEY', $_ENV['RECAPTCHA_SECRET_KEY'] ?? '');
define('RECAPTCHA_ENABLED', filter_var($_ENV['RECAPTCHA_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN));

// Set error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('Africa/Johannesburg');

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', SESSION_LIFETIME * 60);
    ini_set('session.cookie_secure', SESSION_SECURE ? '1' : '0');
    ini_set('session.cookie_httponly', SESSION_HTTP_ONLY ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    session_start();
}
