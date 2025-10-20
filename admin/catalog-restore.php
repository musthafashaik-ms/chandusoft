<?php
require_once __DIR__ . '/../app/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID.");
}

$id = (int)$_GET['id'];

// ✅ Check if item exists
$check = $pdo->prepare("SELECT * FROM catalog WHERE id = ?");
$check->execute([$id]);
$item = $check->fetch();

if (!$item) {
    die("Item not found.");
}

// ✅ Restore the item
$stmt = $pdo->prepare("UPDATE catalog SET status = 'published' WHERE id = ?");
$stmt->execute([$id]);

header("Location: catalog.php?status=archived");
exit;
