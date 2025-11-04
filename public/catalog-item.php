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

$TURNSTILE_SITE = getenv('TURNSTILE_SITE') ?: '0x4AAAAAAB7ii-4RV0QMh131';
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
    }

    .product-image {
        flex: 1;
        max-width: 50%;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .product-info {
        flex: 1;
        padding: 30px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .product-info h1 {
        color: #007BFF;
        margin-bottom: 10px;
    }

    .price {
        color: green;
        font-size: 1.4em;
        margin-bottom: 15px;
    }

    .product-description {
        color: #555;
        margin-bottom: 20px;
    }

    /* Tabs */
    .tabs {
        display: flex;
        border-bottom: 2px solid #007BFF;
        margin-bottom: 20px;
    }

    .tab {
        flex: 1;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        font-weight: bold;
        color: #007BFF;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }

    .tab.active {
        border-color: Blue;
        color: #007bff;
    }

    .tab-content {
        display: none;
        opacity: 0;
        transition: opacity 0.5s ease;
    }

    .tab-content.active {
        display: block;
        opacity: 1;
    }

    form {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 1px 5px rgba(0,0,0,0.1);
    }

    form input, form textarea, form button {
        width: 100%;
        margin-bottom: 12px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    form button {
        background: #007BFF;
        color: white;
        border: none;
        cursor: pointer;
        transition: background 0.3s;
    }

    form button:hover {
        background: #0056b3;
    }
.cf-turnstile {
    margin: 5px 0 15px 45px; /* Add left margin (20px) to move it right */
    width: 100%; /* Full width */
    max-width: 800px; /* Optional: Max width */
    height: 50px; /* Set height */
    transform: scale(1.2); /* Adjust size */
    transform-origin: top center; /* Scale from the top */
    overflow: hidden;
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

    @media (max-width: 768px) {
        .product-card {
            flex-direction: column;
        }
        .product-image, .product-info {
            max-width: 100%;
        }
    }

    .zoom-container {
    overflow: hidden;
    position: relative;
    cursor: zoom-in;
}

.zoom-container.zoomed {
    cursor: zoom-out;
}

.zoom-image {
    transition: transform 0.3s ease;
}

/* Optional: zoom on hover */
.zoom-container:hover .zoom-image {
    transform: scale(1.2);
}

</style>
</head>
<body>

<div class="product-card">
    <!-- Image Section -->
    <div class="product-image zoom-container">
    <?php if ($item['image']): ?>
        <img src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="zoom-image">
    <?php endif; ?>
</div>


    <!-- Info Section -->
    <div class="product-info">
        <h1><?= htmlspecialchars($item['title']) ?></h1>
        <p class="price">$<?= number_format($item['price'], 2) ?></p>
        <p class="product-description"><?= nl2br(htmlspecialchars($item['short_desc'])) ?></p>

        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" data-target="cart-tab">🛒 Add to Cart</div>
            <div class="tab" data-target="enquiry-tab">📩 Enquiry</div>
        </div>

        <!-- Add to Cart Form -->
        <div id="cart-tab" class="tab-content active">
            <form method="post" action="/public/cart">
                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                <label>Quantity:</label>
                <input type="number" name="quantity" value="1" min="1" required>
                <button type="submit">Add to Cart</button>
            </form>
        </div>

        <!-- Enquiry Form -->
        <div id="enquiry-tab" class="tab-content">
            <form method="post" action="/public/send-enquiry">
                <input type="hidden" name="product" value="<?= htmlspecialchars($item['title']) ?>">
                <input type="text" name="name" placeholder="Your name" required>
                <input type="email" name="email" placeholder="Your email" required>
                <textarea name="message" placeholder="Your message" rows="4" required></textarea>
                <div class="cf-turnstile"
                     data-sitekey="<?= htmlspecialchars($TURNSTILE_SITE) ?>"
                     data-theme="light"
                     data-size="compact"></div>
                <button type="submit">Send Enquiry</button>
            </form>
        </div>
    </div>
</div>

<a href="/public/catalog" class="back-to-catalog">← Back to Catalog</a>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script>
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab, .tab-content').forEach(el => el.classList.remove('active'));
        tab.classList.add('active');
        const target = document.getElementById(tab.dataset.target);
        target.classList.add('active');
    });
});


const zoomContainer = document.querySelector('.zoom-container');
const zoomImage = zoomContainer.querySelector('.zoom-image');

zoomContainer.addEventListener('click', () => {
    if (zoomContainer.classList.contains('zoomed')) {
        zoomImage.style.transform = 'scale(1)';
        zoomContainer.classList.remove('zoomed');
    } else {
        zoomImage.style.transform = 'scale(2)'; // Zoom factor
        zoomContainer.classList.add('zoomed');
    }
});

// Optional: change cursor dynamically
zoomContainer.addEventListener('mousemove', (e) => {
    if (zoomContainer.classList.contains('zoomed')) {
        const rect = zoomContainer.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const moveX = (x / rect.width) * 100;
        const moveY = (y / rect.height) * 100;
        zoomImage.style.transformOrigin = `${moveX}% ${moveY}%`;
    } else {
        zoomImage.style.transformOrigin = 'center center';
    }
});
</script>

</body>
</html>
