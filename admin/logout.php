<?php
/**
 * Admin Logout
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy session
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

session_destroy();

// Redirect to login
header('Location: /payments_plus/admin/login.php');
exit;


