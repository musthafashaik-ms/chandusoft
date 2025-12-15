<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| 0. FLAGS (used by webhook to disable redirects)
|--------------------------------------------------------------------------
| public/webhook.php sets IS_WEBHOOK = true before including this file.
*/
if (!defined('IS_WEBHOOK')) {
    define('IS_WEBHOOK', false);
}
if (!defined('DISABLE_REDIRECTS')) {
    define('DISABLE_REDIRECTS', IS_WEBHOOK);
}

/*
|--------------------------------------------------------------------------
| 1. TRUST NGROK / REVERSE PROXY FOR HTTPS
|--------------------------------------------------------------------------
| If we are behind a proxy (like ngrok) it sends X-Forwarded-Proto.
| We use that to decide if the request is effectively HTTPS.
*/
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $_SERVER['HTTPS'] = ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'on' : 'off';
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

/*
|--------------------------------------------------------------------------
| 2. SESSION CONFIG (shared for chandusoft.test + ngrok)
|--------------------------------------------------------------------------
| - host-only cookie (no fixed domain), works on BOTH hosts
| - SameSite=Lax -> safe and allows POST -> redirect -> GET (login)
| - "secure" matches actual HTTPS (ngrok / SSL vhost)
*/
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',           // host-only cookie (best for multi-domain dev)
    'secure'   => $isHttps,     // true on HTTPS, false on plain HTTP
    'httponly' => true,
    'samesite' => 'Lax',
]);

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| 3. BASIC CONFIG: Timezone & Autoload
|--------------------------------------------------------------------------
*/
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| 4. LOAD ENVIRONMENT VARIABLES (.env)
|--------------------------------------------------------------------------
*/
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
} catch (Throwable $e) {
    error_log("Dotenv load error: " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| 5. APPLICATION ENVIRONMENT
|--------------------------------------------------------------------------
*/
$environment = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';

if ($environment === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');

    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }
    ini_set('error_log', $logDir . '/app.log');
}

/*
|--------------------------------------------------------------------------
| 6. DATABASE (PDO)
|--------------------------------------------------------------------------
*/
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? getenv('DB_HOST'),
    $_ENV['DB_NAME'] ?? getenv('DB_NAME')
);

$dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER');
$dbPass = $_ENV['DB_PASS'] ?? getenv('DB_PASS');

try {
    $pdo = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

/*
|--------------------------------------------------------------------------
| 7. BASE URL (Ngrok-compatible)
|--------------------------------------------------------------------------
| If APP_URL=auto, we build it dynamically from current host.
| If APP_URL is set (e.g. in production) we respect that value.
*/
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
$protocol = $isHttps ? 'https://' : 'http://';

if (!empty($_ENV['APP_URL']) && $_ENV['APP_URL'] !== 'auto') {
    $baseUrl = rtrim($_ENV['APP_URL'], '/');
} else {
    $baseUrl = $protocol . $host;
}

define('BASE_URL', $baseUrl);

/*
|--------------------------------------------------------------------------
| 8. HTTPS ENFORCEMENT
|--------------------------------------------------------------------------
| You can control this with FORCE_HTTPS in .env
|   FORCE_HTTPS=true  (default) -> redirects http -> https
|   FORCE_HTTPS=false -> no redirect (useful during early local dev)
|
| Webhooks skip redirects via DISABLE_REDIRECTS.
*/
$forceHttps = filter_var(
    $_ENV['FORCE_HTTPS'] ?? getenv('FORCE_HTTPS') ?? true,
    FILTER_VALIDATE_BOOLEAN
);

if (
    !$isHttps &&
    $forceHttps &&
    !DISABLE_REDIRECTS &&
    !empty($_SERVER['HTTP_HOST']) &&
    !empty($_SERVER['REQUEST_URI'])
) {
    $httpsUrl = 'https://' . $host . $_SERVER['REQUEST_URI'];
    header('Location: ' . $httpsUrl, true, 301);
    exit();
}

/*
|--------------------------------------------------------------------------
| 9. PAYMENT CONSTANTS (Stripe + PayPal)
|--------------------------------------------------------------------------
*/
define('STRIPE_SECRET_KEY',      $_ENV['STRIPE_SECRET_KEY']      ?? '');
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
define('STRIPE_WEBHOOK_SECRET',  $_ENV['STRIPE_WEBHOOK_SECRET']  ?? '');

define('PAYPAL_CLIENT_ID', $_ENV['PAYPAL_CLIENT_ID'] ?? '');
define('PAYPAL_SECRET',    $_ENV['PAYPAL_SECRET']    ?? '');
define('PAYPAL_SANDBOX',   filter_var($_ENV['PAYPAL_SANDBOX'] ?? true, FILTER_VALIDATE_BOOLEAN));

/*
|--------------------------------------------------------------------------
| 10. CLOUDFLARE TURNSTILE
|--------------------------------------------------------------------------
*/
define('TURNSTILE_SITE',   $_ENV['TURNSTILE_SITE']   ?? '');
define('TURNSTILE_SECRET', $_ENV['TURNSTILE_SECRET'] ?? '');
