<?php
echo "<h1>PHP Version Test</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Required for Laravel:</strong> 8.2+</p>";
echo "<p><strong>Status:</strong> " . (version_compare(phpversion(), '8.2.0', '>=') ? '✅ GOOD' : '❌ TOO OLD') . "</p>";

echo "<hr><h2>Loaded Extensions:</h2>";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath'];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>$ext: " . ($loaded ? '✅ Loaded' : '❌ Missing') . "</p>";
}
?>

