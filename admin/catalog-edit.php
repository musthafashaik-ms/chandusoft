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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float) $_POST['price'];
    $short_desc = trim($_POST['short_desc']);
    $status = in_array($_POST['status'], ['published', 'archived']) ? $_POST['status'] : $item['status'];

    // ✅ Generate slug from title
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
    $slug = trim($slug, '-');

    // ✅ Ensure slug is unique (excluding current ID)
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

    // ✅ Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $uniqueName = 'catalog_' . time() . '.' . $ext;

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $target = $uploadDir . $uniqueName;
        $publicPath = 'uploads/' . $uniqueName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = $publicPath;

            // ✅ Delete old image if it exists
            if (!empty($item['image']) && file_exists(__DIR__ . '/../' . $item['image'])) {
                @unlink(__DIR__ . '/../' . $item['image']);
            }
        }
    }

    // ✅ Update record safely
    $stmt = $pdo->prepare("
        UPDATE catalog 
        SET title = ?, slug = ?, price = ?, image = ?, short_desc = ?, status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$title, $slug, $price, $imagePath, $short_desc, $status, $id]);

    header("Location: catalog.php");
    exit;
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
    <h1>Edit Catalog Item</h1>

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
            <option value="archived" <?php if ($item['status'] === 'archived') echo 'selected'; ?>>Archived</option>
        </select>

        <button type="submit">Update</button>
    </form>
</div>

</body>
</html>
