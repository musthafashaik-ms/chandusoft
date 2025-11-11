<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// ✅ Handle Archive / Unarchive toggle in the same file
if (isset($_GET['toggle']) && isset($_GET['action'])) {
    $id = intval($_GET['toggle']);
    $action = $_GET['action'];

    if ($id > 0) {
        if ($action === 'unarchive') {
            $conn->query("UPDATE pages SET status='published', updated_at=NOW() WHERE id=$id");
        } else {
            $conn->query("UPDATE pages SET status='archived', updated_at=NOW() WHERE id=$id");
        }
    }
    // Redirect to remove GET params and refresh the table
    header("Location: pages.php");
    exit;
}

// ✅ Get page counts based on status
$publishedCount = $conn->query("SELECT COUNT(*) as count FROM pages WHERE status = 'published'")->fetch_assoc()['count'];
$draftCount = $conn->query("SELECT COUNT(*) as count FROM pages WHERE status = 'draft'")->fetch_assoc()['count'];
$archivedCount = $conn->query("SELECT COUNT(*) as count FROM pages WHERE status = 'archived'")->fetch_assoc()['count'];
$allCount = $conn->query("SELECT COUNT(*) as count FROM pages")->fetch_assoc()['count'];

// ✅ Search & filter logic
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$whereClause = '';

if ($status) {
    $whereClause = "WHERE status = '" . $conn->real_escape_string($status) . "'";
}

if ($search !== '') {
    if (preg_match('/^[%_]+$/', $search)) {
        $pages = $conn->query("SELECT * FROM pages WHERE 1=0");
    } else {
        if ($whereClause) {
            $whereClause .= " AND";
        } else {
            $whereClause = "WHERE";
        }
        $stmt = $conn->prepare("SELECT * FROM pages $whereClause (title LIKE ? OR slug LIKE ?) ORDER BY updated_at DESC");
        $likeSearch = "%$search%";
        $stmt->bind_param("ss", $likeSearch, $likeSearch);
        $stmt->execute();
        $pages = $stmt->get_result();
        $stmt->close();
    }
} else {
    $pages = $conn->query("SELECT * FROM pages $whereClause ORDER BY updated_at DESC");
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

        .container {
            max-width: 1100px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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

        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin-right: 6px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 4px;
            color: white;
        }

        .btn-edit { background-color: #2ecc71; }
        .btn-archive { background-color: #f39c12; }
        .btn-unarchive { background-color: #2793aeff; }
        .btn-delete { background-color: #e74c3c; }

        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>

<!-- ✅ Navbar -->
<div class="navbar">
    <div><strong>Chandusoft <?= $role ?></strong></div>
    <div class="links">
        Welcome <?= $username ?>!
        <a href="/app/dashboard.php">Dashboard</a>
        <?php if ($role === 'Admin'): ?>
            <a href="/admin/catalog.php">Admin Catalog</a>
            <a href="/public/catalog.php">Public Catalog</a>
            <a href="/admin/orders.php">Orders</a>
        <?php elseif ($role === 'Editor'): ?>
            <a href="/public/catalog.php">Public Catalog</a>
        <?php endif; ?>
        <a href="/admin/admin-leads.php">Leads</a>
        <a href="/admin/pages.php">Pages</a>
        <a href="/admin/logout.php">Logout</a>
    </div>
</div>

<!-- ✅ Main container -->
<div class="container">
    <h1>Pages</h1>

    <div class="top-bar" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="filters" style="gap: 15px; display: flex;">
            <a href="pages.php">All (<?= $allCount ?>)</a>
            <a href="pages.php?status=published">Published (<?= $publishedCount ?>)</a>
            <a href="pages.php?status=draft">Draft (<?= $draftCount ?>)</a>
            <a href="pages.php?status=archived">Archived (<?= $archivedCount ?>)</a>
        </div>

        <form method="get" class="search-form" style="display:flex; gap: 5px;">
            <input type="text" name="search" placeholder="Search title or slug" value="<?= htmlspecialchars($search) ?>">
            <input type="submit" value="Search">
        </form>

        <div>
            <a class="btn btn-edit" href="create.php">+ Create New Page</a>
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

        <?php if ($pages && $pages->num_rows > 0): ?>
            <?php while ($page = $pages->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($page['title']) ?></td>
                    <td><?= htmlspecialchars($page['slug']) ?></td>
                    <td><?= ucfirst($page['status']) ?></td>
                    <td><?= htmlspecialchars($page['updated_at']) ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?= $page['id'] ?>" class="btn btn-edit">Edit</a>

                        <?php if (strtolower($role) === 'admin'): ?>
                            <?php if ($page['status'] === 'archived'): ?>
                                <a href="pages.php?toggle=<?= $page['id'] ?>&action=unarchive" class="btn btn-unarchive">Unarchive</a>
                            <?php else: ?>
                                <a href="pages.php?toggle=<?= $page['id'] ?>&action=archive" class="btn btn-archive">Archive</a>
                            <?php endif; ?>

                            <a href="delete.php?id=<?= $page['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this page?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align:center; padding:15px; color:#d00; font-weight:bold;">
                    🚫 No items found.
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
