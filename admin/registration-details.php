<?php
/**
 * Registration Details Page
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../db_connector.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Registration Details';

// Get registration ID
$registrationId = $_GET['id'] ?? 0;

if (!$registrationId) {
    header('Location: /payments_plus/admin/registrations.php');
    exit;
}

// Get registration details
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT r.*, u.*, p.name as package_name, p.type as package_type, p.price as package_price, p.description as package_description
                       FROM registrations r
                       JOIN users u ON r.user_id = u.id
                       JOIN packages p ON r.package_id = p.id
                       WHERE r.id = ?");
$stmt->execute([$registrationId]);
$registration = $stmt->fetch();

if (!$registration) {
    header('Location: /payments_plus/admin/registrations.php');
    exit;
}

// Get participants if group registration
$participants = [];
if ($registration['registration_type'] === 'group') {
    $stmt = $pdo->prepare("SELECT * FROM registration_participants WHERE registration_id = ?");
    $stmt->execute([$registrationId]);
    $participants = $stmt->fetchAll();
}

// Get payment history
$stmt = $pdo->prepare("SELECT * FROM payments WHERE registration_id = ? ORDER BY created_at DESC");
$stmt->execute([$registrationId]);
$payments = $stmt->fetchAll();

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
                    <h1 class="m-0">Registration #<?php echo $registration['id']; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/payments_plus/admin/">Home</a></li>
                        <li class="breadcrumb-item"><a href="/payments_plus/admin/registrations.php">Registrations</a></li>
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
                <!-- Left Column -->
                <div class="col-md-8">
                    <!-- Registration Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Registration Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-calendar mr-2"></i> Registration Date</strong>
                                    <p class="text-muted"><?php echo date('F d, Y H:i', strtotime($registration['created_at'])); ?></p>

                                    <strong><i class="fas fa-box mr-2"></i> Package</strong>
                                    <p class="text-muted">
                                        <?php echo htmlspecialchars($registration['package_name']); ?><br>
                                        <span class="badge badge-secondary"><?php echo ucfirst($registration['package_type']); ?></span>
                                        <span class="badge badge-info"><?php echo ucfirst($registration['registration_type']); ?></span>
                                    </p>

                                    <strong><i class="fas fa-dollar-sign mr-2"></i> Amount</strong>
                                    <p class="text-muted"><?php echo formatCurrency($registration['total_amount'], $registration['currency']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-check-circle mr-2"></i> Status</strong>
                                    <p>
                                        <span class="badge badge-<?php echo getStatusBadgeClass($registration['status']); ?> badge-lg">
                                            <?php echo ucfirst($registration['status']); ?>
                                        </span>
                                    </p>

                                    <?php if (!empty($registration['payment_reference'])): ?>
                                    <strong><i class="fas fa-receipt mr-2"></i> Payment Reference</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($registration['payment_reference']); ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($registration['exhibition_description'])): ?>
                                    <strong><i class="fas fa-file-alt mr-2"></i> Exhibition Description</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($registration['exhibition_description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Primary Contact Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user"></i> Primary Contact Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-user mr-2"></i> Name</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></p>

                                    <strong><i class="fas fa-envelope mr-2"></i> Email</strong>
                                    <p class="text-muted">
                                        <a href="mailto:<?php echo htmlspecialchars($registration['email']); ?>">
                                            <?php echo htmlspecialchars($registration['email']); ?>
                                        </a>
                                    </p>

                                    <strong><i class="fas fa-phone mr-2"></i> Phone</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($registration['phone'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-building mr-2"></i> Organization</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($registration['organization'] ?? 'N/A'); ?></p>

                                    <strong><i class="fas fa-flag mr-2"></i> Nationality</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($registration['nationality'] ?? 'N/A'); ?></p>

                                    <strong><i class="fas fa-globe mr-2"></i> Country</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($registration['country'] ?? 'N/A'); ?></p>
                                </div>
                            </div>

                            <?php if (!empty($registration['address_line1'])): ?>
                            <hr>
                            <strong><i class="fas fa-map-marker-alt mr-2"></i> Address</strong>
                            <p class="text-muted">
                                <?php echo htmlspecialchars($registration['address_line1']); ?><br>
                                <?php if (!empty($registration['address_line2'])): ?>
                                    <?php echo htmlspecialchars($registration['address_line2']); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($registration['city'] ?? ''); ?>, 
                                <?php echo htmlspecialchars($registration['state'] ?? ''); ?> 
                                <?php echo htmlspecialchars($registration['postal_code'] ?? ''); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Participants (if group registration) -->
                    <?php if ($registration['registration_type'] === 'group' && !empty($participants)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Participants (<?php echo count($participants); ?>)
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Nationality</th>
                                        <th>Organization</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participants as $index => $participant): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($participant['title'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                        <td><?php echo htmlspecialchars($participant['nationality'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($participant['organization'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="registrations.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <a href="mailto:<?php echo htmlspecialchars($registration['email']); ?>" class="btn btn-info btn-block">
                                <i class="fas fa-envelope"></i> Send Email
                            </a>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Payment History</h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($payments)): ?>
                            <p class="text-center p-3 text-muted">No payments yet</p>
                            <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($payments as $payment): ?>
                                <li class="list-group-item">
                                    <strong><?php echo formatCurrency($payment['amount'], $payment['currency']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?>
                                    </small><br>
                                    <span class="badge badge-<?php echo getStatusBadgeClass($payment['payment_status']); ?>">
                                        <?php echo ucfirst($payment['payment_status']); ?>
                                    </span>
                                    <?php if (!empty($payment['transaction_uuid'])): ?>
                                    <br><small>Ref: <?php echo htmlspecialchars(substr($payment['transaction_uuid'], 0, 20)); ?>...</small>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($payments)): ?>
                        <div class="card-footer">
                            <a href="payments.php?registration_id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-primary">
                                View All Payments
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Package Details -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-box"></i> Package Details</h3>
                        </div>
                        <div class="card-body">
                            <p><strong><?php echo htmlspecialchars($registration['package_name']); ?></strong></p>
                            <?php if (!empty($registration['package_description'])): ?>
                            <p class="text-muted small"><?php echo htmlspecialchars($registration['package_description']); ?></p>
                            <?php endif; ?>
                            <p>
                                <strong>Price:</strong> <?php echo formatCurrency($registration['package_price'], $registration['currency']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

