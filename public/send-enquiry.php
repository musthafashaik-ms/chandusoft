<?php
require_once __DIR__ . '/../app/config.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

// Get form values
$product = trim($_POST['product'] ?? 'Unknown product');
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$token   = $_POST['cf-turnstile-response'] ?? '';

// Validate inputs
if (!$name || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit("Please fill in all fields correctly.");
}

// Optional: Turnstile verification (enable in production)
$TURNSTILE_SECRET = getenv('TURNSTILE_SECRET') ?: '0x4AAAAAAB7ii73wAJ7ecUp7fBr4RTvr5N8';

if ($token) {
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $TURNSTILE_SECRET,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $captchaResult = json_decode($response, true);
    if (empty($captchaResult['success'])) {
        http_response_code(403);
        exit("Turnstile verification failed.");
    }
}

// Insert into DB
try {
    $stmt = $pdo->prepare("INSERT INTO enquiries (product, name, email, message, submitted_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$product, $name, $email, $message]);
} catch (Exception $e) {
    http_response_code(500);
    exit("Error saving enquiry.");
}

// Optional: Send email notification
$to = "musthafa.shaik@chandusoft.com";
$subject = "New enquiry: $product";
$body = "Product: $product\nName: $name\nEmail: $email\nMessage:\n$message";
$headers = "From: no-reply@yourdomain.com\r\nReply-To: $email\r\n";

@mail($to, $subject, $body, $headers);

// Success response
echo "✅ Thank you! Your enquiry has been sent.";
?>
