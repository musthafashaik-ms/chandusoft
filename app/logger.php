<?php
define('LOG_FILE', __DIR__ . '/../storage/logs/app.log');

function ensure_log_dir($path) {
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

function log_to_file($message, $level = 'INFO') {
    ensure_log_dir(LOG_FILE);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $timestamp = date('Y-m-d H:i:s');
    $line = "[$timestamp][$level] $message | IP: $ip" . PHP_EOL;
    file_put_contents(LOG_FILE, $line, FILE_APPEND);
    return $line;
}

function send_mailpit_notification($subject, $body) {
    ini_set('SMTP', 'localhost');
    ini_set('smtp_port', '1025');

    $to = "admin@chandusoft.local";
    $headers = "From: Chandusoft <no-reply@chandusoft.local>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    @mail($to, $subject, $body, $headers);
}

function log_error($msg, $level = 'ERROR') {
    log_to_file($msg, $level);
}

function log_login($msg, $level = 'INFO') {
    $line = log_to_file($msg, $level);
    $subject = match ($level) {
        'ERROR', 'WARNING' => "🚫 Login Alert ($level)",
        default => "🔐 Login Activity ($level)",
    };
    send_mailpit_notification($subject, nl2br(htmlspecialchars($line)));
}

// ✅ NEW: Catalog logger
function log_catalog($message, $level = 'INFO') {
    $logDir = __DIR__ . '/../storage/logs/catalog/';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $file = $logDir . 'catalog-' . date('Y-m-d') . '.log';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $timestamp = date('Y-m-d H:i:s');
    $line = "[$timestamp][$level] $message | IP: $ip" . PHP_EOL;
    file_put_contents($file, $line, FILE_APPEND);
    send_mailpit_notification("Catalog Notification: $level", nl2br(htmlspecialchars($line)));
}

// ✅ NEW: Page logger
function log_page($message, $level = 'INFO') {
    $logDir = __DIR__ . '/../storage/logs/pages/';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $file = $logDir . 'pages-' . date('Y-m-d') . '.log';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $timestamp = date('Y-m-d H:i:s');
    $line = "[$timestamp][$level] $message | IP: $ip" . PHP_EOL;
    file_put_contents($file, $line, FILE_APPEND);
    send_mailpit_notification("Pages Notification: $level", nl2br(htmlspecialchars($line)));
}
?>
