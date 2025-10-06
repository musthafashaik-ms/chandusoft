<?php
session_start();

// Protect page: user must be logged in (adjust if you have a user system)
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to your login page
    exit;
}

$user = $_SESSION['user'];
$username = htmlspecialchars($user['username'] ?? 'User');
$role = htmlspecialchars(ucfirst($user['role'] ?? 'Editor')); // Example: Admin or Editor

// DB connection
$conn = new mysqli('localhost', 'root', '', 'chandusoft');
if ($conn->connect_error) {
    die("❌ DB connection failed: " . $conn->connect_error);
}

// Fetch latest 5 leads
$resultLatest = $conn->query("SELECT * FROM leads ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        /* your CSS styles here (same as before, or adjust as needed) */
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
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
        .dashboard-box {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 1200px;
            margin: 20px auto;
        }
        h2 { margin-top: 15px; }
        ul { 
    list-style: none; 
    padding-left: 0; 
}

li {
    margin-bottom: 10px;
    position: relative;
    padding-left: 20px;
}

li::before {
    content: "•";
    position: absolute;
    left: 0;
    color: #2c3e50; /* Optional: bullet color */
    font-weight: bold;
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
            background-color: #3498db;
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
<div class="dashboard-box">
    <h2>Dashboard</h2>

    <ul>
        <li><strong>Total leads:</strong> <?= $conn->query("SELECT COUNT(*) as count FROM leads")->fetch_assoc()['count'] ?></li>
        <li><strong>Pages published:</strong> <?= $conn->query("SELECT COUNT(*) as count FROM pages WHERE status='published'")->fetch_assoc()['count'] ?></li>
        <li><strong>Pages draft:</strong> <?= $conn->query("SELECT COUNT(*) as count FROM pages WHERE status='draft'")->fetch_assoc()['count'] ?></li>
    </ul>

    <h3>Last 5 leads</h3>

    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>Created</th>
            <th>IP</th>
        </tr>
        <?php while ($row = $resultLatest->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['message']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
               <td><?= !empty($row['IP']) ? htmlspecialchars($row['IP']) : '' ?></td>

            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
