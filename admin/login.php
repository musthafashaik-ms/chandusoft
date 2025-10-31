<?php
// ============================================================
// Chandusoft Admin/User Login Page
// ============================================================

// --- Session Configuration ---
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
session_set_cookie_params([
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CSRF Token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Session Timeout ---
$timeout_duration = 1800; // 30 mins
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: /admin/login.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// --- Retrieve old form data ---
$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_email']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 { text-align: center; margin-bottom: 20px; }
        input {
            width: 100%; padding: 12px; margin-bottom: 16px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button {
            width: 100%; padding: 14px;
            background: #1E90FF; color: white;
            border: none; border-radius: 4px; cursor: pointer;
            font-size: 16px;
        }
        button:hover { background: #0077cc; }
        .message {
            padding: 10px; border-radius: 4px; text-align: center; margin-bottom: 15px;
        }
        .message.error { background: #f8d7da; color: #721c24; }
        .message.success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
<div class="container">
    <h2>Login</h2>

    <!-- Display messages -->
    <?php
    if (!empty($_SESSION['login_errors'])) {
        foreach ($_SESSION['login_errors'] as $error) {
            echo "<p class='message error'>" . htmlspecialchars($error) . "</p>";
        }
        unset($_SESSION['login_errors']);
    }

    if (isset($_SESSION['flash_success'])) {
        echo "<p class='message success'>" . htmlspecialchars($_SESSION['flash_success']) . "</p>";
        unset($_SESSION['flash_success']);
    }
    ?>

    <!-- Login form -->
    <form action="/app/authenticate.php" method="POST">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($old_email) ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <button type="submit">Login</button>
    </form>

    <div style="text-align:center; margin-top:15px;">
        <p>Don't have an account?</p>
        <a href="../app/register"><button type="button" style="background:#28a745;">Register</button></a>
    </div>
</div>
</body>
</html>
