<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    session_start();
}

$pageMetaTitle = isset($pageMetaTitle) ? $pageMetaTitle : '';
$pageMetaDesc = isset($pageMetaDesc) ? $pageMetaDesc : '';
$bodyClass = isset($bodyClass) ? $bodyClass : '';

global $pdo;
try {
    $navLinks = pdo_fetch_all("SELECT * FROM navigation_links WHERE menu_location = 'main' AND is_active = 1 ORDER BY sort_order");
} catch (Exception $e) { $navLinks = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= seo_title($pageMetaTitle) ?></title>
<meta name="description" content="<?= seo_description($pageMetaDesc) ?>">
<link rel="canonical" href="<?= current_url() ?>">
<meta property="og:title" content="<?= seo_title($pageMetaTitle) ?>">
<meta property="og:description" content="<?= seo_description($pageMetaDesc) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= current_url() ?>">
<meta property="og:locale" content="en_US">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= seo_title($pageMetaTitle) ?>">
<meta name="twitter:description" content="<?= seo_description($pageMetaDesc) ?>">
<link rel="icon" href="<?= setting('site_favicon') ? asset_url('uploads/'.setting('site_favicon')) : asset_url('images/favicon.svg') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="<?= asset_url('css/main.css') ?>" rel="stylesheet">
<script>
(function(){
  var c1 = '<?= setting('color_primary','#064B20') ?>';
  var c2 = '<?= setting('color_secondary','#0B632A') ?>';
  var c3 = '<?= setting('color_mint','#DDF7E2') ?>';
  var c4 = '<?= setting('color_teal','#59B9B0') ?>';
  var c5 = '<?= setting('color_navy','#102238') ?>';
  var c6 = '<?= setting('color_accent','#F59E42') ?>';
  var c7 = '<?= setting('color_soft_bg','#F5FAF5') ?>';
  var s = document.documentElement.style;
  s.setProperty('--pb-primary', c1);
  s.setProperty('--pb-secondary', c2);
  s.setProperty('--pb-mint', c3);
  s.setProperty('--pb-teal', c4);
  s.setProperty('--pb-navy', c5);
  s.setProperty('--pb-accent', c6);
  s.setProperty('--pb-soft-bg', c7);
  s.setProperty('--pb-gradient', 'linear-gradient(135deg,'+c1+' 0%, '+c2+' 100%)');
  s.setProperty('--pb-mint-gradient', 'linear-gradient(135deg, '+c3+' 0%, '+c7+' 100%)');
  s.setProperty('--pb-teal-gradient', 'linear-gradient(135deg, '+c4+' 0%, '+c2+' 100%)');
})();
</script>
</head>
<body class="<?= e($bodyClass) ?>">

<nav class="pb-navbar">
  <div class="pb-nav-inner">
    <a href="<?= url('index.php') ?>" class="pb-logo">
      <div class="pb-logo-icon"><i class="fas fa-paw"></i></div>
      <div class="pb-logo-text">
        <span class="pb-logo-name"><?= e(setting('site_name', 'PawBridge Rescue')) ?></span>
        <span class="pb-logo-tag">Rescue • Love • Home</span>
      </div>
    </a>
    <ul class="pb-nav-links" id="navLinks">
      <?php foreach($navLinks as $l): ?>
        <li><a href="<?= url($l['url']) ?>" class="<?= is_active(explode('?', basename($l['url']))[0]) ?>"><?= e($l['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
    <div class="pb-nav-actions">
      <button class="pb-search-btn" title="Search" aria-label="Search"><i class="fas fa-search"></i></button>
      <a href="<?= url('donate.php') ?>" class="pb-donate-btn"><i class="fas fa-heart"></i> Donate</a>
      <button class="hamburger" id="hamburger" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<main>
<?= display_flash() ?>
