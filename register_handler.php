<?php
require 'config.php';

// CSRF token check
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['register_errors'] = ['⛔ Invalid security token.'];
    header("Location: register.php");
    exit();
}

// Sanitize input
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

// Validation
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

// Check if email already exists
if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "An account with that email already exists.";
    }
}

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old'] = ['email' => $email, 'username' => $username];
    header("Location: register.php");
    exit();
}

// Insert new user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
$stmt->execute([$email, $username, $hashedPassword]);

$_SESSION['flash_success'] = "✅ Registration successful! You can now log in.";
header("Location: login.php");
exit();
