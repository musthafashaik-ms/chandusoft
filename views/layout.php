<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';

// Get the page slug from URL
$slug = $_GET['page'] ?? '';

// Fetch page data from database if slug exists
if ($slug) {
    $stmt = $pdo->prepare("SELECT title, content_html FROM pages WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
}

// Default site settings
$siteName = get_setting('site_name') ?: 'Chandusoft';
$metaDescription = get_setting('meta_description') ?: 'Chandusoft provides IT & BPO services.';
$metaKeywords = get_setting('meta_keywords') ?: 'IT, BPO, Outsourcing, Chandusoft';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page['title'] ?? $siteName); ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription); ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords); ?>">
    <link rel="stylesheet" href="/styles.css">
</head>
<body>

<?php include __DIR__ . '/../admin/header.php'; ?>

<main>
<?php
if (!empty($page)) {
    echo "<section class='page-content'>";
    echo "<h1>" . htmlspecialchars($page['title']) . "</h1>";
    echo "<div class='content'>" . $page['content_html'] . "</div>";
    echo "</section>";
} else {
    echo "<section class='error'><p>404 - Page Not Found</p></section>";
}
?>
</main>

<?php include __DIR__ . '/../admin/footer.php'; ?>

</body>
</html>
