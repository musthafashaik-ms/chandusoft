<?php
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
 
log_to_file("🛒 Checkout started — Total: $total USD");
 
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
 
$stmt_item = $pdo->prepare("
    INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
    VALUES (?,?,?,?,?,?)
");
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']]['quantity'] ?? 1;
    $subtotal = $p['price'] * $qty;
    $stmt_item->execute([$order_id, $p['id'], $p['title'], $qty, $p['price'], $subtotal]);
}
 
log_to_file("🧾 Order $order_id created (Ref: $order_ref, Payment: $payment_method)");
 
// ============================================================
// 4️⃣ DETERMINE TEST MODE
// ============================================================
$is_test_mode = false;
 
if (defined('STRIPE_SECRET_KEY') && str_starts_with(constant('STRIPE_SECRET_KEY'), 'sk_test_')) 
 {
    $is_test_mode = true;
}
if (defined('PAYPAL_SANDBOX') && constant('PAYPAL_SANDBOX') === true) 
 {
    $is_test_mode = true;
}
 
// ============================================================
// 5️⃣ AUTO-DETECT BASE URL (ngrok or local)
// ============================================================
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost';
 
if (!empty($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
 
    if (strpos($host, 'ngrok-free.dev') !== false || strpos($host, 'ngrok.io') !== false) {
        $appUrl = 'https://' . $host . '/public';
    } else {
        $appUrl = $protocol . $host;
    }
}
 
log_to_file("🌐 Using base URL: $appUrl");
 
// ============================================================
// 6️⃣ STRIPE CHECKOUT FLOW
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
 
    $success_url = $appUrl . '/payment-success.php?order_id=' . $order_id;
    $cancel_url  = $appUrl . '/payment-cancel.php?order_id=' . $order_id;
    if ($is_test_mode) {
        $success_url .= '&testmode=1';
        $cancel_url  .= '&testmode=1';
    }
 
    log_to_file("💳 Creating Stripe session for Order #$order_id");
 
    $session = StripeSession::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'customer_email' => $email,
        'client_reference_id' => $order_ref,
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
    ]);
 
    $_SESSION['cart'] = [];
    log_to_file("✅ Redirecting to Stripe checkout: " . $session->url);
    header("Location: " . $session->url);
    exit;
}
 
// ============================================================
// 7️⃣ PAYPAL CHECKOUT FLOW
// ============================================================
elseif ($payment_method === 'paypal') {
    $environment = PAYPAL_SANDBOX
        ? new SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_SECRET)
        : new ProductionEnvironment(PAYPAL_CLIENT_ID, PAYPAL_SECRET);
 
    $client = new PayPalHttpClient($environment);
 
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
            'cancel_url' => $appUrl . '/payment-cancel.php?order_id=' . $order_id . ($is_test_mode ? '&testmode=1' : ''),
            'return_url' => $appUrl . '/payment-success.php?order_id=' . $order_id . ($is_test_mode ? '&testmode=1' : ''),
            'brand_name' => 'Chandusoft',
            'user_action' => 'PAY_NOW',
        ],
    ];
 
    try {
        $response = $client->execute($request);
        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                $_SESSION['cart'] = [];
                log_to_file("✅ Redirecting to PayPal approval URL for order $order_id");
                header("Location: " . $link->href);
                exit;
            }
        }
        throw new Exception("No approval URL returned from PayPal.");
    } catch (Exception $e) {
        log_to_file("❌ PayPal error: " . $e->getMessage(), 'ERROR');
        die("⚠️ PayPal Checkout failed: " . htmlspecialchars($e->getMessage()));
    }
}
 
// ============================================================
// 8️⃣ FALLBACK - CASH ON DELIVERY
// ============================================================
else {
    $_SESSION['cart'] = [];
    log_to_file("🚚 Cash-on-delivery order $order_id confirmed.");
    echo "<script>
        alert('✅ Thank you! Your order has been placed successfully (Cash on Delivery).');
        window.location.href = '/public/catalog';
    </script>";
}
 
log_to_file("📦 Checkout complete for Order $order_id ($payment_method)");

 // ============================================================
// 🔚 Final cleanup safety (in case of early exits or errors)
// ============================================================
if (!headers_sent() && !empty($_SESSION['cart'])) {
    unset($_SESSION['cart']);
    log_to_file("🧹 Session cart cleared at end of place-order script.");
}
?>
 