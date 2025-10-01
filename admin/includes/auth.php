<?php
/**
 * Admin Authentication Helper
 * Checks if admin is logged in and handles session management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Require admin login (redirect to login if not authenticated)
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /payments_plus/admin/login.php');
        exit;
    }
}

// Get current admin data
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    require_once __DIR__ . '/../../db_connector.php';
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, is_active FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

// Check if current admin is super admin
function isSuperAdmin() {
    $admin = getCurrentAdmin();
    return $admin && $admin['role'] === 'super_admin';
}

// Login admin
function loginAdmin($username, $password) {
    require_once __DIR__ . '/../../db_connector.php';
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        // Set session variables
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_full_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_logged_in'] = true;
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$admin['id']]);
        
        return true;
    }
    
    return false;
}

// Logout admin
function logoutAdmin() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    session_destroy();
}

// Check if admin is logged in, redirect if not
requireAdminLogin();


