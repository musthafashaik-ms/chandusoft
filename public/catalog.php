<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php';
include __DIR__ . '/../admin/header.php';

$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

$where = "WHERE status = 'published'";
$params = [];

if ($search !== '') {
    $searchEscaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
    $where .= " AND title LIKE :search ESCAPE '\\\\'";
    $params[':search'] = "%{$searchEscaped}%";
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


// Log page view
log_page("Visited Catalog Page | Search: $search | Page: $page");
?>
<!DOCTYPE html>
<html lang="en">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<head>
    <meta charset="UTF-8">
    <title>Our Catalog</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0px;
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
            transition: transform 0.2s ease;
        }
        .card h3 {
            margin: 10px 0 5px;
            color: #007BFF;
        }
        .card p {
            margin: 0 0 10px;
            color: #555;
        }
        .card a.card-link {
            display: block;
            color: inherit;
            text-decoration: none;
        }
        .card .view-details-btn {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            text-align: center;
            margin-top: 10px;
        }
        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }
        .card img:hover {
            transform: scale(1.05);
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
        /* Buttons container - display buttons side by side */
.buttons {
    display: flex;
    justify-content: space-between; /* Space between buttons */
    margin-top: 10px;
    width: 100%; /* Ensure buttons take up the full width of the card */
}

/* Quantity input field */
.quantity-input {
    width: 50px;
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    margin-right: 15px;
}

/* Both Buy Now and Add to Cart buttons */
.buy-now-btn, .add-to-cart-btn {
    border: none;
    border-radius: 0; /* Removing rounded corners for rectangular shape */
    cursor: pointer;
    width: 75%; /* Ensure buttons share equal space */
    text-align: center;
    font-size: 1em;
}

/* Buy Now button - Green */
.buy-now-btn {
    background-color: #28a745; /* Green */
    color: white;
}

.buy-now-btn:hover {
    background-color: #218838; /* Darker green for hover */
}

/* Add to Cart button - Blue */
.add-to-cart-btn {
    background-color: #007BFF; /* Blue */
    color: white;
}

.add-to-cart-btn:hover {
    background-color: #0056b3; /* Darker blue for hover */
}

/* Card hover effect */
.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
}

/* Header Styles */
header {
    background-color: #007BFF;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ccc;
}
 
.logo img {
    width: 400px;
    height: auto;
}
 
nav {
    display: flex;
    justify-content: center;
    gap: 15px;
    align-items: center;
}
 
/* Navigation Links */
nav a {
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
    font-weight: bold;
    color: white;
    position: relative;
    transition: color 0.3s ease;
}
 
/* Hover effect: underline only */
nav a::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 0%;
    height: 3px;
    background-color: #FFD700; /* underline color (gold/yellow) */
    transition: width 0.3s ease;
}
 
nav a:hover::after,
nav a.active::after {
    width: 100%; /* full underline on hover or active */
}
 
nav a:hover,
nav a.active {
    color: #FFD700; /* text color change on hover or active (optional) */
}
 


/* Back to Top Button */
/* Back to Top Button */
#back-to-top {
    position: fixed;
    bottom: 50px;
    right: 50px;
    display: none; /* Hidden by default */
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 10px 10px; /* Adjusted padding for a smaller button */
    border-radius: 30%; /* Round shape */
    font-size: 20px; /* Increase font size for better visibility */
    cursor: pointer;
    z-index: 100;
    transition: background-color 0.3s ease;
    text-align: center; /* Center the content inside the button */
    width: 50px; /* Adjust the width of the button */
    height: 50px; /* Adjust the height of the button */
    display: flex;
    justify-content: center;
    align-items: center; /* Center content (the arrow) inside the button */
}



/* Change background color when hovered */
#back-to-top:hover {
    background-color: #0056b3;
}

/* Footer Styles */
footer {
    background: #333;
    color: #fff;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;  /* Fixes the footer at the bottom of the screen */
    left: 0;
    bottom: 0;  /* Ensures it's always at the bottom */
    width: 100%;  /* Full width */
    z-index: 1000;  /* Keeps footer above other content */
}

footer p {
    margin: 0;
    font-size: 14px;
}

footer p b {
    font-weight: bold;
}

.social-icons a {
    color: #fff;
    margin-left: 15px;
    font-size: 16px;
    text-decoration: none;
    transition: color 0.3s;
}

.social-icons a:hover {
    color: #1da1f2; /* Hover color */
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
    <a href="catalog-item.php?slug=<?= urlencode($item['slug']) ?>" class="card-link">
        <?php if ($item['image']): ?>
            <img src="/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
        <?php endif; ?>
        <h3><?= htmlspecialchars($item['title']) ?></h3>
        <p>$<?= number_format($item['price'], 2) ?></p>
    </a>
    
    <!-- Add Buy Now and Add to Cart buttons -->
    <div class="buttons">
        <form method="post" action="/checkout" class="buy-now-form">
            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
            <input type="number" name="quantity" value="1" min="1" class="quantity-input">
            <button type="submit" class="buy-now-btn">Buy Now</button>
        </form>
        <form method="post" action="/public/cart" class="add-to-cart-form">
            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
            <input type="number" name="quantity" value="1" min="1" class="quantity-input">
            <button type="submit" class="add-to-cart-btn">Add to Cart</button>
        </form>
    </div>
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
<?php include __DIR__ . '/../admin/footer.php'; ?>

    <button id="back-to-top" title="Back to Top">↑</button>
    <script src="/include.js"></script>
</body>
</html>
