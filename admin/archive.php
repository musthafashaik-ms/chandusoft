<?php
session_start();

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

// ✅ Connect to database
$conn = new mysqli('localhost', 'root', '', 'chandusoft');
if ($conn->connect_error) {
    die("❌ DB connection failed: " . $conn->connect_error);
}

// ✅ Archive the page
$stmt = $conn->prepare("UPDATE pages SET status = 'archived', updated_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: pages.php?message=Page+archived+successfully");
    exit;
} else {
    echo "❌ Failed to archive page.";
}
?>
