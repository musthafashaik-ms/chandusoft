<?php
session_start();

require_once '../app/config.php'; // Database connection ($pdo)
require_once '../app/logger.php'; // Logger functions

// ✅ Check if user is admin
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role']) !== 'admin') {
    http_response_code(403);
    die('❌ Access denied. Admins only.');
}

// ✅ Get page ID from query
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    $_SESSION['flash_error'] = "Invalid page ID.";
    header("Location: pages.php");
    exit();
}

// ✅ Fetch page info (optional, for logging)
try {
    $stmt = $pdo->prepare("SELECT title, slug FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();

    if (!$page) {
        $_SESSION['flash_error'] = "Page not found.";
        header("Location: pages.php");
        exit();
    }

    $title = $page['title'];
    $slug = $page['slug'];

    // ✅ Delete the page
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$id]);

    // ✅ Log the deletion to Mailpit
    $username = htmlspecialchars($_SESSION['user']['username'] ?? 'Unknown');
    log_page("🗑️ Deleted Page | ID: $id | Title: $title | Slug: $slug | By: $username");

    $_SESSION['flash_success'] = "Page deleted successfully.";
    header("Location: pages.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['flash_error'] = "Database error: " . $e->getMessage();
    header("Location: pages.php");
    exit();
}
?>
