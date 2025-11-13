<?php
session_start();

// Simple admin password protection
define('ADMIN_PASSWORD', 'musthafa');

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['password']) && $_POST['password'] === ADMIN_PASSWORD) {
        // Set user info in session with role admin
        $_SESSION['user'] = [
            'username' => 'Admin',
            'role' => 'admin'
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "❌ Incorrect password.";
    }
}

// Redirect to login form if not logged in
if (!isset($_SESSION['user'])) {
    // Show simple login form and exit
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f7f7f7; display: flex; height: 100vh; justify-content: center; align-items: center; }
            form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            input[type="password"] { padding: 10px; width: 250px; font-size: 16px; margin-bottom: 10px; }
            input[type="submit"] { padding: 10px 20px; font-size: 16px; cursor: pointer; background-color: #3498db; border: none; color: white; border-radius: 4px; }
            .error { color: red; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <form method="post">
            <h2>Admin Login</h2>
            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <input type="password" name="password" placeholder="Enter Admin Password" required autofocus>
            <br>
            <input type="submit" value="Login">
        </form>
    </body>
    </html>
    <?php
    exit;
}

// If logged in, continue to show leads page

// Database connection
$host = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'chandusoft';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("❌ DB connection failed: " . $conn->connect_error);
}

// Handle search
$search = $_GET['search'] ?? '';
$search = trim($search);

if ($search !== '') {
    // If the search term is only % or _ (wildcard characters), show no results
    if (preg_match('/^[%_]+$/', $search)) {
        // Always false condition — no rows returned
        $resultLatest = $conn->query("SELECT * FROM leads WHERE 1=0");
    } else {
        // Normal search
        $stmt = $conn->prepare("
            SELECT * FROM leads 
            WHERE id LIKE ? OR name LIKE ? OR email LIKE ? OR message LIKE ?
            ORDER BY id DESC
        ");
        $likeSearch = "%$search%";
        $stmt->bind_param("ssss", $likeSearch, $likeSearch, $likeSearch, $likeSearch);
        $stmt->execute();
        $resultLatest = $stmt->get_result();
    }
} else {
    // Show all leads if no search term
    $resultLatest = $conn->query("SELECT * FROM leads ORDER BY id DESC");
}


// Get user role for navbar display
$user = $_SESSION['user'];
$role = htmlspecialchars(ucfirst($user['role'] ?? 'Guest'));
$username = htmlspecialchars($user['username'] ?? 'User');
?>


<!DOCTYPE html>
<html>
<head>
    <title>Leads - Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
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

        h1 {
            margin-bottom: 20px;
            color: #007BFF;
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
            margin-top: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #2980b9;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr:hover {
            background-color: #e6f7ff;
        }
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
            <a href="/admin/catalog.php">Admin Catalog</a>
            <a href="/public/catalog.php">Public Catalog</a>
            <a href="/admin/orders.php">Orders</a>
        <?php elseif ($role === 'Editor'): ?>
            <a href="/public/catalog.php">Public Catalog</a>
        <?php endif; ?>
        <!-- Leads Link with Active Class Check -->
        <a href="/admin/admin-leads.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin-leads.php' ? 'active' : '' ?>">Leads</a>
        <a href="/admin/pages.php">Pages</a>
        <a href="/admin/logout.php">Logout</a>
    </div>
</div>


<div class="content">
    <h1>Leads</h1>

    <!-- 🔍 Search -->
    <form method="get" class="search-form">
        <input type="text" name="search" placeholder="Search leads..." value="<?= htmlspecialchars($search) ?>">
        <input type="submit" value="Search">
    </form>

    <table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Message</th>
        <th>Submitted At</th>
        <th>IP</th>
    </tr>

    <?php if ($resultLatest && $resultLatest->num_rows > 0): ?>
        <?php while($row = $resultLatest->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['message']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><?= !empty($row['IP']) ? htmlspecialchars($row['IP']) : '' ?></td>
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
