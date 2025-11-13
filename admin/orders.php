
<?php
session_start();
$user = $_SESSION['user'];
$username = htmlspecialchars($user['username'] ?? 'User');
$role = htmlspecialchars(ucfirst($user['role'] ?? 'Editor'));
require_once __DIR__ . '/../app/config.php';


$statuses = ['pending', 'paid', 'failed', 'refunded', 'cancelled'];
$totalPages = 1;
$page = 1;

// ==========================================================
// Search & Filter
// ==========================================================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

if ($search === '%' || $search === '_' || $search === '') {
    if ($search === '%' || $search === '_') {
        $orders = [];
        $totalOrders = 0;
    } else {
        $search = '';
        goto normal_query;
    }
} else {
    goto normal_query;
}

goto render_page;

// ==========================================================
// Normal Query Section
// ==========================================================
normal_query:

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

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

// Count total records
$countStmt = $pdo->prepare("SELECT COUNT(*) " . $sql);
$countStmt->execute($params);
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $limit);

// Fetch orders
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

// Fetch order items for each order
foreach ($orders as &$order) {
    $stmtItems = $pdo->prepare("SELECT product_name, quantity, price FROM order_items WHERE order_id = :order_id");
    $stmtItems->execute([':order_id' => $order['id']]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    $order['items'] = array_map(function($item){
        return [
            'name' => $item['product_name'],
            'qty'  => (int)$item['quantity'],
            'price'=> (float)$item['price']
        ];
    }, $items);
}
unset($order);

render_page:
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Orders</title>
    <style>
        
         /* General Body Styling */
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #333;
        }

        /* Navbar Styling */
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
            background-color: #007BFF; /* Blue background when active */
            color: white;  /* Ensure text color is white */
            padding: 8px 12px;
            border-radius: 4px;
        }

        /* Page Heading */
        h1 {
            color: #007BFF;
            margin-bottom: 20px;
            font-size: 28px;
            text-align: left;
        }

        /* Content Section */
        .content {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
            background: white;
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        /* Search Form Styling */
.search-form {
    margin-bottom: 20px;
    display: flex;
    gap: 10px; /* Space between the elements */
    justify-content: flex-start; /* Align items to the left */
    align-items: center; /* Center vertically */
}

/* Search Input Styling */
.search-form input[type="text"], .search-form select {
    padding: 12px; /* Increase padding for larger input */
    font-size: 16px; /* Increase font size */
    width: 250px; /* Adjust width of the input and select box */
    border: 1px solid #ccc;
    border-radius: 4px;
}

/* Filter Button Styling */
.search-form button {
    padding: 12px 18px; /* Increase padding for a larger button */
    font-size: 16px; /* Increase font size */
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease; /* Smooth hover effect */
}

/* Hover Effect for Submit Button */
.search-form button:hover {
    background-color: #2980b9;
}


        /* Orders Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr:hover {
            background-color: #e6f7ff;
        }

        /* Action Button Styles */
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

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            position: relative;
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

        /* Pagination */
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

        /* Badge for Order Status */
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
    </style>
</head>
<body>
    <!-- ✅ Navbar -->
<div class="navbar">
    <div><strong>Chandusoft <?= $role ?></strong></div>
    <div class="links">
        Welcome <?= $role ?>!
        <a href="/app/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <?php if ($role === 'Admin'): ?>
            <a href="/admin/catalog.php" class="<?= basename($_SERVER['PHP_SELF']) === 'catalog.php' ? 'active' : '' ?>">Admin Catalog</a>
            <a href="/public/catalog.php">Public Catalog</a>
            <a href="/admin/orders.php" class="<?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>">Orders</a>
        <?php elseif ($role === 'Editor'): ?>
            <a href="/public/catalog.php">Public Catalog</a>
        <?php endif; ?>
        <a href="/admin/admin-leads.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin-leads.php' ? 'active' : '' ?>">Leads</a>
        <a href="/admin/pages.php" class="<?= basename($_SERVER['PHP_SELF']) === 'pages.php' ? 'active' : '' ?>">Pages</a>
        <a href="/admin/logout.php">Logout</a>
    </div>
</div>

    <!-- Orders Page Content -->
    <div class="content">
        <h1>Orders</h1>

        <!-- Search Form -->
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="Search by email or order ref" value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status ?>" <?= ($status === $statusFilter) ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filter</button>
        </form>

        <!-- Orders Table -->
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
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['order_ref']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['customer_email']) ?></td>
                            <td>$<?= number_format($order['total'], 2) ?></td>
                            <td><?= ucfirst($order['payment_gateway']) ?></td>
                            <td><span class="badge <?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span></td>
                            <td><?= htmlspecialchars($order['created_at']) ?></td>
                            <td>
                                <button class="view-btn" data-order='<?= json_encode($order, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
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

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($totalPages > 1): ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="<?= ($i === $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

