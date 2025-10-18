<?php
/**
 * Test script to verify UTF-8 handling in CSV exports
 * This script tests the safeValue function with various accented characters
 */

// Test data with French and Spanish names containing accents
$testData = [
    [
        'first_name' => 'José',
        'last_name' => 'García',
        'email' => 'jose.garcia@example.com',
        'organization' => 'Universidad de Madrid',
        'country' => 'España',
        'city' => 'Madrid'
    ],
    [
        'first_name' => 'François',
        'last_name' => 'Dupont',
        'email' => 'francois.dupont@example.com',
        'organization' => 'Université de Paris',
        'country' => 'France',
        'city' => 'Paris'
    ],
    [
        'first_name' => 'José María',
        'last_name' => 'González',
        'email' => 'jose.maria.gonzalez@example.com',
        'organization' => 'Instituto de Investigación',
        'country' => 'México',
        'city' => 'Ciudad de México'
    ],
    [
        'first_name' => 'Françoise',
        'last_name' => 'Martin',
        'email' => 'francoise.martin@example.com',
        'organization' => 'Centre de Recherche',
        'country' => 'France',
        'city' => 'Lyon'
    ],
    [
        'first_name' => 'Andrés',
        'last_name' => 'Hernández',
        'email' => 'andres.hernandez@example.com',
        'organization' => 'Universidad Nacional',
        'country' => 'Colombia',
        'city' => 'Bogotá'
    ]
];

// Helper function: ensure value is UTF-8 and safe for Excel (handles accents/diacritics correctly)
$safeValue = function($value) {
    if (is_null($value)) return '';
    // If already UTF-8, keep as-is, but ensure any improper bytes fixed
    $str = (string)$value;
    if (!mb_detect_encoding($str, 'UTF-8', true)) {
        $str = mb_convert_encoding($str, 'UTF-8');
    }
    // Some Office/Excel versions may break on long accented chars if not normalized:
    return normalizer_is_normalized($str, \Normalizer::FORM_C) ? $str : normalizer_normalize($str, \Normalizer::FORM_C);
};

// Generate test CSV
$filename = 'test_utf8_export_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$file = fopen('php://output', 'w');

// Add UTF-8 BOM for proper accent display in Excel and other applications
fwrite($file, "\xEF\xBB\xBF");

// CSV Headers
fputcsv($file, [
    'ID',
    'First Name',
    'Last Name',
    'Email',
    'Organization',
    'Country',
    'City'
]);

// CSV Data
foreach ($testData as $index => $person) {
    fputcsv($file, [
        $index + 1,
        $safeValue($person['first_name']),
        $safeValue($person['last_name']),
        $safeValue($person['email']),
        $safeValue($person['organization']),
        $safeValue($person['country']),
        $safeValue($person['city'])
    ]);
}

fclose($file);
?>
