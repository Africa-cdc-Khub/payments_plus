<?php
/**
 * Rate Limit Management Script
 * Use this to view, clear, or manage rate limits during development
 */

require_once 'bootstrap.php';
require_once 'db_connector.php';

// Simple authentication for this script
$adminKey = 'admin123'; // Change this in production
$providedKey = $_GET['key'] ?? '';

if ($providedKey !== $adminKey) {
    die('Access denied. Provide ?key=admin123');
}

$action = $_GET['action'] ?? 'view';
$ip = $_GET['ip'] ?? '';

try {
    $pdo = getConnection();
    
    switch ($action) {
        case 'view':
            echo "<h2>Current Rate Limits</h2>";
            $stmt = $pdo->query("SELECT * FROM rate_limits ORDER BY last_attempt DESC");
            $limits = $stmt->fetchAll();
            
            if (empty($limits)) {
                echo "<p>No rate limits found.</p>";
            } else {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>IP Address</th><th>Action</th><th>Attempts</th><th>First Attempt</th><th>Last Attempt</th><th>Actions</th></tr>";
                foreach ($limits as $limit) {
                    echo "<tr>";
                    echo "<td>{$limit['id']}</td>";
                    echo "<td>{$limit['ip_address']}</td>";
                    echo "<td>{$limit['action']}</td>";
                    echo "<td>{$limit['attempts']}</td>";
                    echo "<td>{$limit['first_attempt']}</td>";
                    echo "<td>{$limit['last_attempt']}</td>";
                    echo "<td><a href='?action=clear&ip={$limit['ip_address']}&action_type={$limit['action']}&key=$adminKey'>Clear</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            break;
            
        case 'clear':
            $actionType = $_GET['action_type'] ?? '';
            if ($ip && $actionType) {
                $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE ip_address = ? AND action = ?");
                $result = $stmt->execute([$ip, $actionType]);
                if ($result) {
                    echo "<p style='color: green;'>Rate limit cleared for IP: $ip, Action: $actionType</p>";
                } else {
                    echo "<p style='color: red;'>Failed to clear rate limit</p>";
                }
            } else {
                echo "<p style='color: red;'>Missing IP or action type</p>";
            }
            echo "<p><a href='?action=view&key=$adminKey'>Back to View</a></p>";
            break;
            
        case 'clear_all':
            $stmt = $pdo->query("DELETE FROM rate_limits");
            $result = $stmt->execute();
            if ($result) {
                echo "<p style='color: green;'>All rate limits cleared</p>";
            } else {
                echo "<p style='color: red;'>Failed to clear all rate limits</p>";
            }
            echo "<p><a href='?action=view&key=$adminKey'>Back to View</a></p>";
            break;
            
        default:
            echo "<p>Invalid action</p>";
    }
    
    echo "<hr>";
    echo "<h3>Quick Actions</h3>";
    echo "<p><a href='?action=clear_all&key=$adminKey' style='color: red;'>Clear All Rate Limits</a></p>";
    echo "<p><a href='?action=view&key=$adminKey'>View Current Rate Limits</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 20px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
