<?php
require_once __DIR__ . '/../app/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float) $_POST['price'];
    $short_desc = trim($_POST['short_desc']);
    $status = in_array($_POST['status'], ['published', 'archived']) ? $_POST['status'] : 'published';

    // ✅ Generate base slug from title
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
    $slug = trim($slug, '-');

    // ✅ Ensure unique slug
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

    // ✅ Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $uniqueName = 'catalog_' . time() . '.' . $ext;

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $target = $uploadDir . $uniqueName;
        $publicPath = 'uploads/' . $uniqueName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = $publicPath;
        }
    }

    try {
        // ✅ Insert into DB
        $stmt = $pdo->prepare("
            INSERT INTO catalog (title, slug, price, image, short_desc, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $slug, $price, $imagePath, $short_desc, $status]);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Catalog Item</title>
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
        .message { margin-bottom: 15px; font-weight: bold; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

<!-- ✅ Simple Navbar -->
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

        <label>Image:</label>
        <input type="file" name="image" accept="image/*">

        <label>Status:</label>
        <select name="status">
            <option value="published" selected>Published</option>
            <option value="archived">Archived</option>
        </select>

        <button type="submit">Save</button>
    </form>
</div>

</body>
</html>
