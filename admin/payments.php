<?php
/**
 * Payments Management Page
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../db_connector.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Payments';

$message = '';
$messageType = '';

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $paymentId = $_POST['payment_id'] ?? 0;
    $newStatus = $_POST['payment_status'] ?? '';
    
    if ($paymentId && in_array($newStatus, ['pending', 'completed', 'failed', 'cancelled'])) {
        if (updatePaymentStatus($paymentId, $newStatus)) {
            $message = 'Payment status updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update payment status.';
            $messageType = 'danger';
        }
    }
}

// Get filter parameters
$filters = [
    'search' => $_GET['search'] ?? '',
    'payment_status' => $_GET['status'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

// Get filtered payments
$payments = getPayments($filters);

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
                    <h1 class="m-0">Payments</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/payments_plus/admin/">Home</a></li>
                        <li class="breadcrumb-item active">Payments</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <i class="icon fas fa-<?php echo $messageType === 'success' ? 'check' : 'ban'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row">
                <?php
                $pdo = getConnection();
                $totalPending = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'pending'")->fetchColumn();
                $totalCompleted = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'completed'")->fetchColumn();
                $totalFailed = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'failed'")->fetchColumn();
                $totalAmount = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'completed'")->fetchColumn() ?: 0;
                ?>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $totalPending; ?></h3>
                            <p>Pending Payments</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                        <a href="?status=pending" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $totalCompleted; ?></h3>
                            <p>Completed Payments</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                        <a href="?status=completed" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $totalFailed; ?></h3>
                            <p>Failed Payments</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                        <a href="?status=failed" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo formatCurrency($totalAmount); ?></h3>
                            <p>Total Collected</p>
                        </div>
                        <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                        <a href="#" class="small-box-footer">Revenue <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card card-primary card-outline collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Name, email, reference, transaction ID..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All</option>
                                        <option value="pending" <?php echo $filters['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo $filters['payment_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="failed" <?php echo $filters['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        <option value="cancelled" <?php echo $filters['payment_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date From</label>
                                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date To</label>
                                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <a href="payments.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payments Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> All Payments (<?php echo count($payments); ?>)
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped <?php echo !empty($payments) ? 'data-table' : ''; ?>">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Package</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Transaction ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No payments found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo $payment['id']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['email']); ?></td>
                                <td><small><?php echo htmlspecialchars($payment['package_name']); ?></small></td>
                                <td><?php echo formatCurrency($payment['amount'], $payment['currency']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo getStatusBadgeClass($payment['payment_status']); ?>">
                                        <?php echo ucfirst($payment['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo $payment['transaction_uuid'] ? htmlspecialchars(substr($payment['transaction_uuid'], 0, 15)) . '...' : 'N/A'; ?></small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateModal<?php echo $payment['id']; ?>" title="Update Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="payment-details.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Update Status Modal -->
                            <div class="modal fade" id="updateModal<?php echo $payment['id']; ?>" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form method="POST" action="">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Payment Status</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                
                                                <div class="form-group">
                                                    <label>Payment Details</label>
                                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></p>
                                                    <p><strong>Amount:</strong> <?php echo formatCurrency($payment['amount'], $payment['currency']); ?></p>
                                                    <p><strong>Current Status:</strong> 
                                                        <span class="badge badge-<?php echo getStatusBadgeClass($payment['payment_status']); ?>">
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
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

