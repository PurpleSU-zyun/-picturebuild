<?php
/**
 * 数据库配置 - 请根据宝塔面板数据库信息填写
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'gallery');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');

/**
 * 管理面板密码
 */
define('ADMIN_PASSWORD', 'admin123');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function getSettings(): array {
    $db = getDB();
    $stmt = $db->query("SELECT `key`, `value` FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['key']] = $row['value'];
    }
    return $settings;
}

function getSetting(string $key, string $default = ''): string {
    $db = getDB();
    $stmt = $db->prepare("SELECT `value` FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
}

function setSetting(string $key, string $value): bool {
    $db = getDB();
    // 使用 REPLACE，兼容性最好
    $stmt = $db->prepare("REPLACE INTO settings (`key`, `value`) VALUES (?, ?)");
    return $stmt->execute([$key, $value]);
}

function getPhotos(): array {
    $db = getDB();
    return $db->query("SELECT * FROM photos ORDER BY id DESC")->fetchAll();
}

function addPhoto(string $url): bool {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO photos (url) VALUES (?)");
    return $stmt->execute([$url]);
}

function deletePhoto(int $id): bool {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM photos WHERE id = ?");
    return $stmt->execute([$id]);
}
