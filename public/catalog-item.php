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
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        padding: 20px;
        margin: 0;
    }

    .product-card {
        display: flex;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        max-width: 1000px;
        margin: 0 auto 30px;
        flex-wrap: wrap;
    }

    .product-image {
        flex: 1 1 300px;
        max-width: 400px;
        height: auto;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .product-info {
        flex: 2 1 500px;
        padding: 30px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-info h1 {
        color: #007BFF;
        margin-bottom: 15px;
    }

    .price {
        color: green;
        font-size: 1.5em;
        margin-bottom: 15px;
    }

    .product-description {
        color: #555;
        margin-bottom: 20px;
    }

    .action-section {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .cart-form, .enquiry-form {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        flex: 1 1 250px;
        box-shadow: 0 1px 5px rgba(0,0,0,0.1);
    }

    .cart-form input[type="number"] {
        width: 80px;
        padding: 8px;
        margin-right: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .cart-form button, .enquiry-form button {
        background: #007BFF;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 10px 16px;
        cursor: pointer;
    }

    .cart-form button:hover, .enquiry-form button:hover {
        background: #0056b3;
    }

    .enquiry-form input, .enquiry-form textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .cf-turnstile { margin: 15px 0; }

    @media (max-width: 768px) {
        .product-card {
            flex-direction: column;
        }
        .product-image, .product-info {
            max-width: 100%;
        }
        .action-section {
            flex-direction: column;
        }
    }

    .back-to-catalog {
        display: inline-block;
        margin-top: 20px;
        background: #007BFF;
        color: #fff;
        padding: 10px 20px;
        border-radius: 4px;
        text-decoration: none;
    }
    .back-to-catalog:hover {
        background: #0056b3;
    }
</style>
</head>
<body>

<div class="product-card">
    <!-- Image Section -->
    <div class="product-image">
        <?php if ($item['image']): ?>
            <img src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
        <?php endif; ?>
    </div>

    <!-- Info Section -->
    <div class="product-info">
        <div>
            <h1><?= htmlspecialchars($item['title']) ?></h1>
            <p class="price">$<?= number_format($item['price'], 2) ?></p>
            <p class="product-description"><?= nl2br(htmlspecialchars($item['short_desc'])) ?></p>
        </div>

        <!-- Action Section -->
        <div class="action-section">
            <!-- Add to Cart Form -->
            <form class="cart-form" method="post" action="/public/cart">
                <h3>Add to Cart</h3>
                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                <label>
                    Quantity:
                    <input type="number" name="quantity" value="1" min="1">
                </label>
                <button type="submit">Add to Cart</button>
            </form>

            <!-- Enquiry Form -->
            <form class="enquiry-form" method="post" action="/public/send-enquiry">
                <h3>Enquire About This Product</h3>
                <input type="hidden" name="product" value="<?= htmlspecialchars($item['title']) ?>">
                <input type="text" name="name" placeholder="Your name" required>
                <input type="email" name="email" placeholder="Your email" required>
                <textarea name="message" placeholder="Your message" rows="4" required></textarea>

                <!-- Optional Turnstile Widget -->
                <div class="cf-turnstile"
                     data-sitekey="<?= htmlspecialchars($TURNSTILE_SITE) ?>"
                     data-theme="light"></div>

                <button type="submit">Send Enquiry</button>
            </form>
        </div>
    </div>
</div>

<a href="catalog.php" class="back-to-catalog">Back to Catalog</a>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</body>
</html>
