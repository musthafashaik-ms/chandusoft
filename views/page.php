<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header("Location: /"); // redirect to home if no slug
    exit;
}

// Fetch page content by slug
$stmt = $pdo->prepare("SELECT title, content_html FROM pages WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/404.php';
    exit;
}

$siteName = get_setting('site_name') ?: 'Chandusoft';

include __DIR__ . '/layout.php';
?>
