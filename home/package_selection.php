<div class="package-selection-container" id="packageSelection" <?php echo (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST') ? 'style="display: none;"' : ''; ?>
>
    <div class="text-center mb-4">
        <div class="alert alert-warning mb-4" style="text-align: left;">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h5>
            <p class="mb-2"><strong>Please ensure you select the correct package for your registration.</strong></p>
            <p class="mb-0">Selecting the wrong package may result in disqualification or additional fees. If you are unsure about which package to choose, please contact our support team at <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" class="text-decoration-none"><strong><?php echo SUPPORT_EMAIL; ?></strong></a> before proceeding.</p>
        </div>
        <a href="registration_lookup.php" class="btn btn-primary btn-lg view-registrations-btn"><i class="fas fa-list-alt me-2"></i>View My Registrations</a>
    </div>
    <?php if (!empty($registrationHistory)): ?>
        <div class="alert alert-info mb-4">
            <h5><i class="fas fa-history me-2"></i>Your Registration History for <?php echo CONFERENCE_SHORT_NAME; ?> (<?php echo CONFERENCE_DATES; ?>)</h5>
            <p class="mb-3">You have previously registered for the following packages for this event:</p>
            <div class="row">
                <?php foreach ($registrationHistory as $registration): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($registration['package_name']); ?></h6>
                                    <div class="d-flex flex-column align-items-end">
                                        <?php if ($registration['payment_status'] === 'completed'): ?>
                                            <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i>Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning mb-1"><i class="fas fa-clock me-1"></i>Pending Payment</span>
                                        <?php endif; ?>
                                        <small class="text-muted"><?php echo ucfirst($registration['status']); ?></small>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    <div>Registration ID: #<?php echo $registration['id']; ?></div>
                                    <div>Date: <?php echo date('M j, Y', strtotime($registration['created_at'])); ?></div>
                                    <div>Amount: <?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?></div>
                                </div>
                                <?php if ($registration['payment_status'] !== 'completed'): ?>
                                    <div class="mt-3">
                                        <a href="registration_lookup.php?action=pay&id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-success"><i class="fas fa-credit-card me-1"></i>Complete Payment</a>
                                        <a href="registration_lookup.php?view=<?php echo $registration['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>View Details</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Registration Policy:</strong> You can register multiple times for different packages. 
                    Only <strong>paid registrations</strong> are considered confirmed for the conference. 
                    Unpaid registrations can be modified or cancelled.
                </small>
                <div class="mt-2">
                    <a href="registration_lookup.php" class="btn btn-sm" style="background: var(--primary-green); border-color: var(--primary-green); color: white;"><i class="fas fa-eye me-1"></i>View Detailed Registration History</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="package-category">
        <h3>Registration Packages</h3>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <h4 class="text-center mb-3">Individual Registration</h4>
            </div>
            <?php foreach ($individualPackages as $package): ?>
                <div class="col-6 col-md-3">
                    <div class="card package-card h-100" data-package-id="<?php echo $package['id']; ?>" data-type="<?php echo $package['type']; ?>" data-package-name="<?php echo htmlspecialchars($package['name']); ?>" data-continent="<?php echo htmlspecialchars($package['continent'] ?? 'all'); ?>">
                        <div class="card-body d-flex flex-column p-3 text-center">
                            <div class="package-icon mb-3">
                                <i class="<?php echo htmlspecialchars($package['icon'] ?? 'fas fa-ticket-alt'); ?> <?php echo htmlspecialchars($package['color'] ?? 'text-primary'); ?> fa-3x"></i>
                            </div>
                            <h5 class="card-title mb-3"><?php echo htmlspecialchars($package['name']); ?></h5>
                            <?php if ($package['price'] > 0): ?>
                                <div class="package-price h4 text-success mb-3"><?php echo formatCurrency($package['price']); ?></div>
                            <?php endif; ?>
                            <button type="button" class="btn btn-primary btn-lg select-package mt-auto">Register</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

