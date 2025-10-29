<?php
session_start();

require_once '../app/logger.php'; // Include logger functions

// ✅ Check login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// ✅ Check role is admin
if (strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
    die("❌ Access denied. Admins only.");
}

// ✅ Get page ID
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("❌ Invalid page ID.");
}

// ✅ Retrieve username
$username = htmlspecialchars($_SESSION['user']['username'] ?? 'Admin');

// ✅ Connect to database
$conn = new mysqli('localhost', 'root', '', 'chandusoft');
if ($conn->connect_error) {
    die("❌ DB connection failed: " . $conn->connect_error);
}

// ✅ Fetch page title for logging
$stmt = $conn->prepare("SELECT title FROM pages WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($pageTitle);
$stmt->fetch();
$stmt->close();

if (!$pageTitle) {
    die("❌ Page not found.");
}

// ✅ Archive the page
$stmt = $conn->prepare("UPDATE pages SET status = 'archived', updated_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // ✅ Log the archive action
    log_page("🗃️Page archived | ID: $id | Title: $pageTitle | By: $username");

    header("Location: pages.php?message=Page+archived+successfully");
    exit;
} else {
    log_error("Failed to archive page | ID: $id | Title: $pageTitle | By: $username");
    echo "❌ Failed to archive page.";
}
?>
