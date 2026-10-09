<?php
define('SITE_NAME', 'PawBridge Rescue');
define('BASE_URL', 'http://localhost/pet_ngo');
define('ADMIN_URL', BASE_URL . '/admin');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads');
define('UPLOAD_URL', BASE_URL . '/assets/uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);

$site_settings = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings");
        $stmt->execute();
        $settings = $stmt->fetchAll();
        foreach ($settings as $s) {
            $site_settings[$s['setting_key']] = $s['setting_value'];
        }
    } catch (Exception $e) {}
}

function setting($key, $default = '') {
    global $site_settings;
    return $site_settings[$key] ?? $default;
}
