<?php
require_once '../app/config.php'; // Loads $pdo and session

// ✅ Redirect if not logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['flash_error'] = "Please log in.";
    header("Location: login.php");
    exit();
}

$role = $_SESSION['user']['role'] ?? 'Editor';
$username = $_SESSION['user']['username'] ?? 'User';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        if (empty($slug)) {
            // Auto-generate slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, status, updated_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$title, $slug, $status]);

            header("Location: pages.php"); // Redirect after successful insert
            exit();
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage(); // Show error during development only
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
        form select {
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
        Welcome <?= $role ?>!
        <a href="../app/dashboard.php">Dashboard</a>
        <a href="../admin/admin-leads.php">Leads</a>
        <a href="../admin/pages.php">Pages</a>
        <a href="../admin/logout.php">Logout</a> <!-- Assuming you have this script -->
    </div>
</div>

<!-- ✅ Page Form -->
<div class="container">
    <h2>Create New Page</h2>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="title">Page Title *</label>
        <input type="text" name="title" id="title" required>

        <label for="slug">Slug (optional)</label>
        <input type="text" name="slug" id="slug" placeholder="auto-generated-if-empty">

        <label for="status">Status</label>
        <select name="status" id="status">
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>

        <button type="submit">Create Page</button>
    </form>
</div>

</body>
</html>
