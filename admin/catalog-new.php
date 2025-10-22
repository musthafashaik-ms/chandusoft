<?php
require_once __DIR__ . '/../app/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float) $_POST['price'];
    $short_desc = trim($_POST['short_desc']);

    // Add 'draft' as valid status
    $validStatuses = ['published', 'archived', 'draft'];
    $status = in_array($_POST['status'], $validStatuses) ? $_POST['status'] : 'published';

    // Generate base slug from title
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
    $slug = trim($slug, '-');

    // Ensure unique slug
    $originalSlug = $slug;
    $counter = 1;
    $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM catalog WHERE slug = ?");
    while (true) {
        $checkSlug->execute([$slug]);
        $count = $checkSlug->fetchColumn();
        if ($count == 0) break;
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    $imagePath = null;

    // Image Upload with max 2MB limit
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] <= 2 * 1024 * 1024) { // 2MB in bytes
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $uniqueName = 'catalog_' . time() . '.' . $ext;

                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $target = $uploadDir . $uniqueName;
                $publicPath = 'uploads/' . $uniqueName;

                // Move the uploaded file
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    // Check if image is too wide (larger than 1600px)
                    $image = imagecreatefromstring(file_get_contents($target));
                    $width = imagesx($image);
                    $height = imagesy($image);

                    if ($width > 1600) {
                        // Resize the image to max width 1600px
                        $newWidth = 1600;
                        $newHeight = ($height / $width) * $newWidth;

                        $resizedImage = imagescale($image, $newWidth, $newHeight);
                        imagejpeg($resizedImage, $target, 90); // Save resized image
                        imagedestroy($resizedImage);
                    }

                    // Generate WebP version (but don't save it to database)
                    $webpName = pathinfo($uniqueName, PATHINFO_FILENAME) . '.webp';
                    $webpPath = 'uploads/' . $webpName;
                    $webpTarget = $uploadDir . $webpName;

                    // Resize WebP image to the same size as the resized image (if resized)
                    if ($width > 1600) {
                        // If the image was resized, resize the WebP version too
                        $resizedWebP = imagecreatefromstring(file_get_contents($target));
                        imagewebp($resizedWebP, $webpTarget, 80); // Save resized WebP
                        imagedestroy($resizedWebP);
                    } else {
                        // If the original image was not resized, just create the WebP version at original size
                        imagewebp($image, $webpTarget, 80); // Save WebP image
                    }

                    $imagePath = $publicPath;

                    imagedestroy($image);
                } else {
                    $message = "❌ Failed to move uploaded file.";
                }
            } else {
                $message = "❌ Image size must be 2MB or less.";
            }
        } else {
            $message = "❌ Error uploading image. Code: " . $_FILES['image']['error'];
        }
    }

    if (!$message) {
        try {
            // Insert the catalog item into the database but don't update the `image_webp` column
            $stmt = $pdo->prepare("
                INSERT INTO catalog (title, slug, price, image, short_desc, status)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $slug, $price, $imagePath, $short_desc, $status]);

            // Redirect to catalog after successful upload
            header("Location: catalog.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "❌ A catalog item with this title already exists.";
            } else {
                $message = "❌ Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Catalog Item</title>
    <style> body { font-family: Arial, sans-serif; background:#f4f4f4; padding:20px; } .navbar { background:#007BFF; padding:10px 20px; display:flex; justify-content:space-between; align-items:center; color:#fff; } .navbar a { color:#fff; text-decoration:none; margin-right:15px; } .navbar a:hover { text-decoration:underline; } .container { max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px; } h1 { text-align:center; color:#007BFF; } label { display:block; margin-top:10px; font-weight:bold; } input[type="text"], input[type="number"], textarea, select { width:100%; padding:10px; margin-top:5px; box-sizing:border-box; } input[type="file"] { margin-top:5px; } button { background:#007BFF; color:#fff; border:none; padding:10px 15px; margin-top:15px; cursor:pointer; } button:hover { background:#0056b3; } .message { margin-bottom: 15px; font-weight: bold; } .success { color: green; } .error { color: red; } .back-btn { position: absolute; bottom: 40px; /* Move up a little */ right: 380px; /* Move left a little */ background-color: #007BFF; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; text-align: center; } .back-btn:hover { background-color: #0056b3; } </style>
</head>
<body>

<!-- Simple Navbar -->
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

 <!-- Back to Catalog Button -->
<a href="catalog.php" class="back-btn">← Back to Catalog</a>
</div>

</body>
</html>
