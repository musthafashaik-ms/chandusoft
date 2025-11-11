<?php
// app/config.php
declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

if (!defined('IS_WEBHOOK')) define('IS_WEBHOOK', false);
if (!defined('DISABLE_REDIRECTS')) define('DISABLE_REDIRECTS', IS_WEBHOOK);

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
} catch (Throwable $e) {
    // error_log('Dotenv load error: ' . $e->getMessage());
}

// ------------------ Environment ------------------
$environment = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';

if ($environment === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
    ini_set('error_log', $logDir . '/app.log');
}

// ------------------ Database ------------------
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? getenv('DB_HOST'),
    $_ENV['DB_NAME'] ?? getenv('DB_NAME')
);

$dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER');
$dbPass = $_ENV['DB_PASS'] ?? getenv('DB_PASS');

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    throw $e;
}

// ------------------ Payment Config ------------------
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY'));
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? getenv('STRIPE_PUBLISHABLE_KEY'));
define('STRIPE_WEBHOOK_SECRET', $_ENV['STRIPE_WEBHOOK_SECRET'] ?? getenv('STRIPE_WEBHOOK_SECRET'));
define('PAYPAL_CLIENT_ID', $_ENV['PAYPAL_CLIENT_ID'] ?? getenv('PAYPAL_CLIENT_ID'));
define('PAYPAL_SECRET', $_ENV['PAYPAL_SECRET'] ?? getenv('PAYPAL_SECRET'));
define('PAYPAL_SANDBOX', filter_var($_ENV['PAYPAL_SANDBOX'] ?? getenv('PAYPAL_SANDBOX'), FILTER_VALIDATE_BOOLEAN));

define('APP_URL', $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '');

// ------------------ Secure Session Setup ------------------
if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = session_get_cookie_params();

    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'],
        'path' => $cookieParams['path'],
        'domain' => $cookieParams['domain'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

// ------------------ HTTPS Enforcement (fixed for ngrok + local) ------------------
$forceHttps = filter_var($_ENV['FORCE_HTTPS'] ?? getenv('FORCE_HTTPS'), FILTER_VALIDATE_BOOLEAN);

if ($forceHttps && !DISABLE_REDIRECTS && !empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])) {
    // ✅ Detect HTTPS from Apache, Nginx, or ngrok proxy headers
    $isSecure = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? 0) == 443
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
    );

    // Prevent infinite redirect loops
    if (!$isSecure) {
        $httpsUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $httpsUrl);
        exit();
    }
}

// NOTE: no echoes or HTML output here.
