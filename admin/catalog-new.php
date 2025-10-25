<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
 
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/functions.php';
 
$message = '';
 
/**
 * 🧩 Simple logging function for catalog
 * Writes to /storage/logs/catalog-YYYY-MM-DD.log
 */
function catalog_log($message) {
    $logDir = __DIR__ . '/../storage/logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . 'catalog-' . date('Y-m-d') . '.log';
    $entry = "[" . date('H:i:s') . "] " . $message . "\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float) $_POST['price'];
    $short_desc = trim($_POST['short_desc']);
 
    // Valid statuses
    $validStatuses = ['published', 'archived', 'draft'];
    $status = in_array($_POST['status'], $validStatuses) ? $_POST['status'] : 'published';
 
    // Generate slug
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
    $slug = trim($slug, '-');
 
    // Ensure unique slug
    $originalSlug = $slug;
    $counter = 1;
    $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM catalog WHERE slug = ?");
    while (true) {
        $checkSlug->execute([$slug]);
        if ($checkSlug->fetchColumn() == 0) break;
        $slug = $originalSlug . '-' . $counter++;
    }
 
    $imagePath = null;
 
    // ✅ IMAGE UPLOAD HANDLING
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] <= 2 * 1024 * 1024) { // 2MB limit
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
 
                // 📂 Create year/month folder
                $year = date('Y');
                $month = date('m');
                $uploadDir = __DIR__ . "/../uploads/$year/$month/";
 
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        $message = "❌ Failed to create upload directory.";
                        catalog_log($message . " Path: $uploadDir");
                    }
                }
 
                if (is_writable(dirname($uploadDir))) {
                    // 🧩 File paths
                    $uniqueBase = 'catalog_' . time() . '_' . bin2hex(random_bytes(3));
                    $targetOriginal = $uploadDir . $uniqueBase . '.' . $ext;
                    $targetWebp = $uploadDir . $uniqueBase . '.webp';
 
                    // Move file
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetOriginal)) {
                        $imgInfo = getimagesize($targetOriginal);
                        if (!$imgInfo) {
                            $message = "❌ Invalid image file.";
                            catalog_log($message);
                        } else {
                            $width = $imgInfo[0];
                            $height = $imgInfo[1];
 
                            switch ($imgInfo[2]) {
                                case IMAGETYPE_JPEG:
                                    $image = imagecreatefromjpeg($targetOriginal);
                                    break;
                                case IMAGETYPE_PNG:
                                    $image = imagecreatefrompng($targetOriginal);
                                    imagepalettetotruecolor($image);
                                    imagealphablending($image, true);
                                    imagesavealpha($image, true);
                                    break;
                                case IMAGETYPE_WEBP:
                                    $image = imagecreatefromwebp($targetOriginal);
                                    break;
                                default:
                                    $message = "❌ Unsupported image type.";
                                    catalog_log($message);
                                    break;
                            }
 
                            if (isset($image)) {
                                // Resize if wider than 1600px
                                if ($width > 1600) {
                                    $newHeight = ($height / $width) * 1600;
                                    $resized = imagescale($image, 1600, $newHeight);
                                    imagedestroy($image);
                                    $image = $resized;
                                }
 
                                // ✅ Save optimized WebP
                                imagewebp($image, $targetWebp, 85);
                                imagedestroy($image);
 
                                // Relative path (for DB)
                                $imagePath = "uploads/$year/$month/" . basename($targetWebp);
 
                                catalog_log("✅ Image uploaded: $imagePath");
                            }
                        }
                    } else {
                        $message = "❌ Failed to move uploaded file.";
                        catalog_log($message);
                    }
                } else {
                    $message = "❌ Upload directory not writable.";
                    catalog_log($message . " Path: $uploadDir");
                }
            } else {
                $message = "❌ Image size must be 2MB or less.";
                catalog_log($message);
            }
        } else {
            $message = "❌ Error uploading image. Code: " . $_FILES['image']['error'];
            catalog_log($message);
        }
    }
 
    // ✅ Insert record into DB
    if (!$message) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO catalog (title, slug, price, image, short_desc, status)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $slug, $price, $imagePath, $short_desc, $status]);
 
            catalog_log("✅ New catalog item added: $title ($slug)");
            header("Location: catalog.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "❌ A catalog item with this title already exists.";
            } else {
                $message = "❌ Database error: " . $e->getMessage();
            }
            catalog_log($message);
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Catalog Item</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background:#f4f4f4;
            padding:20px;
        }
        .navbar {
            background:#007BFF;
            padding:10px 20px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            color:#fff;
        }
        .navbar a {
            color:#fff;
            text-decoration:none;
            margin-right:15px;
        }
        .navbar a:hover { text-decoration:underline; }
        .container {
            max-width:600px;
            margin:auto;
            background:#fff;
            padding:20px;
            border-radius:8px;
            box-shadow:0 4px 8px rgba(0,0,0,0.1);
        }
        h1 { text-align:center; color:#007BFF; }
        label { display:block; margin-top:10px; font-weight:bold; }
        input[type="text"], input[type="number"], textarea, select {
            width:100%; padding:10px; margin-top:5px; box-sizing:border-box;
        }
        input[type="file"] { margin-top:5px; }
        button {
            background:#007BFF; color:#fff; border:none;
            padding:10px 15px; margin-top:15px; cursor:pointer;
        }
        button:hover { background:#0056b3; }
        .message { margin-bottom:15px; font-weight:bold; }
        .error { color:red; }
        .back-btn {
            position:absolute; bottom:40px; right:380px;
            background-color:#007BFF; color:white;
            padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold;
        }
        .back-btn:hover { background-color:#0056b3; }
    </style>
</head>
<body>
 
<!-- Navbar -->
<div class="navbar">
    <div>
        <a href="catalog.php">📦 Catalog</a>
        <a href="catalog-new.php">➕ Add New</a>
    </div>
    <div>
        <a href="/admin/logout.php">Logout</a>
    </div>
</div>
 
<div class="container">
    <h1>New Catalog Item</h1>
 
    <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
 
    <form method="post" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required>
 
        <label>Price:</label>
        <input type="number" step="0.01" name="price" required>
 
        <label>Short Description:</label>
        <textarea name="short_desc" rows="4"></textarea>
 
        <label>Image (Max 2MB):</label>
        <input type="file" name="image" accept="image/*">
 
        <label>Status:</label>
        <select name="status">
            <option value="published" selected>Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>
 
        <button type="submit">Save</button>
    </form>
 
    <a href="catalog.php" class="back-btn">← Back to Catalog</a>
</div>
 
</body>
</html>
 