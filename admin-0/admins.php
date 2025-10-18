<?php
/**
 * Admin Users Management Page
 * Only accessible to super admins
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../db_connector.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Admin Users';

// Check if user is super admin
if (!isSuperAdmin()) {
    header('Location: /payments_plus/admin/');
    exit;
}

$message = '';
$messageType = '';

// Handle admin creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $message = 'All fields are required.';
        $messageType = 'danger';
    } else {
        try {
            $pdo = getConnection();
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $full_name, $role]);
            
            $message = 'Admin user created successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = 'Username or email already exists.';
            } else {
                $message = 'Error creating admin user.';
            }
            $messageType = 'danger';
        }
    }
}

// Handle admin deletion
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $currentAdmin = getCurrentAdmin();
    
    if ($deleteId == $currentAdmin['id']) {
        $message = 'You cannot delete your own account.';
        $messageType = 'danger';
    } else {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$deleteId]);
            
            $message = 'Admin user deleted successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error deleting admin user.';
            $messageType = 'danger';
        }
    }
}

// Handle admin status toggle
if (isset($_GET['toggle'])) {
    $toggleId = $_GET['toggle'];
    $currentAdmin = getCurrentAdmin();
    
    if ($toggleId == $currentAdmin['id']) {
        $message = 'You cannot deactivate your own account.';
        $messageType = 'danger';
    } else {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("UPDATE admins SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$toggleId]);
            
            $message = 'Admin status updated successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error updating admin status.';
            $messageType = 'danger';
        }
    }
}

// Get all admins
$pdo = getConnection();
$stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
$admins = $stmt->fetchAll();

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
                    <h1 class="m-0">Admin Users</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/payments_plus/admin/">Home</a></li>
                        <li class="breadcrumb-item active">Admin Users</li>
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

            <div class="row">
                <!-- Create Admin Form -->
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-plus"></i> Create New Admin</h3>
                        </div>
                        <form method="POST" action="">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="full_name">Full Name</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" required minlength="6">
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select name="role" id="role" class="form-control" required>
                                        <option value="admin">Admin</option>
                                        <option value="super_admin">Super Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="create_admin" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Admin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Admins List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users"></i> All Admins (<?php echo count($admins); ?>)</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?php echo $admin['id']; ?></td>
                                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $admin['role'] === 'super_admin' ? 'danger' : 'primary'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($admin['is_active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $admin['last_login'] ? date('M d, Y', strtotime($admin['last_login'])) : 'Never'; ?>
                                        </td>
                                        <td>
                                            <?php if ($admin['id'] != getCurrentAdmin()['id']): ?>
                                                <a href="?toggle=<?php echo $admin['id']; ?>" class="btn btn-sm btn-warning" title="Toggle Status" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-toggle-<?php echo $admin['is_active'] ? 'on' : 'off'; ?>"></i>
                                                </a>
                                                <a href="?delete=<?php echo $admin['id']; ?>" class="btn btn-sm btn-danger confirm-action" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge badge-info">Current User</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>


