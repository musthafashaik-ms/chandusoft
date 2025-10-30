<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
 
// ✅ Auto-detect current page slug from URL
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$currentPage = str_replace('.php', '', $currentPage);
$currentPage = trim($currentPage, "/");
$currentPage = ($currentPage === '' || $currentPage === false) ? 'index' : $currentPage;
 
// ✅ Get site name and logo path from settings
$siteName = get_setting('site_name') ?: 'Chandusoft Technologies';
$logoPath = get_setting('logo_path') ?: 'images/logo.jpg';
 
// ✅ Fix logo path if it starts with uploads/
if ($logoPath && strpos($logoPath, 'uploads/') === 0) {
    $logoPath = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $logoPath;
}
 
// ✅ Define static pages
$staticPages = [
    'index'    => 'Home',
    'about'    => 'About',
    'services' => 'Services',
    'contact'  => 'Contact'
];
?>
<header>
    <div class="logo">
        <a href="/index">
            <img src="<?php echo htmlspecialchars($logoPath); ?>"
                 alt="<?php echo htmlspecialchars($siteName); ?>"
                 title="<?php echo htmlspecialchars($siteName); ?>"
                 width="150" height="auto"
                 style="vertical-align:middle; max-height:80px;">
        </a>
    </div>
 
    <nav>
        <!-- ✅ Static Pages -->
        <?php foreach ($staticPages as $slug => $title): ?>
            <a href="/<?= $slug ?>" class="<?= ($currentPage === $slug) ? 'active' : '' ?>">
                <b><?= htmlspecialchars($title) ?></b>
            </a>
        <?php endforeach; ?>
 
        <!-- ✅ Dynamic Pages (from DB) -->
        <?php
        try {
            $navStmt = $pdo->query("SELECT title, slug FROM pages WHERE status = 'published' ORDER BY title ASC");
            foreach ($navStmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
                $slug = htmlspecialchars($p['slug']);
                $title = htmlspecialchars($p['title']);
                if (!array_key_exists($slug, $staticPages)) {
                    echo '<a href="/' . $slug . '" class="' . ($currentPage === $slug ? 'active' : '') . '">' . $title . '</a>';
                }
            }
        } catch (PDOException $e) {
            echo "<!-- Navigation fetch error: " . htmlspecialchars($e->getMessage()) . " -->";
        }
        ?>
 
        <!-- ✅ Login/Register -->
        <a href="/admin/login.php" class="<?= ($currentPage === 'login') ? 'active' : '' ?>">
            Login/Register
        </a>
    </nav>
</header>