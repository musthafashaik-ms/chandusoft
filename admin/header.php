<header>
    <div class="logo">
        <a href="index.php">
            <img src="images/logo.jpg" title="Chandusoft Technologies" width="400" height="70" style="vertical-align:middle">
        </a>
    </div>
    <nav>
        <a href="index.php"><b>Home</b></a>
        <a href="about.php"><b>About</b></a>
       

        <?php
        require __DIR__ . '/../app/config.php'; // DB connection

        // -----------------------
        // Fetch pages for navbar
        // -----------------------
        $navStmt = $pdo->query("SELECT title, slug FROM pages WHERE status = 'published'");
        $navPages = $navStmt->fetchAll(PDO::FETCH_ASSOC);

        // -----------------------
        // Dynamically generate navbar links
        // -----------------------
        foreach ($navPages as $p) {
            // Ensure the link goes to the correct location (index.php?page=slug)
            echo '<a href="index.php?page=' . htmlspecialchars($p['slug']) . '">' . htmlspecialchars($p['title']) . '</a>';
        }
        ?>
         <a href="services.php"><b>Services</b></a>
        <a href="contact.php"><b>Contact</b></a>
    </nav>
</header>

