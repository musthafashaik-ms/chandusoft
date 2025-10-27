<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
 
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("UPDATE catalog SET status='published', updated_at=NOW() WHERE id=?");
$success = $stmt->execute([$id]);
 
if ($success) {
    log_catalog("♻️ Restored catalog item #$id", 'RESTORE');
} else {
    log_catalog("❌ Failed to restore item #$id", 'ERROR');
}
 
header("Location: catalog.php?status=archived");
exit;
?>