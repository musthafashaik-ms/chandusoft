<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
 
// Get site name and logo path
$siteName = get_setting('site_name') ?: 'Chandusoft';
$logoPath = get_setting('logo_path') ?: 'images/logo.jpg';
 
// Fix logo path (uploads/)
if ($logoPath && strpos($logoPath, 'uploads/') === 0) {
    $logoPath = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $logoPath;
}
 
// Get current URI (e.g. /index, /about, /services)
$currentURI = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
// Example results: "index", "about", "services", "slug", "app/register"
?>
<header>
    <div class="logo">
        <a href="/index">
            <img src="<?php echo htmlspecialchars($logoPath); ?>"
                 alt="<?php echo htmlspecialchars($siteName); ?>"
                 title="<?php echo htmlspecialchars($siteName); ?>"
                 width="150" height="auto" style="vertical-align:middle; max-height: 80px;">
        </a>
    </div>
 
    <nav>
        <!-- Static Pages -->
        <a href="/index" class="<?php echo ($currentURI === 'index') ? 'active' : ''; ?>"><b>Home</b></a>
        <a href="/about" class="<?php echo ($currentURI === 'about') ? 'active' : ''; ?>"><b>About</b></a>
        <a href="/services" class="<?php echo ($currentURI === 'services') ? 'active' : ''; ?>"><b>Services</b></a>
        <a href="/contact" class="<?php echo ($currentURI === 'contact') ? 'active' : ''; ?>"><b>Contact</b></a>
        <a href="/public/catalog" class="<?php echo ($currentURI === 'public/catalog') ? 'active' : ''; ?>"><b>Catalog</b></a>
 
        <!-- Dynamic Pages (from DB) -->
        <?php
        try {
            $navStmt = $pdo->query("SELECT title, slug FROM pages WHERE status = 'published'");
            foreach ($navStmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
                $slug = htmlspecialchars($p['slug']);
                $isActive = ($currentURI === $slug);
                echo '<a href="/' . $slug . '" class="' . ($isActive ? 'active' : '') . '">' . htmlspecialchars($p['title']) . '</a>';
            }
        } catch (PDOException $e) {
            echo "<!-- Navigation fetch error: " . htmlspecialchars($e->getMessage()) . " -->";
        }
        ?>
 
        <!-- ✅ Login/Register -->
<span>
  <a href="/admin/login.php" class="<?= ($currentPage === 'login') ? 'active' : '' ?>">Login</a>
  /
  <a href="/app/register" class="<?= ($currentPage === 'register') ? 'active' : '' ?>">Register</a>
</span>
    </nav>
</header>