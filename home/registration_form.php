<div class="registration-container" id="registrationForm" <?php echo (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST') ? 'style="display: block;"' : 'style="display: none;"'; ?>>
    <div class="form-header">
        <div class="selected-package-info" id="selectedPackageInfo">
            <?php if (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($formData['package_id'])): ?>
                <?php 
                $selectedPackage = getPackageById($formData['package_id']);
                if ($selectedPackage): 
                ?>
                    <div class="selected-package-card">
                        <div class="package-icon mb-2">
                            <i class="<?php echo htmlspecialchars($selectedPackage['icon'] ?? 'fas fa-ticket-alt'); ?> <?php echo htmlspecialchars($selectedPackage['color'] ?? 'text-primary'); ?> fa-2x"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($selectedPackage['name']); ?></h4>
                        <?php if ($selectedPackage['price'] > 0): ?>
                            <div class="package-price"><?php echo formatCurrency($selectedPackage['price']); ?></div>
                        <?php endif; ?>
                        <div class="package-description mt-3" id="packageDescription" style="display: none;">
                            <small class="text-muted" id="packageDescriptionText">Loading description...</small>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="mb-0">Complete Your Registration</h2>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-12 text-end">
            <button type="button" class="btn btn-secondary" id="changePackage">
                <i class="fas fa-arrow-left me-2"></i>Change Package
            </button>
        </div>
    </div>

    <?php include __DIR__ . '/registration_form_fields.php'; ?>
</div>

