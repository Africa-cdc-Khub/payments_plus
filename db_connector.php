<?php
// Load environment variables
require_once __DIR__ . '/bootstrap.php';

function getConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch(PDOException $e) {
        if (APP_DEBUG) {
            die("Database connection failed: " . $e->getMessage());
        } else {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
}

// Create database if it doesn't exist
function createDatabase() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        return true;
    } catch(PDOException $e) {
        if (APP_DEBUG) {
            die("Database creation failed: " . $e->getMessage());
        } else {
            error_log("Database creation failed: " . $e->getMessage());
            die("Database creation failed. Please check your configuration.");
        }
    }
}

// Test database connection
function testConnection() {
    try {
        $pdo = getConnection();
        return true;
    } catch(Exception $e) {
        return false;
    }
}