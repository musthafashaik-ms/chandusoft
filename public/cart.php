<?php
session_start();
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update':
            $pid = intval($_POST['product_id']);
            $qty = max(1, intval($_POST['quantity'] ?? 1));
            if (isset($_SESSION['cart'][$pid])) {
                $_SESSION['cart'][$pid]['quantity'] = $qty; // Set exact quantity
            }
            break;

        case 'remove':
            $pid = intval($_POST['product_id']);
            unset($_SESSION['cart'][$pid]);
            break;

        case 'empty':
            $_SESSION['cart'] = [];
            break;

        default:
            // Adding new item (no action field)
            if (isset($_POST['product_id'])) {
                $pid = intval($_POST['product_id']);
                $qty = max(1, intval($_POST['quantity'] ?? 1));
                if (isset($_SESSION['cart'][$pid])) {
                    $_SESSION['cart'][$pid]['quantity'] += $qty;
                } else {
                    $_SESSION['cart'][$pid] = ['quantity' => $qty];
                }
            }
            break;
    }

    // Redirect to cart after any action
    header("Location: /public/cart");
    exit;
}

// Empty the Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'empty') {
    $_SESSION['cart'] = []; // Clear all items from cart
    // Redirect back to cart page
    header("Location: /public/cart");
    exit;
}

// Fetch product details from DB for cart items
$cart_items = [];
$total = 0.0;

if ($_SESSION['cart']) {
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
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shopping Cart</title>
<style>
body { font-family: Arial,sans-serif; background:#f8f9fa; padding:20px;}
table { width:100%; border-collapse: collapse; margin-bottom:20px;}
th,td { border:1px solid #ccc; padding:10px; text-align:center;}
th { background:#007BFF; color:white;}
button { padding:5px 10px; background:#007BFF; color:white; border:none; border-radius:4px; cursor:pointer;}
button:hover { background:#0056b3; }
a.checkout { display:inline-block; padding:10px 20px; background:green; color:white; border-radius:4px; text-decoration:none; }
img.product-image { width: 50px; height: 50px; object-fit: cover; }
/* Checkout Button */
a.checkout {
    display: inline-block;
    padding: 10px 20px;
    background-color: #28a745; /* Green for Proceed to Checkout */
    color: white;
    border-radius: 4px;
    text-decoration: none;
    margin-top: 10px; /* Space between buttons */
    font-size: 1.1em;
    text-align: center;
}

a.checkout:hover {
    background-color: #218838; /* Darker green for hover */
}

</style>
</head>
<body>

<h1>Shopping Cart</h1>

<?php if ($cart_items): ?>
<form method="post">
<table>
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Subtotal</th>
        <th>Action</th>
    </tr>
    <?php foreach ($cart_items as $item): ?>
    <tr>
        <td>
            <img src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="product-image">
            <?= htmlspecialchars($item['title']) ?>
        </td>
        <td>$<?= number_format($item['price'],2) ?></td>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1">
                <input type="hidden" name="action" value="update">
                <button type="submit">Update</button>
            </form>
        </td>
        <td>$<?= number_format($item['subtotal'],2) ?></td>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                <input type="hidden" name="action" value="remove">
                <button type="submit">Remove</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="3"><strong>Total:</strong></td>
        <td colspan="2"><strong>$<?= number_format($total,2) ?></strong></td>
    </tr>
</table>
</form>

<!-- Empty Cart Button -->
<form method="post">
    <input type="hidden" name="action" value="empty">
    <button type="submit">Empty Cart</button>
</form>

<!-- Proceed to Checkout Button -->
<a href="/checkout" class="checkout">Proceed to Checkout</a>

<a href="catalog.php" class="checkout">Back to Catalog</a>

<?php else: ?>
<p>Your cart is empty. <a href="catalog.php">Go shopping</a>.</p>
<?php endif; ?>
