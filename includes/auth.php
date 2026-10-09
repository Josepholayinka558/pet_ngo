<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

function regenerate_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function is_admin_logged_in() {
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_username']);
}

function require_admin() {
    if (!is_admin_logged_in()) {
        flash('error', 'Please log in to access this area.');
        redirect(BASE_URL . '/admin/login.php');
    }
    regenerate_session();
}

function require_super_admin() {
    require_admin();
    if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
        flash('error', 'You do not have permission to access this area.');
        redirect(BASE_URL . '/admin/dashboard.php');
    }
}

function current_admin_id() {
    return $_SESSION['admin_id'] ?? null;
}

function current_admin() {
    if (!is_admin_logged_in()) return null;
    return pdo_fetch("SELECT id, username, email, full_name, role, profile_image FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
}

function admin_login($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();
    if ($admin && $admin['status'] && password_verify($password, $admin['password'])) {
        regenerate_session();
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$admin['id']]);
        generate_csrf_token();
        return true;
    }
    return false;
}

function admin_logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
