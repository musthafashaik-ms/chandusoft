<?php
session_start();
$_SESSION['role'] = 'admin'; // 🔥 Temporary: allow access for testing
require_once __DIR__ . '/../app/config.php';

// ==========================================================
// 🔍 Search & Filter
// ==========================================================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// ==========================================================
// 📄 Pagination setup
// ==========================================================
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ==========================================================
// 📋 Build base SQL
// ==========================================================
$sql = "FROM orders o WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (o.customer_email LIKE :search OR o.order_ref LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($statusFilter !== '') {
    $sql .= " AND o.payment_status = :status";
    $params[':status'] = $statusFilter;
}

// ==========================================================
// 📊 Count total records
// ==========================================================
$countStmt = $pdo->prepare("SELECT COUNT(*) " . $sql);
$countStmt->execute($params);
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $limit);

// ==========================================================
// 🧾 Fetch orders
// ==========================================================
$sqlOrders = "
    SELECT o.id, o.order_ref, o.customer_name, o.customer_email, 
           o.total, o.payment_gateway, o.payment_status, o.created_at
    $sql
    ORDER BY o.created_at DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sqlOrders);

foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================================
// 🧮 Status options
// ==========================================================
$statuses = ['pending', 'paid', 'failed', 'refunded', 'cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Orders</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
            margin: 40px;
        }
        h1 {
            color: #007BFF;
            margin-bottom: 20px;
        }
        table {
    border-collapse: collapse;
    width: 100%;
    background: #fff;
    border: 1px solid #ccc; /* outer border */
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

th, td {
    padding: 12px 15px;
    border: 1px solid #ccc; /* vertical + horizontal lines */
    text-align: left;
}

thead {
    background: #007BFF;
    color: #fff;
    border-bottom: 2px solid #0056b3;
}

        
        .filter-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        input[type="text"], select {
            padding: 8px;
            font-size: 14px;
        }
        button {
            padding: 8px 15px;
            cursor: pointer;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
        }
        button:hover {
            background: #0056b3;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 6px 10px;
            margin: 2px;
            background: #eee;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
        }
        .pagination a.active {
            background: #007BFF;
            color: #fff;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            color: #fff;
            font-size: 12px;
            text-transform: capitalize;
        }
        .badge.pending { background: #f0ad4e; }
        .badge.paid { background: #5cb85c; }
        .badge.failed { background: #d9534f; }
        .badge.refunded { background: #0275d8; }
        .badge.cancelled { background: #999; }
        .view-btn {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .view-btn:hover {
            background: #138496;
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: #fff;
            margin: 10% auto;
            padding: 20px;
            width: 400px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            position: relative;
        }
        .modal-content h2 {
            margin-top: 0;
            color: #333;
        }
        .modal-content p {
            margin: 8px 0;
            font-size: 14px;
        }
        .close {
            position: absolute;
            right: 10px;
            top: 10px;
            font-size: 18px;
            cursor: pointer;
        }
        .close:hover {
            color: #007BFF;
        }
        
    </style>
</head>
<body>

<h1>Orders</h1>

<form method="get" class="filter-bar">
    <input type="text" name="search" placeholder="Search by email or order ref"
           value="<?= htmlspecialchars($search) ?>">

    <select name="status">
        <option value="">All Statuses</option>
        <?php foreach ($statuses as $status): ?>
            <option value="<?= $status ?>" <?= ($status === $statusFilter) ? 'selected' : '' ?>>
                <?= ucfirst($status) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Filter</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Order Ref</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Total</th>
            <th>Gateway</th>
            <th>Status</th>
            <th>Created</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($orders): ?>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['order_ref']) ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><?= htmlspecialchars($order['customer_email']) ?></td>
                    <td>$<?= number_format($order['total'], 2) ?></td>
                    <td><?= ucfirst($order['payment_gateway']) ?></td>
                    <td><span class="badge <?= $order['payment_status'] ?>">
                        <?= ucfirst($order['payment_status']) ?>
                    </span></td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                    <td>
                        <button class="view-btn"
                            data-order='<?= json_encode($order, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                            View
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9">No orders found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="pagination">
    <?php if ($totalPages > 1): ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>"
               class="<?= ($i === $page) ? 'active' : '' ?>">
               <?= $i ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Order Details</h2>
        <div id="modalBody"></div>
    </div>
</div>

<script>
    const modal = document.getElementById('orderModal');
    const modalBody = document.getElementById('modalBody');
    const closeBtn = document.querySelector('.close');

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const order = JSON.parse(btn.getAttribute('data-order'));
            modalBody.innerHTML = `
                <p><strong>Order Ref:</strong> ${order.order_ref}</p>
                <p><strong>Customer:</strong> ${order.customer_name}</p>
                <p><strong>Email:</strong> ${order.customer_email}</p>
                <p><strong>Payment Gateway:</strong> ${order.payment_gateway}</p>
                <p><strong>Status:</strong> ${order.payment_status}</p>
                <p><strong>Total:</strong> $${parseFloat(order.total).toFixed(2)}</p>
                <p><strong>Created At:</strong> ${order.created_at}</p>
            `;
            modal.style.display = 'block';
        });
    });

    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };
</script>

</body>
</html>
