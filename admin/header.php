<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';

// Get site name and logo path from the database
$siteName = get_setting('site_name') ?: 'Chandusoft';
$logoPath = get_setting('logo_path') ?: 'images/logo.jpg'; // Default logo if no logo in the DB

// Debugging: Output the full logo path to check if it's correct
echo "<!-- Full Logo Path: " . htmlspecialchars($logoPath) . " -->";  // Debugging line for logo path

// If logo path is provided and stored correctly in the database, make sure it outputs the full URL path
if ($logoPath) {
    // Make sure the path is correct relative to the web root
    if (strpos($logoPath, 'uploads/') === 0) {  // If path starts with 'uploads/'
        $logoPath = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $logoPath; // Construct full URL
    }
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
    <!-- Static Pages -->
    <a href="index.php" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/index.php') !== false || $_SERVER['REQUEST_URI'] == '/') ? 'active' : ''; ?>"><b>Home</b></a>
    <a href="about.php" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/about.php') !== false) ? 'active' : ''; ?>"><b>About</b></a>
    <a href="services.php" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/services.php') !== false) ? 'active' : ''; ?>"><b>Services</b></a>
    <a href="contact.php" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/contact.php') !== false) ? 'active' : ''; ?>"><b>Contact</b></a>

    <!-- Dynamic Pages -->
    <?php
    try {
        // Fetch published pages from the database
        $navStmt = $pdo->query("SELECT title, slug FROM pages WHERE status = 'published'");
        foreach ($navStmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
            // Check if the current dynamic page matches the URL
            $currentPage = isset($_GET['page']) && $_GET['page'] === $p['slug'];
            echo '<a href="/' . htmlspecialchars($p['slug']) . '" class="' . ($currentPage ? 'active' : '') . '">' . htmlspecialchars($p['title']) . '</a>';
        }
    } catch (PDOException $e) {
        // In case of any errors, we can log them or display a comment
        echo "<!-- Navigation fetch error: " . htmlspecialchars($e->getMessage()) . " -->";
    }
    
    ?>
   <!-- Register -->
    <a href="app/register.php" class="<?php echo ($_SERVER['REQUEST_URI'] == '/app/register.php' ? 'active' : ''); ?>">Login/Register</a>
</nav>



</header>
