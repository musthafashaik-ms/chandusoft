<?php
require_once __DIR__ . '/config.php';

/**
 * Get a setting value by its key.
 *
 * @param string $key The setting key.
 * @return string The setting value or an empty string if not found.
 */
function get_setting($key) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT value FROM site_settings WHERE `key` = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: '';
    } catch (PDOException $e) {
        error_log("Error fetching setting '$key': " . $e->getMessage());
        return '';
    }
}

/**
 * Set a setting value by its key. If the setting exists, update it. If not, insert it.
 *
 * @param string $key The setting key.
 * @param string $value The setting value.
 * @return bool Returns true on success, false on failure.
 */
function set_setting($key, $value) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO site_settings (`key`, `value`, created_at, updated_at)
            VALUES (?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = NOW()
        ");
        return $stmt->execute([$key, $value]);
    } catch (PDOException $e) {
        error_log("Error saving setting '$key': " . $e->getMessage());
        return false;
    }
}


/**
 * Ensure the `site_settings` table exists with necessary columns and constraints.
 */
function ensure_site_settings_table() {
    global $pdo;
    try {
        // Create the table if it doesn't exist
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS site_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(255) NOT NULL UNIQUE,  -- Ensure 'key' is unique
                `value` TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        $stmt->execute();
    } catch (PDOException $e) {
        // Log the error if the table creation fails
        error_log("Error ensuring site_settings table: " . $e->getMessage());
    }
}

/**
 * Update a setting value by its key.
 *
 * @param string $key The setting key.
 * @param string $value The setting value.
 * @return bool Returns true on success, false on failure.
 */
// Update this part in the functions.php file

function update_site_setting($key, $value) {
    global $pdo;

    // Make sure the $key is a valid column name in the table
    $validKeys = ['site_name', 'logo_path']; // List all valid columns

    if (!in_array($key, $validKeys)) {
        throw new Exception("Invalid setting key: $key");
    }

    // Check if the setting exists in the database
    $stmt = $pdo->prepare("SELECT * FROM site_settings WHERE id = 1");
    $stmt->execute();
    $existingSetting = $stmt->fetch();

    if ($existingSetting) {
        // Update the setting
        $updateStmt = $pdo->prepare("UPDATE site_settings SET $key = ?, updated_at = NOW() WHERE id = 1");
        $updateStmt->execute([$value]);
    } else {
        // Insert the new setting (first-time setup)
        if ($key === 'site_name') {
            $insertStmt = $pdo->prepare("INSERT INTO site_settings (site_name, logo_path, created_at, updated_at) VALUES (?, '', NOW(), NOW())");
            $insertStmt->execute([$value]);
        } else if ($key === 'logo_path') {
            $insertStmt = $pdo->prepare("INSERT INTO site_settings (site_name, logo_path, created_at, updated_at) VALUES ('', ?, NOW(), NOW())");
            $insertStmt->execute([$value]);
        }
    }
}


/**
 * Ensure the uploads directory exists.
 */
function ensure_uploads_directory() {
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
}


/**
 * Check if GD and WebP support are available for image manipulation.
 *
 * @return bool Returns true if WebP support is available, false otherwise.
 */
function is_webp_supported() {
    return function_exists('imagewebp');
}

/**
 * Resize and convert an image to WebP format.
 *
 * @param string $source The path to the source image.
 * @param string $target The target path for the WebP image.
 * @param int $maxWidth The maximum width of the resized image.
 * @param int $maxHeight The maximum height of the resized image.
 * @return bool Returns true if the image was resized and converted successfully, false otherwise.
 */
function resize_and_convert_to_webp($source, $target, $maxWidth = 1600, $maxHeight = 1600) {
    if (!is_webp_supported()) {
        error_log("WebP support is not enabled in GD.");
        return false;
    }

    try {
        // Get image dimensions
        list($width, $height, $type) = getimagesize($source);

        // If the image is too large, resize it
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = $width / $height;
            if ($width > $height) {
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $ratio;
            } else {
                $newHeight = $maxHeight;
                $newWidth = $maxHeight * $ratio;
            }

            // Create a new image resource from the original image
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($source);
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($source);
                    break;
                case IMAGETYPE_GIF:
                    $image = imagecreatefromgif($source);
                    break;
                default:
                    error_log("Unsupported image type.");
                    return false;
            }

            // Create a new empty image with the resized dimensions
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save the image as WebP
            if (imagewebp($newImage, $target)) {
                imagedestroy($image);
                imagedestroy($newImage);
                return true;
            } else {
                error_log("Failed to save image as WebP.");
                imagedestroy($image);
                imagedestroy($newImage);
                return false;
            }
        } else {
            // If the image doesn't need resizing, just save as WebP
            return imagewebp(imagecreatefromjpeg($source), $target);
        }
    } catch (Exception $e) {
        // Log any other errors
        error_log("Error during image resizing or WebP conversion: " . $e->getMessage());
        return false;
    }
}

?>
