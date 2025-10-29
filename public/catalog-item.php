<?php
require_once __DIR__ . '/../app/config.php';
// ✅ Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Retrieve user info safely
$user = $_SESSION['user'] ?? [];
$username = htmlspecialchars($user['username'] ?? 'User');
$role = htmlspecialchars(ucfirst($user['role'] ?? 'Editor'));

// 🔍 For debugging: display sitekey in HTML comment
echo "<!-- SITEKEY: " . htmlspecialchars(getenv('TURNSTILE_SITE')) . " -->";

$slug = $_GET['slug'] ?? '';  // Slug is the unique identifier for the product
$stmt = $pdo->prepare("SELECT * FROM catalog WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    echo "<h1>404 - Item not found</h1>";
    exit;
}

// Turnstile site key fallback for development
$TURNSTILE_SITE = getenv('TURNSTILE_SITE') ?: '0x4AAAAAAB7ii-4RV0QMh131';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($item['title']) ?></title>

    <!-- ✅ JSON-LD schema for SEO -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "<?= htmlspecialchars($item['title']) ?>",
      "image": "https://yourdomain.com/<?= htmlspecialchars($item['image']) ?>",
      "description": "<?= htmlspecialchars($item['short_desc']) ?>",
      "offers": {
        "@type": "Offer",
        "priceCurrency": "USD",
        "price": "<?= $item['price'] ?>",
        "availability": "https://schema.org/InStock"
      }
    }
    </script>

    <!-- ✅ Styles -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
        }

        .navbar .links a:hover {
            text-decoration: none;
        }
        
        h1 {
            color: #007BFF;
            text-align: center;
        }
        
        img {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .price {
            font-size: 1.4em;
            color: green;
            margin-bottom: 20px;
            text-align: center;
        }

        .product-description {
            margin-bottom: 30px;
            text-align: center;
            color: #555;
        }

        form {
         background: #f8f8f8;
         padding: 12px;
         border-radius: 8px;
         margin-top: 30px;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
         position: relative;
         width: 80%;
         max-width: 600px;
         margin-left: auto;
         margin-right: auto;
        }

       form input,
          form textarea {
         width: 100%;
         padding: 8px 0px;
         margin: 8px 0;
         border: 1px solid #ccc;
         border-radius: 4px;
         font-size: 0.9em;
       }

        form button {
        background: #007BFF;
        color: white;
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: auto;
        font-size: 0.9em;
        position: absolute;
        bottom: 10px;
        left: 10px;
        display: inline-block;
       }

       form button:hover {
        background: #0056b3;
       }
       
       .cf-turnstile {
        margin: 30px 0;
        }
    </style>

    <!-- ✅ Turnstile script -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body>
     
<!-- ✅ Navbar -->
<div class="navbar">
    <div><strong>Chandusoft Admin</strong></div>
    <div class="links">
        Welcome <?= $role ?>!
        <a href="/app/dashboard.php">Dashboard</a>
         <!-- Dynamic catalog link based on user role -->
    <?php if ($role === 'Admin'): ?>
        <a href="/admin/catalog.php">Admin Catalog</a>
        <a href="/public/catalog.php">Public Catalog</a>
    <?php elseif ($role === 'Editor'): ?>
        <a href="/public/catalog.php">Public Catalog</a>
    <?php endif; ?>
        <a href="/admin/admin-leads.php">Leads</a>
        <a href="/admin/pages.php">Pages</a>
        <a href="/admin/logout.php">Logout</a>

    </div>
</div>

<h1><?= htmlspecialchars($item['title']) ?></h1>

<?php if ($item['image']): ?>
    <img src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
<?php endif; ?>

<p class="price">$<?= number_format($item['price'], 2) ?></p>

<p class="product-description"><?= nl2br(htmlspecialchars($item['short_desc'])) ?></p>

<!-- Enquiry Form -->
<h2>Enquire about this product</h2>
<form id="enquiry-form" method="POST" action="send-enquiry.php">
    <input type="hidden" name="product" value="<?= htmlspecialchars($item['title']) ?>">
    <input type="text" name="name" placeholder="Your name" required>
    <input type="email" name="email" placeholder="Your email" required>
    <textarea name="message" placeholder="Your message" rows="4" required></textarea>

    <!-- Turnstile Widget (Optional, enable in production) -->
    <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($TURNSTILE_SITE) ?>" data-theme="light"></div>

    <button type="submit">Send Enquiry</button>
</form>

<!-- Optional JS for AJAX submission -->
<script>
document.getElementById('enquiry-form').addEventListener('submit', function(e){
    e.preventDefault(); // prevent default page reload

    const form = e.target;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        alert(data); // show success/error message
        form.reset();
    })
    .catch(err => alert('Error submitting form.'));
});
</script>


</body>
</html>
