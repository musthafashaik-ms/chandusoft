<?php
require_once __DIR__ . '/../app/config.php';

$id = (int)$_GET['id'];

// Fetch item
$stmt = $pdo->prepare("SELECT * FROM catalog WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float) $_POST['price'];
    $short_desc = trim($_POST['short_desc']);

    // Accept draft too
    $validStatuses = ['published', 'archived', 'draft'];
    $status = in_array($_POST['status'], $validStatuses) ? $_POST['status'] : $item['status'];

    // Generate slug from title
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
    $slug = trim($slug, '-');

    // Ensure slug unique except current item
    $originalSlug = $slug;
    $counter = 1;
    $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM catalog WHERE slug = ? AND id != ?");
    while (true) {
        $checkSlug->execute([$slug, $id]);
        $count = $checkSlug->fetchColumn();
        if ($count == 0) break;
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    $imagePath = $item['image'];

    // Handle image upload with max 2MB
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] <= 2 * 1024 * 1024) { // 2MB
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $uniqueName = 'catalog_' . time() . '.' . $ext;

                // Generate the directory path based on current year/month
                $year = date('Y');
                $month = date('m');
                $uploadDir = __DIR__ . '/../uploads/' . $year . '/' . $month . '/';

                // Create the directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $target = $uploadDir . $uniqueName;
                $publicPath = 'uploads/' . $year . '/' . $month . '/' . $uniqueName;

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
                    $webpPath = 'uploads/' . $year . '/' . $month . '/' . $webpName;
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

                    // Delete old image if exists
                    if (!empty($item['image']) && file_exists(__DIR__ . '/../' . $item['image'])) {
                        @unlink(__DIR__ . '/../' . $item['image']);
                    }
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

    // Update DB only if no error message
    if (!$message) {
        $stmt = $pdo->prepare("
            UPDATE catalog 
            SET title = ?, slug = ?, price = ?, image = ?, short_desc = ?, status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$title, $slug, $price, $imagePath, $short_desc, $status, $id]);

        header("Location: catalog.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Catalog Item</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; padding:20px; }
        .navbar { background:#007BFF; padding:10px 20px; display:flex; justify-content:space-between; align-items:center; color:#fff; }
        .navbar a { color:#fff; text-decoration:none; margin-right:15px; }
        .navbar a:hover { text-decoration:underline; }

        .container { max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px; }
        h1 { text-align:center; color:#007BFF; }
        label { display:block; margin-top:10px; font-weight:bold; }
        input[type="text"], input[type="number"], textarea, select {
            width:100%; padding:10px; margin-top:5px; box-sizing:border-box;
        }
        input[type="file"] { margin-top:5px; }
        button { background:#007BFF; color:#fff; border:none; padding:10px 15px; margin-top:15px; cursor:pointer; }
        button:hover { background:#0056b3; }
        img.preview { max-width:200px; margin-top:10px; display:block; border-radius:4px; }
        .message { margin-bottom: 15px; font-weight: bold; color: red; }

        /* Back to Catalog Button */
        .back-btn {
            position: absolute;
            bottom: -180px;
            right: 380px;
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
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
    <h1>Edit Catalog Item</h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required>

        <label>Price:</label>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($item['price']); ?>" required>

        <label>Short Description:</label>
        <textarea name="short_desc" rows="4"><?php echo htmlspecialchars($item['short_desc']); ?></textarea>

        <label>Image:</label>
        <?php if ($item['image']): ?>
            <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="Current Image" class="preview">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">

        <label>Status:</label>
        <select name="status">
            <option value="published" <?php if ($item['status'] === 'published') echo 'selected'; ?>>Published</option>
            <option value="draft" <?php if ($item['status'] === 'draft') echo 'selected'; ?>>Draft</option>
            <option value="archived" <?php if ($item['status'] === 'archived') echo 'selected'; ?>>Archived</option>
        </select>

        <button type="submit">Update</button>
    </form>
</div>

<!-- Back to Catalog Button -->
<a href="catalog.php" class="back-btn">← Back to Catalog</a>

</body>
</html>
