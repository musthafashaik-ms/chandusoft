<?php
require_once __DIR__ . '/config.php';

/**
 * -------------------------------
 * SETTINGS FUNCTIONS
 * -------------------------------
 */

/**
 * Get a setting value by key.
 *
 * @param string $key The setting key.
 * @return string The setting value or empty string if not found.
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
 * Set a setting value by key. Insert or update.
 *
 * @param string $key The setting key.
 * @param string $value The setting value.
 * @return bool True on success, false on failure.
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
 * Update a site setting (alias for set_setting)
 *
 * @param string $key
 * @param string $value
 * @return bool
 */
function update_site_setting($key, $value) {
    return set_setting($key, $value);
}

/**
 * Ensure site_settings table exists.
 */
function ensure_site_settings_table() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS site_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(255) NOT NULL UNIQUE,
                `value` TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error creating site_settings table: " . $e->getMessage());
    }
}

/**
 * -------------------------------
 * UPLOAD DIRECTORY FUNCTIONS
 * -------------------------------
 */

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
 * -------------------------------
 * IMAGE FUNCTIONS
 * -------------------------------
 */

/**
 * Check if WebP support is available.
 *
 * @return bool
 */
function is_webp_supported() {
    return function_exists('imagewebp');
}

/**
 * Resize and convert image to WebP.
 *
 * @param string $source Path to source image.
 * @param string $target Path to save WebP image.
 * @param int $maxWidth Maximum width.
 * @param int $maxHeight Maximum height.
 * @return bool True on success, false on failure.
 */
function resize_and_convert_to_webp($source, $target, $maxWidth = 1600, $maxHeight = 1600) {
    if (!is_webp_supported()) {
        error_log("WebP support is not enabled in GD.");
        return false;
    }

    try {
        list($width, $height, $type) = getimagesize($source);

        // Calculate new dimensions
        $newWidth = $width;
        $newHeight = $height;
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = $width / $height;
            if ($width > $height) {
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $ratio;
            } else {
                $newHeight = $maxHeight;
                $newWidth = $maxHeight * $ratio;
            }
        }

        // Create image resource
        switch ($type) {
            case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($source); break;
            case IMAGETYPE_PNG:  $image = imagecreatefrompng($source); break;
            case IMAGETYPE_GIF:  $image = imagecreatefromgif($source); break;
            default:
                error_log("Unsupported image type: $type");
                return false;
        }

        // Resize if needed
        if ($newWidth !== $width || $newHeight !== $height) {
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            // Preserve transparency for PNG/GIF
            if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
                imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $imageToSave = $resized;
        } else {
            $imageToSave = $image;
        }

        $success = imagewebp($imageToSave, $target);
        imagedestroy($image);
        if (isset($resized)) imagedestroy($resized);

        return $success;
    } catch (Exception $e) {
        error_log("Error resizing/converting image: " . $e->getMessage());
        return false;
    }
}
?>
