<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_admin();
$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'Dashboard';
$parentActive = $parentActive ?? '';
$adminUser = current_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | PawBridge Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset_url('css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<div class="admin-wrapper">
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon"><i class="fas fa-paw"></i></div>
            <div>
                <h2>PawBridge</h2>
                <small>Admin Dashboard</small>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-section">Main</li>
            <li><a href="<?= url('admin/dashboard.php') ?>" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="<?= url('admin/animals.php') ?>" class="<?= in_array($currentPage, ['animals.php','animal-add.php','animal-edit.php']) ? 'active' : '' ?>"><i class="fas fa-dog"></i> Animals</a></li>
            <li class="menu-section">Applications</li>
            <li><a href="<?= url('admin/adoption-applications.php') ?>" class="<?= $currentPage === 'adoption-applications.php' ? 'active' : '' ?>"><i class="fas fa-file-heart"></i> Adoption Apps</a></li>
            <li><a href="<?= url('admin/foster-applications.php') ?>" class="<?= $currentPage === 'foster-applications.php' ? 'active' : '' ?>"><i class="fas fa-house-heart"></i> Foster Apps</a></li>
            <li><a href="<?= url('admin/volunteers.php') ?>" class="<?= $currentPage === 'volunteers.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Volunteers</a></li>
            <li class="menu-section">Content</li>
            <li><a href="<?= url('admin/rescue-stories.php') ?>" class="<?= in_array($currentPage, ['rescue-stories.php','rescue-story-add.php','rescue-story-edit.php']) ? 'active' : '' ?>"><i class="fas fa-book-heart"></i> Rescue Stories</a></li>
            <li><a href="<?= url('admin/blog.php') ?>" class="<?= in_array($currentPage, ['blog.php','blog-post-add.php','blog-post-edit.php']) ? 'active' : '' ?>"><i class="fas fa-blog"></i> Blog</a></li>
            <li><a href="<?= url('admin/events.php') ?>" class="<?= in_array($currentPage, ['events.php','event-add.php','event-edit.php']) ? 'active' : '' ?>"><i class="fas fa-calendar-star"></i> Events</a></li>
            <li><a href="<?= url('admin/donations.php') ?>" class="<?= $currentPage === 'donations.php' ? 'active' : '' ?>"><i class="fas fa-hand-holding-dollar"></i> Donations</a></li>
            <li><a href="<?= url('admin/messages.php') ?>" class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>"><i class="fas fa-envelope"></i> Messages <?php
                $unread = (int)pdo_fetch("SELECT COUNT(*) AS c FROM contact_messages WHERE is_read = 0")['c'] ?? 0;
                if ($unread > 0) echo '<span class="badge" style="background:#F59E42;color:white;margin-left:6px;">' . $unread . '</span>';
            ?></a></li>
            <li class="menu-section">Library & People</li>
            <li><a href="<?= url('admin/media.php') ?>" class="<?= $currentPage === 'media.php' ? 'active' : '' ?>"><i class="fas fa-images"></i> Media Library</a></li>
            <li><a href="<?= url('admin/team.php') ?>" class="<?= in_array($currentPage, ['team.php','team-add.php','team-edit.php']) ? 'active' : '' ?>"><i class="fas fa-user-group"></i> Team Members</a></li>
            <li><a href="<?= url('admin/subscribers.php') ?>" class="<?= $currentPage === 'subscribers.php' ? 'active' : '' ?>"><i class="fas fa-bell"></i> Subscribers</a></li>
            <li class="menu-section">Settings</li>
            <li><a href="<?= url('admin/pages.php') ?>" class="<?= in_array($currentPage, ['pages.php','page-add.php','page-edit.php']) ? 'active' : '' ?>"><i class="fas fa-file-lines"></i> Pages</a></li>
            <li><a href="<?= url('admin/homepage.php') ?>" class="<?= $currentPage === 'homepage.php' ? 'active' : '' ?>"><i class="fas fa-house"></i> Homepage</a></li>
            <li><a href="<?= url('admin/settings.php') ?>" class="<?= in_array($currentPage, ['settings.php']) ? 'active' : '' ?>"><i class="fas fa-sliders"></i> Website Settings</a></li>
            <?php if (($_SESSION['admin_role'] ?? '') === 'super_admin'): ?>
            <li><a href="<?= url('admin/users.php') ?>" class="<?= in_array($currentPage, ['users.php','user-add.php','user-edit.php']) ? 'active' : '' ?>"><i class="fas fa-user-shield"></i> Admin Users</a></li>
            <?php endif; ?>
        </ul>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar"><?= strtoupper(substr($adminUser['full_name'] ?? 'A', 0, 1)) ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:600;"><?= e($adminUser['full_name'] ?? 'Admin') ?></div>
                    <div style="font-size:11px;opacity:0.6;"><?= e(ucfirst($adminUser['role'] ?? '')) ?></div>
                </div>
                <a href="<?= url('admin/logout.php') ?>" style="color:white;opacity:0.7;" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </aside>
    <main class="admin-main">
        <div class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebarToggle" class="btn btn-sm btn-secondary d-lg-none" type="button"><i class="fas fa-bars"></i></button>
                <h1><?= e($pageTitle) ?></h1>
            </div>
            <div class="topbar-actions">
                <a href="<?= url('index.php') ?>" class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
                <a href="<?= url('admin/messages.php') ?>" class="btn btn-sm btn-secondary position-relative">
                    <i class="fas fa-envelope"></i>
                    <?php if ($unread > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unread ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        <div class="admin-content">
            <?= display_flash() ?>
