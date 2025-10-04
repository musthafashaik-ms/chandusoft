<?php
require 'config.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Repopulate old input if validation fails
$old = $_SESSION['register_old'] ?? ['email' => '', 'username' => ''];
unset($_SESSION['register_old']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Create an Account</h2>

<?php
// Show flash messages
if (!empty($_SESSION['register_errors'])) {
    foreach ($_SESSION['register_errors'] as $error) {
        echo "<p style='color:red;'>{$error}</p>";
    }
    unset($_SESSION['register_errors']);
}

if (isset($_SESSION['flash_success'])) {
    echo "<p style='color:green;'>{$_SESSION['flash_success']}</p>";
    unset($_SESSION['flash_success']);
}
?>

<form action="register_handler.php" method="POST">
    <label>Email:</label><br>
    <input type="text" name="email" value="<?= htmlspecialchars($old['email']) ?>" required><br><br>

    <label>Username:</label><br>
    <input type="text" name="username" value="<?= htmlspecialchars($old['username']) ?>" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="login.php">Login here</a></p>

</body>
</html>
