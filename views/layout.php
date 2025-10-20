<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageData['title'] ?? 'Untitled Page') ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Your navigation and header section -->
    <?php include __DIR__ . '/header.php'; ?>

    <!-- Main content -->
    <main>
        <?= $content; ?>
    </main>

    <!-- Your footer section (if applicable) -->
    <?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
