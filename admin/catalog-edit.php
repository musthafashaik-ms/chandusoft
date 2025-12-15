<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
 
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';

// ✅ Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Retrieve user info safely
$user = $_SESSION['user'] ?? [];
$username = htmlspecialchars($user['username'] ?? 'User');
$role = htmlspecialchars(ucfirst($user['role'] ?? 'Editor'));
 
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM catalog WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();
 
if (!$item) {
   log_catalog("Catalog item updated: ID {$id} by {$username}");
   echo "<h2 style='color:red;text-align:center;'>Item not found</h2>";
    exit;
}
 
$message = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float)$_POST['price'];
    $short_desc = trim($_POST['short_desc']);
    $status = in_array($_POST['status'], ['published', 'draft', 'archived']) ? $_POST['status'] : 'draft';
 
    $imagePath = $item['image'];
 
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] <= 2 * 1024 * 1024) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $uniqueName = 'catalog_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
 
                $target = $uploadDir . $uniqueName;
                $publicPath = 'uploads/' . $uniqueName;
 
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $imagePath = $publicPath;
                    log_catalog("🖼️ Image updated for item #$id — file: $publicPath");
                } else {
                    $message = "❌ Failed to upload image.";
                    log_catalog("⚠️ Image upload failed for edit of item #$id");
                }
            } else {
                $message = "❌ Image must be under 2MB.";
                log_catalog("⚠️ Image too large for edit of item #$id");
            }
        } else {
            $message = "❌ Upload error code: " . $_FILES['image']['error'];
            log_catalog("⚠️ Upload error ({$_FILES['image']['error']}) editing item #$id");
        }
    }
 
    if (!$message) {
        $stmt = $pdo->prepare("
            UPDATE catalog
            SET title=?, price=?, image=?, short_desc=?, status=?, updated_at=NOW()
            WHERE id=?
        ");
        $stmt->execute([$title, $price, $imagePath, $short_desc, $status, $id]);
 
        log_catalog("✏️ Edited catalog item #$id — title: '$title', price: $price, status: '$status'");
        header("Location: catalog.php");
        exit;
    } else {
        log_catalog("❌ Edit failed for item #$id — reason: $message");
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Catalog Item</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
        }

        .navbar .links a.active {
            background-color: #007BFF;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 20px;
        }

        form .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        form .form-group label {
            width: 180px;
            font-weight: bold;
        }

        form .form-group input,
        form .form-group textarea,
        form .form-group select {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            max-width: 100%;
            box-sizing: border-box;
        }

        form textarea {
            resize: vertical;
        }

        form input[type="file"] {
            padding: 5px;
        }

        button {
            background: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .preview img {
            max-width: 150px;
            border-radius: 4px;
            margin-top: 5px;
        }

        .message {
            text-align: center;
            color: red;
            margin-bottom: 15px;
        }

        /* Bottom-right button wrapper */
        .form-bottom {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        /* Blue Back Button (matching Save button) */
        .back-button {
            background: #007BFF;
            color: #fff;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .back-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div><strong>Chandusoft <?= $role ?></strong></div>
    <div class="links">
        Welcome <?= $role ?>!
        <a href="/app/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <?php if ($role === 'Admin'): ?>
            <a href="/admin/catalog.php" class="<?= strpos($_SERVER['PHP_SELF'], 'catalog') !== false ? 'active' : '' ?>">Admin Catalog</a>
            <a href="/public/catalog.php">Public Catalog</a>
            <a href="/admin/orders.php">Orders</a>
        <?php elseif ($role === 'Editor'): ?>
            <a href="/public/catalog.php">Public Catalog</a>
        <?php endif; ?>
        <a href="/admin/admin-leads.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin-leads.php' ? 'active' : '' ?>">Leads</a>
        <a href="/admin/pages.php" class="<?= basename($_SERVER['PHP_SELF']) === 'pages.php' ? 'active' : '' ?>">Pages</a>
        <a href="/admin/logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Edit Catalog Item</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Price</label>
            <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($item['price']) ?>" required>
        </div>

        <div class="form-group">
            <label>Short Description</label>
            <textarea name="short_desc" rows="4"><?= htmlspecialchars($item['short_desc']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="published" <?= $item['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= $item['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="archived" <?= $item['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <div class="form-group">
            <label>Current Image</label>
            <div class="preview">
                <?php if ($item['image']): ?>
                    <img src="../<?= htmlspecialchars($item['image']) ?>" alt="Current Image">
                <?php else: ?>
                    <p>No image uploaded.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Upload New Image</label>
            <input type="file" name="image" accept="image/*">
        </div>

        <button type="submit">Update Item</button>

        <div class="form-bottom">
            <a href="/admin/catalog.php" class="back-button">← Back to Catalog</a>
        </div>

    </form>
</div>

</body>
</html>
