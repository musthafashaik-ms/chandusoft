<?php
session_start();
require_once __DIR__ . '/../app/config.php'; // Ensure the correct Stripe keys are included from config.php
require_once __DIR__ . '/../app/logger.php';

// Get order ID from the URL
$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id) {
    // Fetch the order details from the orders table
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Fetch the associated order items from the order_items table
        $stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt_items->execute([$order_id]);
        $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        // Display order details
        echo "<h2>✅ Payment successful! Your order $order_id has been confirmed.</h2>";

        echo "<h3>Order Details:</h3>";
        echo "<p><strong>Customer Name:</strong> " . htmlspecialchars($order['customer_name']) . "</p>";
        
        // Check if 'payment_method' exists and is not null
        $payment_method = isset($order['payment_method']) && $order['payment_method'] !== null ? ucfirst($order['payment_method']) : "Not Available";
        echo "<p><strong>Payment Method:</strong> " . htmlspecialchars($payment_method) . "</p>";  // Displays the payment method

        echo "<p><strong>Total Amount:</strong> $" . number_format($order['total'], 2) . "</p>";

        // Display product details
        echo "<h3>Purchased Products:</h3>";
        echo "<table border='1' cellpadding='10'>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>";

        $total = 0;
        foreach ($order_items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
            echo "<tr>
                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                    <td>" . htmlspecialchars($item['quantity']) . "</td>
                    <td>$" . number_format($item['price'], 2) . "</td>
                    <td>$" . number_format($subtotal, 2) . "</td>
                </tr>";
        }

        echo "</table>";

        // Display payment status and order confirmation
        echo "<p><strong>Payment Status:</strong> " . ucfirst($order['payment_status']) . "</p>";
    } else {
        echo "<h3>Order not found.</h3>";
    }
} else {
    echo "<h3>Invalid order ID.</h3>";
}

echo "<a href='/public/catalog'>Back to Shop</a>";
?>
