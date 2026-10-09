<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
admin_logout();
flash('success', 'You have been logged out successfully.');
redirect(BASE_URL . '/admin/login.php');
