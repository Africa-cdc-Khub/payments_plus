<?php if (isset($success) && $success): ?>
    <?php 
    $userEmail = $userData['email'] ?? '';
    $userRegistrationHistory = [];
    if ($userEmail) {
        $userRegistrationHistory = getRegistrationHistory($userEmail, CONFERENCE_DATES);
    }
    ?>
    <div class="alert alert-success">
        <h3>Registration Successful!</h3>
        <p>Thank you for registering for CPHIA 2025. <?php if ($package['price'] > 0): ?>A payment link has been sent to your email address.<?php else: ?>Your registration is complete - no payment required.<?php endif; ?></p>
        <?php if (isset($registrationId) && !$isSideEvent && !$isExhibition && $package['price'] > 0): ?>
            <div class="mt-4">
                <h5>Complete Your Registration</h5>
                <p class="mb-3">Choose how you'd like to complete your payment:</p>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-credit-card fa-3x text-success mb-3"></i>
                                <h6 class="card-title">Pay Now</h6>
                                <p class="card-text small">Complete your payment immediately to finalize your registration.</p>
                                <a href="registration_lookup.php?action=pay&id=<?php echo $registrationId; ?>" class="btn btn-success">
                                    <i class="fas fa-credit-card me-2"></i>Pay Now
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                                <h6 class="card-title">Pay Later</h6>
                                <p class="card-text small">We'll send you a payment link via email so you can pay when convenient.</p>
                                <button onclick="sendPaymentLink(<?php echo $registrationId; ?>)" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-2"></i>Send Payment Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <p class="small text-muted mb-2">Registration ID: #<?php echo $registrationId; ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php if (isset($isDuplicateRegistration) && $isDuplicateRegistration): ?>
        <div class="alert alert-warning">
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="alert-heading">Duplicate Registration Detected</h4>
                    <p class="mb-3"><?php echo $duplicateMessage; ?></p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="registration_lookup.php" class="btn btn-lg" style="background: var(--primary-green); border-color: var(--primary-green); color: white;">
                            <i class="fas fa-eye me-2"></i>View My Registrations
                        </a>
                        <?php if ($duplicateRegistrationStatus === 'pending'): ?>
                            <a href="registration_lookup.php?action=pay&id=<?php echo $duplicateRegistrationId; ?>" class="btn btn-success">
                                <i class="fas fa-credit-card me-2"></i>Complete Payment
                            </a>
                        <?php endif; ?>
                        <a href="mailto:support@cphia2025.com?subject=Registration%20Inquiry&body=Registration%20ID:%20%23<?php echo $duplicateRegistrationId; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-envelope me-2"></i>Contact Support
                        </a>
                    </div>
                    <hr class="my-3">
                    <small class="text-muted">
                        <strong>Need help?</strong> If you believe this is an error or need to make changes to your registration, 
                        please contact our support team with your registration ID: <strong>#<?php echo $duplicateRegistrationId; ?></strong>
                    </small>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($userRegistrationHistory)): ?>
        <div class="mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Your Registration History for <?php echo CONFERENCE_SHORT_NAME; ?> (<?php echo CONFERENCE_DATES; ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($userRegistrationHistory as $registration): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">
                                            <?php echo htmlspecialchars($registration['package_name']); ?>
                                            <?php if ($registration['registration_type'] === 'group'): ?>
                                                <span class="badge bg-info ms-2"><i class="fas fa-users me-1"></i>Group</span>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="d-flex flex-column align-items-end">
                                            <?php if ($registration['payment_status'] === 'completed'): ?>
                                                <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning mb-1"><i class="fas fa-clock me-1"></i>Pending Payment</span>
                                            <?php endif; ?>
                                            <small class="text-muted"><?php echo ucfirst($registration['status']); ?></small>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="text-muted small">
                                            <div><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></div>
                                            <div><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($registration['email']); ?></div>
                                            <div><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($registration['phone']); ?></div>
                                            <?php if (!empty($registration['organization'])): ?>
                                                <div><i class="fas fa-building me-1"></i><?php echo htmlspecialchars($registration['organization']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-muted small mb-2">
                                        <div><strong>Registration ID:</strong> #<?php echo $registration['id']; ?></div>
                                        <div><strong>Date:</strong> <?php echo date('M j, Y', strtotime($registration['created_at'])); ?></div>
                                        <div><strong>Amount:</strong> <?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?></div>
                                        <?php if ($registration['registration_type'] === 'group'): ?>
                                            <div class="text-info small"><i class="fas fa-info-circle me-1"></i>Group registration - payment made by focal person</div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($registration['payment_status'] !== 'completed' && $registration['total_amount'] > 0): ?>
                                        <div class="mt-2">
                                            <a href="registration_lookup.php?action=pay&id=<?php echo $registration['id']; ?>" class="btn btn-success btn-sm"><i class="fas fa-credit-card me-1"></i>Complete Payment</a>
                                        </div>
                                    <?php elseif ($registration['payment_status'] === 'completed'): ?>
                                        <div class="mt-2">
                                            <a href="registration_lookup.php?action=receipt&id=<?php echo $registration['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-receipt me-1"></i>View Receipt</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-2"><span class="badge bg-success">No Payment Required</span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="registration_lookup.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-search me-1"></i>Search All My Registrations</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <h3>Please correct the following errors:</h3>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

