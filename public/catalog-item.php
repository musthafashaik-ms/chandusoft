<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM catalog WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    echo "<h1>404 - Item not found</h1>";
    exit;
}

// Turnstile site key (optional for local)
$TURNSTILE_SITE = getenv('TURNSTILE_SITE') ?: '0x4AAAAAAB7ii-4RV0QMh131';

// Log view
log_catalog("Viewed catalog item: {$item['title']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($item['title']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; }
        h1 { color: #007BFF; text-align: center; margin-bottom: 20px; }
        img { display: block; margin: 0 auto 20px auto; width: 100%; max-width: 400px; height: 300px; object-fit: cover; border-radius: 8px; }
        .price { text-align: center; color: green; font-size: 1.4em; margin-bottom: 10px; }
        .product-description { text-align: center; color: #555; margin-bottom: 30px; }
        form { background: #fff; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        form input, form textarea { width: 100%; padding: 8px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 4px; }
        form button { background: #007BFF; color: #fff; border: none; border-radius: 4px; padding: 10px 16px; cursor: pointer; }
        form button:hover { background: #0056b3; }
        .cf-turnstile { margin: 20px 0; }
        .back-to-catalog { position: fixed; bottom: 20px; right: 20px; background: #007BFF; color: #fff; padding: 10px 20px; border-radius: 4px; text-decoration: none; }
        .back-to-catalog:hover { background: #0056b3; }
    </style>
</head>
<body>

<h1><?= htmlspecialchars($item['title']) ?></h1>

<?php if ($item['image']): ?>
    <img src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
<?php endif; ?>

<p class="price">$<?= number_format($item['price'], 2) ?></p>
<p class="product-description"><?= nl2br(htmlspecialchars($item['short_desc'])) ?></p>

<h2 style="text-align:center;">Enquire about this product</h2>
<form method="post" action="/public/send-enquiry">
    <input type="hidden" name="product" value="<?= htmlspecialchars($item['title']) ?>">
    <input type="text" name="name" placeholder="Your name" required>
    <input type="email" name="email" placeholder="Your email" required>
    <textarea name="message" placeholder="Your message" rows="4" required></textarea>

    <!-- ✅ Optional Turnstile Widget -->
    <div class="cf-turnstile"
         data-sitekey="<?= htmlspecialchars($TURNSTILE_SITE) ?>"
         data-theme="light"></div>

    <button type="submit">Send Enquiry</button>
</form>

<form method="post" action="/public/cart">
    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
    <input type="number" name="quantity" value="1" min="1">
    <button type="submit">Add to Cart</button>
</form>


<a href="catalog.php" class="back-to-catalog">Back to Catalog</a>

</body>
</html>
