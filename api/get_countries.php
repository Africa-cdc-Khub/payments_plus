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
    // New: continent policy: 'africa' | 'other' | 'all' for package filtering
    $continentPolicy = isset($_GET['continent_policy']) ? strtolower(trim($_GET['continent_policy'])) : null;
    $packageId = $_GET['package_id'] ?? null;
    $packageName = $_GET['package_name'] ?? null;
    $nationalitiesOnly = $_GET['nationalities_only'] ?? false;
    
    // Get countries based on filters
    if ($continent) {
        $countries = getCountriesByContinent($continent);
    } else {
        $countries = getAllCountries();
    }
    
    // Apply package-based filtering (legacy by name) or by continent policy
    if ($continentPolicy === 'africa') {
        $countries = array_values(array_filter($countries, function($country) {
            return ($country['continent'] ?? '') === 'Africa';
        }));
    } elseif ($continentPolicy === 'other') {
        $countries = array_values(array_filter($countries, function($country) {
            return ($country['continent'] ?? '') !== 'Africa';
        }));
    } elseif ($continentPolicy === 'all') {
        // no-op
    } elseif ($packageName) {
        $normalizedPackageName = strtolower(trim($packageName));
        
        if ($normalizedPackageName === 'african nationals') {
            // Filter to African countries only
            $countries = array_filter($countries, function($country) {
                return ($country['continent'] ?? '') === 'Africa';
            });
        } elseif ($normalizedPackageName === 'non african nationals') {
            // Filter to non-African countries only
            $countries = array_filter($countries, function($country) {
                return ($country['continent'] ?? '') !== 'Africa';
            });
        }
        // For other packages (students, delegates, side events, exhibitions), return all countries
    }
    
    // Re-index array after filtering
    $countries = array_values($countries);
    
    // If nationalities only requested, return unique nationalities
    if ($nationalitiesOnly) {
        $nationalities = [];
        $seenNationalities = [];
        
        foreach ($countries as $country) {
            if (!empty($country['nationality']) && !in_array($country['nationality'], $seenNationalities)) {
                $nationalities[] = [
                    'nationality' => $country['nationality'],
                    'country_name' => $country['name'],
                    'country_code' => $country['code'],
                    'continent' => $country['continent']
                ];
                $seenNationalities[] = $country['nationality'];
            }
        }
        
        $response['success'] = true;
        $response['nationalities'] = $nationalities;
        $response['count'] = count($nationalities);
    } else if (!empty($_GET['include_nationalities'])) {
        // Return both countries and unique nationalities
        $nationalities = [];
        $seenNationalities = [];
        foreach ($countries as $country) {
            if (!empty($country['nationality']) && !in_array($country['nationality'], $seenNationalities)) {
                $nationalities[] = [
                    'nationality' => $country['nationality'],
                    'country_name' => $country['name'],
                    'country_code' => $country['code'],
                    'continent' => $country['continent']
                ];
                $seenNationalities[] = $country['nationality'];
            }
        }
        $response['success'] = true;
        $response['countries'] = $countries;
        $response['nationalities'] = $nationalities;
        $response['count'] = [
            'countries' => count($countries),
            'nationalities' => count($nationalities)
        ];
    } else {
        $response['success'] = true;
        $response['countries'] = $countries;
        $response['count'] = count($countries);
    }
    
} catch (Exception $e) {
    $response['error'] = 'Failed to retrieve countries: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
