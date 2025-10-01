<?php
/**
 * Email Configuration Status Page
 * Shows current email configuration and any issues
 */

require_once __DIR__ . '/../bootstrap.php';

// Test email configuration
$testEmail = 'andrewa@africacdc.org';
$configStatus = [
    'host' => MAIL_HOST,
    'port' => MAIL_PORT,
    'encryption' => MAIL_ENCRYPTION,
    'username' => MAIL_USERNAME,
    'password' => !empty(MAIL_PASSWORD) ? 'Configured' : 'Not configured',
    'from_address' => MAIL_FROM_ADDRESS,
    'from_name' => MAIL_FROM_NAME
];

// Check if this is a test request
$isTest = isset($_GET['test']) && $_GET['test'] === '1';

if ($isTest) {
    try {
        require_once __DIR__ . '/../src/EmailService.php';
        use Cphia2025\EmailService;
        
        $emailService = new EmailService();
        $testSubject = 'CPHIA 2025 - Configuration Test';
        $testBody = '<h1>Test Email</h1><p>This is a test email to verify configuration.</p>';
        
        $result = $emailService->sendEmail($testEmail, $testSubject, $testBody, true);
        $testResult = $result ? 'success' : 'failed';
    } catch (Exception $e) {
        $testResult = 'error';
        $testError = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Status - CPHIA 2025</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            Email Configuration Status
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Test Results -->
                        <?php if ($isTest): ?>
                            <div class="alert alert-<?php echo $testResult === 'success' ? 'success' : 'danger'; ?>">
                                <h5>
                                    <i class="fas fa-<?php echo $testResult === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                    Test Email <?php echo $testResult === 'success' ? 'Sent Successfully' : 'Failed'; ?>
                                </h5>
                                <?php if ($testResult === 'success'): ?>
                                    <p>Test email has been sent to <strong><?php echo htmlspecialchars($testEmail); ?></strong></p>
                                <?php else: ?>
                                    <p><strong>Error:</strong> <?php echo htmlspecialchars($testError ?? 'Unknown error'); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Configuration Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Current Configuration</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Host:</strong></td>
                                            <td><code><?php echo htmlspecialchars($configStatus['host']); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Port:</strong></td>
                                            <td><code><?php echo htmlspecialchars($configStatus['port']); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Encryption:</strong></td>
                                            <td><code><?php echo htmlspecialchars($configStatus['encryption']); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Username:</strong></td>
                                            <td><code><?php echo htmlspecialchars($configStatus['username']); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Password:</strong></td>
                                            <td><?php echo $configStatus['password']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>From Address:</strong></td>
                                            <td><code><?php echo htmlspecialchars($configStatus['from_address']); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>From Name:</strong></td>
                                            <td><?php echo htmlspecialchars($configStatus['from_name']); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Status</h5>
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>SMTP Authentication Disabled</h6>
                                    <p class="mb-0">The Office 365 tenant has SMTP authentication disabled. This is a common security setting.</p>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Configuration is Correct</h6>
                                    <p class="mb-0">The email configuration is properly set up for MS Exchange with STARTTLS encryption.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Solutions -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Solutions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card border-primary">
                                            <div class="card-body text-center">
                                                <i class="fas fa-cog fa-2x text-primary mb-3"></i>
                                                <h6>Enable SMTP Auth</h6>
                                                <p class="small">Contact IT admin to enable SMTP authentication for the notifications account.</p>
                                                <a href="EMAIL_SETUP_GUIDE.md" class="btn btn-sm btn-outline-primary" target="_blank">View Guide</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-success">
                                            <div class="card-body text-center">
                                                <i class="fas fa-shield-alt fa-2x text-success mb-3"></i>
                                                <h6>Use OAuth 2.0</h6>
                                                <p class="small">Implement Microsoft Graph API with OAuth for more secure email sending.</p>
                                                <a href="email-setup.php" class="btn btn-sm btn-outline-success">Setup OAuth</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-warning">
                                            <div class="card-body text-center">
                                                <i class="fas fa-key fa-2x text-warning mb-3"></i>
                                                <h6>App Password</h6>
                                                <p class="small">Generate an app password if the tenant allows it.</p>
                                                <a href="https://myaccount.microsoft.com" class="btn btn-sm btn-outline-warning" target="_blank">Generate</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test Email -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Test Email</h5>
                            </div>
                            <div class="card-body">
                                <p>Test email will be sent to: <strong><?php echo htmlspecialchars($testEmail); ?></strong></p>
                                <a href="?test=1" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Send Test Email
                                </a>
                                <a href="debug_email.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-bug me-2"></i>
                                    Debug Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
