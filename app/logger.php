<?php
/**
 * Logger Helper
 * ------------------------
 * - General app logs → storage/logs/app.log
 * - Catalog logs → storage/logs/catalog/catalog-YYYY-MM-DD.log
 */
 
// ✅ General app log file path
define('LOG_FILE', __DIR__ . '/../storage/logs/app.log');
 
/**
 * Log general application messages (errors, info, etc.)
 */
function log_error($msg) {
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
 
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp][ERROR] $msg | IP: $ip" . PHP_EOL;
 
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
}
 
/**
 * Log catalog-specific actions
 * Stored in /storage/logs/catalog/catalog-YYYY-MM-DD.log
 */
function log_catalog($message, $level = 'INFO') {
    $logDir = __DIR__ . '/../storage/logs/catalog/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
 
    $file = $logDir . 'catalog-' . date('Y-m-d') . '.log';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp][$level] $message | IP: $ip" . PHP_EOL;
 
    file_put_contents($file, $entry, FILE_APPEND);
}
 
 