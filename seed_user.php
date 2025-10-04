<?php
require 'config.php';

// For development: show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$password = password_hash("secret123", PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
    $stmt->execute(['admin@example.com', 'admin', $password]);
    echo "✅ Admin user created successfully.";
} catch (PDOException $e) {
    die("Error inserting user: " . $e->getMessage());
}
