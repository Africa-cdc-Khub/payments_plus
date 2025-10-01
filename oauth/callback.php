<?php
/**
 * OAuth Callback Handler for Microsoft Exchange
 * Handles the OAuth callback from Microsoft and exchanges code for tokens
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/ExchangeOAuth.php';

use Cphia2025\ExchangeOAuth;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $oauth = new ExchangeOAuth();
    
    // Check for authorization code
    if (isset($_GET['code']) && isset($_GET['state'])) {
        $code = $_GET['code'];
        $state = $_GET['state'];
        
        // Exchange code for tokens
        $success = $oauth->exchangeCodeForToken($code, $state);
        
        if ($success) {
            // Clear the state
            unset($_SESSION['oauth_state']);
            
            // Redirect to admin page or success page
            header('Location: /admin/email-oauth.php?success=1');
            exit;
        } else {
            throw new \Exception('Failed to exchange authorization code for tokens');
        }
    } elseif (isset($_GET['error'])) {
        // Handle OAuth error
        $error = $_GET['error'];
        $errorDescription = $_GET['error_description'] ?? 'Unknown error';
        
        throw new \Exception('OAuth error: ' . $error . ' - ' . $errorDescription);
    } else {
        throw new \Exception('No authorization code received');
    }
    
} catch (Exception $e) {
    // Log error
    error_log('OAuth callback error: ' . $e->getMessage());
    
    // Redirect to error page
    header('Location: /admin/email-oauth.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>
