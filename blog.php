<?php
$pageMetaTitle = 'Blog - Animal Care Tips, Adoption Guides & News';
$pageMetaDesc = 'Animal care tips, adoption guides, rescue updates, and helpful resources from the PawBridge Rescue team.';
require_once __DIR__ . '/includes/header.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;
$catId = (int)($_GET['cat'] ?? 0);
$search = $_GET['q'] ?? '';

$where = ["bp.status = 'Published'"];
$params = [];
if ($catId > 0) { $where[] = "bp.category_id = ?"; $params[] = $catId; }
if ($search) { $where[] = "(bp.title LIKE ? OR bp.content LIKE ? OR bp.excerpt LIKE ?)"; $p = "%$search%"; $params[] = $p; $params[] = $p; $params[] = $p; }
$whereSql = implode(' AND ', $where);

$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM blog_posts bp WHERE $whereSql", $params)['c'] ?? 0);
$posts = pdo_fetch_all("SELECT bp.*, bc.name AS category_name, a.full_name AS author_name
    FROM blog_posts bp
    LEFT JOIN blog_categories bc ON bc.id = bp.category_id
    LEFT JOIN admins a ON a.id = bp.author_id
    WHERE $whereSql
    ORDER BY bp.featured DESC, COALESCE(bp.published_at, bp.created_at) DESC
    LIMIT $perPage OFFSET $offset", $params);

$featured = $catId === 0 && $search === '' && $page === 1 ? ($posts[0] ?? null) : null;
if ($featured) $posts = array_slice($posts, 1);

$categories = pdo_fetch_all("SELECT bc.id, bc.name, bc.slug, (SELECT COUNT(*) FROM blog_posts WHERE category_id = bc.id AND status='Published') AS cnt FROM blog_categories bc ORDER BY bc.name LIMIT 12");
?>
<section class="page-hero">
    <span class="ph-eyebrow" style="background:#DBEAFE;color:#1E40AF;">Tips &amp; Updates</span>
    <h1>The <em style="color:var(--pb-teal);font-style:italic;">PawBridge</em> Blog</h1>
    <p>Helpful animal care tips, adoption guides, rescue news, and heartwarming stories from our community.</p>
</section>

<section class="section section-tight" style="padding-top:40px;">
    <div class="container-xl">
        <form method="GET" class="animal-filter-bar" style="margin-bottom:40px;">
            <input type="search" name="q" class="form-control" placeholder="Search articles..." value="<?= e($search) ?>" style="max-width:360px;flex:2;">
            <select name="cat" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?> (<?= (int)$c['cnt'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <a href="<?= url('blog.php') ?>" class="btn btn-secondary">Reset</a>
        </form>

        <?php if ($featured): ?>
        <div class="blog-grid" style="margin-bottom:50px;">
            <div class="blog-card" onclick="window.location='<?= url('post.php?slug='.$featured['slug']) ?>'" style="grid-column:1/-1;">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="blog-img" style="height:100%;min-height:380px;">
                            <img src="<?= $featured['featured_image'] ? asset_url('uploads/'.$featured['featured_image']) : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=1000&q=80' ?>" alt="<?= e($featured['title']) ?>" loading="eager">
                        </div>
                    </div>
                    <div class="col-md-6" style="display:flex;align-items:center;">
                        <div class="blog-body" style="padding:50px;">
                            <span class="blog-cat"><i class="fas fa-star" style="color:var(--pb-accent);margin-right:4px;"></i> Featured <?= $featured['category_name'] ? ' • '.e($featured['category_name']) : '' ?></span>
                            <h3 style="font-size:2.2rem;line-height:1.2;"><?= e($featured['title']) ?></h3>
                            <p class="blog-excerpt" style="font-size:1rem;"><?= e($featured['excerpt'] ?: mb_substr(strip_tags($featured['content']), 0, 240).'...') ?></p>
                            <div class="blog-meta" style="padding-top:20px;display:flex;justify-content:space-between;align-items:center;">
                                <span><i class="far fa-calendar"></i> <?= format_date($featured['published_at'] ?? $featured['created_at'], 'F j, Y') ?><?= $featured['author_name'] ? ' • by '.e($featured['author_name']) : '' ?></span>
                                <a href="<?= url('post.php?slug='.$featured['slug']) ?>" style="color:var(--pb-primary);font-weight:700;">Read Article <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:30px;">
            <a href="<?= url('blog.php') ?>" class="btn btn-sm btn-outline" style="<?= !$catId ? 'background:var(--pb-primary);color:white;border-color:var(--pb-primary);' : '' ?>">All Posts</a>
            <?php foreach ($categories as $c): ?>
                <a href="<?= url('blog.php?cat='.$c['id']) ?>" class="btn btn-sm btn-outline" style="<?= $catId === (int)$c['id'] ? 'background:var(--pb-primary);color:white;border-color:var(--pb-primary);' : '' ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($posts)): ?>
        <div class="blog-grid" style="grid-template-columns:repeat(3,1fr);gap:28px;">
            <?php foreach ($posts as $p): ?>
            <div class="blog-card" onclick="window.location='<?= url('post.php?slug='.$p['slug']) ?>'">
                <div class="blog-img">
                    <img src="<?= $p['featured_image'] ? asset_url('uploads/'.$p['featured_image']) : 'https://placehold.co/600x400/0B632A/fff?text=📝' ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                </div>
                <div class="blog-body">
                    <span class="blog-cat"><?= e($p['category_name'] ?? 'News') ?></span>
                    <h3 style="font-size:1.35rem;line-height:1.35;"><?= e($p['title']) ?></h3>
                    <p class="blog-excerpt"><?= e($p['excerpt'] ?: mb_substr(strip_tags($p['content']), 0, 140).'...') ?></p>
                    <div class="blog-meta">
                        <span><i class="far fa-calendar"></i> <?= format_date($p['published_at'] ?? $p['created_at'], 'M j, Y') ?></span>
                        <a href="<?= url('post.php?slug='.$p['slug']) ?>" style="color:var(--pb-primary);font-weight:600;">Read →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($posts) && !$featured): ?>
            <div class="text-center py-5" style="background:white;border-radius:var(--radius-xl);padding:60px;">
                <i class="fas fa-blog fa-4x mb-3" style="color:var(--pb-teal);opacity:0.3;"></i>
                <h4 style="margin-bottom:12px;">No articles found</h4>
                <p style="color:var(--pb-gray);">Check back soon for new posts!</p>
            </div>
        <?php endif; ?>

        <div style="margin-top:60px;">
            <?= paginate($page, $perPage, $total, 'blog.php') ?>
        </div>
    </div>
</section>

<section class="section donate-cta" style="background:var(--pb-navy);">
    <div class="container-xl">
        <h2>Stay in the <em style="color:var(--pb-mint);font-style:italic;">Know</em></h2>
        <p>Subscribe to our newsletter for the latest rescue updates, care tips, and adoption announcements — straight to your inbox.</p>
        <div class="donate-cta-buttons">
            <a href="<?= url('contact.php') ?>" class="btn btn-lg" style="background:var(--pb-teal);color:white;">
                <i class="fas fa-envelope"></i> Contact Us
            </a>
            <a href="<?= url('stories.php') ?>" class="btn btn-accent btn-lg">
                <i class="fas fa-book-heart"></i> Read Rescue Stories
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
