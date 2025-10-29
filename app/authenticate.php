<?php
// ============================================================
// Chandusoft Authentication with Mailpit + File Logging
// ============================================================

session_start();

// Load database connection
require_once 'config.php';

// Load logger
require_once 'logger.php';

// --- CSRF Check ---
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['login_errors'] = ['⛔ Security token invalid.'];
    log_error("CSRF token mismatch for: " . ($_POST['email'] ?? 'unknown'));
    header("Location: ../admin/login.php");
    exit();
}

// --- Sanitize input ---
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

// --- Validate input ---
if (empty($email)) $errors[] = "Email is required";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email";
if (empty($password)) $errors[] = "Password is required";

if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['old_email'] = $email;
    header("Location: ../admin/login.php");
    exit();
}

// --- Authenticate user ---
try {
    $stmt = $pdo->prepare("SELECT id, email, username, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $ip = $_SERVER['REMOTE_ADDR'];
    $time = date("Y-m-d H:i:s");

    if ($user && password_verify($password, $user['password'])) {
        // ✅ SUCCESSFUL LOGIN
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        $_SESSION['flash_success'] = "Login successful!";
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // ✅ Log + Mailpit notification
        log_login("✅Successful login by {$user['username']} ({$user['email']}) - Role: {$user['role']}", 'INFO');

        header("Location: ../app/dashboard.php");
        exit();

    } else {
        // ❌ FAILED LOGIN
        $_SESSION['login_errors'] = ["Invalid email or password"];
        $_SESSION['old_email'] = $email;

        log_login("Failed login attempt for {$email}", 'WARNING');

        header("Location: ../admin/login.php");
        exit();
    }

} catch (PDOException $e) {
    log_error("Database error: " . $e->getMessage());
    $_SESSION['login_errors'] = ["An error occurred. Please try again later."];
    header("Location: ../admin/login.php");
    exit();
}
?>
