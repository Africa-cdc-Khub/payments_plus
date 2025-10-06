<?php
/**
 * Get Package Description API
 * Returns package description for a given package ID
 */

// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

try {
    // Load required files
    require_once __DIR__ . '/bootstrap.php';
    require_once __DIR__ . '/db_connector.php';
    require_once __DIR__ . '/functions.php';
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['package_id'])) {
        throw new Exception('Package ID is required');
    }
    
    $packageId = (int)$input['package_id'];
    
    if ($packageId <= 0) {
        throw new Exception('Invalid package ID');
    }
    
    // Get package from database
    $package = getPackageById($packageId);
    
    if (!$package) {
        throw new Exception('Package not found');
    }
    
    // Return package description
    echo json_encode([
        'success' => true,
        'description' => $package['description'] ?? 'No description available for this package.',
        'package_name' => $package['name'],
        'package_price' => $package['price']
    ]);
    
} catch (Exception $e) {
    error_log('Package description API error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
