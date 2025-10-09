<?php
require_once '../app/config.php'; // Loads $pdo and session

// ✅ Redirect if not logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['flash_error'] = "Please log in.";
    header("Location: login.php");
    exit();
}

$role = $_SESSION['user']['role'] ?? 'editor';
$username = $_SESSION['user']['username'] ?? 'User';

$error = '';
$success = '';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
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

    // ✅ Initialize content_html to existing DB value
    $content_html = $page['content_html'] ?? '';

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $content_html = $_POST['content_html'] ?? ''; // Raw HTML allowed (not escaped)

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        }

        try {
            $stmt = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content_html = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $slug, $content_html, $status, $id]);

            header("Location: pages.php");
            exit();
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Page</title>
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
        .navbar .links a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-top: 0;
        }
        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        form input[type="text"],
        form select,
        form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form button {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        form button:hover {
            background-color: #2980b9;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>

<!-- ✅ Navbar -->
<div class="navbar">
    <div><strong>Chandusoft Admin</strong></div>
    <div class="links">
        Welcome <?= htmlspecialchars($role) ?>!
        <a href="../app/dashboard.php">Dashboard</a>
        <a href="../admin/admin-leads.php">Leads</a>
        <a href="../admin/pages.php">Pages</a>
        <a href="../admin/logout.php">Logout</a>
    </div>
</div>

<!-- ✅ Edit Form -->
<div class="container">
    <h2>Edit Page</h2>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="title">Page Title *</label>
        <input type="text" name="title" id="title" required value="<?= htmlspecialchars($page['title']) ?>">

        <label for="slug">Slug (optional)</label>
        <input type="text" name="slug" id="slug" placeholder="auto-generated-if-empty" value="<?= htmlspecialchars($page['slug']) ?>">

        <label for="content_html">Content (HTML allowed)</label>
        <textarea name="content_html" id="content_html" rows="10"><?= htmlspecialchars($content_html) ?></textarea>

        <label for="status">Status</label>
        <select name="status" id="status">
            <option value="published" <?= $page['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $page['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="archived" <?= $page['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
        </select>

        <button type="submit">Update Page</button>
    </form>
</div>

</body>
</html>

