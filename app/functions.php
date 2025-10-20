<?php
require_once __DIR__ . '/config.php';

function get_setting($key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM site_settings WHERE `key` = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn() ?: '';
}

function set_setting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO site_settings (`key`, `value`)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE value = VALUES(value)
    ");
    return $stmt->execute([$key, $value]);
}
