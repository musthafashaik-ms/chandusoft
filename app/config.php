<?php
// Set the environment (change to 'production' for live server)
$environment = 'development';  // Change to 'production' when in live environment

// Set error reporting based on environment
if ($environment === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);  // Hide errors in production
    error_reporting(0);
    // Log errors to file
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/app.log');
}

// Database connection settings (adjust as necessary for your environment)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=chandusoft;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Log the error and show a generic message
    log_error("Database connection failed: " . $e->getMessage());
    die("Internal Server Error");
}
?>
