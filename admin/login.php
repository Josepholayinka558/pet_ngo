<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$error = '';
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid CSRF token. Please try again.');
    } else {
        $username = clean_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (admin_login($username, $password)) {
            flash('success', 'Welcome back, ' . $_SESSION['admin_name'] . '!');
            redirect(BASE_URL . '/admin/dashboard.php');
        } else {
            flash('error', 'Invalid username or password.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | PawBridge Rescue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset_url('css/admin.css') ?>" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div style="width:72px;height:72px;background:linear-gradient(135deg,#59B9B0,#0B632A);border-radius:22px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:36px;color:white;">
                <i class="fas fa-paw"></i>
            </div>
            <h1>PawBridge Rescue</h1>
            <p>Admin Dashboard — Sign in to continue</p>
        </div>
        <?= display_flash() ?>
        <form method="POST" action="">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <div class="input-group">
                    <span class="input-group-text" style="border-radius:10px 0 0 10px;background:var(--pb-soft-bg);border-color:#E5E7EB;"><i class="fas fa-user" style="color:var(--pb-secondary);"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="admin@pawbridge.org" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="border-radius:10px 0 0 10px;background:var(--pb-soft-bg);border-color:#E5E7EB;"><i class="fas fa-lock" style="color:var(--pb-secondary);"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2" style="font-size:15px;border-radius:12px;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        <div style="margin-top:24px;padding:16px;background:var(--pb-soft-bg);border-radius:12px;font-size:13px;color:var(--pb-gray);text-align:center;">
            <strong>Default login:</strong><br>
            Username: <code>admin</code> / Password: <code>admin123</code>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
