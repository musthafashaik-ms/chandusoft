<?php
// --- DEBUGGING FOR DEVELOPMENT ONLY ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Secure session setup
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// Database connection
try {
   $pdo = new PDO("mysql:host=localhost;dbname=chandusoft;charset=utf8mb4", "root", "", [

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Internal Server Error");
}
