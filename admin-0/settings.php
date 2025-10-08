<?php
/**
 * Settings Page
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../db_connector.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Settings';

$message = '';
$messageType = '';
$currentAdmin = getCurrentAdmin();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = 'All fields are required.';
        $messageType = 'danger';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'New passwords do not match.';
        $messageType = 'danger';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $messageType = 'danger';
    } else {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$currentAdmin['id']]);
        $admin = $stmt->fetch();
        
        if (password_verify($currentPassword, $admin['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $currentAdmin['id']]);
            
            $message = 'Password changed successfully!';
            $messageType = 'success';
        } else {
            $message = 'Current password is incorrect.';
            $messageType = 'danger';
        }
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    
    if (empty($fullName) || empty($email)) {
        $message = 'Full name and email are required.';
        $messageType = 'danger';
    } else {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("UPDATE admins SET full_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$fullName, $email, $currentAdmin['id']]);
            
            $_SESSION['admin_full_name'] = $fullName;
            $message = 'Profile updated successfully!';
            $messageType = 'success';
            $currentAdmin['full_name'] = $fullName;
            $currentAdmin['email'] = $email;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = 'Email already exists.';
            } else {
                $message = 'Error updating profile.';
            }
            $messageType = 'danger';
        }
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
                    <h1 class="m-0">Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/payments_plus/admin/">Home</a></li>
                        <li class="breadcrumb-item active">Settings</li>
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
                <!-- Profile Settings -->
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user"></i> Profile Settings</h3>
                        </div>
                        <form method="POST" action="">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($currentAdmin['username']); ?>" disabled>
                                    <small class="text-muted">Username cannot be changed</small>
                                </div>
                                <div class="form-group">
                                    <label for="full_name">Full Name</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo htmlspecialchars($currentAdmin['full_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($currentAdmin['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Role</label>
                                    <input type="text" class="form-control" value="<?php echo ucfirst(str_replace('_', ' ', $currentAdmin['role'])); ?>" disabled>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-key"></i> Change Password</h3>
                        </div>
                        <form method="POST" action="">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="change_password" class="btn btn-warning">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> System Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong><i class="fas fa-tag mr-2"></i> Application Name</strong>
                                    <p class="text-muted"><?php echo APP_NAME; ?></p>
                                </div>
                                <div class="col-md-3">
                                    <strong><i class="fas fa-server mr-2"></i> Environment</strong>
                                    <p class="text-muted"><?php echo strtoupper(APP_ENV); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <strong><i class="fas fa-globe mr-2"></i> Application URL</strong>
                                    <p class="text-muted"><a href="<?php echo APP_URL; ?>" target="_blank"><?php echo APP_URL; ?></a></p>
                                </div>
                                <div class="col-md-3">
                                    <strong><i class="fas fa-database mr-2"></i> Database</strong>
                                    <p class="text-muted"><?php echo DB_NAME; ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong><i class="fas fa-calendar mr-2"></i> Conference Dates</strong>
                                    <p class="text-muted"><?php echo CONFERENCE_DATES; ?></p>
                                </div>
                                <div class="col-md-3">
                                    <strong><i class="fas fa-map-marker-alt mr-2"></i> Location</strong>
                                    <p class="text-muted"><?php echo CONFERENCE_LOCATION; ?></p>
                                </div>
                                <div class="col-md-3">
                                    <strong><i class="fas fa-building mr-2"></i> Venue</strong>
                                    <p class="text-muted"><?php echo CONFERENCE_VENUE; ?></p>
                                </div>
                                <div class="col-md-3">
                                    <strong><i class="fas fa-dollar-sign mr-2"></i> Currency</strong>
                                    <p class="text-muted"><?php echo DEFAULT_CURRENCY; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="/payments_plus/admin/email-oauth.php" class="btn btn-info">
                                <i class="fas fa-envelope"></i> Configure Email
                            </a>
                            <a href="/payments_plus/admin/email-status.php" class="btn btn-secondary">
                                <i class="fas fa-envelope-circle-check"></i> Email Status
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>


