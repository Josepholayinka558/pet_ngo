<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

if (!is_post()) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$email = clean_input($_POST['email'] ?? '');
$name = clean_input($_POST['name'] ?? '');

if (empty($email) || !validate_email($email)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$existing = pdo_fetch("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?", [$email]);

if ($existing) {
    if (empty($existing['is_active'])) {
        pdo_update('newsletter_subscribers', ['is_active' => 1, 'token' => bin2hex(random_bytes(32)), 'unsubscribed_at' => null], 'id = ?', [$existing['id']]);
        echo json_encode(['success' => true, 'message' => "Welcome back! You've been re-subscribed to our newsletter."]);
    } else {
        echo json_encode(['success' => true, 'message' => 'You are already subscribed to our newsletter. Thank you!']);
    }
    exit;
}

$token = bin2hex(random_bytes(32));
pdo_insert('newsletter_subscribers', [
    'email' => $email,
    'name' => $name,
    'source' => clean_input($_POST['source'] ?? 'website'),
    'is_active' => 1,
    'token' => $token,
]);

echo json_encode(['success' => true, 'message' => 'Thank you for subscribing! 🐾']);
