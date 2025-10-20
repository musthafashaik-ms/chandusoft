<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';

$siteName = get_setting('site_name') ?: 'Chandusoft';
$logoPath = get_setting('logo_path') ?: 'images/logo.jpg';

// Ensure logo path is root-relative so browser loads it correctly
if ($logoPath && $logoPath[0] !== '/') {
    $logoPath = '/' . $logoPath;
}
?>

<header>
    <div class="logo">
        <a href="index.php">
            <img src="<?php echo htmlspecialchars($logoPath); ?>" 
     alt="<?php echo htmlspecialchars($siteName); ?>"
     title="<?php echo htmlspecialchars($siteName); ?>" 
     width="150" height="auto" style="vertical-align:middle; max-height: 80px;">

        </a>
    </div>
    <nav>
        <a href="index.php"><b>Home</b></a>
        <a href="about.php"><b>About</b></a>
        <a href="services"><b>Services</b></a>
        <a href="contact"><b>Contact</b></a>

        <?php
        try {
            $navStmt = $pdo->query("SELECT title, slug FROM pages WHERE status = 'published'");
            foreach ($navStmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
                // Output link with slug, make sure to sanitize it
                echo '<a href="' . htmlspecialchars($p['slug']) . '">' . htmlspecialchars($p['title']) . '</a>';
            }
        } catch (PDOException $e) {
            echo "<!-- Navigation fetch error: " . htmlspecialchars($e->getMessage()) . " -->";
        }
        ?>
    </nav>
</header>
