<?php
session_start();
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';

if (empty($_SESSION['cart'])) {
    header("Location: /public/cart");
    exit;
}


// Fetch cart items
$cart_items = [];
$total = 0.0;

$ids = implode(',', array_keys($_SESSION['cart']));
$stmt = $pdo->query("SELECT * FROM catalog WHERE id IN ($ids) AND status='published'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']]['quantity'];
    $p['quantity'] = $qty;
    $p['subtotal'] = $p['price'] * $qty;
    $total += $p['subtotal'];
    $cart_items[] = $p;
}

// Log checkout page visit
log_page("Visited Checkout Page");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout</title>
<style>
body { font-family: Arial,sans-serif; background:#f8f9fa; padding:20px; }
form { max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1);}
input, select { width:100%; padding:8px; margin-bottom:12px; border:1px solid #ccc; border-radius:4px; }
button { background:#28a745; color:white; border:none; padding:10px 16px; border-radius:4px; cursor:pointer; }
button:hover { background:#218838; }
</style>
</head>
<body>

<h1>Checkout</h1>

<form method="post" action="/public/place-order">
   <h2>Billing Details</h2>
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="address" placeholder="Address" required>
    <input type="text" name="city" placeholder="City" required>
    <input type="text" name="postal_code" placeholder="Postal Code" required>

    <h2>Payment Method</h2>
    <select name="payment_method" required>
        <option value="stripe">Stripe Checkout</option>
        <option value="paypal">PayPal Express</option>
    </select>

    <h3>Total: $<?= number_format($total, 2) ?></h3>

    <button type="submit">Place Order</button>
</form>


</body>
</html>
