<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/logger.php'; // ✅ Central logger
 
$message = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = (float)$_POST['price'];
    $short_desc = trim($_POST['short_desc']);
    $status = in_array($_POST['status'], ['published', 'draft', 'archived']) ? $_POST['status'] : 'draft';
 
    // ✅ Slug generation
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    $slug = trim($slug, '-');
    $originalSlug = $slug;
    $counter = 1;
    $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM catalog WHERE slug = ?");
    while (true) {
        $checkSlug->execute([$slug]);
        if ($checkSlug->fetchColumn() == 0) break;
        $slug = $originalSlug . '-' . $counter++;
    }
 
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] <= 2 * 1024 * 1024) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $uniqueName = 'catalog_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
 
                $target = $uploadDir . $uniqueName;
                $publicPath = 'uploads/' . $uniqueName;
 
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $imagePath = $publicPath;
                    log_catalog("📸 Image uploaded for '$title' — file: $publicPath");
                } else {
                    $message = "❌ Failed to upload image.";
                    log_catalog("⚠️ Image upload failed for '$title'", 'ERROR');
                }
            } else {
                $message = "❌ Image must be under 2MB.";
                log_catalog("⚠️ Image too large for '$title' (" . $_FILES['image']['size'] . " bytes)", 'ERROR');
            }
        } else {
            $message = "❌ Upload error code: " . $_FILES['image']['error'];
            log_catalog("⚠️ Upload error ({$_FILES['image']['error']}) for '$title'", 'ERROR');
        }
    }
 
    if (!$message) {
        $stmt = $pdo->prepare("
            INSERT INTO catalog (title, slug, price, image, short_desc, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$title, $slug, $price, $imagePath, $short_desc, $status]);
 
        $id = $pdo->lastInsertId();
        log_catalog("🆕 Created catalog item #$id — title: '$title', price: $price, status: '$status'", 'CREATE');
 
        header("Location: catalog.php");
        exit;
    } else {
        log_catalog("❌ Catalog creation failed for '$title' — reason: $message", 'ERROR');
    }
}
?>
 
 