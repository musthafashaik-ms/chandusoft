<?php
require_once __DIR__ . '/../app/config.php';

// For testing, directly assign your secret key here
$TURNSTILE_SECRET = '0x4AAAAAAB7ii73wAJ7ecUp7fBr4RTvr5N8';

// Debug: Log received product value
error_log('Product received: ' . ($_POST['product'] ?? 'NOT SET'));

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

// Get and sanitize form inputs
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$product = trim($_POST['product'] ?? 'Unknown product');
$token   = $_POST['cf-turnstile-response'] ?? '';

// Validate token presence
if (!$token) {
    http_response_code(400);
    exit("Turnstile verification failed: no token.");
}

// Validate other form data
if (!$name || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit("Invalid form data.");
}

// Verify Turnstile token using cURL
$ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');

$postFields = http_build_query([
    'secret' => $TURNSTILE_SECRET,
    'response' => $token,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    error_log("cURL error during Turnstile verification: " . curl_error($ch));
    http_response_code(500);
    exit("Internal server error during CAPTCHA verification.");
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    error_log("Turnstile verification HTTP error: $httpCode");
    http_response_code(500);
    exit("Turnstile verification service error.");
}

$captchaResult = json_decode($response, true);

if (empty($captchaResult['success'])) {
    http_response_code(403);
    exit("Turnstile verification failed.");
}

// Insert into database
try {
    $stmt = $pdo->prepare("INSERT INTO enquiries (product, name, email, message, submitted_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$product, $name, $email, $message]);
} catch (Exception $e) {
    error_log('Database insert error: ' . $e->getMessage());
    http_response_code(500);
    exit("Server error while saving your enquiry: " . htmlspecialchars($e->getMessage()));
}

// Send notification email
$to = "musthafa.shaik@chandusoft.com";
$subject = "New product enquiry: $product";
$body = <<<EMAIL
You received a new enquiry.

Product: $product
Name: $name
Email: $email

Message:
$message
EMAIL;

$headers = [
    'From' => 'no-reply@yourdomain.com',
    'Reply-To' => $email,
    'Content-Type' => 'text/plain; charset=UTF-8'
];

mail($to, $subject, $body, implode("\r\n", $headers));

// Success response
echo "✅ Thank you! Your enquiry has been sent.";
