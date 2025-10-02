<?php
/**
 * Test what's actually displayed in the web interface
 */

require_once 'bootstrap.php';
require_once 'functions.php';

// Simulate the web request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = 'agabaandre@gmail.com';
$_POST['phone'] = '0702449883';
$_POST['search_registrations'] = '1';

echo "🌐 TESTING WEB DISPLAY\n";
echo "======================\n\n";

// Capture the output
ob_start();
include 'registration_lookup.php';
$output = ob_get_clean();

// Check for specific elements
echo "1. Checking for Complete Payment buttons:\n";
if (strpos($output, 'Complete Payment') !== false) {
    echo "   ✅ Complete Payment buttons found in output\n";
    
    // Count how many
    $count = substr_count($output, 'Complete Payment');
    echo "   - Found $count 'Complete Payment' buttons\n";
    
    // Check specifically for registration #20
    if (strpos($output, 'action=pay&id=20') !== false) {
        echo "   ❌ Registration #20 Complete Payment button found (this should NOT happen)\n";
    } else {
        echo "   ✅ Registration #20 Complete Payment button NOT found (correct)\n";
    }
} else {
    echo "   ❌ No Complete Payment buttons found\n";
}

echo "\n2. Checking for Payment Status displays:\n";
if (strpos($output, 'Payment Status') !== false) {
    echo "   ✅ Payment Status displays found\n";
} else {
    echo "   ❌ No Payment Status displays found\n";
}

echo "\n3. Checking for registration #20 specifically:\n";
if (strpos($output, 'Registration ID: #20') !== false) {
    echo "   ✅ Registration #20 found in output\n";
    
    // Extract the section around registration #20
    $pattern = '/Registration ID: #20.*?(?=Registration ID: #|$)/s';
    if (preg_match($pattern, $output, $matches)) {
        $section = $matches[0];
        echo "   - Section around registration #20:\n";
        echo "   " . str_replace("\n", "\n   ", trim($section)) . "\n";
        
        if (strpos($section, 'Complete Payment') !== false) {
            echo "   ❌ Complete Payment button found for registration #20 (BUG!)\n";
        } else {
            echo "   ✅ No Complete Payment button for registration #20 (correct)\n";
        }
    }
} else {
    echo "   ❌ Registration #20 not found in output\n";
}

echo "\n4. Output length: " . strlen($output) . " characters\n";

echo "\n🎯 CONCLUSION:\n";
echo "==============\n";
echo "If registration #20 shows a Complete Payment button in the web output,\n";
echo "there's a bug in the display logic or data handling.\n";
echo "If it doesn't show the button, then the issue is elsewhere.\n";
