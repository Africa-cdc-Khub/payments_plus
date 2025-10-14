<?php
require_once 'bootstrap.php';
require_once 'functions.php';

// Simple authentication check
$admin_key = $_ENV['ADMIN_KEY'] ?? 'admin123'; // Change this to a secure key
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $admin_key) {
    http_response_code(403);
    die('Access denied');
}

$message = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_reminders'])) {
    // Run the reminders command
    $command = 'cd ' . __DIR__ . ' && php daily_reminders.php 2>&1';
    $output = shell_exec($command);
    $message = 'Reminders command executed successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Run Reminders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .admin-container { max-width: 800px; margin: 50px auto; }
        .command-output { background: #000; color: #00ff00; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container admin-container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-tasks"></i> Admin - Run Reminders</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <p>This will run the daily reminders command to process email queues and send payment reminders.</p>
                
                <form method="POST">
                    <button type="submit" name="run_reminders" class="btn btn-primary">
                        <i class="fas fa-play"></i> Run Reminders Now
                    </button>
                </form>
                
                <?php if ($output): ?>
                    <h5 class="mt-4">Command Output:</h5>
                    <div class="command-output"><?php echo htmlspecialchars($output); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
