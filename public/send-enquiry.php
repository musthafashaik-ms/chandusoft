<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

// Get and validate form data
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$product = trim($_POST['product'] ?? 'Unknown product');

if (!$name || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit("Invalid form data.");
}

// Save enquiry to DB
try {
    $stmt = $pdo->prepare("INSERT INTO enquiries (product, name, email, message, submitted_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$product, $name, $email, $message]);
} catch (Exception $e) {
    log_error("Database insert failed: " . $e->getMessage());
    http_response_code(500);
    exit("Server error while saving enquiry.");
}

// Prepare email
$to = "admin@chandusoft.local";
$subject = "New Product Enquiry: $product";
$body = <<<EMAIL
<h3>New Product Enquiry</h3>
<p><strong>Product:</strong> {$product}</p>
<p><strong>Name:</strong> {$name}</p>
<p><strong>Email:</strong> {$email}</p>
<p><strong>Message:</strong><br>{$message}</p>
EMAIL;

// Send email via Mailpit
send_mailpit_notification($subject, $body);

// Log the enquiry
log_catalog("New enquiry for $product from $email");

// Success response (popup + redirect)
echo "<script>
    alert('✅ Thank you! Your enquiry has been sent successfully.');
    window.location.href = '/public/catalog.php';
</script>";
