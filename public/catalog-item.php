<?php
require_once __DIR__ . '/../vendor/autoload.php'; // For Dotenv
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
 
use Dotenv\Dotenv;
 
// ---------------------------
// LOAD ENV VARIABLES
// ---------------------------
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // won't throw if .env is missing
 
// Turnstile
$TURNSTILE_SITE = $_ENV['TURNSTILE_SITE'] ?? getenv('TURNSTILE_SITE');
$TURNSTILE_SECRET = $_ENV['TURNSTILE_SECRET'] ?? getenv('TURNSTILE_SECRET');
// ---------------------------
// LOAD ITEM
// ---------------------------
$slug = $_GET['slug'] ?? '';
 
$stmt = $pdo->prepare("SELECT * FROM catalog WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$item = $stmt->fetch();
 
if (!$item) {
    http_response_code(404);
    echo "<h1>404 - Item not found</h1>";
    exit;
}
 
// Logging
log_catalog("Viewed catalog item: {$item['title']}");
 
// ---------------------------
// PREVENT UNDEFINED VARIABLE WARNINGS
// ---------------------------
$enquirySuccess = $_GET['success'] ?? false;
$errors = [];
$csrf_token = $_SESSION['_csrf'] ?? bin2hex(random_bytes(16));
$_SESSION['_csrf'] = $csrf_token;
 
// JSON-LD (optional)
$jsonLd = [
    "@context" => "https://schema.org/",
    "@type" => "Product",
    "name" => $item['title'],
    "image" => "/".$item['image'],
    "description" => $item['short_desc'],
    "offers" => [
        "@type" => "Offer",
        "priceCurrency" => "USD",
        "price" => $item['price'],
        "availability" => "https://schema.org/InStock"
    ]
];
 
$siteKey = $TURNSTILE_SITE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($item['title']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
 
  <!-- Turnstile -->
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
 
  <!-- Panzoom -->
  <script src="https://unpkg.com/@panzoom/panzoom/dist/panzoom.min.js"></script>
 
  <style>
    :root {
      --primary: #007BFF;
      --primary-dark: #0056b3;
      --accent: #28a745;
      --bg: #f2f4f8;
      --card-bg: #ffffff;
      --border: #d8dce6;
    }
 
    body {
      margin: 0;
      background: var(--bg);
      font-family: Inter, "Segoe UI", Arial;
      color: #0f172a;
    }
 
    .container {
      max-width: 1100px;
      margin: 50px auto;
      padding: 25px;
    }
 
    .product-card {
      background: var(--card-bg);
      padding: 28px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 36px;
      border-radius: 14px;
      box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
    }
 
    .image-thumb {
      width: 100%;
      border-radius: 12px;
      overflow: hidden;
      cursor: zoom-in;
      background: #fff;
      box-shadow: 0 6px 18px rgba(15,23,42,0.1);
    }
 
    .image-thumb img {
      width: 100%;
      display: block;
    }
 
    /* ---------- ZOOM MODAL ---------- */
 
    #zoomModal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.85);
      z-index: 9999;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }
 
    #zoomWrapper {
      max-width: 90%;
      max-height: 85%;
      overflow: hidden;
    }
 
    #modalImage {
      width: 100%;
      cursor: grab;
      touch-action: none;
      user-select: none;
    }
 
    /* Zoom Buttons */
    .zoom-controls {
      margin-top: 22px;
      display: flex;
      gap: 14px;
    }
   
 
    /* --------------------------------- */
 
    .product-info h1 { font-size: 28px; margin: 0 0 12px; }
    .product-price { font-size: 22px; color: var(--primary); font-weight: 700; margin-bottom: 12px; }
    .short-desc { font-size: 15px; color: #374151; margin-bottom: 18px; }
 
    .quantity-top-input {
      width: 80px;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid var(--border);
    }
 
    .action-btn, .back-btn, .send-btn {
      padding: 12px 20px;
      font-size: 15px;
      border-radius: 10px;
      background: var(--primary);
      border: none;
      color: #fff;
      cursor: pointer;
    }
 
    .send-btn { background: var(--accent); }
 
    .enquiry-box {
  padding: 30px;
  background: #fff;
  border-radius: 14px;
  margin-top: 35px;
  box-shadow: 0 8px 30px rgba(15, 23, 42, 0.05);
  border: 1px solid #e8ebf2;
}
 
.enquiry-box h2 {
  font-size: 22px;
  margin-bottom: 20px;
  font-weight: 700;
}
 
/* Equal spacing between inputs */
.form-group {
  margin-bottom: 16px;
}
 
/* Input styles */
.enquiry-box input,
.enquiry-box textarea {
  width: 100%;
  padding: 8px 8px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: #fafbff;
  font-size: 15px;
}
 
textarea {
  min-height: 130px;
  resize: vertical;
}
 
/* Success + Error messages */
.success,
.error {
  border-radius: 10px;
  padding: 14px;
  margin-bottom: 15px;
}
 
.success {
  background: #e6f9ee;
  border: 1px solid #27ae60;
  color: #1b7f47;
}
 
.error {
  background: #ffe6e9;
  border: 1px solid #ff8a8a;
  color: #b71c1c;
}
 
/* Footer buttons spacing */
.enquiry-footer {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;  /* FIX: Perfect left/right spacing */
  align-items: center;
}
 
/* Buttons */
.send-btn,
.back-btn {
  padding: 12px 24px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
  border: none;
  cursor: pointer;
}
 
.send-btn {
  background: var(--accent);
  color: #fff;
}
 
.back-btn {
  background: var(--primary);
  color: #fff;
}
 
       @media (max-width: 980px) {
       .product-card {
        grid-template-columns: 1fr;
       }
      }
  </style>
</head>
<body>
  <div class="container">
    <div class="product-card">
 
      <!-- PRODUCT IMAGE -->
      <div class="image-thumb">
        <img id="myZoomImage" src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
      </div>
 
      <!-- PRODUCT INFO -->
      <div class="product-info">
        <h1><?= htmlspecialchars($item['title']) ?></h1>
        <div class="product-price">$<?= number_format($item['price'], 2) ?></div>
        <p class="short-desc"><?= nl2br(htmlspecialchars($item['short_desc'])) ?></p>
 
        <form method="post" action="/public/cart">
          <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
          <input type="number" name="quantity" min="1" value="1" class="quantity-top-input">
          <button class="action-btn">🛒 Add to Cart</button>
        </form>
      </div>
 
    </div>
 
    <div class="enquiry-box">
      <h2>Enquire Now</h2>
 
      <?php if ($enquirySuccess): ?>
        <div class="success">Message sent successfully!</div>
      <?php endif; ?>
 
      <form method="post" action="/public/send-enquiry">
        <input type="hidden" name="_csrf" value="<?= $csrf_token ?>">
        <input type="hidden" name="product" value="<?= htmlspecialchars($item['title']) ?>">
 
        <div class="form-group">
          <input type="text" name="name" placeholder="Your Name" required>
        </div>
 
        <div class="form-group">
          <input type="email" name="email" placeholder="Your Email" required>
        </div>
 
        <div class="form-group">
          <textarea name="message" placeholder="Your Message" required></textarea>
        </div>
 
        <div class="form-group">
    <?php if ($TURNSTILE_SITE): ?>
        <div class="cf-turnstile"
             data-sitekey="<?= htmlspecialchars($TURNSTILE_SITE) ?>">
        </div>
    <?php else: ?>
        <p><em>CAPTCHA not configured.</em></p>
    <?php endif; ?>
</div>
 
<div class="enquiry-footer">
    <button type="submit" class="send-btn">Send Enquiry</button>
    <a href="/public/catalog" class="back-btn">← Back to Catalog</a>
</div>
 
 
 
    <!-- ZOOM MODAL -->
    <div id="zoomModal">
      <div id="zoomWrapper">
        <img id="modalImage" src="/<?= htmlspecialchars($item['image']) ?>">
      </div>
      <div class="zoom-controls"></div>
    </div>
  </div>
<!-- ================== ZOOM SCRIPT (ALL FEATURES) ================== -->
<script>
document.addEventListener("DOMContentLoaded", function () {
 
    const thumb = document.getElementById("myZoomImage");
    const modal = document.getElementById("zoomModal");
    const closeBtn = document.getElementById("closeZoomBtn");
 
    const modalImg = document.getElementById("modalImage");
 
    // Initialize Panzoom
    const panzoom = Panzoom(modalImg, {
        maxScale: 6,
        minScale: 1,
        contain: 'outside',
        step: 0.3
    });
 
    // Mouse wheel zoom
    modalImg.parentElement.addEventListener("wheel", panzoom.zoomWithWheel);
 
    // Open zoom modal
    thumb.addEventListener("click", () => {
        modal.style.display = "flex";
        panzoom.reset();
    });
    // Click outside image closes modal
    modal.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
    });
 
    // Zoom buttons
    document.getElementById("zoomInBtn").onclick = () => panzoom.zoomIn();
    document.getElementById("zoomOutBtn").onclick = () => panzoom.zoomOut();
    document.getElementById("resetZoomBtn").onclick = () => panzoom.reset();
 
    /* --------- DOUBLE TAP ZOOM (Like Instagram) --------- */
 
    let lastTap = 0;
 
    modalImg.addEventListener("touchend", function () {
        const now = Date.now();
        if (now - lastTap < 300) {
            panzoom.zoomIn({ animate: true });
        }
        lastTap = now;
    });
 
    /* --------- PINCH ZOOM (Mobile) --------- */
 
    let touch1 = null, touch2 = null;
 
    modalImg.addEventListener("touchstart", function(e) {
        if (e.touches.length === 2) {
            touch1 = e.touches[0];
            touch2 = e.touches[1];
        }
    });
 
    modalImg.addEventListener("touchmove", function(e) {
        if (e.touches.length === 2) {
            const newTouch1 = e.touches[0];
            const newTouch2 = e.touches[1];
 
            const oldDist = Math.hypot(
                touch1.pageX - touch2.pageX,
                touch1.pageY - touch2.pageY
            );
 
            const newDist = Math.hypot(
                newTouch1.pageX - newTouch2.pageX,
                newTouch1.pageY - newTouch2.pageY
            );
 
            if (newDist > oldDist) {
                panzoom.zoomIn();
            } else {
                panzoom.zoomOut();
            }
 
            touch1 = newTouch1;
            touch2 = newTouch2;
        }
    });
 
});
</script>
 
</body>
</html>
 