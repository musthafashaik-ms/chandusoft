<?php
require_once __DIR__ . '/../app/functions.php'; // Ensure this is included only once

// Fetch site name and logo path from the database
$siteName = get_setting('site_name') ?: '';
$logoPath = get_setting('logo_path') ?: '';
$message = '';

// Handle the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle site name update
    $newSiteName = trim($_POST['site_name']);
    if (!empty($newSiteName)) {
        set_setting('site_name', $newSiteName); // Call function from functions.php
        $siteName = $newSiteName;
        $message .= "✅ Site name updated.<br>";
    }

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['logo']['type'], $allowedTypes)) {
            $message .= "❌ Invalid file type. Only JPG, PNG, or GIF allowed.<br>";
        } else {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $uniqueName = 'logo_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $uniqueName;
            $publicPath = 'uploads/' . $uniqueName;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                // Update the logo path in the database
                set_setting('logo_path', $publicPath);
                $logoPath = $publicPath; // Update logo path
                $message .= "✅ Logo uploaded successfully.<br>";
            } else {
                $message .= "❌ Error uploading logo.<br>";
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($siteName); ?> - Admin Settings</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            max-width: 600px;
            background: #ffffff;
            margin: 50px auto;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
        }

        .admin-container h1 {
            text-align: center;
            color: #007BFF;
            font-size: 28px;
            margin-bottom: 25px;
        }

        .admin-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px 15px;
            border-left: 4px solid #2e7d32;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
            color: #333;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input[type="text"]:focus,
        input[type="file"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        button[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s ease;
            display: block;
            width: 100%;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        img {
            display: block;
            max-width: 300px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 5px;
            background-color: #fafafa;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Admin Settings</h1>

        <?php if (!empty($message)): ?>
            <div class="admin-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Site Name:</label>
            <input type="text" name="site_name" value="<?php echo htmlspecialchars($siteName); ?>" required>

            <label>Upload Logo:</label>
            <input type="file" name="logo" accept="image/*">

            <?php if ($logoPath): ?>
                <p>Current Logo:</p>
                <img src="../<?php echo htmlspecialchars($logoPath); ?>" alt="Logo">
            <?php endif; ?>

            <button type="submit">Save</button>
        </form>
    </div>
</body>
</html>
