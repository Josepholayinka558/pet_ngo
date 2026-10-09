<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

function clean_input($data) {
    $data = trim($data ?? '');
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } elseif (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function display_flash() {
    $html = '';
    if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $msg) {
            $alertClass = $type === 'success' ? 'alert-success' : ($type === 'error' ? 'alert-danger' : 'alert-' . $type);
            $html .= '<div class="alert ' . $alertClass . ' alert-dismissible fade show mb-3" role="alert">';
            $html .= e($msg);
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            $html .= '</div>';
        }
        $_SESSION['flash'] = [];
    }
    return $html;
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function pdo_execute($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function pdo_fetch($sql, $params = []) {
    $stmt = pdo_execute($sql, $params);
    return $stmt->fetch();
}

function pdo_fetch_all($sql, $params = []) {
    $stmt = pdo_execute($sql, $params);
    return $stmt->fetchAll();
}

function pdo_insert($table, $data) {
    global $pdo;
    $keys = array_keys($data);
    $placeholders = ':' . implode(', :', $keys);
    $sql = "INSERT INTO {$table} (" . implode(', ', $keys) . ") VALUES ({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

function pdo_update($table, $data, $where, $whereParams = []) {
    global $pdo;
    $set = [];
    foreach (array_keys($data) as $key) {
        $set[] = "{$key} = :set_{$key}";
    }
    $params = [];
    foreach ($data as $key => $value) {
        $params["set_{$key}"] = $value;
    }
    foreach ($whereParams as $key => $value) {
        $params[$key] = $value;
    }
    $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function pdo_delete($table, $where, $params = []) {
    global $pdo;
    $sql = "DELETE FROM {$table} WHERE {$where}";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function secure_upload($file, $folder = '') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'File upload error'];
    }
    $uploadDir = UPLOAD_PATH . ($folder ? '/' . $folder : '');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($ext, ALLOWED_IMAGE_TYPES) || !in_array($mimeType, ALLOWED_MIME_TYPES)) {
        return ['error' => 'Invalid file type'];
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['error' => 'File too large'];
    }
    $newName = uniqid() . '_' . time() . '.' . $ext;
    $destination = $uploadDir . '/' . $newName;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $relPath = ($folder ? $folder . '/' : '') . $newName;
        return ['success' => true, 'path' => $relPath, 'url' => UPLOAD_URL . '/' . $relPath, 'name' => $newName];
    }
    return ['error' => 'Failed to move file'];
}

function delete_file($path) {
    $fullPath = UPLOAD_PATH . '/' . ltrim($path, '/');
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

function format_date($date, $format = 'F j, Y') {
    if (!$date) return '';
    $ts = is_numeric($date) ? $date : strtotime($date);
    return date($format, $ts);
}

function animal_image($animal, $size = '') {
    $img = UPLOAD_URL . '/placeholder-dog.png';
    if (is_array($animal) && isset($animal['featured_image'])) {
        $img = UPLOAD_URL . '/' . $animal['featured_image'];
    } elseif (is_numeric($animal)) {
        $row = pdo_fetch("SELECT image_path FROM animal_images WHERE animal_id = ? AND is_primary = 1 LIMIT 1", [$animal]);
        if ($row) $img = UPLOAD_URL . '/' . $row['image_path'];
    }
    return $img;
}

function get_primary_animal_image($animalId) {
    $row = pdo_fetch("SELECT image_path FROM animal_images WHERE animal_id = ? AND is_primary = 1 LIMIT 1", [$animalId]);
    return $row ? UPLOAD_URL . '/' . $row['image_path'] : '';
}

function paginate($page, $perPage, $total, $url) {
    $totalPages = ceil($total / $perPage);
    if ($totalPages <= 1) return '';
    $html = '<nav class="pagination-nav"><ul class="pagination justify-content-center">';
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);
    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=1">&laquo;</a></li>';
        if ($startPage > 2) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    for ($i = $startPage; $i <= $endPage; $i++) {
        $active = ($i === $page) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
    }
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $totalPages . '">&raquo;</a></li>';
    }
    $html .= '</ul></nav>';
    return $html;
}

function seo_title($title = '') {
    return $title ? e($title) . ' | ' . e(setting('site_name', SITE_NAME)) : e(setting('seo_default_title', SITE_NAME));
}

function seo_description($desc = '') {
    return $desc ? e($desc) : e(setting('seo_default_description', ''));
}

function asset_url($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function url($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

function current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function is_active($page) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $page ? 'active' : '';
}
