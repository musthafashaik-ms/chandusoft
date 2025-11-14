<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is Admin
$user = $_SESSION['user'] ?? [];
$role = strtolower($user['role'] ?? '');
if ($role !== 'admin') {
    die("Access denied");
}

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid ID");
}

try {
    $stmt = $pdo->prepare("DELETE FROM catalog WHERE id = ?");
    $stmt->execute([$id]);
    log_catalog("Item permanently deleted | ID: $id");
    header("Location: catalog.php?msg=Item+deleted");
    exit;
} catch (Exception $e) {
    log_error("Failed to permanently delete catalog item: " . $e->getMessage(), "ERROR");
    die("Error deleting item");
}
