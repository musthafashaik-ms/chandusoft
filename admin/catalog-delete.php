<?php
require_once __DIR__ . '/../app/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: catalog-archived.php");
    exit;
}

$id = (int)$_GET['id'];

$check = $pdo->prepare("SELECT * FROM catalog WHERE id = ?");
$check->execute([$id]);
$item = $check->fetch();

if (!$item) {
    die("Item not found.");
}

$stmt = $pdo->prepare("UPDATE catalog SET status = 'published' WHERE id = ?");
$stmt->execute([$id]);

header("Location: catalog-archived.php");
exit;
