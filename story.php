<?php
$slug = $_GET['slug'] ?? '';
if (!$slug) { redirect('stories.php'); }

$story = pdo_fetch("SELECT rs.*, a.name AS animal_name, a.id AS animal_id
    FROM rescue_stories rs LEFT JOIN animals a ON a.id = rs.animal_id
    WHERE rs.slug = ?", [$slug]);
if (!$story) { redirect('stories.php'); }

if ($story['status'] !== 'Published' && !is_admin_logged_in()) {
    redirect('stories.php');
}

try {
    pdo_update('rescue_stories', ['views' => ($story['views'] ?? 0) + 1], 'id = ?', [$story['id']]);
} catch (Exception $e) {}

$gallery = pdo_fetch_all("SELECT * FROM rescue_story_images WHERE story_id = ? ORDER BY sort_order, id", [$story['id']]);

$related = pdo_fetch_all("SELECT id, title, slug, excerpt, featured_image, published_at, category
    FROM rescue_stories WHERE status = 'Published' AND id != ?
    ORDER BY RAND() LIMIT 3", [$story['id']]);

$pageMetaTitle = !empty($story['seo_title']) ? $story['seo_title'] : $story['title'];
$pageMetaDesc = !empty($story['seo_description']) ? $story['seo_description'] : mb_substr(strip_tags($story['excerpt'] ?? $story['content']), 0, 160);
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero" style="padding-bottom:0;">
    <div class="container-xl">
        <div class="breadcrumb" style="justify-content:center;">
            <a href="<?= url('index.php') ?>"><i class="fas fa-house"></i> Home</a>
            <span>/</span>
            <a href="<?= url('stories.php') ?>">Rescue Stories</a>
            <span>/</span>
            <span style="color:var(--pb-navy);"><?= e(mb_substr($story['title'], 0, 40)) ?><?= strlen($story['title']) > 40 ? '...' : '' ?></span>
        </div>
        <span class="ph-eyebrow" style="background:var(--pb-mint);color:var(--pb-primary);"><?= e($story['category'] ?? 'Rescue Story') ?></span>
        <h1><?= e($story['title']) ?></h1>
        <p style="margin-bottom:40px;">
            <?php if (!empty($story['rescue_date'])): ?>
                <i class="fas fa-calendar"></i> Rescued <?= format_date($story['rescue_date'], 'F j, Y') ?>
            <?php endif; ?>
            <?php if ($story['animal_name']): ?>
                &nbsp; • &nbsp; <i class="fas fa-paw"></i> <a href="<?= url('animal.php?id='.$story['animal_id']) ?>" style="color:var(--pb-primary);"><?= e($story['animal_name']) ?></a>
            <?php endif; ?>
            <?php if (!empty($story['location'])): ?>
                &nbsp; • &nbsp; <i class="fas fa-location-dot"></i> <?= e($story['location']) ?>
            <?php endif; ?>
            &nbsp; • &nbsp; <i class="fas fa-eye"></i> <?= (int)($story['views'] ?? 0) ?> views
        </p>
    </div>
</section>

<section class="section section-tight" style="padding-top:10px;">
    <div class="container-xl">
        <div class="row justify-content-center g-5">
            <div class="col-lg-10">
                <div style="border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-lg);margin-bottom:40px;">
                    <img src="<?= $story['featured_image'] ? asset_url('uploads/'.$story['featured_image']) : 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=1400&q=80' ?>" alt="<?= e($story['title']) ?>" style="width:100%;height:clamp(300px,50vw,560px);object-fit:cover;">
                </div>

                <div class="post-content" style="background:white;padding:50px;border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);">
                    <?= $story['content'] ?>
                </div>

                <?php if (!empty($gallery)): ?>
                <div style="margin-top:40px;">
                    <h3 style="margin-bottom:24px;text-align:center;"><i class="fas fa-images" style="color:var(--pb-teal);"></i> Photo Gallery</h3>
                    <div class="animal-detail-gallery" style="grid-template-columns:repeat(4,1fr);grid-template-rows:repeat(2, 200px);gap:12px;">
                        <?php foreach ($gallery as $i => $g): ?>
                            <div style="<?= $i === 0 ? 'grid-column:1/3;grid-row:1/3;' : '' ?>">
                                <img src="<?= asset_url('uploads/'.$g['image_path']) ?>" alt="<?= e($g['caption'] ?? 'Story photo '.($i+1)) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius-md);">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($story['animal_id'] && $story['animal_name']): ?>
                <div style="margin-top:50px;padding:40px;background:var(--pb-mint-gradient);border-radius:var(--radius-xl);text-align:center;">
                    <h3 style="margin-bottom:10px;"><i class="fas fa-paw" style="color:var(--pb-teal);"></i> Is This Sweet Soul Still Looking?</h3>
                    <p style="margin-bottom:24px;max-width:500px;margin-left:auto;margin-right:auto;">Check if <?= e($story['animal_name']) ?> is still waiting for their forever home, or meet other animals just like them.</p>
                    <a href="<?= url('animal.php?id='.$story['animal_id']) ?>" class="btn btn-primary btn-lg"><i class="fas fa-heart"></i> Meet <?= e($story['animal_name']) ?></a>
                </div>
                <?php endif; ?>

                <div style="margin-top:40px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button onclick="shareStory()" class="btn btn-outline btn-sm"><i class="fas fa-share-nodes"></i> Share Story</button>
                        <a href="<?= url('stories.php') ?>" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> All Stories</a>
                    </div>
                    <div style="color:var(--pb-gray);font-size:0.9rem;">
                        Published: <?= format_date($story['published_at'] ?? $story['created_at']) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($related)): ?>
        <div class="pb-section-title mt-5">
            <span class="eyebrow">More Tails of Hope</span>
            <h2>Other <em>Stories</em> You'll Love</h2>
        </div>
        <div class="blog-grid" style="grid-template-columns:repeat(3,1fr);gap:28px;">
            <?php foreach ($related as $s): ?>
            <div class="blog-card" onclick="window.location='<?= url('story.php?slug='.$s['slug']) ?>'">
                <div class="blog-img">
                    <img src="<?= $s['featured_image'] ? asset_url('uploads/'.$s['featured_image']) : 'https://placehold.co/600x400/0B632A/fff?text=🐾' ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                </div>
                <div class="blog-body">
                    <span class="blog-cat"><?= e($s['category'] ?? 'Story') ?></span>
                    <h3 style="font-size:1.3rem;"><?= e($s['title']) ?></h3>
                    <p class="blog-excerpt"><?= e($s['excerpt'] ?: mb_substr(strip_tags($s['content'] ?? ''), 0, 120).'...') ?></p>
                    <div class="blog-meta">
                        <span><i class="far fa-calendar"></i> <?= format_date($s['published_at'] ?? '', 'M j, Y') ?></span>
                        <a href="<?= url('story.php?slug='.$s['slug']) ?>" style="color:var(--pb-primary);font-weight:600;">Read →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function shareStory() {
    const url = window.location.href;
    const text = "Read this amazing rescue story from PawBridge Rescue! 💚🐾";
    if (navigator.share) { navigator.share({title:document.title, text:text, url:url}).catch(()=>{}); }
    else { navigator.clipboard.writeText(url).then(()=>alert('Story link copied! Share it to spread the hope 💚')); }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
