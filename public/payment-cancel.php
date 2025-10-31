<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';

$order_id = intval($_GET['order_id'] ?? 0);
if ($order_id) {
    $pdo->prepare("UPDATE orders SET payment_status='failed' WHERE id=?")->execute([$order_id]);
    log_catalog("Payment canceled for order $order_id");
}
echo "<h2>❌ Payment canceled. Please try again or choose a different method.</h2>";
echo "<a href='/public/catalog'>Back to Shop</a>";
?>
