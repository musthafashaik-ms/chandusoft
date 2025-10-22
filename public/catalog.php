<?php
require_once __DIR__ . '/../app/config.php';

// ✅ Public Catalog List (only published items)
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

$where = "WHERE status = 'published'";
$params = [];

if ($search) {
    $where .= " AND title LIKE ?";
    $params[] = "%$search%";
}

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM catalog $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Fetch paginated items
$stmt = $pdo->prepare("SELECT * FROM catalog $where ORDER BY created_at DESC LIMIT $offset, $limit");
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Catalog</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 30px;
        }
        .search-bar {
            text-align: center;
            margin-bottom: 30px;
        }
        .search-bar input[type="text"] {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-bar button {
            padding: 8px 15px;
            background: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: auto;
        }
        .card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 6px;
        }
        .card h3 {
            margin: 10px 0 5px;
            color: #333;
        }
        .card p {
            margin: 0 0 10px;
            color: #555;
        }
        .card a {
            text-decoration: none;
            padding: 8px 12px;
            background: #007BFF;
            color: white;
            border-radius: 4px;
            text-align: center;
        }
        .pagination {
            text-align: center;
            margin-top: 30px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 6px 12px;
            text-decoration: none;
            background: #eee;
            color: #007BFF;
            border-radius: 4px;
        }
        .pagination a.active {
            background: #007BFF;
            color: white;
        }
    </style>
</head>
<body>

<h1>Our Catalog</h1>

<div class="search-bar">
    <form method="get">
        <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>
</div>

<div class="grid">
    <?php if ($items): ?>
        <?php foreach ($items as $item): ?>
            <div class="card">
                <?php if ($item['image']): ?>
                    <img src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                <?php endif; ?>
                <h3><?= htmlspecialchars($item['title']) ?></h3>
                <p>$<?= number_format($item['price'], 2) ?></p>
                <a href="catalog-item.php?slug=<?= urlencode($item['slug']) ?>">View Details</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center;">No items found.</p>
    <?php endif; ?>
</div>

<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

</body>
</html>
