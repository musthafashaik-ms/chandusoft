<?php
require_once __DIR__ . '/../app/config.php';

// 🔍 For debug: show sitekey value in HTML comment
echo "<!-- SITEKEY: " . htmlspecialchars(getenv('TURNSTILE_SITE')) . " -->";

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM catalog WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    echo "<h1>404 - Item not found</h1>";
    exit;
}

// Turnstile site key fallback for dev
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
            padding: 20px;
            max-width: 800px;
            margin: auto;
            background-color: #f8f9fa;
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
         padding: 12px; /* Reduced padding */
         border-radius: 8px;
         margin-top: 30px;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
         position: relative; /* Needed for positioning the button */
         width: 80%; /* Reduce the width to make the form smaller */
         max-width: 600px; /* Set a max width for larger screens */
         margin-left: auto; /* Center the form horizontally */
         margin-right: auto; /* Center the form horizontally */
        }

       form input,
          form textarea {
         width: 100%;
         padding: 8px 0px; /* Reduced padding for a smaller input field */
         margin: 8px 0; /* Reduced margin for a tighter layout */
         border: 1px solid #ccc;
         border-radius: 4px;
         font-size: 0.9em; /* Smaller font size */
       }

 /* Smaller Send Enquiry button */
        form button {
        background: #007BFF;
        color: white;
        padding: 6px 12px; /* Smaller padding */
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: auto; /* Set width to auto for smaller size */
        font-size: 0.9em; /* Smaller font size */
        position: absolute; /* Absolute position to move it to the left corner */
        bottom: 10px; /* Reduced distance from the bottom */
        left: 10px; /* Position it at the left side */
        display: inline-block;
       }

 /* Hover effect for Send Enquiry button */
       form button:hover {
        background: #0056b3;
       }
       .cf-turnstile {
        margin: 30px 0;
        }

        .back-to-catalog {
            position: absolute;
            bottom: -110px;
            right: 380px;
            background: #007BFF;
            color: #edf1f5ff;
            border: 1px solid #007BFF;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 1em;
            cursor: pointer;
        }

        .back-to-catalog:hover {
            background: #007BFF;
            color: white;
        }
    </style>

    <!-- ✅ Turnstile script -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body>

<h1><?= htmlspecialchars($item['title']) ?></h1>

<?php if ($item['image']): ?>
    <img src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
<?php endif; ?>

<p class="price">$<?= number_format($item['price'], 2) ?></p>

<p class="product-description"><?= nl2br(htmlspecialchars($item['short_desc'])) ?></p>

<!-- ✅ Enquiry Form -->
<h2>Enquire about this product</h2>
<form id="enquiry-form" method="post" action="send-enquiry.php">
    <input type="hidden" name="product" value="<?= htmlspecialchars($item['title']) ?>">
    <input type="text" name="name" placeholder=" Your name" required>
    <input type="email" name="email" placeholder=" Your email" required>
    <textarea name="message" placeholder=" Your message" rows="4" required></textarea>

    <!-- ✅ Turnstile Widget -->
    <div class="cf-turnstile"
         data-sitekey="<?= htmlspecialchars($TURNSTILE_SITE) ?>"
         data-theme="light">
    </div>

    <!-- Smaller Send Enquiry Button -->
    <button type="submit">Send Enquiry</button>
</form>

<!-- ✅ Back to Catalog Button -->
<a href="catalog.php" class="back-to-catalog">Back to Catalog</a>

</body>
</html>
