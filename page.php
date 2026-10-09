<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    redirect('index.php');
}

$page = pdo_fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
if (!$page) {
    http_response_code(404);
    $pageMetaTitle = 'Page Not Found';
    $pageMetaDesc = '';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <section class="page-hero">
        <h1>Page Not Found</h1>
        <p>The page you are looking for does not exist or may have been removed.</p>
        <a href="<?= url('index.php') ?>" class="btn btn-primary">Back to Home</a>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

if ($page['status'] !== 'Published' && !is_admin_logged_in()) {
    redirect('index.php');
}

$pageMetaTitle = !empty($page['seo_title']) ? $page['seo_title'] : $page['title'];
$pageMetaDesc = !empty($page['seo_description']) ? $page['seo_description'] : mb_substr(strip_tags($page['content'] ?? ''), 0, 160);
$template = $page['template'] ?? 'default';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <h1><?= e($page['title']) ?></h1>
</section>

<section class="section section-tight" style="padding-top:40px;">
    <div class="container-xl">
        <?php if ($template === 'full-width'): ?>
        <div class="row justify-content-center">
            <div class="col-12">
                <?php if ($page['featured_image']): ?>
                <div style="border-radius:var(--radius-xl);overflow:hidden;margin-bottom:40px;">
                    <img src="<?= asset_url('uploads/'.$page['featured_image']) ?>" alt="<?= e($page['title']) ?>" style="width:100%;max-height:480px;object-fit:cover;">
                </div>
                <?php endif; ?>
                <div class="page-content"><?= $page['content'] ?></div>
            </div>
        </div>
        <?php elseif ($template === 'sidebar'): ?>
        <div class="row g-5">
            <div class="col-lg-8">
                <?php if ($page['featured_image']): ?>
                <div style="border-radius:var(--radius-xl);overflow:hidden;margin-bottom:40px;">
                    <img src="<?= asset_url('uploads/'.$page['featured_image']) ?>" alt="<?= e($page['title']) ?>" style="width:100%;max-height:400px;object-fit:cover;">
                </div>
                <?php endif; ?>
                <div class="page-content"><?= $page['content'] ?></div>
            </div>
            <div class="col-lg-4">
                <div class="form-card" style="position:sticky;top:100px;">
                    <h4 style="margin-bottom:16px;">Get Involved</h4>
                    <ul style="list-style:none;padding:0;margin:0;">
                        <li style="margin-bottom:12px;"><a href="<?= url('animals.php') ?>"><i class="fas fa-paw" style="color:var(--pb-teal);margin-right:8px;"></i> Adopt a Pet</a></li>
                        <li style="margin-bottom:12px;"><a href="<?= url('foster.php') ?>"><i class="fas fa-house-heart" style="color:var(--pb-teal);margin-right:8px;"></i> Foster Program</a></li>
                        <li style="margin-bottom:12px;"><a href="<?= url('volunteer.php') ?>"><i class="fas fa-hand-holding-heart" style="color:var(--pb-teal);margin-right:8px;"></i> Volunteer</a></li>
                        <li><a href="<?= url('donate.php') ?>"><i class="fas fa-heart" style="color:var(--pb-accent);margin-right:8px;"></i> Donate</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <?php if ($page['featured_image']): ?>
                <div style="border-radius:var(--radius-xl);overflow:hidden;margin-bottom:40px;">
                    <img src="<?= asset_url('uploads/'.$page['featured_image']) ?>" alt="<?= e($page['title']) ?>" style="width:100%;max-height:420px;object-fit:cover;">
                </div>
                <?php endif; ?>
                <div class="page-content"><?= $page['content'] ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
