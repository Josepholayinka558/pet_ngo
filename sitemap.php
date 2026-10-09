<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$base = $protocol . '://' . $host . $path;

$today = date('Y-m-d');
$staticPages = [
    ['', '1.0', 'daily'],
    ['about.php', '0.9', 'weekly'],
    ['animals.php', '1.0', 'daily'],
    ['stories.php', '0.9', 'daily'],
    ['events.php', '0.8', 'weekly'],
    ['blog.php', '0.9', 'daily'],
    ['foster.php', '0.8', 'monthly'],
    ['volunteer.php', '0.8', 'monthly'],
    ['donate.php', '0.9', 'weekly'],
    ['contact.php', '0.7', 'monthly'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($staticPages as $p): ?>
<url>
  <loc><?= $base ?><?= $p[0] ? '/' . $p[0] : '' ?></loc>
  <lastmod><?= $today ?></lastmod>
  <changefreq><?= $p[2] ?></changefreq>
  <priority><?= $p[1] ?></priority>
</url>
<?php endforeach; ?>

<?php
try {
    $animals = pdo_fetch_all("SELECT id, updated_at FROM animals WHERE status IN ('Available','Adoption Pending','Adopted')");
    foreach ($animals as $a) {
        echo "<url>\n  <loc>{$base}/animal.php?id=" . (int)$a['id'] . "</loc>\n  <lastmod>" . date('Y-m-d', strtotime($a['updated_at'])) . "</lastmod>\n  <changefreq>weekly</changefreq>\n  <priority>0.8</priority>\n</url>\n";
    }
    $stories = pdo_fetch_all("SELECT slug, updated_at FROM rescue_stories WHERE status='Published'");
    foreach ($stories as $s) {
        echo "<url>\n  <loc>{$base}/story.php?slug=" . e($s['slug']) . "</loc>\n  <lastmod>" . date('Y-m-d', strtotime($s['updated_at'])) . "</lastmod>\n  <changefreq>monthly</changefreq>\n  <priority>0.7</priority>\n</url>\n";
    }
    $posts = pdo_fetch_all("SELECT slug, updated_at FROM blog_posts WHERE status='Published'");
    foreach ($posts as $p) {
        echo "<url>\n  <loc>{$base}/post.php?slug=" . e($p['slug']) . "</loc>\n  <lastmod>" . date('Y-m-d', strtotime($p['updated_at'])) . "</lastmod>\n  <changefreq>monthly</changefreq>\n  <priority>0.6</priority>\n</url>\n";
    }
} catch (Exception $e) {}
?>
</urlset>
