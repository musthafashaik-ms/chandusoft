<?php
// public/payment-success.php

require_once __DIR__ . '/../app/config.php';   // already starts session safely
require_once __DIR__ . '/../app/logger.php';

/**
 * Safe HTML escape helper.
 * Avoids "Passing null to parameter #1 of htmlspecialchars() is deprecated"
 */
function e($value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h2 {
            color: #28a745;
            text-align: center;
            margin-bottom: 25px;
        }
        h3 {
            color: #007BFF;
            margin-top: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            margin: 6px 0;
        }
        strong { color: #000; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        th {
            background-color: #007BFF;
            color: #fff;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover { background-color: #f1f1f1; }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s;
        }
        .back-link:hover { background-color: #0056b3; }
        @media (max-width: 768px) {
            body { padding: 15px; }
            .container { padding: 20px; }
            th, td { padding: 10px; }
        }
    </style>
</head>
<body>
<div class="container">
<?php
$order_id    = intval($_GET['order_id'] ?? 0);
$is_test_mode = isset($_GET['testmode']); // if you need this later

if ($order_id > 0) {
    // Fetch order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $payment_method = $order['payment_gateway'] ?? '';
        $status         = ucfirst($order['payment_status'] ?? '');
        $txn_id         = $order['txn_id'] ?? '';

        // Status message
        if ($status === 'Paid') {
            echo "<h2>✅ Payment Successful! Your order #".e($order_id)." is confirmed and paid.</h2>";
            log_to_file("✅Payment successful for Order ID: $order_id. Transaction ID: $txn_id. Payment Gateway: $payment_method.");
        } elseif ($status === 'Failed') {
            echo "<h2>❌ Payment Failed! Please try again or contact support.</h2>";
        } else {
            echo "<h2>🧪 Payment Pending! Order #".e($order_id)." is awaiting confirmation from Stripe.</h2>";
        }

        // Order details
        echo "<h3>Order Details:</h3>";
        echo "<p><strong>Customer Name:</strong> " . e($order['customer_name'] ?? '') . "</p>";
        echo "<p><strong>Order Reference:</strong> " . e($order['order_ref'] ?? '') . "</p>";
        echo "<p><strong>Payment Gateway:</strong> " . e($payment_method) . "</p>";
        echo "<p><strong>Total Amount:</strong> $" . number_format((float)($order['total'] ?? 0), 2) . "</p>";
        echo "<p><strong>Payment Status:</strong> " . e($status) . "</p>";
        echo "<p><strong>Transaction ID:</strong> " . e($txn_id) . "</p>";

        // Order items
        $stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt_items->execute([$order_id]);
        $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        if ($order_items) {
            echo "<h3>Purchased Products:</h3>";
            echo "<table>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>";

            foreach ($order_items as $item) {
                $price    = (float)($item['price'] ?? 0);
                $quantity = (int)($item['quantity'] ?? 0);
                $subtotal = $price * $quantity;

                echo "<tr>
                        <td>" . e($item['product_name'] ?? '') . "</td>
                        <td>" . e((string)$quantity) . "</td>
                        <td>$" . number_format($price, 2) . "</td>
                        <td>$" . number_format($subtotal, 2) . "</td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No items found for this order.</p>";
        }
    } else {
        echo "<h3>⚠️ Order not found.</h3>";
    }
} else {
    echo "<h3>⚠️ Invalid order ID.</h3>";
}
?>
    <a href="/public/catalog" class="back-link">Back to Shop</a>
</div>
</body>
</html>
