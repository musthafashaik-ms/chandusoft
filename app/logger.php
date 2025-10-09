<?php
// Define log file path
define('LOG_FILE', __DIR__ . '/../storage/logs/app.log');

// Function to log errors or general messages
function log_error($msg) {
    // Ensure the log directory exists
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Get the client's IP address
    $ip = $_SERVER['REMOTE_ADDR'];

    // Prepare the log message with a timestamp and IP address
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $msg | IP: $ip" . PHP_EOL;

    // Write to the log file
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
}



