<?php
// Include the logger
require_once '../app/logger.php';

// Start the session and initialize settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
session_start();

// CSRF Check
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['login_errors'] = ['⛔ Security token invalid.'];
    log_error("CSRF token invalid for email: " . $_POST['email']);  // Log CSRF error
    header("Location: ../admin/login.php");
    exit();
}

// Get and sanitize user input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

// Validate inputs
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
    header("Location: ../admin/login.php");
    exit();
}

try {
    // Connect to the database
    require_once 'config.php'; // Make sure config.php handles the DB connection

    // Prepare and execute query to fetch the user
    $stmt = $pdo->prepare("SELECT id, email, username, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        session_regenerate_id(true);  // Regenerate session ID for security
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        $_SESSION['flash_success'] = "Login successful!";
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // Regenerate CSRF token
        header("Location: ../app/dashboard.php");
        exit();
    } else {
        // Failed login - Invalid email or password
        $_SESSION['login_errors'] = ["Invalid email or password"];
        $_SESSION['old_email'] = $email;
        
        // Log the failed login attempt with email, IP, and the failed status
        log_error("❌ FAILED login | Email: " . $email);

        header("Location: ../admin/login.php");
        exit();
    }
} catch (PDOException $e) {
    // Log the database error
    log_error("Database error: " . $e->getMessage());

    // Show a generic error message to the user
    $_SESSION['login_errors'] = ["An error occurred, please try again later."];
    header("Location: ../admin/login.php");
    exit();
}



