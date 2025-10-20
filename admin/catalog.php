<?php
require_once __DIR__ . '/../app/config.php';

// ✅ Pagination & Filters
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['published', 'archived']) 
    ? $_GET['status'] 
    : 'published';

// ✅ Build WHERE clause
$where = "WHERE status = ?";
$params = [$statusFilter];

if ($search) {
    $where .= " AND title LIKE ?";
    $params[] = "%$search%";
}

// ✅ Count total records
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM catalog $where");
$totalStmt->execute($params);
$total = $totalStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// ✅ Fetch paginated data
$offset = ($page - 1) * $perPage;
$stmt = $pdo->prepare("SELECT * FROM catalog $where ORDER BY created_at DESC LIMIT $offset, $perPage");
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Catalog - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:20px; }
        .navbar { background:#007BFF; padding:10px 20px; display:flex; justify-content:space-between; align-items:center; color:#fff; }
        .navbar a { color:#fff; text-decoration:none; margin-right:15px; }
        .navbar a:hover { text-decoration:underline; }

        .container { max-width: 1000px; margin: 20px auto; background:#fff; padding:20px; border-radius:8px; }
        h1 { text-align:center; color:#007BFF; margin-bottom:20px; }

        form.search { display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
        form.search input[type=text], form.search select {
            padding:8px; 
            border:1px solid #ccc;
            border-radius:4px;
        }
        form.search button { padding:8px 15px; background:#007BFF; color:#fff; border:none; cursor:pointer; border-radius:4px; }
        form.search button:hover { background:#0056b3; }
        .new-btn { display:inline-block; background:#007BFF; color:#fff; padding:8px 15px; text-decoration:none; border-radius:5px; }

        table { width:100%; border-collapse: collapse; margin-top:15px; }
        th, td { padding:10px; border-bottom:1px solid #ddd; text-align:left; }
        th { background:#007BFF; color:#fff; }
        img.thumb { max-width:80px; border-radius:4px; }

        .actions a { margin-right:10px; color:#007BFF; text-decoration:none; }
        .actions a:hover { text-decoration:underline; }

        .pagination { text-align:center; margin-top:20px; }
        .pagination a { padding:5px 10px; margin:0 3px; background:#eee; text-decoration:none; border-radius:3px; }
        .pagination a.active { background:#007BFF; color:#fff; }
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
    <h1>Catalog Management</h1>

    <!-- ✅ Search & Filter Form -->
    <form class="search" method="get">
        <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="status">
            <option value="published" <?php if ($statusFilter === 'published') echo 'selected'; ?>>Published</option>
            <option value="archived" <?php if ($statusFilter === 'archived') echo 'selected'; ?>>Archived</option>
        </select>
        <button type="submit">Filter</button>
        <a href="catalog-new.php" class="new-btn">+ Add New</a>
    </form>

    <!-- ✅ Catalog Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Title</th>
            <th>Price</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php if ($items): ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td>
                        <?php if ($item['image']): ?>
                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" class="thumb" alt="Image">
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($item['status']); ?></td>
                    <td class="actions">
                        <a href="catalog-edit.php?id=<?php echo $item['id']; ?>">Edit</a>
                        <?php if ($item['status'] === 'published'): ?>
<a href="catalog-delete.php?id=<?php echo $item['id']; ?>" 
   onclick="return confirm('Archive this item?')">Archive</a>
                        <?php else: ?>
                            <a href="catalog-restore.php?id=<?php echo $item['id']; ?>" onclick="return confirm('Restore this item?')">Restore</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No items found.</td></tr>
        <?php endif; ?>
    </table>

    <!-- ✅ Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&page=<?php echo $i; ?>" 
               class="<?php if ($i == $page) echo 'active'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>

</body>
</html>
