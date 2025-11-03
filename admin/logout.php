<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Also clear specific session variables that might be causing the issue
unset($_SESSION['old_email']);

// Redirect to login page
header("Location: /admin/login.php");
exit();
?>
