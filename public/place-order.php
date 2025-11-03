<?php
session_start();
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
require_once __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

// ============================================================
// 1️⃣ CART VALIDATION
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
    $qty = $_SESSION['cart'][$p['id']]['quantity'] ?? 1;
    $p['quantity'] = $qty;
    $p['subtotal'] = $p['price'] * $qty;
    $total += $p['subtotal'];
    $cart_items[] = $p;
}

log_page("Visited Checkout Page");

// ============================================================
// 2️⃣ FORM VALIDATION
// ============================================================
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');
$payment_method = $_POST['payment_method'] ?? '';

if (!$name || !$email || !$address || !$city || !$postal_code) {
    http_response_code(400);
    exit("Missing required fields.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit("Invalid email format.");
}

if (!in_array($payment_method, ['stripe', 'paypal'])) {
    http_response_code(400);
    exit("Invalid payment method.");
}

// ============================================================
// 3️⃣ CREATE ORDER RECORD
// ============================================================
$order_ref = strtoupper(bin2hex(random_bytes(6)));

$stmt = $pdo->prepare("
    INSERT INTO orders 
    (order_ref, customer_name, customer_email, address, city, postal_code, payment_gateway, total, payment_status, created_at)
    VALUES (?,?,?,?,?,?,?,?, 'pending', NOW())
");
$stmt->execute([$order_ref, $name, $email, $address, $city, $postal_code, $payment_method, $total]);
$order_id = $pdo->lastInsertId();

// Insert items
$stmt_item = $pdo->prepare("
    INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
    VALUES (?,?,?,?,?,?)
");
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']]['quantity'] ?? 1;
    $subtotal = $p['price'] * $qty;
    $stmt_item->execute([$order_id, $p['id'], $p['title'], $qty, $p['price'], $subtotal]);
}

// ============================================================
// 4️⃣ STRIPE CHECKOUT FLOW
// ============================================================
if ($payment_method === 'stripe') {
    Stripe::setApiKey(STRIPE_SECRET_KEY);

    $line_items = [];
    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']]['quantity'] ?? 1;
        $line_items[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => ['name' => $p['title']],
                'unit_amount' => intval($p['price'] * 100),
            ],
            'quantity' => $qty,
        ];
    }

    $session = StripeSession::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'customer_email' => $email,
       'success_url' => APP_URL . '/payment-success?order_id=' . $order_id,
'cancel_url'  => APP_URL . '/payment-cancel?order_id=' . $order_id,
    ]);

    $_SESSION['cart'] = [];
    header("Location: " . $session->url);
    exit;
}

// ============================================================
// 5️⃣ PAYPAL CHECKOUT v2 FLOW
// ============================================================
elseif ($payment_method === 'paypal') {
    // Choose environment
    $environment = PAYPAL_SANDBOX
        ? new SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_SECRET)
        : new ProductionEnvironment(PAYPAL_CLIENT_ID, PAYPAL_SECRET);

    $client = new PayPalHttpClient($environment);

    // Build item list
    $items = [];
    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']]['quantity'] ?? 1;
        $items[] = [
            'name' => $p['title'],
            'unit_amount' => ['currency_code' => 'USD', 'value' => number_format($p['price'], 2, '.', '')],
            'quantity' => (string)$qty,
        ];
    }

    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
    $request->body = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'reference_id' => $order_ref,
            'amount' => [
                'currency_code' => 'USD',
                'value' => number_format($total, 2, '.', ''),
                'breakdown' => [
                    'item_total' => ['currency_code' => 'USD', 'value' => number_format($total, 2, '.', '')],
                ],
            ],
            'items' => $items,
        ]],
        'application_context' => [
            'cancel_url' => APP_URL . '/payment-cancel?order_id=' . $order_id,
    'return_url' => APP_URL . '/payment-success?order_id=' . $order_id,
            'brand_name' => 'Chandusoft',
            'user_action' => 'PAY_NOW',
        ],
    ];

    try {
        $response = $client->execute($request);
        $approvalUrl = null;
        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                $approvalUrl = $link->href;
                break;
            }
        }
        if (!$approvalUrl) throw new Exception("No approval URL returned from PayPal.");

        $_SESSION['cart'] = [];
        header("Location: " . $approvalUrl);
        exit;

    } catch (Exception $e) {
        error_log("PayPal V2 Error: " . $e->getMessage());
        die("⚠️ PayPal Checkout failed: " . htmlspecialchars($e->getMessage()));
    }
}

// ============================================================
// 6️⃣ FALLBACK - CASH ON DELIVERY
// ============================================================
else {
    $_SESSION['cart'] = [];
    echo "<script>
        alert('✅ Thank you! Your order has been placed successfully (Cash on Delivery).');
        window.location.href = '/public/catalog';
    </script>";
}

log_catalog("Order created: ID $order_id, Payment: $payment_method, Total: $$total");
?>
