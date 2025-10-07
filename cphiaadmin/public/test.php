<?php
phpinfo();
echo "\n\nLaravel Path Test:\n";
echo "File: " . __FILE__ . "\n";
echo "Directory: " . __DIR__ . "\n";
echo "Laravel Index exists: " . (file_exists(__DIR__ . '/index.php') ? 'YES' : 'NO') . "\n";

