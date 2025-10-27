<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page['title'] ?? 'Untitled Page') ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Navigation and header -->
    <?php include __DIR__ . '/header.php'; ?>

    <!-- Main content -->
    <main>
        <h1><?= htmlspecialchars($page['title']) ?></h1>
        <div>
            <?= $page['content_html'] ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
