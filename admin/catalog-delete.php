<?php
require_once __DIR__ . '/../app/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: catalog.php");
    exit;
}

$id = (int)$_GET['id'];

// Check if item exists
$check = $pdo->prepare("SELECT * FROM catalog WHERE id = ?");
$check->execute([$id]);
$item = $check->fetch();

if (!$item) {
    die("Item not found.");
}

// Archive the item instead of deleting
$stmt = $pdo->prepare("UPDATE catalog SET status = 'archived' WHERE id = ?");
$stmt->execute([$id]);

// ✅ Redirect to archived page
header("Location: catalog-archived.php");
exit;
