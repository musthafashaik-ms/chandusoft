<?php
// app/authenticate.php

require_once __DIR__ . '/config.php';   // session + env + $pdo
require_once __DIR__ . '/logger.php';   // logging helpers

// -------------------------------------------------------------
// 1) Only allow POST
// -------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: /admin/login');
    exit;
}

// -------------------------------------------------------------
// 2) CSRF check
// -------------------------------------------------------------
$sessionToken = $_SESSION['csrf_token'] ?? '';
$formToken    = $_POST['csrf_token']     ?? '';

if (empty($formToken) || empty($sessionToken) || !hash_equals($sessionToken, $formToken)) {
    $_SESSION['login_errors'] = ['⛔ Security token invalid. Please try again.'];
    log_error("CSRF mismatch for email: " . ($_POST['email'] ?? 'unknown'));

    header('Location: /admin/login');
    exit;
}

// -------------------------------------------------------------
// 3) Sanitize & validate input
// -------------------------------------------------------------
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors   = [];

if ($email === '') {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address';
}

if ($password === '') {
    $errors[] = 'Password is required';
}

if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['old_email']    = $email;

    header('Location: /admin/login');
    exit;
}

// -------------------------------------------------------------
// 4) Authenticate user
// -------------------------------------------------------------
try {
    $stmt = $pdo->prepare(
        "SELECT id, email, username, password, role 
         FROM users 
         WHERE email = ? 
         LIMIT 1"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'       => $user['id'],
            'email'    => $user['email'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ];
        $_SESSION['flash_success'] = 'Login successful!';
        $_SESSION['csrf_token']    = bin2hex(random_bytes(32));
        $_SESSION['LAST_ACTIVITY'] = time(); // start timeout window

        // Debug login log
        log_login(
            "AUTH OK session_id=" . session_id() .
            " user=" . json_encode($_SESSION['user']),
            'INFO'
        );

        // Go to dashboard (relative URL → works on chandusoft.test & ngrok)
        header('Location: /app/dashboard.php');
        exit;
    }

    // ---------------------------------------------------------
    // 5) Invalid credentials
    // ---------------------------------------------------------
    $_SESSION['login_errors'] = ['Invalid email or password'];
    $_SESSION['old_email']    = $email;
    log_login("⚠️ Failed login attempt for {$email}", 'WARNING');

    header('Location: /admin/login');
    exit;

} catch (PDOException $ex) {
    log_error('DB error on login: ' . $ex->getMessage());
    $_SESSION['login_errors'] = ['An error occurred. Please try again later.'];

    header('Location: /admin/login');
    exit;
}
