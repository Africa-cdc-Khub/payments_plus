<?php
/**
 * Registrations Management Page
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../db_connector.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Registrations';

// Get filter parameters
$filters = [
    'search' => $_GET['search'] ?? '',
    'registration_type' => $_GET['registration_type'] ?? '',
    'package_type' => $_GET['package_type'] ?? '',
    'status' => $_GET['status'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

// Get filtered registrations
$registrations = getRegistrations($filters);

// Export functionality
if (isset($_GET['export'])) {
    $exportType = $_GET['export'];
    
    if ($exportType === 'registrations') {
        // Export registrations to CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="registrations_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'ID', 'Date', 'First Name', 'Last Name', 'Email', 'Phone', 'Country', 'Nationality',
            'Organization', 'Package Name', 'Package Type', 'Registration Type', 'Amount', 'Currency',
            'Status', 'Payment Reference'
        ]);
        
        // Data
        foreach ($registrations as $reg) {
            fputcsv($output, [
                $reg['id'],
                date('Y-m-d H:i:s', strtotime($reg['created_at'])),
                $reg['first_name'],
                $reg['last_name'],
                $reg['email'],
                $reg['phone'] ?? '',
                $reg['country'] ?? '',
                $reg['nationality'] ?? '',
                $reg['organization'] ?? '',
                $reg['package_name'],
                ucfirst($reg['package_type']),
                ucfirst($reg['registration_type']),
                $reg['total_amount'],
                $reg['currency'],
                ucfirst($reg['status']),
                $reg['payment_reference'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    if ($exportType === 'participants') {
        // Export all participants to CSV
        $pdo = getConnection();
        $sql = "SELECT rp.*, r.id as registration_id, r.payment_reference, r.created_at as registration_date,
                u.first_name as primary_first_name, u.last_name as primary_last_name, u.email as primary_email,
                p.name as package_name
                FROM registration_participants rp
                JOIN registrations r ON rp.registration_id = r.id
                JOIN users u ON r.user_id = u.id
                JOIN packages p ON r.package_id = p.id
                ORDER BY r.created_at DESC, rp.id ASC";
        $stmt = $pdo->query($sql);
        $participants = $stmt->fetchAll();
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="participants_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'Registration ID', 'Registration Date', 'Payment Reference', 'Package',
            'Primary Contact', 'Primary Email',
            'Participant Title', 'Participant First Name', 'Participant Last Name', 
            'Participant Email', 'Participant Nationality', 'Passport Number', 'Organization'
        ]);
        
        // Data
        foreach ($participants as $p) {
            fputcsv($output, [
                $p['registration_id'],
                date('Y-m-d H:i:s', strtotime($p['registration_date'])),
                $p['payment_reference'] ?? '',
                $p['package_name'],
                $p['primary_first_name'] . ' ' . $p['primary_last_name'],
                $p['primary_email'],
                $p['title'] ?? '',
                $p['first_name'],
                $p['last_name'],
                $p['email'],
                $p['nationality'] ?? '',
                $p['passport_number'] ?? '',
                $p['organization'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
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
                    <h1 class="m-0">Registrations</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/payments_plus/admin/">Home</a></li>
                        <li class="breadcrumb-item active">Registrations</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filters Card -->
            <div class="card card-primary card-outline collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i> Filters
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Name, email, reference..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Registration Type</label>
                                    <select name="registration_type" class="form-control">
                                        <option value="">All</option>
                                        <option value="individual" <?php echo $filters['registration_type'] === 'individual' ? 'selected' : ''; ?>>Individual</option>
                                        <option value="group" <?php echo $filters['registration_type'] === 'group' ? 'selected' : ''; ?>>Group</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Package Type</label>
                                    <select name="package_type" class="form-control">
                                        <option value="">All</option>
                                        <option value="individual" <?php echo $filters['package_type'] === 'individual' ? 'selected' : ''; ?>>Individual</option>
                                        <option value="group" <?php echo $filters['package_type'] === 'group' ? 'selected' : ''; ?>>Group</option>
                                        <option value="exhibition" <?php echo $filters['package_type'] === 'exhibition' ? 'selected' : ''; ?>>Exhibition</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All</option>
                                        <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="paid" <?php echo $filters['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date Range</label>
                                    <div class="input-group">
                                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <a href="registrations.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Registrations Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> All Registrations (<?php echo count($registrations); ?>)
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filters, ['export' => 'registrations'])); ?>">
                                    <i class="fas fa-file-csv"></i> Export Registrations
                                </a>
                                <a class="dropdown-item" href="?<?php echo http_build_query(['export' => 'participants']); ?>">
                                    <i class="fas fa-users"></i> Export All Participants
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped <?php echo !empty($registrations) ? 'data-table' : ''; ?>">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Country</th>
                                <th>Package</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($registrations)): ?>
                            <tr>
                                <td colspan="10" class="text-center">No registrations found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($registrations as $reg): ?>
                            <tr>
                                <td><?php echo $reg['id']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($reg['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                <td><?php echo htmlspecialchars($reg['country'] ?? 'N/A'); ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($reg['package_name']); ?></small><br>
                                    <span class="badge badge-secondary"><?php echo ucfirst($reg['package_type']); ?></span>
                                </td>
                                <td><span class="badge badge-info"><?php echo ucfirst($reg['registration_type']); ?></span></td>
                                <td><?php echo formatCurrency($reg['total_amount'], $reg['currency']); ?></td>
                                <td><span class="badge badge-<?php echo getStatusBadgeClass($reg['status']); ?>"><?php echo ucfirst($reg['status']); ?></span></td>
                                <td>
                                    <a href="registration-details.php?id=<?php echo $reg['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
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

