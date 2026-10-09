<?php
$pageMetaTitle = 'Rescue Stories - Inspiring Tails of Hope & Healing';
$pageMetaDesc = 'Read the inspiring rescue stories of animals we\'ve helped save. From the streets to loving forever homes. Every rescue has a story.';
require_once __DIR__ . '/includes/header.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;
$category = $_GET['cat'] ?? '';

$where = ["status = 'Published'"];
$params = [];
if ($category) { $where[] = "category = ?"; $params[] = $category; }
$whereSql = implode(' AND ', $where);

$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM rescue_stories WHERE $whereSql", $params)['c'] ?? 0);
$stories = pdo_fetch_all("SELECT rs.*, a.name AS animal_name
    FROM rescue_stories rs LEFT JOIN animals a ON a.id = rs.animal_id
    WHERE $whereSql
    ORDER BY rs.featured DESC, COALESCE(rs.published_at, rs.created_at) DESC
    LIMIT $perPage OFFSET $offset", $params);

$featured = (!$category && $page === 1) ? ($stories[0] ?? null) : null;
if ($featured) $stories = array_slice($stories, 1);

$categories = pdo_fetch_all("SELECT DISTINCT category FROM rescue_stories WHERE category IS NOT NULL AND category != '' LIMIT 10");
?>
<section class="page-hero">
    <span class="ph-eyebrow" style="background:var(--pb-mint);color:var(--pb-primary);">Hope &amp; Healing</span>
    <h1>Rescue <em style="color:var(--pb-teal);font-style:italic;">Stories</em></h1>
    <p>Every animal we rescue has a unique story. These are the tales of hope, perseverance, and the transformative power of love and second chances.</p>
</section>

<section class="section section-tight" style="padding-top:40px;">
    <div class="container-xl">
        <?php if (!empty($categories)): ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-bottom:40px;">
            <a href="<?= url('stories.php') ?>" class="btn btn-sm btn-outline" style="<?= !$category ? 'background:var(--pb-primary);color:white;border-color:var(--pb-primary);' : '' ?>">All Stories</a>
            <?php foreach ($categories as $c): ?>
                <a href="<?= url('stories.php?cat='.urlencode($c['category'])) ?>" class="btn btn-sm btn-outline" style="<?= $category === $c['category'] ? 'background:var(--pb-primary);color:white;border-color:var(--pb-primary);' : '' ?>"><?= e($c['category']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($featured): ?>
        <div class="stories-grid" style="margin-bottom:50px;">
            <div class="story-featured" onclick="window.location='<?= url('story.php?slug='.$featured['slug']) ?>'" style="min-height:560px;">
                <img src="<?= $featured['featured_image'] ? asset_url('uploads/'.$featured['featured_image']) : 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=1200&q=80' ?>" alt="<?= e($featured['title']) ?>" loading="eager">
                <div class="sf-overlay">
                    <span class="sf-tag">Featured Story<?= $featured['category'] ? ' • '.e($featured['category']) : '' ?></span>
                    <h3><?= e($featured['title']) ?></h3>
                    <p><?= e($featured['excerpt'] ?: mb_substr(strip_tags($featured['content']), 0, 280).'...') ?></p>
                    <div style="color:rgba(255,255,255,0.65);font-size:0.85rem;margin-bottom:14px;">
                        <?php if (!empty($featured['rescue_date'])): ?>
                            <i class="fas fa-calendar"></i> <?= format_date($featured['rescue_date'], 'F j, Y') ?>
                        <?php endif; ?>
                        <?= $featured['animal_name'] ? ' • <i class="fas fa-paw"></i> '.e($featured['animal_name']) : '' ?>
                    </div>
                    <a class="read-story-btn" href="<?= url('story.php?slug='.$featured['slug']) ?>">Read Their Story <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="story-side">
                <?php for ($i = 0; $i < min(3, count($stories)); $i++): ?>
                    <?php $s = $stories[$i]; ?>
                    <div class="story-sm" onclick="window.location='<?= url('story.php?slug='.$s['slug']) ?>'">
                        <img src="<?= $s['featured_image'] ? asset_url('uploads/'.$s['featured_image']) : 'https://placehold.co/140x140/0B632A/fff?text=🐾' ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                        <div>
                            <span class="meta-tag"><?= e($s['category'] ?? 'Rescue Story') ?></span>
                            <h4><?= e($s['title']) ?></h4>
                            <p><?= e($s['excerpt'] ?: mb_substr(strip_tags($s['content']), 0, 120).'...') ?></p>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php $stories = array_slice($stories, 3); ?>
        <?php endif; ?>

        <?php if (!empty($stories)): ?>
        <div class="blog-grid" style="grid-template-columns:repeat(3,1fr);gap:28px;">
            <?php foreach ($stories as $s): ?>
            <div class="blog-card" onclick="window.location='<?= url('story.php?slug='.$s['slug']) ?>'">
                <div class="blog-img">
                    <img src="<?= $s['featured_image'] ? asset_url('uploads/'.$s['featured_image']) : 'https://placehold.co/600x400/0B632A/fff?text=Story' ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                </div>
                <div class="blog-body">
                    <span class="blog-cat"><?= e($s['category'] ?? 'Rescue Story') ?></span>
                    <h3 style="font-size:1.35rem;"><?= e($s['title']) ?></h3>
                    <p class="blog-excerpt"><?= e($s['excerpt'] ?: mb_substr(strip_tags($s['content']), 0, 150).'...') ?></p>
                    <div class="blog-meta">
                        <span><i class="far fa-calendar"></i> <?= format_date($s['published_at'] ?? $s['created_at'], 'M j, Y') ?></span>
                        <a href="<?= url('story.php?slug='.$s['slug']) ?>" style="color:var(--pb-primary);font-weight:600;">Read →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($stories) && !$featured): ?>
            <div class="text-center py-5" style="background:white;border-radius:var(--radius-xl);padding:60px;">
                <i class="fas fa-book-heart fa-4x mb-3" style="color:var(--pb-teal);opacity:0.3;"></i>
                <h4 style="margin-bottom:12px;">Stories coming soon</h4>
                <p style="color:var(--pb-gray);">Check back soon for inspiring rescue stories!</p>
            </div>
        <?php endif; ?>

        <div style="margin-top:60px;">
            <?= paginate($page, $perPage, $total, 'stories.php') ?>
        </div>
    </div>
</section>

<section class="section donate-cta" style="background:var(--pb-gradient);">
    <div class="container-xl">
        <h2>Your Support <em>Makes</em> Stories Like This <em style="color:#FFE8B8;font-style:italic;">Possible</em></h2>
        <p>Behind every happy ending is a donor, foster, adopter, or volunteer who chose to care. Join us in writing more stories of hope.</p>
        <div class="donate-cta-buttons">
            <a href="<?= url('donate.php') ?>" class="btn btn-accent btn-lg" style="background:white;color:var(--pb-secondary);box-shadow:0 10px 30px rgba(0,0,0,0.2);">
                <i class="fas fa-heart" style="color:var(--pb-accent);"></i> Donate Now
            </a>
            <a href="<?= url('foster.php') ?>" class="btn btn-lg" style="background:rgba(255,255,255,0.15);color:white;border:2px solid rgba(255,255,255,0.4);">
                Become a Foster <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
