<?php

$envFile = __DIR__ . '/.env';
$envContent = file_get_contents($envFile);

// Update database configuration
$envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=mysql', $envContent);
$envContent = preg_replace('/# DB_HOST=.*/', 'DB_HOST=127.0.0.1', $envContent);
$envContent = preg_replace('/# DB_PORT=.*/', 'DB_PORT=3306', $envContent);
$envContent = preg_replace('/# DB_DATABASE=.*/', 'DB_DATABASE=cphia_payments', $envContent);
$envContent = preg_replace('/# DB_USERNAME=.*/', 'DB_USERNAME=root', $envContent);
$envContent = preg_replace('/# DB_PASSWORD=.*/', 'DB_PASSWORD=Admin!2025', $envContent);

// Uncomment DB settings
$envContent = str_replace('# DB_HOST=', 'DB_HOST=', $envContent);
$envContent = str_replace('# DB_PORT=', 'DB_PORT=', $envContent);
$envContent = str_replace('# DB_DATABASE=', 'DB_DATABASE=', $envContent);
$envContent = str_replace('# DB_USERNAME=', 'DB_USERNAME=', $envContent);
$envContent = str_replace('# DB_PASSWORD=', 'DB_PASSWORD=', $envContent);

file_put_contents($envFile, $envContent);

echo ".env file updated successfully!\n";

