<?php
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
$message = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float)$_POST['price'];
    $short_desc = trim($_POST['short_desc']);
    $status = in_array($_POST['status'], ['published', 'draft', 'archived']) ? $_POST['status'] : 'draft';
 
    // ✅ Slug generation
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    $slug = trim($slug, '-');
    $originalSlug = $slug;
    $counter = 1;
    $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM catalog WHERE slug = ?");
    while (true) {
        $checkSlug->execute([$slug]);
        if ($checkSlug->fetchColumn() == 0) break;
        $slug = $originalSlug . '-' . $counter++;
    }
 
    // ✅ Image upload
    $imagePath = '';
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
                    log_catalog("📸 Image uploaded for '$title' — file: $publicPath");
                } else {
                    $message = "❌ Failed to upload image.";
                    log_catalog("⚠️ Image upload failed for '$title'", 'ERROR');
                }
            } else {
                $message = "❌ Image must be under 2MB.";
                log_catalog("⚠️ Image too large for '$title' (" . $_FILES['image']['size'] . " bytes)", 'ERROR');
            }
        } else {
            $message = "❌ Upload error code: " . $_FILES['image']['error'];
            log_catalog("⚠️ Upload error ({$_FILES['image']['error']}) for '$title'", 'ERROR');
        }
    }
 
    // ✅ Database insert
    if (!$message) {
        $stmt = $pdo->prepare("
            INSERT INTO catalog (title, slug, price, image, short_desc, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$title, $slug, $price, $imagePath, $short_desc, $status]);
 
        $id = $pdo->lastInsertId();
        log_catalog("🆕 Created catalog item #$id — title: '$title', price: $price, status: '$status'", 'CREATE');
 
        header("Location: catalog.php");
        exit;
    } else {
        log_catalog("Catalog item added: '{$title}' by {$username}");

    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Catalog - Admin</title>
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

        h1 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 20px;
        }

        /* Form layout */
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

    <h1>Add New Catalog Item</h1>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" required>
        </div>

        <div class="form-group">
            <label>Price</label>
            <input type="number" step="0.01" name="price" required>
        </div>

        <div class="form-group">
            <label>Short Description</label>
            <textarea name="short_desc" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
            </select>
        </div>

        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" accept="image/*">
        </div>

        <button type="submit">Save Catalog Item</button>

        <!-- Bottom-right back button -->
        <div class="form-bottom">
            <a href="catalog.php" class="back-button">← Back to Catalog</a>
        </div>

    </form>
</div>

</body>
</html>
