    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-success elevation-4">
        <!-- Brand Logo -->
        <a href="/payments_plus/admin/" class="brand-link">
            <img src="/payments_plus/images/logo.png" alt="CPHIA Logo" class="brand-image img-circle elevation-3" style="opacity: .8" onerror="this.style.display='none'">
            <span class="brand-text font-weight-light">CPHIA 2025 Admin</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-white"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?php echo htmlspecialchars($admin['full_name']); ?></a>
                    <small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?></small>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="/payments_plus/admin/" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- Registrations -->
                    <li class="nav-item">
                        <a href="/payments_plus/admin/registrations.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'registrations.php' || basename($_SERVER['PHP_SELF']) == 'registration-details.php') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                Registrations
                                <span class="badge badge-info right">
                                    <?php
                                    $regCount = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
                                    echo $regCount;
                                    ?>
                                </span>
                            </p>
                        </a>
                    </li>

                    <!-- Payments -->
                    <li class="nav-item">
                        <a href="/payments_plus/admin/payments.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'payments.php' || basename($_SERVER['PHP_SELF']) == 'payment-details.php') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-money-bill-wave"></i>
                            <p>
                                Payments
                                <?php
                                $pendingPayments = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'pending'")->fetchColumn();
                                if ($pendingPayments > 0):
                                ?>
                                <span class="badge badge-warning right"><?php echo $pendingPayments; ?></span>
                                <?php endif; ?>
                            </p>
                        </a>
                    </li>

                    <!-- Divider -->
                    <li class="nav-header">EMAIL MANAGEMENT</li>
                    
                    <!-- Email OAuth -->
                    <li class="nav-item">
                        <a href="/payments_plus/admin/email-oauth.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'email-oauth.php') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-envelope-open-text"></i>
                            <p>OAuth Setup</p>
                        </a>
                    </li>

                    <!-- Email Status -->
                    <li class="nav-item">
                        <a href="/payments_plus/admin/email-status.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'email-status.php') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-envelope-circle-check"></i>
                            <p>Email Status</p>
                        </a>
                    </li>

                    <!-- Divider -->
                    <li class="nav-header">SYSTEM</li>

                    <!-- Admin Users -->
                    <?php if (isSuperAdmin()): ?>
                    <li class="nav-item">
                        <a href="/payments_plus/admin/admins.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'admins.php') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>Admin Users</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Settings -->
                    <li class="nav-item">
                        <a href="/payments_plus/admin/settings.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Settings</p>
                        </a>
                    </li>

                    <!-- Logout -->
                    <li class="nav-item">
                        <a href="/payments_plus/admin/logout.php" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

