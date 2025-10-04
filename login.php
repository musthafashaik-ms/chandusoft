<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Use true if HTTPS is always enforced
ini_set('session.use_strict_mode', 1);
session_start();
?>

<?php
require 'config.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Save old input
$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_email']);
?>

<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>

<h2>Login</h2>

<?php
// Show errors
if (!empty($_SESSION['login_errors'])) {
    foreach ($_SESSION['login_errors'] as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
    unset($_SESSION['login_errors']);
}

// Show flash error
if (isset($_SESSION['flash_error'])) {
    echo "<p style='color:red;'>{$_SESSION['flash_error']}</p>";
    unset($_SESSION['flash_error']);
}
?>

<form action="authenticate.php" method="POST">
    <label>Email:</label><br>
    <input type="text" name="email" value="<?= htmlspecialchars($old_email) ?>" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <button type="submit">Login</button>
</form>

</body>
</html>
