<?php
// =============================================================
// Chandusoft – Stripe Webhook Receiver (handles order_ref + order_id)
// =============================================================
 
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
require_once __DIR__ . '/../vendor/autoload.php';
 
use Stripe\Stripe;
use Stripe\Webhook;
 
// -------------------------------------------------------------
// 1️⃣ Setup
// -------------------------------------------------------------
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
Stripe::setApiKey(STRIPE_SECRET_KEY);
 
log_to_file("✅ Stripe webhook initialized | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
 
// Detect environment host
$host = $_SERVER['HTTP_HOST'] ?? 'unknown';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$appUrl = $protocol . $host;
log_to_file("🌐 Webhook triggered from host: $appUrl");
 
// -------------------------------------------------------------
// 2️⃣ Read Payload
// -------------------------------------------------------------
$rawInput = file_get_contents('php://input');
$payload = trim($rawInput);
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;
$endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? (defined('STRIPE_WEBHOOK_SECRET') ? STRIPE_WEBHOOK_SECRET : null);
 
if (empty($payload)) {
    log_to_file("⚠️ Empty webhook payload received | Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
    http_response_code(400);
    exit('No payload');
}
 
try {
    if ($endpointSecret && $sigHeader) {
        $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        log_to_file("✅ Stripe signature verified successfully");
    } else {
        $event = json_decode($payload, false);
        log_to_file("⚠️ No signature header — proceeding with raw JSON decode");
    }
} catch (Exception $e) {
    log_to_file("❌ Webhook verification failed: " . $e->getMessage(), 'ERROR');
    http_response_code(400);
    exit('Invalid signature');
}
 
// -------------------------------------------------------------
// 3️⃣ Event Handling
// -------------------------------------------------------------
$type = $event->type ?? 'unknown';
$data = $event->data->object ?? null;
log_to_file("🔔 Stripe Event Received: $type");
 
// -------------------------------------------------------------
// 4️⃣ Checkout Completed → Mark Paid
// -------------------------------------------------------------
if ($type === 'checkout.session.completed') {
    $orderRef = $data->client_reference_id ?? ($data->metadata->order_ref ?? null);
    $orderId  = $data->metadata->order_id ?? null;
    $txnId    = $data->payment_intent ?? $data->id ?? null;
    $email    = $data->customer_email ?? 'unknown';
    $amount   = isset($data->amount_total) ? ($data->amount_total / 100) : null;
 
    if ($orderRef || $orderId) {
        try {
            if ($orderId) {
                $check = $pdo->prepare("SELECT payment_status FROM orders WHERE id = ?");
                $check->execute([$orderId]);
                $status = $check->fetchColumn();
 
                if ($status !== 'paid') {
                    $stmt = $pdo->prepare("
                        UPDATE orders
                        SET payment_status = 'paid',
                            txn_id = ?,
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$txnId, $orderId]);
                    log_to_file("✅ Order ID $orderId marked PAID — txn: $txnId, amount: $amount, email: $email");
                } else {
                    log_to_file("ℹ️ Order ID $orderId already marked PAID — skipping.");
                }
            } else {
                $check = $pdo->prepare("SELECT payment_status FROM orders WHERE order_ref = ?");
                $check->execute([$orderRef]);
                $status = $check->fetchColumn();
 
                if ($status !== 'paid') {
                    $stmt = $pdo->prepare("
                        UPDATE orders
                        SET payment_status = 'paid',
                            txn_id = ?,
                            updated_at = NOW()
                        WHERE order_ref = ?
                    ");
                    $stmt->execute([$txnId, $orderRef]);
                    log_to_file("✅ Order Ref $orderRef marked PAID — txn: $txnId, amount: $amount, email: $email");
                } else {
                    log_to_file("ℹ️ Order Ref $orderRef already marked PAID — skipping.");
                }
            }
        } catch (Exception $e) {
            log_to_file("❌ DB update failed for PAID order: " . $e->getMessage(), 'ERROR');
        }
    } else {
        log_to_file("⚠️ Missing order_ref/order_id in checkout.session.completed");
    }
}
 
// -------------------------------------------------------------
// 5️⃣ Payment Failed → Mark Failed
// -------------------------------------------------------------
elseif ($type === 'payment_intent.payment_failed') {
    $orderRef = $data->metadata->order_ref ?? null;
    $orderId  = $data->metadata->order_id ?? null;
    $errorMsg = $data->last_payment_error->message ?? 'Unknown failure reason';
 
    if ($orderRef || $orderId) {
        try {
            if ($orderId) {
                $stmt = $pdo->prepare("
                    UPDATE orders
                    SET payment_status = 'failed',
                        updated_at = NOW()
                    WHERE id = ? AND payment_status != 'paid'
                ");
                $stmt->execute([$orderId]);
                log_to_file("❌ Payment failed for Order ID $orderId — $errorMsg");
            } else {
                $stmt = $pdo->prepare("
                    UPDATE orders
                    SET payment_status = 'failed',
                        updated_at = NOW()
                    WHERE order_ref = ? AND payment_status != 'paid'
                ");
                $stmt->execute([$orderRef]);
                log_to_file("❌ Payment failed for Order Ref $orderRef — $errorMsg");
            }
        } catch (Exception $e) {
            log_to_file("❌ DB update failed on failed event: " . $e->getMessage(), 'ERROR');
        }
    } else {
        log_to_file("⚠️ Missing order_ref/order_id in failed payment webhook");
    }
}
 
// -------------------------------------------------------------
// 6️⃣ Unhandled Event
// -------------------------------------------------------------
else {
    log_to_file("ℹ️ Unhandled event type: $type");
}
 
// -------------------------------------------------------------
// 7️⃣ Response to Stripe
// -------------------------------------------------------------
http_response_code(200);
echo json_encode(['received' => true]);
log_to_file("✅ Webhook processed successfully — Event: $type");
 
 