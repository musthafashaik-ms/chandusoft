<?php
require_once __DIR__ . '/../app/config.php'; // Include the database connection

// Default page slug is 'home' if no page is specified
$page = isset($_GET['page']) ? $_GET['page'] : 'home'; // Default page is home

// Fetch page content based on the 'page' slug
$stmt = $pdo->prepare("SELECT title, content_html FROM pages WHERE slug = ? AND status = 'published'");
$stmt->execute([$page]);
$pageData = $stmt->fetch();

// If the page is found, display the content; otherwise, show a 404 error
if ($pageData) {
    ob_start();  // Start output buffering
    echo "<h1>" . htmlspecialchars($pageData['title']) . "</h1>";
    echo "<div>" . (!empty($pageData['content_html']) ? $pageData['content_html'] : '<p>No content available.</p>') . "</div>";
    $content = ob_get_clean(); // Capture the content and assign it to $content
} else {
    // Page not found
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/404.php';
    exit;
}

// Include the layout (header, footer, etc.)
include __DIR__ . '/layout.php';
?>
