<?php
$pageMetaTitle = '';
$pageMetaDesc = '';
require_once __DIR__ . '/includes/header.php';

$heroImg1 = setting('hero_image_primary') ? asset_url('uploads/'.setting('hero_image_primary')) : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=800&q=80';
$heroImg2 = setting('hero_image_secondary') ? asset_url('uploads/'.setting('hero_image_secondary')) : 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=600&q=80';
$heroImg3 = setting('hero_image_tertiary') ? asset_url('uploads/'.setting('hero_image_tertiary')) : 'https://images.unsplash.com/photo-1537151625747-768eb6cf92b2?w=500&q=80';

$stats = pdo_fetch_all("SELECT * FROM statistics WHERE display_on_home = 1 ORDER BY sort_order, id LIMIT 4");

$featuredAnimals = pdo_fetch_all("SELECT a.*,
    (SELECT image_path FROM animal_images WHERE animal_id = a.id AND is_primary = 1 LIMIT 1) AS image
    FROM animals a WHERE a.featured = 1 AND a.status IN ('Available','Adoption Pending')
    ORDER BY a.id DESC LIMIT 4");

if (count($featuredAnimals) < 3) {
    $featuredAnimals = pdo_fetch_all("SELECT a.*,
        (SELECT image_path FROM animal_images WHERE animal_id = a.id AND is_primary = 1 LIMIT 1) AS image
        FROM animals a WHERE a.status IN ('Available','Adoption Pending')
        ORDER BY a.id DESC LIMIT 4");
}

$rescueStory = pdo_fetch("SELECT rs.*, a.name AS animal_name FROM rescue_stories rs
    LEFT JOIN animals a ON a.id = rs.animal_id
    WHERE rs.status = 'Published' AND rs.featured = 1 ORDER BY COALESCE(rs.published_at, rs.created_at) DESC LIMIT 1");

if (!$rescueStory) {
    $rescueStory = pdo_fetch("SELECT rs.*, a.name AS animal_name FROM rescue_stories rs
        LEFT JOIN animals a ON a.id = rs.animal_id
        WHERE rs.status = 'Published' ORDER BY COALESCE(rs.published_at, rs.created_at) DESC LIMIT 1");
}

$stories = [];
if ($rescueStory) {
    $stories = pdo_fetch_all("SELECT rs.id, rs.title, rs.slug, rs.excerpt, rs.featured_image, rs.published_at, rs.category
        FROM rescue_stories rs WHERE rs.status = 'Published' AND rs.id != ?
        ORDER BY COALESCE(rs.published_at, rs.created_at) DESC LIMIT 3", [$rescueStory['id']]);
}

$homeStats = $stats;
$activitiesSection = pdo_fetch("SELECT * FROM homepage_sections WHERE section_key = 'activities' LIMIT 1");
$bridgeSection = pdo_fetch("SELECT * FROM homepage_sections WHERE section_key = 'bridge' LIMIT 1");
$getInvolvedSection = pdo_fetch("SELECT * FROM homepage_sections WHERE section_key = 'get_involved' LIMIT 1");

$activities = $activitiesSection && $activitiesSection['content'] ? json_decode($activitiesSection['content'], true) : null;
$bridge = $bridgeSection && $bridgeSection['content'] ? json_decode($bridgeSection['content'], true) : null;
$involved = $getInvolvedSection && $getInvolvedSection['content'] ? json_decode($getInvolvedSection['content'], true) : null;

if (!$activities) {
    $activities = ['blocks' => [
        ['title' => 'RESCUE', 'description' => 'Animals in danger and urgent need.', 'icon' => 'fa-shield-dog'],
        ['title' => 'FOSTER', 'description' => 'Providing temporary safe and loving homes.', 'icon' => 'fa-house-chimney-heart'],
        ['title' => 'ADOPT', 'description' => 'Helping animals find their forever families.', 'icon' => 'fa-heart'],
        ['title' => 'SUPPORT', 'description' => 'Helping us continue saving lives.', 'icon' => 'fa-hand-holding-dollar'],
    ]];
}
if (!$bridge) {
    $bridge = ['steps' => [
        ['title' => 'Danger', 'icon' => 'fa-triangle-exclamation', 'description' => 'Animals facing immediate danger or neglect.'],
        ['title' => 'Rescue', 'icon' => 'fa-truck-medical', 'description' => 'Our team steps in to bring them to safety.'],
        ['title' => 'Care', 'icon' => 'fa-stethoscope', 'description' => 'Medical care, food, and rehabilitation.'],
        ['title' => 'Foster', 'icon' => 'fa-house', 'description' => 'Temporary loving foster homes.'],
        ['title' => 'Forever Home', 'icon' => 'fa-heart-circle-check', 'description' => 'Placed with their perfect family.'],
    ]];
}
if (!$involved) {
    $involved = ['blocks' => [
        ['title' => 'ADOPT', 'description' => 'Give an animal a forever home.', 'link' => 'animals.php'],
        ['title' => 'FOSTER', 'description' => 'Give an animal a safe place to heal.', 'link' => 'foster.php'],
        ['title' => 'VOLUNTEER', 'description' => 'Give your time and skills.', 'link' => 'volunteer.php'],
        ['title' => 'DONATE', 'description' => 'Help us rescue more animals.', 'link' => 'donate.php'],
    ]];
}

$upcomingEvents = pdo_fetch_all("SELECT * FROM events WHERE status = 'Published' AND event_date >= CURDATE()
    ORDER BY event_date ASC LIMIT 3");

$animalImg = function($row) {
    return $row['image'] ? asset_url('uploads/'.$row['image']) : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600&q=80';
};
?>

<section class="pb-hero">
    <span class="paw-decor paw-d1"><i class="fas fa-paw"></i></span>
    <span class="paw-decor paw-d2"><i class="fas fa-paw"></i></span>
    <span class="paw-decor paw-d3"><i class="fas fa-paw"></i></span>
    <span class="paw-decor paw-d4"><i class="fas fa-paw"></i></span>

    <div class="container-xl pb-hero-inner">
        <div class="pb-hero-content">
            <div class="pb-hero-heading">
                <h1>
                    <?= e(setting('hero_heading_line1', 'Every Paw')) ?><br>
                    <span class="line2"><?= e(setting('hero_heading_line2', 'Deserves a')) ?></span>
                    <span class="line3"><?= e(setting('hero_heading_line3', 'Second Chance')) ?></span>
                </h1>
            </div>

            <div class="pb-hero-animals">
                <div class="pb-float-card pb-float-1" style="background:white;">
                    <div class="icon" style="background:var(--pb-mint);color:var(--pb-primary);"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="num" style="color:var(--pb-primary);">24/7</div>
                        <div class="lbl">Rescue Support</div>
                    </div>
                </div>

                <div class="pb-float-card pb-float-2" style="background:white;">
                    <div class="icon" style="background:#FEF3C7;color:var(--pb-accent);"><i class="fas fa-paw"></i></div>
                    <div>
                        <div class="num" style="color:var(--pb-navy);">
                            <?php
                            $s500 = pdo_fetch("SELECT stat_value FROM statistics WHERE stat_key = 'animals_rescued'");
                            echo e($s500['stat_value'] ?? '500+');
                            ?>
                        </div>
                        <div class="lbl">Animals Helped</div>
                    </div>
                </div>

                <div class="pb-float-card pb-float-3" style="background:white;">
                    <div class="icon" style="background:#DBFEFE;color:var(--pb-teal);"><i class="fas fa-heart"></i></div>
                    <div>
                        <div class="num" style="font-size:1.1rem;color:var(--pb-navy);font-weight:700;">Forever Home</div>
                        <div class="lbl">Find a Match Today</div>
                    </div>
                </div>

                <img src="<?= e($heroImg1) ?>" alt="Happy rescue dog with owner" class="pb-animal-img pb-animal-center" loading="eager">
                <img src="<?= e($heroImg2) ?>" alt="Cute cat waiting for adoption" class="pb-animal-img pb-animal-left" loading="lazy">
                <img src="<?= e($heroImg3) ?>" alt="Happy puppy playing" class="pb-animal-img pb-animal-right" loading="lazy">
            </div>

            <div class="pb-hero-sub">
                <p><?= e(setting('hero_description', 'We rescue, transport and rehome animals at risk, helping them move from danger to safety.')) ?></p>
            </div>

            <div class="pb-hero-ctas">
                <a href="<?= url(setting('hero_cta1_link', 'animals.php')) ?>" class="btn btn-primary btn-lg">
                    <?= e(setting('hero_cta1_text', 'Adopt a Pet')) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= url(setting('hero_cta2_link', 'donate.php')) ?>" class="btn btn-secondary btn-lg">
                    <i class="fas fa-heart"></i>
                    <?= e(setting('hero_cta2_text', 'Help Us Rescue')) ?>
                </a>
                <a href="#stories" class="circle-btn" title="Watch Stories">
                    <i class="fas fa-play"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:white;">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow">What We Do</span>
            <h2>Our <em>Mission</em> in Action</h2>
            <p><?= e(setting('mission_text', 'Every animal deserves safety, care, and a chance to thrive. We work tirelessly across four core areas.')) ?></p>
        </div>

        <div class="activities-grid">
            <?php
            $actColors = [
                ['bg' => '#DDF7E2', 'fg' => '#064B20'],
                ['bg' => '#DBFEFE', 'fg' => '#59B9B0'],
                ['bg' => '#FEF3C7', 'fg' => '#F59E42'],
                ['bg' => '#DBEAFE', 'fg' => '#102238'],
            ];
            foreach ($activities['blocks'] as $i => $b):
                $c = $actColors[$i % 4]; ?>
            <div class="activity-card">
                <div class="activity-icon" style="background:<?= $c['bg'] ?>;color:<?= $c['fg'] ?>;">
                    <i class="fas <?= e($b['icon'] ?? 'fa-heart') ?>"></i>
                </div>
                <h3><?= e($b['title']) ?></h3>
                <p><?= e($b['description']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section animals-section">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow">Meet Them</span>
            <h2>Meet the <em>Animals</em> Waiting for You</h2>
            <p>These sweet souls are looking for their forever families. Could you be the one?</p>
        </div>

        <?php if (!empty($featuredAnimals)): ?>
        <div class="animals-showcase">
            <?php
            $first = $featuredAnimals[0] ?? null;
            $rest = array_slice($featuredAnimals, 1, 3);
            if ($first): ?>
            <div class="animal-card featured" onclick="window.location='<?= url('animal.php?id='.$first['id']) ?>'">
                <img src="<?= $animalImg($first) ?>" alt="<?= e($first['name']) ?> - <?= e($first['species']) ?>" class="ac-img">
                <div class="ac-info">
                    <span class="ac-badge">Featured</span>
                    <div class="ac-name"><?= e($first['name']) ?></div>
                    <div class="ac-meta">
                        <?= e($first['breed'] ?? $first['species']) ?>
                        • <?= ($first['age_years'] ? $first['age_years'].'y ' : '').($first['age_months'] ? $first['age_months'].'m' : ($first['age_years'] ? '' : 'Young')) ?>
                        • <?= e($first['gender']) ?>
                    </div>
                    <span class="ac-cta">Ready for adoption <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
            <?php endif;
            foreach ($rest as $a):
                $img2 = $animalImg($a); ?>
            <div class="animal-card" onclick="window.location='<?= url('animal.php?id='.$a['id']) ?>'">
                <img src="<?= $img2 ?>" alt="<?= e($a['name']) ?>" class="ac-img">
                <div class="ac-info">
                    <?php if ($a['status'] === 'Adoption Pending'): ?>
                    <span class="ac-badge" style="background:var(--pb-secondary);">Pending</span>
                    <?php endif; ?>
                    <div class="ac-name"><?= e($a['name']) ?></div>
                    <div class="ac-meta">
                        <?= e($a['species']) ?><?= $a['breed'] ? ' • '.e($a['breed']) : '' ?>
                    </div>
                    <span class="ac-cta">Meet <?= e($a['name']) ?> <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="<?= url('animals.php') ?>" class="btn btn-outline btn-lg">
                See All Animals <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="section bridge-section">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow" style="background:rgba(89,185,176,0.18);color:var(--pb-teal);">The Bridge</span>
            <h2 style="color:white;">From <em style="color:var(--pb-accent);">Danger</em> <span style="color:var(--pb-teal);">→</span> To <em style="color:var(--pb-mint);">Safety</em></h2>
            <p style="color:rgba(255,255,255,0.7);">This is the journey every animal takes with PawBridge. From their first call for help to their loving forever home.</p>
        </div>

        <div class="bridge-journey">
            <?php foreach ($bridge['steps'] as $step): ?>
            <div class="bridge-step">
                <div class="bridge-step-icon">
                    <i class="fas <?= e($step['icon'] ?? 'fa-heart') ?>"></i>
                </div>
                <div>
                    <h4><?= e($step['title']) ?></h4>
                    <p><?= e($step['description']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section stories-section" id="stories">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow">Success Stories</span>
            <h2>Every Rescue Has a <em>Story</em></h2>
            <p>These are the tails of hope, healing, and happy endings. Read how far our animals have come.</p>
        </div>

        <div class="stories-grid">
            <?php if ($rescueStory): ?>
            <div class="story-featured" onclick="window.location='<?= url('story.php?slug='.$rescueStory['slug']) ?>'">
                <img src="<?= $rescueStory['featured_image'] ? asset_url('uploads/'.$rescueStory['featured_image']) : 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=1000&q=80' ?>" alt="<?= e($rescueStory['title']) ?>" loading="lazy">
                <div class="sf-overlay">
                    <span class="sf-tag"><?= e($rescueStory['category'] ?? 'Rescue Story') ?></span>
                    <h3><?= e($rescueStory['title']) ?></h3>
                    <p><?= e($rescueStory['excerpt'] ?: mb_substr(strip_tags($rescueStory['content']), 0, 180).'...') ?></p>
                    <a class="read-story-btn" href="<?= url('story.php?slug='.$rescueStory['slug']) ?>">
                        Read Their Story <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <div class="story-side">
                <?php foreach ($stories as $s): ?>
                <div class="story-sm" onclick="window.location='<?= url('story.php?slug='.$s['slug']) ?>'">
                    <img src="<?= $s['featured_image'] ? asset_url('uploads/'.$s['featured_image']) : 'https://placehold.co/140x140/0B632A/fff?text='.urlencode('Story') ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                    <div>
                        <span class="meta-tag"><?= e($s['category'] ?? 'Story') ?></span>
                        <h4><?= e($s['title']) ?></h4>
                        <p><?= e($s['excerpt'] ?: mb_substr(strip_tags($s['content'] ?? ''), 0, 100)) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <a href="<?= url('stories.php') ?>" class="btn btn-outline btn-lg w-100 justify-content-center mt-2">
                    Read All Stories <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section impact-section">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow" style="background:rgba(6,75,32,0.08);color:var(--pb-primary);">Our Impact</span>
            <h2>Making <em>Real</em> Change Happen</h2>
            <p>Every number is a life saved, a heart healed, and a family made whole.</p>
        </div>

        <div class="impact-grid">
            <?php
            $iconsFilled = 0;
            if (!empty($homeStats)) foreach ($homeStats as $s): $iconsFilled++; ?>
            <div class="impact-stat">
                <div class="impact-icon"><i class="fas <?= e($s['icon'] ?? 'fa-heart') ?>"></i></div>
                <span class="impact-num" data-count="<?= e($s['stat_value']) ?>"><?= e($s['stat_value']) ?></span>
                <div class="impact-label"><?= e($s['stat_label']) ?></div>
            </div>
            <?php endforeach;
            $defaultStats = [
                ['label' => 'Animals Rescued', 'value' => '1,200+', 'icon' => 'fa-paw'],
                ['label' => 'Forever Homes Found', 'value' => '850+', 'icon' => 'fa-house-heart'],
                ['label' => 'Foster Families', 'value' => '300+', 'icon' => 'fa-users'],
                ['label' => 'Active Volunteers', 'value' => '120+', 'icon' => 'fa-hand-holding-heart'],
            ];
            for ($i = $iconsFilled; $i < 4; $i++):
                $d = $defaultStats[$i] ?? null; if (!$d) break; ?>
            <div class="impact-stat">
                <div class="impact-icon"><i class="fas <?= $d['icon'] ?>"></i></div>
                <span class="impact-num" data-count="<?= $d['value'] ?>"><?= $d['value'] ?></span>
                <div class="impact-label"><?= $d['label'] ?></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section class="section" style="background:white;">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow">Join Us</span>
            <h2>You Can Be <em>the Bridge</em></h2>
            <p>There are so many ways to help animals in need. Find your perfect way to get involved.</p>
        </div>

        <div class="involve-grid">
            <?php foreach ($involved['blocks'] as $i => $b): ?>
            <div class="involve-card involve-<?= ($i % 4) + 1 ?>" onclick="window.location='<?= url($b['link'] ?? '#') ?>'">
                <div>
                    <h3><?= e($b['title']) ?></h3>
                    <p><?= e($b['description']) ?></p>
                </div>
                <span class="involve-link">
                    <?= e($b['title'] === 'DONATE' ? 'Donate Now' : 'Learn More') ?>
                    <i class="fas fa-arrow-right"></i>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($upcomingEvents)): ?>
<section class="section" style="background:var(--pb-soft-bg);">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow" style="color:var(--pb-accent);background:#FEF3C7;">Upcoming Events</span>
            <h2>Come Say <em>Hello</em></h2>
            <p>Join us at adoption days, fundraisers, and community events.</p>
        </div>
        <div class="events-grid">
            <?php foreach ($upcomingEvents as $ev): ?>
            <div class="event-card">
                <div class="event-img">
                    <img src="<?= $ev['image'] ? asset_url('uploads/'.$ev['image']) : 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=600&q=80' ?>" alt="<?= e($ev['event_name']) ?>" loading="lazy">
                    <div class="event-date-badge">
                        <span class="day"><?= date('j', strtotime($ev['event_date'])) ?></span>
                        <span class="month"><?= date('M', strtotime($ev['event_date'])) ?></span>
                    </div>
                </div>
                <div class="event-body">
                    <h4><?= e($ev['event_name']) ?></h4>
                    <div class="event-meta">
                        <?php if ($ev['event_time']): ?><span><i class="far fa-clock"></i> <?= date('g:i A', strtotime($ev['event_time'])) ?></span><?php endif; ?>
                        <?php if ($ev['location']): ?><span><i class="fas fa-location-dot"></i> <?= e(mb_substr($ev['location'], 0, 40)) ?></span><?php endif; ?>
                    </div>
                    <?php if ($ev['registration_url']): ?>
                    <a href="<?= e($ev['registration_url']) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                        Register <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= url('events.php') ?>" class="btn btn-outline btn-lg">
                View All Events <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section donate-cta">
    <div class="container-xl">
        <h2><?= e(setting('donation_cta_title', 'A Small Act Can <em>Change</em> a Life')) ?></h2>
        <p><?= e(setting('donation_cta_text', 'Your support helps provide rescue, transport, food, medical care and safe homes for animals in need.')) ?></p>
        <div class="donate-cta-buttons">
            <a href="<?= url('donate.php') ?>" class="btn btn-accent btn-lg" style="background:white;color:var(--pb-secondary);box-shadow:0 10px 30px rgba(0,0,0,0.2);">
                <i class="fas fa-heart" style="color:var(--pb-accent);"></i> Donate Now
            </a>
            <a href="<?= url('about.php#impact') ?>" class="btn btn-lg" style="background:rgba(255,255,255,0.15);color:white;border:2px solid rgba(255,255,255,0.4);">
                See How Donations Help <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
