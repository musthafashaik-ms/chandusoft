<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
 
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("UPDATE catalog SET status='archived', updated_at=NOW() WHERE id=?");
$success = $stmt->execute([$id]);
 
if ($success) {
    log_catalog("🗃️ Archived catalog item #$id", 'ARCHIVE');
} else {
    log_catalog("Catalog item archived: ID {$id} by {$username}");

}
 
header("Location: catalog.php");
exit;
?>
 
 