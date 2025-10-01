<?php
/**
 * Payment Details Page
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../db_connector.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Payment Details';

// Get payment ID
$paymentId = $_GET['id'] ?? 0;

if (!$paymentId) {
    header('Location: /payments_plus/admin/payments.php');
    exit;
}

// Get payment details
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT p.*, r.id as registration_id, r.registration_type, r.payment_reference as reg_payment_ref,
                       u.first_name, u.last_name, u.email, u.phone, u.organization, u.country,
                       pkg.name as package_name, pkg.type as package_type
                       FROM payments p
                       JOIN registrations r ON p.registration_id = r.id
                       JOIN users u ON r.user_id = u.id
                       JOIN packages pkg ON r.package_id = pkg.id
                       WHERE p.id = ?");
$stmt->execute([$paymentId]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: /payments_plus/admin/payments.php');
    exit;
}

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
                    <h1 class="m-0">Payment #<?php echo $payment['id']; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/payments_plus/admin/">Home</a></li>
                        <li class="breadcrumb-item"><a href="/payments_plus/admin/payments.php">Payments</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <!-- Payment Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-dollar-sign mr-2"></i> Amount</strong>
                                    <p class="text-muted h4"><?php echo formatCurrency($payment['amount'], $payment['currency']); ?></p>

                                    <strong><i class="fas fa-check-circle mr-2"></i> Status</strong>
                                    <p>
                                        <span class="badge badge-<?php echo getStatusBadgeClass($payment['payment_status']); ?> badge-lg">
                                            <?php echo ucfirst($payment['payment_status']); ?>
                                        </span>
                                    </p>

                                    <strong><i class="fas fa-calendar mr-2"></i> Payment Date</strong>
                                    <p class="text-muted">
                                        <?php echo $payment['payment_date'] ? date('F d, Y H:i', strtotime($payment['payment_date'])) : 'Not paid yet'; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-calendar-plus mr-2"></i> Created Date</strong>
                                    <p class="text-muted"><?php echo date('F d, Y H:i', strtotime($payment['created_at'])); ?></p>

                                    <?php if (!empty($payment['transaction_uuid'])): ?>
                                    <strong><i class="fas fa-hashtag mr-2"></i> Transaction UUID</strong>
                                    <p class="text-muted"><code><?php echo htmlspecialchars($payment['transaction_uuid']); ?></code></p>
                                    <?php endif; ?>

                                    <?php if (!empty($payment['payment_reference'])): ?>
                                    <strong><i class="fas fa-receipt mr-2"></i> Payment Reference</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($payment['payment_reference']); ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($payment['payment_method'])): ?>
                                    <strong><i class="fas fa-credit-card mr-2"></i> Payment Method</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($payment['payment_method']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user"></i> User Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-user mr-2"></i> Name</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></p>

                                    <strong><i class="fas fa-envelope mr-2"></i> Email</strong>
                                    <p class="text-muted">
                                        <a href="mailto:<?php echo htmlspecialchars($payment['email']); ?>">
                                            <?php echo htmlspecialchars($payment['email']); ?>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-phone mr-2"></i> Phone</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($payment['phone'] ?? 'N/A'); ?></p>

                                    <strong><i class="fas fa-building mr-2"></i> Organization</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($payment['organization'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-file-alt"></i> Registration Information</h3>
                        </div>
                        <div class="card-body">
                            <p>
                                <strong>Registration ID:</strong> 
                                <a href="registration-details.php?id=<?php echo $payment['registration_id']; ?>">
                                    #<?php echo $payment['registration_id']; ?>
                                </a>
                            </p>
                            <p><strong>Package:</strong> <?php echo htmlspecialchars($payment['package_name']); ?></p>
                            <p>
                                <strong>Type:</strong> 
                                <span class="badge badge-info"><?php echo ucfirst($payment['registration_type']); ?></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="payments.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to Payments
                            </a>
                            <a href="registration-details.php?id=<?php echo $payment['registration_id']; ?>" class="btn btn-info btn-block">
                                <i class="fas fa-file-alt"></i> View Registration
                            </a>
                            <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#updateStatusModal">
                                <i class="fas fa-edit"></i> Update Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="payments.php">
                <div class="modal-header">
                    <h5 class="modal-title">Update Payment Status</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                    
                    <div class="form-group">
                        <label>Current Status</label>
                        <p>
                            <span class="badge badge-<?php echo getStatusBadgeClass($payment['payment_status']); ?> badge-lg">
                                <?php echo ucfirst($payment['payment_status']); ?>
                            </span>
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="payment_status">New Status</label>
                        <select name="payment_status" id="payment_status" class="form-control" required>
                            <option value="pending" <?php echo $payment['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $payment['payment_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $payment['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="cancelled" <?php echo $payment['payment_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Setting status to "Completed" will also update the registration status to "Paid".
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

