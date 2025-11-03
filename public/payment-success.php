<?php
session_start();
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
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
        color: #28a745; /* success green */
        text-align: center;
        margin-bottom: 25px;
    }

    h3 {
        color: #007BFF; /* blue headings to match product page */
        margin-top: 20px;
    }

    p {
        font-size: 16px;
        line-height: 1.6;
        margin: 6px 0;
    }

    strong {
        color: #000;
    }

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

    tr:hover {
        background-color: #f1f1f1;
    }

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

    .back-link:hover {
        background-color: #0056b3;
    }

    @media (max-width: 768px) {
        body {
            padding: 15px;
        }

        .container {
            padding: 20px;
        }

        th, td {
            padding: 10px;
        }
    }
</style>
</head>
<body>

<div class="container">
<?php


// Ensure that the order ID is passed as a query parameter
$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id) {
    // Fetch order details from the database
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Determine the payment method
        $payment_method = $order['payment_gateway'];

        // If payment was successful, update the order status to 'paid'
        if ($payment_method === 'stripe') {
            // For Stripe, check the payment status (this should ideally be done via webhook too)
            $stmt_update = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
            $stmt_update->execute([$order_id]);

            echo "<h2>✅ Payment Successful! Your order id: $order_id is Confirmed and Paid.</h2>";
        } elseif ($payment_method === 'paypal') {
            // For PayPal, update the payment status
            $stmt_update = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
            $stmt_update->execute([$order_id]);

            echo "<h2>✅ Payment Successful! Your order id: $order_id is confirmed and paid.</h2>";
        }

        // Display order details
        echo "<h3>Order Details:</h3>";
        echo "<p><strong>Customer Name:</strong> " . htmlspecialchars($order['customer_name']) . "</p>";
        echo "<p><strong>Order Reference:</strong> " . htmlspecialchars($order['order_ref']) . "</p>";
        echo "<p><strong>Payment Gateway:</strong> " . htmlspecialchars($payment_method) . "</p>";
        echo "<p><strong>Total Amount:</strong> $" . number_format($order['total'], 2) . "</p>";
        echo "<p><strong>Payment Status:</strong> " . ucfirst($order['payment_status']) . "</p>";


        // Show ordered products
        $stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt_items->execute([$order_id]);
        $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>Purchased Products:</h3>";
        echo "<table>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>";

        foreach ($order_items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            echo "<tr>
                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                    <td>" . htmlspecialchars($item['quantity']) . "</td>
                    <td>$" . number_format($item['price'], 2) . "</td>
                    <td>$" . number_format($subtotal, 2) . "</td>
                </tr>";
        }

        echo "</table>";
    } else {
        echo "<h3>Order not found.</h3>";
    }
} else {
    echo "<h3>Invalid order ID.</h3>";
}
?>
<a href="/public/catalog" class="back-link">Back to Shop</a>
