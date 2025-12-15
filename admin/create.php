<?php
session_start(); // Ensure session starts at the top

require_once '../app/logger.php'; // Include logger functions

// ✅ Redirect if not logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['flash_error'] = "Please log in.";
    header("Location: login.php");
    exit();
}

// ✅ Retrieve user info safely
$user = $_SESSION['user'] ?? [];
$username = htmlspecialchars($user['username'] ?? 'User');
$role = htmlspecialchars(ucfirst($user['role'] ?? 'Editor'));

// Initialize variables
$title = '';
$slug = '';
$status = 'draft';
$content_html = '';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $content_html = $_POST['content_html'] ?? '';

    // Escape title and slug
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $slug = htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        }

        try {
            // Insert page into the database
            $conn = new mysqli('localhost', 'root', '', 'chandusoft');
            if ($conn->connect_error) {
                die("❌ DB connection failed: " . $conn->connect_error);
            }

            $stmt = $conn->prepare("INSERT INTO pages (title, slug, status, content_html, updated_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $title, $slug, $status, $content_html);
            $stmt->execute();

            // ✅ Log page creation
            log_page("🆕 Page created | Title: $title | Slug: $slug | Status: $status | By: $username");

            $success = "Page created successfully.";
            header("Location: pages.php");
            exit();
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
            log_error("Page creation failed: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Page</title>
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

        form .form-group input[type="text"],
        form .form-group select,
        form .form-group textarea {
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

        form input[type="text"]::placeholder {
            color: #999;
        }

        form button {
            background: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        form button:hover {
            background: #0056b3;
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
            margin-left: 10px;
            display: inline-block;
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

        <a href="/admin/pages.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['pages.php','create.php','edit.php']) ? 'active' : '' ?>">Pages</a>

        <a href="/admin/logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Create New Page</h2>

    <form method="POST">
        <div class="form-group">
            <label for="title">Page Title *</label>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required>
        </div>

        <div class="form-group">
            <label for="slug">Slug (optional)</label>
            <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($slug) ?>" placeholder="auto-generated-if-empty">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="published" <?= $status == 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="archived" <?= $status == 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <div class="form-group">
            <label for="content_html">Content (HTML allowed)</label>
            <textarea name="content_html" id="content_html" rows="10" placeholder="Enter the page content..."><?= htmlspecialchars($content_html) ?></textarea>
        </div>

        <button type="submit">Create Page</button>

        <div class="form-bottom">
            <a href="pages.php" class="back-button">← Back to Pages</a>
        </div>
    </form>
</div>

</body>
</html>

