<?php
/**
 * OAuth Email Setup Page
 * Allows administrators to configure and test MS Exchange OAuth email
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../db_connector.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../src/ExchangeOAuth.php';

use Cphia2025\ExchangeOAuth;

$pageTitle = 'Email OAuth Setup';

$oauth = new ExchangeOAuth();
$isConfigured = $oauth->isConfigured();
$message = '';
$messageType = '';

// Handle messages from OAuth callback
if (isset($_GET['success'])) {
    $message = 'OAuth authentication successful! Email service is now configured.';
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = 'OAuth authentication failed: ' . htmlspecialchars($_GET['error']);
    $messageType = 'error';
}

// Handle test email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    try {
        $testEmail = $_POST['test_email'];
        $testSubject = 'CPHIA 2025 - OAuth Email Test';
        $testBody = '
        <html>
        <body>
            <h2>OAuth Email Test Successful!</h2>
            <p>This is a test email from the CPHIA 2025 Registration System using Microsoft Graph API with OAuth 2.0.</p>
            <p>If you received this email, your OAuth configuration is working correctly.</p>
            <p>Sent at: ' . date('Y-m-d H:i:s') . '</p>
        </body>
        </html>';
        
        $success = $oauth->sendEmail($testEmail, $testSubject, $testBody, true);
        
        if ($success) {
            $message = 'Test email sent successfully to ' . $testEmail;
            $messageType = 'success';
        } else {
            $message = 'Failed to send test email';
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Error sending test email: ' . $e->getMessage();
        $messageType = 'error';
    }
}

?>

<?php
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Email OAuth Setup</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/payments_plus/admin/">Home</a></li>
                        <li class="breadcrumb-item active">Email OAuth Setup</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fab fa-microsoft mr-2"></i>
                                OAuth Email Setup - Microsoft Graph API
                            </h3>
                        </div>
                        <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>">
                                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Configuration Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card <?php echo $isConfigured ? 'border-success' : 'border-warning'; ?>">
                                    <div class="card-body text-center">
                                        <i class="fas fa-<?php echo $isConfigured ? 'check-circle text-success' : 'exclamation-triangle text-warning'; ?> fa-3x mb-3"></i>
                                        <h5>OAuth Status</h5>
                                        <p class="mb-0">
                                            <?php echo $isConfigured ? 'Configured' : 'Not Configured'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <i class="fas fa-shield-alt fa-3x mb-3 text-info"></i>
                                        <h5>Authentication</h5>
                                        <p class="mb-0">OAuth 2.0 + Microsoft Graph</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Instructions -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Azure AD App Registration Setup</h5>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li>
                                        <strong>Register your application in Azure AD:</strong>
                                        <ul>
                                            <li>Go to <a href="https://portal.azure.com" target="_blank">Azure Portal</a></li>
                                            <li>Navigate to "Azure Active Directory" → "App registrations"</li>
                                            <li>Click "New registration"</li>
                                            <li>Name: "CPHIA 2025 Email Service"</li>
                                            <li>Redirect URI: <code><?php echo EXCHANGE_REDIRECT_URI; ?></code></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <strong>Configure API permissions:</strong>
                                        <ul>
                                            <li>Go to "API permissions" → "Add a permission"</li>
                                            <li>Select "Microsoft Graph" → "Delegated permissions"</li>
                                            <li>Add "Mail.Send" permission</li>
                                            <li>Click "Grant admin consent"</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <strong>Get your credentials:</strong>
                                        <ul>
                                            <li>Copy "Application (client) ID"</li>
                                            <li>Go to "Certificates & secrets" → "New client secret"</li>
                                            <li>Copy the secret value</li>
                                            <li>Copy "Directory (tenant) ID" from Overview page</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <strong>Update your .env file:</strong>
                                        <ul>
                                            <li>Set <code>EXCHANGE_TENANT_ID</code> to your tenant ID</li>
                                            <li>Set <code>EXCHANGE_CLIENT_ID</code> to your client ID</li>
                                            <li>Set <code>EXCHANGE_CLIENT_SECRET</code> to your client secret</li>
                                        </ul>
                                    </li>
                                </ol>
                            </div>
                        </div>

                        <!-- OAuth Authentication -->
                        <?php if (!$isConfigured): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">OAuth Authentication</h5>
                                </div>
                                <div class="card-body">
                                    <p>Click the button below to authenticate with Microsoft and configure email access:</p>
                                    <a href="<?php echo $oauth->getAuthorizationUrl(); ?>" class="btn btn-primary btn-lg">
                                        <i class="fab fa-microsoft me-2"></i>
                                        Authenticate with Microsoft
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Test Email -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Test OAuth Email</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label for="test_email" class="form-label">Test Email Address</label>
                                            <input type="email" class="form-control" id="test_email" name="test_email" 
                                                   value="andrewa@africacdc.org" placeholder="Enter email address to test" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-paper-plane me-2"></i>
                                            Send Test Email
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Re-authenticate -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Re-authenticate</h5>
                                </div>
                                <div class="card-body">
                                    <p>If you need to re-authenticate or update permissions:</p>
                                    <a href="<?php echo $oauth->getAuthorizationUrl(); ?>" class="btn btn-warning">
                                        <i class="fas fa-sync me-2"></i>
                                        Re-authenticate
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Current Configuration -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Current Configuration</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Tenant ID:</strong><br>
                                        <code><?php echo EXCHANGE_TENANT_ID ?: 'Not configured'; ?></code>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Client ID:</strong><br>
                                        <code><?php echo EXCHANGE_CLIENT_ID ?: 'Not configured'; ?></code>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <strong>Redirect URI:</strong><br>
                                        <code><?php echo EXCHANGE_REDIRECT_URI; ?></code>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Scope:</strong><br>
                                        <code><?php echo EXCHANGE_SCOPE; ?></code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Benefits of OAuth -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Why OAuth 2.0?</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-shield-alt text-success me-2"></i>Security Benefits</h6>
                                        <ul class="small">
                                            <li>No password storage required</li>
                                            <li>Token-based authentication</li>
                                            <li>Automatic token refresh</li>
                                            <li>Revocable access</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-cog text-primary me-2"></i>Technical Benefits</h6>
                                        <ul class="small">
                                            <li>No SMTP authentication needed</li>
                                            <li>Uses Microsoft Graph API</li>
                                            <li>Modern authentication standard</li>
                                            <li>Better error handling</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
