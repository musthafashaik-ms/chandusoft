<?php
session_start();
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
require_once __DIR__ . '/../vendor/autoload.php';


// Ensure that the cart is not empty
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

// Validate form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$payment_method = $_POST['payment_method'] ?? '';

// Ensure payment method is valid
if (!in_array($payment_method, ['stripe', 'paypal'])) {
    http_response_code(400);
    exit("Invalid payment method.");
}

// Validate other required fields
if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit("Invalid form data.");
}

// Get address, city, and postal_code from the form data
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');

// Ensure address fields are not empty
if (!$address || !$city || !$postal_code) {
    http_response_code(400);
    exit("Missing required address information.");
}

// Ensure cart is not empty
if (empty($_SESSION['cart'])) {
    http_response_code(400);
    exit("Cart is empty.");
}

// Generate unique order reference
$order_ref = strtoupper(bin2hex(random_bytes(6))); // Generates a unique order reference (e.g., ABC123)

// Insert order into the database with the selected payment method and payment status 'pending'
$stmt = $pdo->prepare("
    INSERT INTO orders 
    (order_ref, customer_name, customer_email, address, city, postal_code, payment_gateway, total, payment_status, created_at) 
    VALUES (?,?,?,?,?,?,?,?, 'pending', NOW())
");
$stmt->execute([$order_ref, $name, $email, $address, $city, $postal_code, $payment_method, $total]);
$order_id = $pdo->lastInsertId();

// Insert order items into the database
$stmt_item = $pdo->prepare("
    INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
    VALUES (?,?,?,?,?,?)
");
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']]['quantity'];
    $subtotal = $p['price'] * $qty;
    $stmt_item->execute([$order_id, $p['id'], $p['title'], $qty, $p['price'], $subtotal]);
}

// ========== PAYMENT GATEWAY REDIRECT ==========

if ($payment_method === 'stripe') {
    // ----------------- STRIPE CHECKOUT -----------------
  // Set your Stripe secret key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Now you can proceed with the Stripe code
$line_items = [];
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']]['quantity'];
    $line_items[] = [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => ['name' => $p['title']], // Use 'title' here
            'unit_amount' => intval($p['price'] * 100), // Convert to cents
        ],
        'quantity' => $qty,
    ];
}

// For Stripe
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => $line_items,
    'mode' => 'payment',
    'customer_email' => $email,
    'success_url' => APP_URL . '/payment-success?order_id=' . $order_id,  // Correct URL without `/public/`
    'cancel_url'  => APP_URL . '/payment-cancel?order_id=' . $order_id,   // Correct URL without `/public/`
]);

// Empty cart before redirect
$_SESSION['cart'] = [];
header("Location: " . $session->url);
exit;
} elseif ($payment_method === 'paypal') {
    // ----------------- PAYPAL EXPRESS -----------------
    $paypal_base = PAYPAL_SANDBOX ? "https://www.sandbox.paypal.com" : "https://www.paypal.com";
    $return_url = APP_URL . "/payment-success.php?order_id=" . $order_id;
    $cancel_url = APP_URL . "/payment-cancel.php?order_id=" . $order_id;
    $paypal_url = "$paypal_base/cgi-bin/webscr?cmd=_xclick&business=" . urlencode(PAYPAL_CLIENT_ID)
        . "&item_name=" . urlencode("Order #$order_id from Chandusoft")
        . "&amount=" . number_format($total, 2)
        . "&currency_code=USD"
        . "&return=" . urlencode($return_url)
        . "&cancel_return=" . urlencode($cancel_url);

    // Empty cart before redirect
    $_SESSION['cart'] = [];
    header("Location: $paypal_url");
    exit;

} else {
    // ----------------- CASH ON DELIVERY -----------------
    $_SESSION['cart'] = [];
    echo "<script>
        alert('✅ Thank you! Your order has been placed successfully (Cash on Delivery).');
        window.location.href = '/public/catalog';
    </script>";
}

log_catalog("Order created: ID $order_id, Payment: $payment_method, Total: $$total");

?>
