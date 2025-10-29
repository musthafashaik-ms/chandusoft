<?php
session_start();
require_once 'config.php';

// CSRF validation
if (
    empty($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['register_errors'] = ['⛔ Invalid security token.'];
    $_SESSION['register_old'] = [
        'email' => $_POST['email'] ?? '',
        'username' => $_POST['username'] ?? ''
    ];
    header("Location: register.php");
    exit();
}
// Valid token — optionally unset it:
unset($_SESSION['csrf_token']);

// Sanitize and validate inputs
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Enter a valid email.";
}
if (empty($username)) {
    $errors[] = "Username is required.";
}
if (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters.";
} elseif ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}
if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "An account with that email already exists.";
    }
}

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old'] = ['email'=>$email, 'username'=>$username];
    header("Location: register.php");
    exit();
}

// Insert new user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
$stmt->execute([$email, $username, $hashedPassword]);

// Optionally: log registration event
// log_to_file("Registration successful: {$username} ({$email})", 'INFO');

$_SESSION['flash_success'] = "✅ Registration successful! You can now log in.";
header("Location: ../admin/login.php");
exit();
