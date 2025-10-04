<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Use true if HTTPS is always enforced
ini_set('session.use_strict_mode', 1);
session_start();

require 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['flash_error'] = "You must log in first.";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body>

<?php
// Display success message before <h2>
if (isset($_SESSION['flash_success'])) {
    echo "<p style='color:green;'>".htmlspecialchars($_SESSION['flash_success'])."</p>";
    unset($_SESSION['flash_success']);
}
?>

<h2>Welcome to the Dashboard</h2>

<?php
$username = htmlspecialchars($_SESSION['user']['username'] ?? 'User');
?>

<p>Hello, <strong><?= $username ?></strong>!</p>

<p><a href="logout.php">Logout</a></p>

</body>
</html>
