<?php
require_once '../app/config.php'; // Database connection ($pdo)
require_once '../app/logger.php'; // Logger functions

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

$error = '';
$success = '';

// Initialize the message variable
$message = ''; // Initialize the message variable to avoid "undefined variable" warning

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    $_SESSION['flash_error'] = "Invalid page ID.";
    header("Location: pages.php");
    exit();
}

// ✅ Fetch existing page data
try {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();

    if (!$page) {
        $_SESSION['flash_error'] = "Page not found.";
        header("Location: pages.php");
        exit();
    }

    // Initialize form fields
    $title = $page['title'] ?? '';
    $slug = $page['slug'] ?? '';
    $status = $page['status'] ?? 'draft';
    $content_html = $page['content_html'] ?? '';

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $content_html = $_POST['content_html'] ?? ''; // Raw HTML allowed

    if (empty($title)) {
        $message = "Title is required.";
    } else {
        // Auto-generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        }

        try {
            $stmt = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content_html = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $slug, $content_html, $status, $id]);

            // ✅ Log the page update
            log_page("✏️Edit Page | ID: $id | Title: $title | Slug: $slug | Status: $status | By: $username");

            $_SESSION['flash_success'] = "Page updated successfully.";
            header("Location: pages.php");
            exit();
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Page</title>
    <style>
        /* Your existing styles remain unchanged */
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

        .navbar .links a:hover {
            text-decoration: none;
        }

        /* Highlight Active Link */
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
            font-weight: bold;
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

        <a href="/admin/pages.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['pages.php','create.php','edit.php']) ? 'active' : '' ?>">Pages</a>

        <a href="/admin/logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <h2>Edit Page</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="title">Page Title *</label>
            <input type="text" name="title" id="title" required value="<?= htmlspecialchars($page['title']) ?>">
        </div>

        <div class="form-group">
            <label for="slug">Slug (optional)</label>
            <input type="text" name="slug" id="slug" placeholder="auto-generated-if-empty" value="<?= htmlspecialchars($page['slug']) ?>">
        </div>

        <div class="form-group">
            <label for="content_html">Content (HTML allowed)</label>
            <textarea name="content_html" id="content_html" rows="10"><?= htmlspecialchars($content_html) ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="published" <?= $page['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= $page['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="archived" <?= $page['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <button type="submit">Update Page</button>

        <!-- Back to Pages Button -->
        <div class="form-bottom">
            <a href="pages.php" class="back-button">← Back to Pages</a>
        </div>
    </form>
</div>

</body>
</html>
