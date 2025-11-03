<?php
// ============================================================
// Chandusoft Database Connection
// ============================================================

$environment = 'development'; // change to 'production' when live

if ($environment === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/app.log');
}

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=chandusoft;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ==== STRIPE CONFIG ====
define('STRIPE_SECRET_KEY', 'sk_test_51SNs3wAhJkYkXNzXsOYQJ2VYGfYIfcXd20lVISFv3Dq4W1eafNWaVQQQFEVUplug1FUx2jY4PDivCDC3bJSL2gX900hbscfEG2');

// ==== PAYPAL CONFIG ====
define('PAYPAL_CLIENT_ID', 'ARM375iNx3xH7GY9tDWGqPbIoASrXuLrzMPneG9KnV_1preXUCf2tdIeKF7Alqw3DuhremaHrr5x5JXK');
define('PAYPAL_SECRET', 'EHXvqbutdXCuFsl6fPkSPSiOqV-5zBpFGAESii_ACZ8DLEumi8auz8jbJWhbQNnIQ-mhuw73noHbCUnl');
define('PAYPAL_SANDBOX', true); // set to false in production

// ==== APP BASE URL ====
define('APP_URL', 'http://chandusoft.test');

