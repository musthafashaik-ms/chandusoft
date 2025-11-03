<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$environment = $_ENV['APP_ENV'] ?? 'production';

if ($environment === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/app.log');
}

// Database connection
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Stripe & PayPal config
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY']);
define('PAYPAL_CLIENT_ID', $_ENV['PAYPAL_CLIENT_ID']);
define('PAYPAL_SECRET', $_ENV['PAYPAL_SECRET']);
define('PAYPAL_SANDBOX', filter_var($_ENV['PAYPAL_SANDBOX'], FILTER_VALIDATE_BOOLEAN));
define('APP_URL', $_ENV['APP_URL']);
