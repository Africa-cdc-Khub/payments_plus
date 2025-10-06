<?php
/**
 * API endpoint to get countries data
 * Returns countries in JSON format for frontend consumption
 */

require_once '../bootstrap.php';
require_once '../functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'countries' => []];

try {
    // Get filter parameters
    $continent = $_GET['continent'] ?? null;
    $packageId = $_GET['package_id'] ?? null;
    $packageName = $_GET['package_name'] ?? null;
    
    // Get countries based on filters
    if ($continent) {
        $countries = getCountriesByContinent($continent);
    } else {
        $countries = getAllCountries();
    }
    
    // Apply package-based filtering
    if ($packageName) {
        $normalizedPackageName = strtolower(trim($packageName));
        
        if ($normalizedPackageName === 'african nationals') {
            // Filter to African countries only
            $countries = array_filter($countries, function($country) {
                return $country['continent'] === 'Africa';
            });
        } elseif ($normalizedPackageName === 'non african nationals') {
            // Filter to non-African countries only
            $countries = array_filter($countries, function($country) {
                return $country['continent'] !== 'Africa';
            });
        }
        // For other packages (students, delegates, side events, exhibitions), return all countries
    }
    
    // Re-index array after filtering
    $countries = array_values($countries);
    
    $response['success'] = true;
    $response['countries'] = $countries;
    $response['count'] = count($countries);
    
} catch (Exception $e) {
    $response['error'] = 'Failed to retrieve countries: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
