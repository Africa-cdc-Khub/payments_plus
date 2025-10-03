<?php
require_once 'bootstrap.php';
require_once 'functions.php';

// Handle payment response from CyberSource
$paymentStatus = 'failed';
$message = 'Payment processing failed';
$registrationId = null;

if (isset($_POST['req_transaction_uuid'])) {
    $transactionUuid = $_POST['req_transaction_uuid'];
    
    // Get payment record
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT p.*, r.id as registration_id, r.user_id, r.total_amount 
                          FROM payments p 
                          JOIN registrations r ON p.registration_id = r.id 
                          WHERE p.transaction_uuid = ?");
    $stmt->execute([$transactionUuid]);
    $payment = $stmt->fetch();
    
    if ($payment) {
        $registrationId = $payment['registration_id'];
        
        // Check payment status from CyberSource response
        if (isset($_POST['decision']) && $_POST['decision'] === 'ACCEPT') {
            $paymentStatus = 'completed';
            $message = 'Payment completed successfully!';
            
            // Update payment status
            updatePaymentStatus($transactionUuid, 'completed', $_POST['req_reference_number'] ?? null);
            
            // Update registration status
            updateRegistrationStatus($registrationId, 'paid', $_POST['req_reference_number'] ?? null);
            
            // Send payment confirmation emails
            if ($registration) {
                $user = [
                    'first_name' => $registration['first_name'],
                    'last_name' => $registration['last_name'],
                    'email' => $registration['user_email']
                ];
                
                $participants = getRegistrationParticipants($registrationId);
                $participantData = [];
                foreach ($participants as $participant) {
                    $participantData[] = [
                        'name' => $participant['title'] . ' ' . $participant['first_name'] . ' ' . $participant['last_name'],
                        'email' => $participant['email'],
                        'nationality' => $participant['nationality']
                    ];
                }
                
                sendPaymentConfirmationEmails(
                    $user,
                    $registrationId,
                    $registration['total_amount'],
                    $_POST['req_reference_number'] ?? $transactionUuid,
                    $participantData
                );
            }
            
        } else {
            $paymentStatus = 'failed';
            $message = 'Payment was declined. Please try again.';
            
            // Update payment status
            updatePaymentStatus($transactionUuid, 'failed');
        }
    }
}

