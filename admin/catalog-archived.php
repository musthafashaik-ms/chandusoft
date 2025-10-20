<?php
require_once __DIR__ . '/../app/config.php';

$stmt = $pdo->query("SELECT * FROM catalog WHERE status = 'archived' ORDER BY updated_at DESC");
$archivedItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Catalog Items</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; padding:20px; }
        .navbar { background:#007BFF; padding:10px 20px; display:flex; justify-content:space-between; align-items:center; color:#fff; }
        .navbar a { color:#fff; text-decoration:none; margin-right:15px; }
        .navbar a:hover { text-decoration:underline; }
        .container { max-width: 900px; margin:auto; background:#fff; padding:20px; border-radius:8px; }
        h1 { text-align:center; color:#007BFF; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:10px; border-bottom:1px solid #ddd; text-align:left; }
        th { background:#007BFF; color:#fff; }
        img.thumb { max-width:80px; border-radius:4px; }
        .actions a { margin-right:10px; color:#007BFF; text-decoration:none; }
        .actions a:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="navbar">
    <div>
        <a href="catalog.php">📦 Published</a>
        <a href="catalog-archived.php">🗃 Archived</a>
    </div>
    <div><a href="/admin/logout.php">Logout</a></div>
</div>

<div class="container">
    <h1>Archived Catalog Items</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Title</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php if ($archivedItems): ?>
            <?php foreach ($archivedItems as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td>
                        <?php if ($item['image']): ?>
                            <img src="../<?= htmlspecialchars($item['image']) ?>" class="thumb">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['title']) ?></td>
                    <td><?= htmlspecialchars($item['status']) ?></td>
                    <td class="actions">
                        <a href="catalog-edit.php?id=<?= $item['id'] ?>">Edit</a>
                        <a href="catalog-restore.php?id=<?= $item['id'] ?>" onclick="return confirm('Restore this item?')">Restore</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No archived items.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
