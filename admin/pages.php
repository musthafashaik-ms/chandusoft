<?php
session_start();

// ✅ Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$username = htmlspecialchars($user['username'] ?? 'User');
$role = htmlspecialchars(ucfirst($user['role'] ?? 'Editor'));

// ✅ DB connection
$conn = new mysqli('localhost', 'root', '', 'chandusoft');
if ($conn->connect_error) {
    die("❌ DB connection failed: " . $conn->connect_error);
}

// ✅ Get page counts based on status
$publishedCount = $conn->query("SELECT COUNT(*) as count FROM pages WHERE status = 'published'")->fetch_assoc()['count'];
$draftCount = $conn->query("SELECT COUNT(*) as count FROM pages WHERE status = 'draft'")->fetch_assoc()['count'];
$archivedCount = $conn->query("SELECT COUNT(*) as count FROM pages WHERE status = 'archived'")->fetch_assoc()['count'];
$allCount = $conn->query("SELECT COUNT(*) as count FROM pages")->fetch_assoc()['count'];

// ✅ Search logic
$search = $_GET['search'] ?? '';
$search = trim($search);

if ($search !== '') {
    // Search by title or slug
    $stmt = $conn->prepare("SELECT * FROM pages WHERE title LIKE ? OR slug LIKE ? ORDER BY updated_at DESC");
    $likeSearch = "%$search%";
    $stmt->bind_param("ss", $likeSearch, $likeSearch);
    $stmt->execute();
    $pages = $stmt->get_result();
    $stmt->close();
} else {
    // No search, show all
    $pages = $conn->query("SELECT * FROM pages ORDER BY updated_at DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pages - Admin</title>
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
            max-width: 1100px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .top-bar .filters a {
            margin-right: 15px;
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
        }

        .top-bar .filters a:hover {
            text-decoration: underline;
        }

        .top-bar input[type="text"] {
            padding: 8px;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .top-bar .create-btn {
            background-color: #27ae60;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .search-form input[type="text"] {
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
        }

        .search-form input[type="submit"] {
            padding: 8px 12px;
            font-size: 14px;
            border: none;
            background-color: #3498db;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-form input[type="submit"]:hover {
            background-color: #2980b9;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background-color: #eef7ff;
        }

        .actions a {
            margin-right: 10px;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        /* New style for Edit button */
        .actions a.edit-btn {
            background-color: #27ae60; /* green */
            color: white;
        }

        .actions a.edit-btn:hover {
            background-color: #219150; /* darker green on hover */
        }
.actions .btn {
    display: inline-block;
    padding: 6px 12px;
    margin-right: 6px;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    border-radius: 4px;
    color: white;
}

.btn-edit {
    background-color: #2ecc71; /* Green */
}

.btn-archive {
    background-color: #f39c12; /* Orange */
}

.btn-delete {
    background-color: #e74c3c; /* Red */
}

.btn:hover {
    opacity: 0.9;
}

        /* Optional: style Archive and Delete links differently if needed */
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

<!-- ✅ Main container -->
<div class="container">
    <div class="top-bar" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="filters" style="gap: 15px; display: flex;">
            <a href="pages.php">All (<?= $allCount ?>)</a>
            <a href="pages.php?status=published">Published (<?= $publishedCount ?>)</a>
            <a href="pages.php?status=draft">Draft (<?= $draftCount ?>)</a>
            <a href="pages.php?status=archived">Archived (<?= $archivedCount ?>)</a>
        </div>

        <!-- 🔍 Search -->
        <form method="get" class="search-form" style="display:flex; gap: 5px;">
            <input type="text" name="search" placeholder="Search title or slug" value="<?= htmlspecialchars($search) ?>">
            <input type="submit" value="Search">
        </form>

        <div>
            <a class="create-btn" href="create.php" style="padding: 8px 15px; background-color:#27ae60; color:#fff; text-decoration:none; border-radius:4px; font-weight:bold;">
                + Create New Page
            </a>
        </div>
    </div>

    <table>
        <tr>
            <th>Title</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Updated</th>
            <th>Actions</th>
        </tr>

       <?php while ($page = $pages->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($page['title']) ?></td>
        <td><?= htmlspecialchars($page['slug']) ?></td>
        <td><?= ucfirst($page['status']) ?></td>
        <td><?= htmlspecialchars($page['updated_at']) ?></td>
        <td class="actions">
            <!-- Edit button -->
            <a href="edit.php?id=<?= $page['id'] ?>" class="btn btn-edit">Edit</a>

            <!-- Admin-only buttons -->
            <?php if (strtolower($role) === 'admin'): ?>
                <a href="archive.php?id=<?= $page['id'] ?>" class="btn btn-archive">Archive</a>
                <a href="delete.php?id=<?= $page['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this page?')">Delete</a>
            <?php endif; ?>
        </td>
    </tr>
<?php endwhile; ?>

    </table>
</div>

</body>
</html>
