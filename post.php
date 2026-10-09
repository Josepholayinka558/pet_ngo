<?php
$slug = $_GET['slug'] ?? '';
if (!$slug) { redirect('blog.php'); }

$post = pdo_fetch("SELECT bp.*, bc.name AS category_name, bc.slug AS category_slug, a.full_name AS author_name, a.profile_image AS author_image
    FROM blog_posts bp
    LEFT JOIN blog_categories bc ON bc.id = bp.category_id
    LEFT JOIN admins a ON a.id = bp.author_id
    WHERE bp.slug = ?", [$slug]);
if (!$post) { redirect('blog.php'); }

if ($post['status'] !== 'Published' && !is_admin_logged_in()) {
    redirect('blog.php');
}

try {
    pdo_update('blog_posts', ['views' => ($post['views'] ?? 0) + 1], 'id = ?', [$post['id']]);
} catch (Exception $e) {}

$related = pdo_fetch_all("SELECT bp.id, bp.title, bp.slug, bp.excerpt, bp.featured_image, bp.published_at, bc.name AS cat
    FROM blog_posts bp LEFT JOIN blog_categories bc ON bc.id = bp.category_id
    WHERE bp.status = 'Published' AND bp.id != ?
    ORDER BY RAND() LIMIT 3", [$post['id']]);

$pageMetaTitle = !empty($post['seo_title']) ? $post['seo_title'] : $post['title'];
$pageMetaDesc = !empty($post['seo_description']) ? $post['seo_description'] : mb_substr(strip_tags($post['excerpt'] ?? $post['content']), 0, 160);
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero" style="padding-bottom:0;">
    <div class="container-xl">
        <div class="breadcrumb" style="justify-content:center;">
            <a href="<?= url('index.php') ?>"><i class="fas fa-house"></i> Home</a>
            <span>/</span>
            <a href="<?= url('blog.php') ?>">Blog</a>
            <?php if ($post['category_name']): ?>
            <span>/</span>
            <a href="<?= url('blog.php?cat='.($post['category_id'] ?? 0)) ?>"><?= e($post['category_name']) ?></a>
            <?php endif; ?>
            <span>/</span>
            <span style="color:var(--pb-navy);"><?= e(mb_substr($post['title'], 0, 35)) ?><?= strlen($post['title']) > 35 ? '...' : '' ?></span>
        </div>
        <?php if ($post['category_name']): ?>
        <span class="ph-eyebrow" style="background:var(--pb-mint);color:var(--pb-primary);"><?= e($post['category_name']) ?></span>
        <?php endif; ?>
        <h1 style="max-width:900px;margin:0 auto 20px;"><?= e($post['title']) ?></h1>
        <div style="display:flex;align-items:center;justify-content:center;gap:20px;flex-wrap:wrap;color:var(--pb-gray);font-size:0.92rem;margin-bottom:40px;">
            <span><i class="far fa-calendar"></i> <?= format_date($post['published_at'] ?? $post['created_at'], 'F j, Y') ?></span>
            <?php if ($post['author_name']): ?><span><i class="fas fa-user"></i> <?= e($post['author_name']) ?></span><?php endif; ?>
            <span><i class="far fa-eye"></i> <?= (int)($post['views'] ?? 0) ?> views</span>
            <?php if ($post['tags']): ?><span><i class="fas fa-tags"></i> <?= e(mb_substr($post['tags'], 0, 40)) ?></span><?php endif; ?>
        </div>
    </div>
</section>

<section class="section section-tight" style="padding-top:0;">
    <div class="container-xl">
        <div class="row justify-content-center g-5">
            <div class="col-lg-9">
                <?php if ($post['featured_image']): ?>
                <div style="border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-lg);margin-bottom:40px;">
                    <img src="<?= asset_url('uploads/'.$post['featured_image']) ?>" alt="<?= e($post['title']) ?>" style="width:100%;height:clamp(280px,45vw,500px);object-fit:cover;">
                </div>
                <?php endif; ?>

                <div class="post-content" style="background:white;padding:50px;border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);">
                    <?= $post['content'] ?>
                </div>

                <?php if ($post['tags']): ?>
                <div style="margin-top:30px;">
                    <?php foreach (array_filter(array_map('trim', explode(',', $post['tags']))) as $t): ?>
                        <span class="trait-chip" style="margin:4px;"><i class="fas fa-tag"></i> <?= e($t) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($post['author_name']): ?>
                <div style="margin-top:40px;padding:36px;background:white;border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);display:flex;gap:24px;align-items:center;flex-wrap:wrap;">
                    <img src="<?= $post['author_image'] ? asset_url('uploads/'.$post['author_image']) : 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&q=80' ?>" alt="<?= e($post['author_name']) ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:4px solid var(--pb-mint);">
                    <div style="flex:1;min-width:200px;">
                        <h4 style="margin-bottom:4px;"><?= e($post['author_name']) ?></h4>
                        <p style="color:var(--pb-teal);font-weight:600;font-size:0.88rem;margin-bottom:8px;"><i class="fas fa-feather-pointed"></i> Author &amp; Team Member</p>
                        <p style="color:var(--pb-gray);font-size:0.9rem;margin:0;">Passionate advocate for animals in need. Writes about animal care, rescue stories, and adoption tips.</p>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-top:30px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button onclick="sharePost()" class="btn btn-outline btn-sm"><i class="fas fa-share-nodes"></i> Share</button>
                        <a href="<?= url('blog.php') ?>" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back to Blog</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($related)): ?>
        <div class="pb-section-title mt-5">
            <span class="eyebrow">Keep Reading</span>
            <h2>Related <em>Articles</em></h2>
        </div>
        <div class="blog-grid" style="grid-template-columns:repeat(3,1fr);gap:28px;">
            <?php foreach ($related as $r): ?>
            <div class="blog-card" onclick="window.location='<?= url('post.php?slug='.$r['slug']) ?>'">
                <div class="blog-img">
                    <img src="<?= $r['featured_image'] ? asset_url('uploads/'.$r['featured_image']) : 'https://placehold.co/600x400/0B632A/fff?text=📝' ?>" alt="<?= e($r['title']) ?>" loading="lazy">
                </div>
                <div class="blog-body">
                    <span class="blog-cat"><?= e($r['cat'] ?? 'Blog') ?></span>
                    <h3 style="font-size:1.25rem;line-height:1.35;"><?= e($r['title']) ?></h3>
                    <div class="blog-meta">
                        <span><i class="far fa-calendar"></i> <?= format_date($r['published_at'] ?? '', 'M j, Y') ?></span>
                        <a href="<?= url('post.php?slug='.$r['slug']) ?>" style="color:var(--pb-primary);font-weight:600;">Read →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function sharePost() {
    const url = window.location.href;
    const text = document.title;
    if (navigator.share) { navigator.share({title:text, text:text, url:url}).catch(()=>{}); }
    else { navigator.clipboard.writeText(url).then(()=>alert('Link copied! Share this article with your friends 💚')); }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
