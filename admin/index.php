<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../db_connector.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';

// Get dashboard statistics
$stats = getDashboardStats();
$recentRegistrations = getRecentRegistrations(5);

// Get recent pending payments
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT p.*, r.id as registration_id, u.first_name, u.last_name, u.email
                       FROM payments p
                       JOIN registrations r ON p.registration_id = r.id
                       JOIN users u ON r.user_id = u.id
                       WHERE p.payment_status = 'pending'
                       ORDER BY p.created_at DESC
                       LIMIT 5");
$stmt->execute();
$pendingPayments = $stmt->fetchAll();

// Get registration trend data (last 7 days)
$stmt = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count 
                     FROM registrations 
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                     GROUP BY DATE(created_at)
                     ORDER BY date ASC");
$trendData = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <!-- Total Registrations -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info stat-card">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total_registrations']); ?></h3>
                            <p>Total Registrations</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="registrations.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success stat-card">
                        <div class="inner">
                            <h3><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="payments.php?status=completed" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Payments -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning stat-card">
                        <div class="inner">
                            <h3><?php echo number_format($stats['pending_payments']); ?></h3>
                            <p>Pending Payments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="payments.php?status=pending" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Failed Payments -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger stat-card">
                        <div class="inner">
                            <h3><?php echo number_format($stats['failed_payments']); ?></h3>
                            <p>Failed Payments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="payments.php?status=failed" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <!-- Registration Trend -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Registration Trend (Last 7 Days)
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="trendChart" style="height: 250px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Registration Type Distribution -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Registration Type Distribution
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="typeChart" style="height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- More Charts Row -->
            <div class="row">
                <!-- Package Type Distribution -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-box mr-1"></i>
                                Package Type Distribution
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="packageChart" style="height: 250px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Nationality Distribution -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-globe mr-1"></i>
                                Nationality Distribution
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="nationalityChart" style="height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="row">
                <!-- Recent Registrations -->
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Recent Registrations</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Package</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentRegistrations)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No registrations yet</td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($recentRegistrations as $reg): ?>
                                        <tr>
                                            <td>
                                                <a href="registration-details.php?id=<?php echo $reg['id']; ?>">
                                                    <?php echo htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($reg['package_name']); ?></td>
                                            <td><span class="badge badge-info"><?php echo ucfirst($reg['registration_type']); ?></span></td>
                                            <td><?php echo formatCurrency($reg['total_amount'], $reg['currency']); ?></td>
                                            <td><span class="badge badge-<?php echo getStatusBadgeClass($reg['status']); ?>"><?php echo ucfirst($reg['status']); ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($reg['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <a href="registrations.php" class="btn btn-sm btn-info float-right">View All Registrations</a>
                        </div>
                    </div>
                </div>

                <!-- Pending Payments -->
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Pending Payments</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pendingPayments)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No pending payments</td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($pendingPayments as $payment): ?>
                                        <tr>
                                            <td>
                                                <a href="payment-details.php?id=<?php echo $payment['id']; ?>">
                                                    <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo formatCurrency($payment['amount'], $payment['currency']); ?></td>
                                            <td><?php echo date('M d', strtotime($payment['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <a href="payments.php?status=pending" class="btn btn-sm btn-warning float-right">View All Pending</a>
                        </div>
                    </div>
                </div>
            </div>

        </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
// Registration Trend Chart
<?php
$trendLabels = array_column($trendData, 'date');
$trendCounts = array_column($trendData, 'count');
?>
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($trendLabels); ?>,
        datasets: [{
            label: 'Registrations',
            data: <?php echo json_encode($trendCounts); ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Registration Type Chart
const typeCtx = document.getElementById('typeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_map('ucfirst', array_keys($stats['by_type']))); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($stats['by_type'])); ?>,
            backgroundColor: ['#3498db', '#9b59b6']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Package Type Chart
const packageCtx = document.getElementById('packageChart').getContext('2d');
new Chart(packageCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_map('ucfirst', array_keys($stats['by_package_type']))); ?>,
        datasets: [{
            label: 'Registrations',
            data: <?php echo json_encode(array_values($stats['by_package_type'])); ?>,
            backgroundColor: ['#3498db', '#2ecc71', '#f39c12']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Nationality Chart
const nationalityCtx = document.getElementById('nationalityChart').getContext('2d');
new Chart(nationalityCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_keys($stats['by_nationality'])); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($stats['by_nationality'])); ?>,
            backgroundColor: ['#e74c3c', '#3498db']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>


