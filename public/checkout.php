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

// Optional CSRF token for extra security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout - Chandusoft</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    font-family: Arial, sans-serif;
    background: #f8f9fa;
    margin: 0;
    padding: 20px;
}
.container {
    max-width: 900px;
    margin: 0 auto;
}
form {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
input, select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
button {
    background: #28a745;
    color: white;
    border: none;
    padding: 12px 18px;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
}
button:hover {
    background: #218838;
}
.cart-summary {
    margin-top: 30px;
}
.cart-item {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px solid #ddd;
    padding: 8px 0;
}
.cart-item:last-child {
    border-bottom: none;
}
.total {
    font-size: 20px;
    font-weight: bold;
    text-align: right;
    margin-top: 15px;
}
@media (max-width: 768px) {
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
</head>
<body>
<div class="container">
    <h1>Checkout</h1>

    <form method="post" action="/public/place-order">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <h2>Billing Details</h2>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="address" placeholder="Street Address" required>
        <input type="text" name="city" placeholder="City" required>
        <input type="text" name="postal_code" placeholder="Postal Code" required>

        <h2>Payment Method</h2>
        <select name="payment_method" required>
            <option value="">-- Select Payment Method --</option>
            <option value="stripe">💳 Stripe Checkout</option>
            <option value="paypal">🅿️ PayPal Checkout</option>
        </select>

        <div class="cart-summary">
            <h2>Your Order</h2>
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <span><?= htmlspecialchars($item['title']) ?> (x<?= $item['quantity'] ?>)</span>
                    <span>$<?= number_format($item['subtotal'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="total">Total: $<?= number_format($total, 2) ?></div>
        </div>

        <button type="submit">Place Order</button>
    </form>
</div>
</body>
</html>
