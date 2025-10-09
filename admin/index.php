<?php
// Include the config file for database connection
require_once __DIR__ . '/../app/config.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chandusoft</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <!-- Include header -->
    <div id="header"></div>
    <?php include("header.php"); ?>

    <main>
        <?php
        // Check if the 'page' slug is set in the URL
        if (isset($_GET['page'])) {
            $slug = $_GET['page'];


            // Fetch the page by slug using PDO
$stmt = $pdo->prepare("SELECT title, content_html FROM pages WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$page = $stmt->fetch();

// Debugging: Check if a page is found
if ($page) {
    echo "<section class='page-content'>";
    echo "<h1>" . htmlspecialchars($page['title']) . "</h1>";
    echo "<div class='content'>" . (!empty($page['content_html']) ? $page['content_html'] : '<p>No content available.</p>') . "</div>";
    echo "</section>";
} else {
    // Page not found
    echo "<section class='error'><p>Page not found.</p></section>";
}

        } else {
            // Default hero section if no specific page is requested
            ?>
            <section class="hero">
                <div class="hero-content">
                    <h1>Welcome to Chandusoft</h1>
                    <p>Delivering IT & BPO solutions for over 15 years.</p>
                    <a href="services.php" class="btn-hero"><b>Explore Services</b></a>
                </div>
            </section>
            <section class="testimonials">
                <h2 style="color: rgb(42, 105, 240);">What Our Clients Say</h2>
                <div class="testimonial-container">
                    <div class="testimonial">
                        <p>"Chandusoft helped us streamline our processes. Their 24/7 support means we never miss a client query."</p>
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
                </div>
            </section>
            <?php
        }
        ?>
    </main>

    <!-- Include footer -->
    <div id="footer"></div>
    <?php include("footer.php"); ?>

    <!-- The "Back to Top" button -->
    <button id="back-to-top" title="Back to Top">↑</button>

    <!-- Include JavaScript -->
    <script src="include.js"></script>
</body>

</html>
