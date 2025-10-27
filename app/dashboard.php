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

// Query counts for the dashboard stats
$totalLeadsResult = $conn->query("SELECT COUNT(*) AS count FROM leads");
$totalLeads = $totalLeadsResult->fetch_assoc()['count'];

$publishedPagesResult = $conn->query("SELECT COUNT(*) AS count FROM pages WHERE status='published'");
$publishedPages = $publishedPagesResult->fetch_assoc()['count'];

$draftPagesResult = $conn->query("SELECT COUNT(*) AS count FROM pages WHERE status='draft'");
$draftPages = $draftPagesResult->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Chandusoft Admin</title>
    <style>
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
            color: #2c3e50;
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
        <a href="/app/dashboard">Dashboard</a>
        <a href="/admin/admin-leads">Leads</a>
        <a href="/admin/pages">Pages</a>
        <a href="/admin/logout">Logout</a>

    </div>
</div>

<div class="dashboard-box">
    <h2>Dashboard</h2>

    <ul>
        <li><strong>Total leads:</strong> <?= number_format($totalLeads) ?></li>
        <li><strong>Pages published:</strong> <?= number_format($publishedPages) ?></li>
        <li><strong>Pages draft:</strong> <?= number_format($draftPages) ?></li>
    </ul>

    <h3>Last 5 Leads</h3>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Created</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $resultLatest->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['message']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><?= !empty($row['IP']) ? htmlspecialchars($row['IP']) : 'N/A' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
// Close the DB connection
$conn->close();
?>