// Get registration details
$registration = null;
if ($registrationId) {
    $registration = getRegistrationById($registrationId);
    $participants = getRegistrationParticipants($registrationId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPHIA 2025 - Payment Response</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <img src="images/logo.png" alt="CPHIA 2025" class="logo-img">
                </div>
                       <div class="header-text">
                           <div class="au-branding">
                               <img src="images/au-logo.svg" alt="African Union" class="au-logo">
                               <span class="au-text">African Union</span>
                           </div>
                           <h1><?php echo CONFERENCE_NAME; ?></h1>
                           <h2><?php echo CONFERENCE_SHORT_NAME; ?></h2>
                           <p class="conference-dates"><?php echo CONFERENCE_DATES; ?> • <?php echo CONFERENCE_LOCATION; ?></p>
                       </div>
            </div>
        </header>

        <div class="response-container">
            <div class="response-content">
                <!-- Payment Status -->
                <div class="status-section">
                    <?php if ($paymentStatus === 'completed'): ?>
                        <div class="status-success">
                            <div class="status-icon">✅</div>
                            <h2>Payment Successful!</h2>
                            <p>Your registration for CPHIA 2025 has been confirmed.</p>
                        </div>
                    <?php else: ?>
                        <div class="status-error">
                            <div class="status-icon">❌</div>
                            <h2>Payment Failed</h2>
                            <p><?php echo htmlspecialchars($message); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($registration): ?>
                <!-- Registration Details -->
                <div class="details-section">
                    <h3>Registration Details</h3>
                    <div class="details-card">
                        <div class="detail-item">
                            <span class="detail-label">Registration ID:</span>
                            <span class="detail-value">#<?php echo $registration['id']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Package:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($registration['package_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Registration Type:</span>
                            <span class="detail-value"><?php echo ucfirst($registration['registration_type']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Registrant:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($registration['user_email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Amount Paid:</span>
                            <span class="detail-value"><?php echo formatCurrency($registration['total_amount']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value status-<?php echo $paymentStatus; ?>"><?php echo ucfirst($paymentStatus); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Participants (if group registration) -->
                <?php if (!empty($participants)): ?>
                <div class="participants-section">
                    <h3>Group Participants</h3>
                    <div class="participants-grid">
                        <?php foreach ($participants as $index => $participant): ?>
                        <div class="participant-item">
                            <div class="participant-number"><?php echo $index + 1; ?></div>
                            <div class="participant-details">
                                <div class="participant-name">
                                    <?php echo htmlspecialchars($participant['title'] . ' ' . $participant['first_name'] . ' ' . $participant['last_name']); ?>
                                </div>
                                <div class="participant-email"><?php echo htmlspecialchars($participant['email']); ?></div>
                                <?php if ($participant['nationality']): ?>
                                <div class="participant-nationality"><?php echo htmlspecialchars($participant['nationality']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Next Steps -->
                <div class="next-steps">
                    <?php if ($paymentStatus === 'completed'): ?>
                        <h3>What's Next?</h3>
                        <div class="steps-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4>Confirmation Email</h4>
                                    <p>A confirmation email has been sent to your registered email address with all the details.</p>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4>Conference Updates</h4>
                                    <p>You will receive regular updates about the conference, including program details and logistics.</p>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4>Conference Materials</h4>
                                    <p>Conference materials and access information will be sent closer to the event date.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <h3>What to Do Next?</h3>
                        <div class="steps-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4>Check Payment Details</h4>
                                    <p>Verify your payment information and try again.</p>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4>Contact Support</h4>
                                    <p>If you continue to experience issues, please contact our support team.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="actions-section">
                    <?php if ($paymentStatus === 'completed'): ?>
                        <a href="index.php" class="btn btn-primary">Register Another Person</a>
                        <a href="https://cphia2025.com" class="btn btn-secondary" target="_blank">Visit Conference Website</a>
                    <?php else: ?>
                        <a href="checkout.php?registration_id=<?php echo $registrationId; ?>" class="btn btn-primary">Try Payment Again</a>
                        <a href="index.php" class="btn btn-secondary">Start New Registration</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
        .response-container {
            max-width: 800px;
            margin: 0 auto;
            padding: var(--spacing-8) 0;
        }

        .response-content {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-8);
        }

        .status-section {
            text-align: center;
            margin-bottom: var(--spacing-8);
        }

        .status-success, .status-error {
            padding: var(--spacing-6);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-4);
        }

        .status-success {
            background-color: #f0fdf4;
            border: 2px solid var(--accent-green);
        }

        .status-error {
            background-color: #fef2f2;
            border: 2px solid var(--accent-red);
        }

        .status-icon {
            font-size: 4rem;
            margin-bottom: var(--spacing-4);
        }

        .status-success h2 {
            color: var(--accent-green);
            margin-bottom: var(--spacing-2);
        }

        .status-error h2 {
            color: var(--accent-red);
            margin-bottom: var(--spacing-2);
        }

        .details-section, .participants-section, .next-steps {
            margin-bottom: var(--spacing-8);
        }

        .details-card {
            background: var(--light-gray);
            border-radius: var(--radius-lg);
            padding: var(--spacing-4);
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-2) 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .detail-value {
            font-weight: 600;
        }

        .status-completed {
            color: var(--accent-green);
        }

        .status-failed {
            color: var(--accent-red);
        }

        .participants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-4);
        }

        .participant-item {
            display: flex;
            align-items: center;
            background: var(--light-gray);
            border-radius: var(--radius-lg);
            padding: var(--spacing-4);
        }

        .participant-number {
            background: var(--primary-blue);
            color: var(--white);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: var(--spacing-3);
        }

        .participant-details {
            flex: 1;
        }

        .participant-name {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: var(--spacing-1);
        }

        .participant-email {
            color: var(--medium-gray);
            font-size: var(--font-size-sm);
        }

        .participant-nationality {
            color: var(--medium-gray);
            font-size: var(--font-size-sm);
            font-style: italic;
        }

        .steps-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-4);
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-4);
        }

        .step-number {
            background: var(--primary-blue);
            color: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }

        .step-content h4 {
            color: var(--primary-blue);
            margin-bottom: var(--spacing-1);
        }

        .step-content p {
            color: var(--medium-gray);
        }

        .actions-section {
            text-align: center;
            padding-top: var(--spacing-6);
            border-top: 1px solid #e2e8f0;
        }

        .actions-section .btn {
            margin: 0 var(--spacing-2);
        }
    </style>
</body>
</html>
