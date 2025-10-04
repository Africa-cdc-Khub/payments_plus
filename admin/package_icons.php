<?php
/**
 * Package Icons Management
 * Admin interface to manage package icons and colors
 */

require_once '../bootstrap.php';
require_once '../functions.php';

// Check if user is admin (you can implement proper authentication)
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE packages SET icon = ?, color = ? WHERE id = ?");
    
    foreach ($_POST['packages'] as $packageId => $data) {
        $stmt->execute([
            $data['icon'],
            $data['color'],
            $packageId
        ]);
    }
    
    $success = "Package icons updated successfully!";
}

// Get all packages
$pdo = getConnection();
$result = $pdo->query("SELECT * FROM packages ORDER BY id");
$packages = $result->fetchAll(PDO::FETCH_ASSOC);

// Available icons and colors
$availableIcons = [
    'fas fa-globe-africa' => 'Globe Africa',
    'fas fa-globe-americas' => 'Globe Americas',
    'fas fa-globe-asia' => 'Globe Asia',
    'fas fa-graduation-cap' => 'Graduation Cap',
    'fas fa-user-tie' => 'User Tie',
    'fas fa-calendar-plus' => 'Calendar Plus',
    'fas fa-store' => 'Store',
    'fas fa-shield-alt' => 'Shield',
    'fas fa-dove' => 'Dove',
    'fas fa-hands-helping' => 'Hands Helping',
    'fas fa-crown' => 'Crown',
    'fas fa-ticket-alt' => 'Ticket',
    'fas fa-user' => 'User',
    'fas fa-users' => 'Users',
    'fas fa-star' => 'Star',
    'fas fa-heart' => 'Heart',
    'fas fa-gem' => 'Gem',
    'fas fa-medal' => 'Medal',
    'fas fa-trophy' => 'Trophy'
];

$availableColors = [
    'text-primary' => 'Primary (Blue)',
    'text-secondary' => 'Secondary (Gray)',
    'text-success' => 'Success (Green)',
    'text-danger' => 'Danger (Red)',
    'text-warning' => 'Warning (Yellow)',
    'text-info' => 'Info (Cyan)',
    'text-light' => 'Light',
    'text-dark' => 'Dark'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Icons Management - CPHIA 2025</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .icon-preview {
            font-size: 2rem;
            margin: 0.5rem;
            display: inline-block;
        }
        .package-card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .package-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-palette me-2"></i>Package Icons Management</h1>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Admin
                    </a>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <?php foreach ($packages as $package): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="package-card">
                                    <div class="text-center mb-3">
                                        <div class="icon-preview">
                                            <i class="<?php echo htmlspecialchars($package['icon']); ?> <?php echo htmlspecialchars($package['color']); ?>"></i>
                                        </div>
                                        <h5><?php echo htmlspecialchars($package['name']); ?></h5>
                                        <small class="text-muted"><?php echo ucfirst($package['type']); ?> Package</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Icon</label>
                                        <select name="packages[<?php echo $package['id']; ?>][icon]" class="form-select" onchange="updatePreview(this, '<?php echo $package['id']; ?>')">
                                            <?php foreach ($availableIcons as $iconClass => $iconName): ?>
                                                <option value="<?php echo $iconClass; ?>" <?php echo ($package['icon'] === $iconClass) ? 'selected' : ''; ?>>
                                                    <?php echo $iconName; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Color</label>
                                        <select name="packages[<?php echo $package['id']; ?>][color]" class="form-select" onchange="updatePreview(this, '<?php echo $package['id']; ?>')">
                                            <?php foreach ($availableColors as $colorClass => $colorName): ?>
                                                <option value="<?php echo $colorClass; ?>" <?php echo ($package['color'] === $colorClass) ? 'selected' : ''; ?>>
                                                    <?php echo $colorName; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="preview-icon" id="preview-<?php echo $package['id']; ?>">
                                            <i class="<?php echo htmlspecialchars($package['icon']); ?> <?php echo htmlspecialchars($package['color']); ?> fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update All Icons
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updatePreview(selectElement, packageId) {
            const iconSelect = selectElement.name.includes('[icon]') ? selectElement : 
                selectElement.parentElement.parentElement.querySelector('select[name*="[icon]"]');
            const colorSelect = selectElement.name.includes('[color]') ? selectElement : 
                selectElement.parentElement.parentElement.querySelector('select[name*="[color]"]');
            
            const icon = iconSelect.value;
            const color = colorSelect.value;
            
            const previewElement = document.getElementById('preview-' + packageId);
            previewElement.innerHTML = `<i class="${icon} ${color} fa-2x"></i>`;
        }
    </script>
</body>
</html>
