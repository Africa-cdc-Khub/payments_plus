<?php
/**
 * Test script for Registrations API
 * Run: php test_registrations_api.php [action] [params]
 * 
 * Examples:
 *   php test_registrations_api.php list
 *   php test_registrations_api.php list status=pending
 *   php test_registrations_api.php list payment_status=paid
 *   php test_registrations_api.php list delegate_only=1
 *   php test_registrations_api.php show 57
 *   php test_registrations_api.php stats
 */

$action = $argv[1] ?? 'list';
$baseUrl = 'http://localhost:8000/api/registrations';

echo "Testing Registrations API\n";
echo str_repeat('=', 50) . "\n\n";

switch ($action) {
    case 'list':
        // Build query parameters
        $params = [];
        for ($i = 2; $i < $argc; $i++) {
            if (strpos($argv[$i], '=') !== false) {
                list($key, $value) = explode('=', $argv[$i], 2);
                $params[$key] = $value;
            }
        }
        
        $url = $baseUrl;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        echo "Action: List Registrations\n";
        echo "URL: {$url}\n";
        if (!empty($params)) {
            echo "Filters:\n";
            foreach ($params as $key => $value) {
                echo "  - {$key}: {$value}\n";
            }
        }
        echo "\n";
        break;
        
    case 'show':
        $id = $argv[2] ?? null;
        if (!$id) {
            echo "Error: Registration ID required\n";
            echo "Usage: php test_registrations_api.php show {id}\n";
            exit(1);
        }
        
        $url = $baseUrl . '/' . $id;
        echo "Action: Show Registration\n";
        echo "Registration ID: {$id}\n";
        echo "URL: {$url}\n\n";
        break;
        
    case 'stats':
        $url = $baseUrl . '/stats';
        echo "Action: Get Statistics\n";
        echo "URL: {$url}\n\n";
        break;
        
    default:
        echo "Unknown action: {$action}\n";
        echo "Available actions: list, show, stats\n";
        exit(1);
}

// Make request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

echo "Sending request...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "\n❌ cURL Error: {$error}\n";
    echo "Make sure the dev server is running: php artisan serve\n";
    exit(1);
}

// Parse response
$result = json_decode($response, true);

echo "\nHTTP Status: {$httpCode}\n";
echo str_repeat('-', 50) . "\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
echo str_repeat('-', 50) . "\n\n";

// Display summary
if ($httpCode === 200 && isset($result['success']) && $result['success']) {
    echo "✅ SUCCESS!\n\n";
    
    switch ($action) {
        case 'list':
            $count = count($result['data']);
            $total = $result['pagination']['total'] ?? $count;
            echo "📊 Found {$count} registrations (Total: {$total})\n";
            
            if ($count > 0) {
                echo "\nRegistrations:\n";
                foreach ($result['data'] as $index => $reg) {
                    $num = $index + 1;
                    echo "  {$num}. ID: {$reg['id']} | {$reg['user']['full_name']} | {$reg['package']['name']}\n";
                    echo "     Status: {$reg['status']} | Payment: {$reg['payment_status']}\n";
                    echo "     Email: {$reg['user']['email']}\n";
                }
                
                if (isset($result['pagination'])) {
                    $pag = $result['pagination'];
                    echo "\n📄 Page {$pag['current_page']} of {$pag['last_page']} ({$pag['total']} total)\n";
                }
            }
            break;
            
        case 'show':
            $reg = $result['data'];
            echo "Registration Details:\n";
            echo "  ID: {$reg['id']}\n";
            echo "  User: {$reg['user']['full_name']} ({$reg['user']['email']})\n";
            echo "  Package: {$reg['package']['name']}\n";
            echo "  Status: {$reg['status']}\n";
            echo "  Payment Status: {$reg['payment_status']}\n";
            echo "  Amount: \${$reg['amount']}\n";
            echo "  Organization: {$reg['user']['organization']}\n";
            echo "  Country: {$reg['user']['country']}\n";
            if ($reg['rejection_reason']) {
                echo "  Rejection Reason: {$reg['rejection_reason']}\n";
            }
            echo "  Created: {$reg['created_at']}\n";
            break;
            
        case 'stats':
            $stats = $result['data'];
            echo "Registration Statistics:\n\n";
            echo "  Total Registrations: {$stats['total']}\n\n";
            
            echo "  By Status:\n";
            echo "    - Pending: {$stats['by_status']['pending']}\n";
            echo "    - Approved: {$stats['by_status']['approved']}\n";
            echo "    - Rejected: {$stats['by_status']['rejected']}\n\n";
            
            echo "  By Payment Status:\n";
            echo "    - Pending: {$stats['by_payment_status']['pending']}\n";
            echo "    - Paid: {$stats['by_payment_status']['paid']}\n";
            echo "    - Failed: {$stats['by_payment_status']['failed']}\n\n";
            
            echo "  Delegates:\n";
            echo "    - Total: {$stats['delegates']['total']}\n";
            echo "    - Pending: {$stats['delegates']['pending']}\n";
            echo "    - Approved: {$stats['delegates']['approved']}\n";
            echo "    - Rejected: {$stats['delegates']['rejected']}\n\n";
            
            echo "  Revenue:\n";
            echo "    - Total Paid: \${$stats['revenue']['total']}\n";
            echo "    - Pending: \${$stats['revenue']['pending']}\n";
            break;
    }
} else {
    echo "❌ FAILED!\n";
    echo "   Error: " . ($result['error'] ?? 'Unknown error') . "\n";
}

echo "\n";

