<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Use true if HTTPS is always enforced
ini_set('session.use_strict_mode', 1);
session_start();
?>

<?php
require 'config.php';

// CSRF check
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['login_errors'] = ['⛔ Security token invalid.'];
    header("Location: login.php");
    exit();
}

// Get & sanitize input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Enter a valid email";
}

if (empty($password)) {
    $errors[] = "Password is required";
}

if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['old_email'] = $email;
    header("Location: login.php");
    exit();
}

// ✅ Securely fetch user with role
$stmt = $pdo->prepare("SELECT id, email, username, password, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // ✅ Successful login
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'username' => $user['username'],
        'role' => $user['role'] // ✅ Store role in session
    ];
    $_SESSION['flash_success'] = "Login successful!";
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Optional: CSRF token regeneration

    header("Location: dashboard.php");
    exit();
} else {
    // ❌ Login failed
    $_SESSION['login_errors'] = ["Invalid email or password"];
    $_SESSION['old_email'] = $email;
    header("Location: login.php");
    exit();
}




