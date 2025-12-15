<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
 
// ============================================================
// Handle direct "Buy Now" POST (adds item to session cart)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int) $_POST['product_id'];
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
 
    // Fetch product from DB
    $stmt = $pdo->prepare("SELECT id, title, price, image FROM catalog WHERE id = ? AND status = 'published'");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if ($product) {
        // Initialize session cart if not present
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
 
        // Add or update item
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
    }
 
    // Redirect to GET checkout to prevent form resubmission
    header("Location: /public/checkout");
    exit;
}
 
// ============================================================
// Normal Checkout Logic (for cart-based flow)
// ============================================================
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
 
// Optional CSRF token for security
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
/* General Body Styling */
body {
    font-family: 'Poppins', Arial, sans-serif;
    background: linear-gradient(135deg, #eef3ff, #e1ecff); /* Smooth gradient background */
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    color: #333;
}

/* Main Container */
.container {
    width: 100%;
    max-width: 800px;  /* Wider max-width for desktop */
    background: #fff;
    padding: 30px;
    margin-top: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
    border: 1px solid #f0f0f0;
}

/* Heading Styling */
h1 {
    text-align: center;
    color: #0078D7;
    font-size: 28px;  /* Larger font size for desktop */
    font-weight: 600;
    margin-bottom: 30px;
}

h2 {
    color: #333;
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Input Fields and Select Dropdown Styling */
input, select {
    width: 100%;
    padding: 8px ; /* Remove left/right padding and just use '12px' for all sides */
    margin-bottom: 10px;
    border-radius: 8px;
    border: 1px solid #ddd; /* Same border on both sides */
    font-size: 16px;
    background: #f9f9f9;
    transition: all 0.3s ease;
}

/* Focus State for Inputs */
input:focus, select:focus {
    border-color: #0078D7;
    background: #fff;
    box-shadow: 0 0 5px rgba(0, 120, 215, 0.2); /* Blue glow on focus */
    outline: none;
}

/* Button Styling */
button {
    padding: 12px 20px;
    font-size: 16px;
    font-weight: 600;
    background: linear-gradient(135deg, #0078D7, #1E90FF);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

button:hover {
    background: #0066cc; /* Darker shade on hover */
    transform: translateY(-2px); /* Slight hover effect */
}

/* Cart Summary Section */
.cart-summary {
    margin-top: 30px;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

/* Cart Item Styling */
.cart-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
    font-size: 16px;
}

.cart-item:last-child {
    border-bottom: none;
}

/* Total Price Styling */
.total {
    font-size: 22px;
    font-weight: bold;
    text-align: right;
    color: #0078D7;
    margin-top: 20px;
}


</style>
</head>
<body>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Chandusoft</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="path/to/your/css-file.css">
</head>
<body>
<div class="container">
    <h1>Checkout</h1>

    <form method="post" action="/public/place-order">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- Billing Details Section -->
        <div class="billing-details">
            <h2>Billing Details</h2>
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="address" placeholder="Street Address" required>
            <input type="text" name="city" placeholder="City" required>
            <input type="text" name="postal_code" placeholder="Postal Code" required>
        </div>

        <!-- Payment Method Section -->
        <div class="payment-method">
            <h2>Payment Method</h2>
            <select name="payment_method" required>
                <option value="">-- Select Payment Method --</option>
                <option value="stripe">Stripe Checkout</option>
                <option value="paypal">PayPal Checkout</option>
            </select>
        </div>

        <!-- Cart Summary Section -->
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
