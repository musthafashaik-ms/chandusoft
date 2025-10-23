<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';


$siteName = get_setting('site_name') ?: 'Chandusoft';
$logoPath = get_setting('logo_path') ?: 'uploads/default-logo.png';
$metaDescription = get_setting('meta_description') ?: 'Chandusoft provides IT & BPO services.';
$metaKeywords = get_setting('meta_keywords') ?: 'IT, BPO, Outsourcing, Chandusoft';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/styles.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords); ?>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Header -->
    <?php include("header.php"); ?>

    <main>
        <?php
        if (isset($_GET['page'])) {
            $slug = $_GET['page'];
            $stmt = $pdo->prepare("SELECT title, content_html FROM pages WHERE slug = ? AND status = 'published'");
            $stmt->execute([$slug]);
            $page = $stmt->fetch();

            if ($page) {
                echo "<section class='page-content'>";
                echo "<h1>" . htmlspecialchars($page['title']) . "</h1>";
                echo "<div class='content'>" . (!empty($page['content_html']) ? $page['content_html'] : '<p>No content available.</p>') . "</div>";
                echo "</section>";
            } else {
                echo "<section class='error'><p>Page not found.</p></section>";
            }
        } else {
            ?>
            <section class="hero">
                <div class="hero-content">
                    <h1>Welcome to <?php echo htmlspecialchars($siteName); ?></h1>
                    <p>Delivering IT & BPO solutions for over 15 years.</p>
                    <a href="services" class="btn-hero"><b>Explore Services</b></a>
                </div>
            </section>

            <section class="testimonials">
  <h2 style="color: rgb(42, 105, 240);">What Our Clients Say</h2>
  <div class="testimonial-container">
    <div class="testimonial">
      <p>Chandusoft helped us streamline our processes. Their 24/7 support means we never miss a client query."</p>
      <h4>John Smith</h4>
      <span>Operations Manager, GlobalTech</span>
    </div>
    <div class="testimonial">
      <p>"Our e-commerce platform scaled smoothly after migrating with Chandusoft. Sales grew by 40% in just 6 months!"</p>
      <h4>Priya Verma</h4>
      <span>Founder, TrendyMart</span>
    </div>
    <div class="testimonial">
      <p>"The QA team at Chandusoft made our product launch seamless. Bug-free delivery on time!"</p>
      <h4>Ahmed Khan</h4>
      <span>Product Lead, Medisoft</span>
    </div>
</section>
            <?php
        }
        ?>
    </main>

    <?php include("footer.php"); ?>

    <button id="back-to-top" title="Back to Top">↑</button>
    <script src="include.js"></script>
</body>
</html>
