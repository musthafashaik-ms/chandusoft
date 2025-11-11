<?php
// =============================================================
// Chandusoft – Stripe Checkout Cancel Handler
// =============================================================
 
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
 
// -------------------------------------------------------------
// 1️⃣ Identify canceled order
// -------------------------------------------------------------
$order_id = intval($_GET['order_id'] ?? 0);
$reason = "User canceled payment on Stripe checkout page.";
 
if ($order_id > 0) {
    try {
        // Fetch order info for clarity
        $stmt = $pdo->prepare("SELECT order_ref, payment_status FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
 
        if ($order) {
            // Only mark as failed if not already paid
            if ($order['payment_status'] !== 'paid') {
                $update = $pdo->prepare("
                    UPDATE orders
                    SET payment_status = 'failed', updated_at = NOW()
                    WHERE id = ?
                ");
                $update->execute([$order_id]);
 
                // Change log function to log_to_file for app.log
                log_to_file("❌ Payment canceled for Order ID {$order_id} (Ref: {$order['order_ref']}) — $reason");
            } else {
                log_to_file("⚠️ Cancel attempted for already paid Order ID {$order_id} (Ref: {$order['order_ref']}) — skipped.");
            }
        } else {
            log_to_file("⚠️ Payment cancel attempted for non-existent Order ID $order_id");
        }
    } catch (Exception $e) {
        log_to_file("❌ DB update failed on cancel for Order ID $order_id: " . $e->getMessage(), 'ERROR');
    }
} else {
    log_to_file("⚠️ Payment cancel accessed without valid order_id parameter.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Canceled</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        text-align: center;
        padding: 50px;
    }
    h2 {
        color: #dc3545;
        margin-bottom: 20px;
    }
    a {
        display: inline-block;
        margin-top: 20px;
        background-color: #007BFF;
        color: #fff;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 6px;
        transition: 0.3s;
    }
    a:hover {
        background-color: #0056b3;
    }
</style>
</head>
<body>
 
<h2>❌ Payment canceled. Please try again or choose a different method.</h2>
<a href="/public/catalog">Back to Shop</a>
 
</body>
</html>