<!-- Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="modalBody">
            <!-- Invoice content will be injected here -->
        </div>
        <button onclick="window.print()" class="print-btn">Print Invoice</button>
    </div>
</div>

<script>
const modal = document.getElementById('orderModal');
const modalBody = document.getElementById('modalBody');
const closeBtn = document.querySelector('.close');

document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const order = JSON.parse(btn.getAttribute('data-order'));

        // Company Info
        const companyName = "Chandusoft Technologies Pvt Ltd";
        const companyAddress = "Module No.6, First Floor, IT Tower Medha,\nSurvey No. 52 & 53, Kesarapalli Village, Krishna District,\nAndhra Pradesh - 521102";
        const companyEmail = "Chandusoft Email 1";
        const companyPhone = "+91 8025 266 524";

        const items = order.items || [];
        let itemsHTML = `<table class="invoice-items">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>`;
        let grandTotal = 0;
        items.forEach(item => {
            const totalItem = item.qty * item.price;
            grandTotal += totalItem;
            itemsHTML += `<tr>
                <td>${item.name}</td>
                <td>${item.qty}</td>
                <td>$${parseFloat(item.price).toFixed(2)}</td>
                <td>$${totalItem.toFixed(2)}</td>
            </tr>`;
        });
        itemsHTML += `<tr class="grand-total">
            <td colspan="3">Grand Total</td>
            <td>$${grandTotal.toFixed(2)}</td>
        </tr></tbody></table>`;

        modalBody.innerHTML = `
            <div class="invoice-header">
                <div class="header-left">
                    <img src="../images/logo.jpg" alt="${companyName}" class="company-logo">
                    <h2>${companyName}</h2>
                    <p>${companyAddress.replace(/\n/g,'<br>')}<br>Email: ${companyEmail} | Phone: ${companyPhone}</p>
                </div>
            </div>

            <div class="invoice-info">
                <h3>Invoice</h3>
                <p><strong>Order Ref:</strong> ${order.order_ref}<br>
                   <strong>Date:</strong> ${order.created_at}<br>
                   <strong>Customer:</strong> ${order.customer_name}<br>
                   <strong>Email:</strong> ${order.customer_email}</p>
            </div>

            ${itemsHTML}

            <div class="invoice-footer">
                <p><strong>Payment Gateway:</strong> ${order.payment_gateway}</p>
                <p><strong>Status:</strong> ${order.payment_status}</p>
                <p style="margin-top:15px; font-style:italic;">Thank you for your order!</p>
            </div>
        `;

        modal.style.display = 'block';
    });
});

closeBtn.onclick = () => modal.style.display = 'none';
window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };
</script>

<style>
/* Modal Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
}
.modal-content {
    position: absolute;
    top: -65px;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    padding: 20px;
    width: 700px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
.close {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 22px;
    cursor: pointer;
}
.close:hover { color: #007BFF; }
.print-btn {
    margin-top: 15px;
    padding: 8px 16px;
    background: #007BFF;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.print-btn:hover { background: #0056b3; }

/* Invoice Styling */
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #007BFF;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.header-left {
    max-width: 65%;
    text-align: left;
}
.company-logo {
    max-width: 200px;
    margin-bottom: 10px;
}
.invoice-info { margin-bottom: 20px; }
.invoice-items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.invoice-items th, .invoice-items td { border: 1px solid #ccc; padding: 10px; text-align: left; }
.invoice-items th { background: #007BFF; color: #fff; }
.invoice-items tbody tr:nth-child(even) { background: #f9f9f9; }
.grand-total td { font-weight: bold; background: #eee; }
.invoice-footer { font-size: 13px; color: #555; }

/* Print Styles */
@media print {
    body * { visibility: hidden; }
    #orderModal, #orderModal * { visibility: visible; }
    #orderModal { position: absolute; left: 0; top: 0; width: 100%; }
    #orderModal .modal-content { box-shadow: none; border-radius: 0; }
    .print-btn { display: none; }
}
</style>


</body> 
</html>