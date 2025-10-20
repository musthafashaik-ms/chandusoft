<?php
// Check if 'page' parameter is set in the URL
if (isset($_GET['page'])) {
    $slug = $_GET['page']; // Get the slug from URL

    // Fetch the page content from the database using the slug
    $stmt = $pdo->prepare("SELECT title, content_html FROM pages WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $pageData = $stmt->fetch();

    // Check if the page exists in the database
    if ($pageData) {
        ob_start();  // Start output buffering
        echo "<h1>" . htmlspecialchars($pageData['title']) . "</h1>";
        echo "<div>" . (!empty($pageData['content_html']) ? $pageData['content_html'] : '<p>No content available.</p>') . "</div>";
        $content = ob_get_clean(); // Get the buffered content and assign to $content
    } else {
        // If no page is found, return a 404
        header("HTTP/1.0 404 Not Found");
        include __DIR__ . '/404.php';
        exit;
    }
} else {
    // If no page is specified, you could display the homepage or default content
    header("Location: /admin/index");
    exit;
}

// Include the layout (header, footer, etc.)
include __DIR__ . '/layout.php';
?>
