<?php
$pageMetaTitle = 'Events - PawBridge Rescue Adoption Days, Fundraisers & More';
$pageMetaDesc = 'Join us at upcoming adoption events, fundraisers, community outreach programs, and volunteer orientations.';
require_once __DIR__ . '/includes/header.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$filter = $_GET['filter'] ?? 'upcoming';
$today = date('Y-m-d');

if ($filter === 'past') {
    $where = "status = 'Published' AND event_date < ?";
    $order = "event_date DESC";
    $params = [$today];
} else {
    $where = "status = 'Published' AND event_date >= ?";
    $order = "featured DESC, event_date ASC, event_time ASC";
    $params = [$today];
}

$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM events WHERE $where", $params)['c'] ?? 0);
$events = pdo_fetch_all("SELECT * FROM events WHERE $where ORDER BY $order LIMIT $perPage OFFSET $offset", $params);
?>
<section class="page-hero">
    <span class="ph-eyebrow" style="background:#DBEAFE;color:#1E40AF;">Come Say Hello</span>
    <h1>Upcoming <em style="color:var(--pb-teal);font-style:italic;">Events</em></h1>
    <p>Join us for adoption days, fundraisers, volunteer orientations, and community events. Meet our animals, meet the team, and be part of the PawBridge family.</p>
</section>

<section class="section section-tight" style="padding-top:40px;">
    <div class="container-xl">
        <div style="display:flex;justify-content:center;gap:12px;margin-bottom:40px;flex-wrap:wrap;">
            <a href="<?= url('events.php?filter=upcoming') ?>" class="btn btn-sm btn-outline" style="<?= $filter !== 'past' ? 'background:var(--pb-primary);color:white;border-color:var(--pb-primary);' : '' ?>"><i class="fas fa-calendar-day"></i> Upcoming</a>
            <a href="<?= url('events.php?filter=past') ?>" class="btn btn-sm btn-outline" style="<?= $filter === 'past' ? 'background:var(--pb-primary);color:white;border-color:var(--pb-primary);' : '' ?>"><i class="fas fa-calendar-check"></i> Past Events</a>
        </div>

        <?php if (empty($events)): ?>
            <div class="text-center py-5" style="background:white;border-radius:var(--radius-xl);padding:60px;">
                <i class="fas fa-calendar-xmark fa-4x mb-3" style="color:var(--pb-teal);opacity:0.3;"></i>
                <h4 style="margin-bottom:12px;">No <?= $filter === 'past' ? 'past' : 'upcoming' ?> events</h4>
                <p style="color:var(--pb-gray);">Check back soon for new <?= $filter === 'past' ? 'event recaps' : 'events' ?>!</p>
                <a href="<?= url('blog.php') ?>" class="btn btn-outline mt-2">See News & Blog</a>
            </div>
        <?php else: ?>

        <div class="events-grid">
            <?php foreach ($events as $e):
                $isPast = strtotime($e['event_date']) < strtotime($today);
            ?>
            <div class="event-card">
                <div class="event-img">
                    <img src="<?= $e['image'] ? asset_url('uploads/'.$e['image']) : 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=800&q=80' ?>" alt="<?= e($e['event_name']) ?>" loading="lazy">
                    <div class="event-date-badge">
                        <span class="day"><?= date('j', strtotime($e['event_date'])) ?></span>
                        <span class="month"><?= date('M', strtotime($e['event_date'])) ?></span>
                    </div>
                    <?php if (!empty($e['featured'])): ?>
                        <span style="position:absolute;top:18px;right:18px;background:var(--pb-accent);color:white;padding:5px 14px;border-radius:50px;font-size:0.7rem;font-weight:700;letter-spacing:1px;"><i class="fas fa-star"></i> FEATURED</span>
                    <?php endif; ?>
                </div>
                <div class="event-body">
                    <?php if ($e['category']): ?>
                        <span class="blog-cat" style="margin-bottom:10px;"><?= e($e['category']) ?></span>
                    <?php endif; ?>
                    <h4 style="line-height:1.35;margin-bottom:14px;"><?= e($e['event_name']) ?></h4>
                    <div class="event-meta">
                        <?php if ($e['event_date']): ?><span><i class="far fa-calendar"></i> <?= date('l, F j, Y', strtotime($e['event_date'])) ?></span><?php endif; ?>
                        <?php if ($e['event_time']): ?><span><i class="far fa-clock"></i> <?= date('g:i A', strtotime($e['event_time'])) ?><?= $e['end_time'] ? ' - '.date('g:i A', strtotime($e['end_time'])) : '' ?></span><?php endif; ?>
                        <?php if ($e['location']): ?><span><i class="fas fa-location-dot"></i> <?= e(mb_substr($e['location'], 0, 60)) ?><?= strlen($e['location']) > 60 ? '...' : '' ?></span><?php endif; ?>
                        <?php if ($e['max_attendees']): ?><span><i class="fas fa-users"></i> Max <?= (int)$e['max_attendees'] ?> attendees</span><?php endif; ?>
                    </div>
                    <?php if ($e['description']): ?>
                        <p style="margin:16px 0;color:var(--pb-gray);font-size:0.9rem;line-height:1.6;"><?= e(mb_substr(strip_tags($e['description']), 0, 160)) ?><?= strlen($e['description']) > 160 ? '...' : '' ?></p>
                    <?php endif; ?>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <?php if (!$isPast && $e['registration_url']): ?>
                            <a href="<?= e($e['registration_url']) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                                Register Now <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($e['location']): ?>
                            <a href="https://www.google.com/maps/search/<?= urlencode($e['location']) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                                <i class="fas fa-map-location-dot"></i> Map
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:50px;">
            <?= paginate($page, $perPage, $total, 'events.php') ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section donate-cta" style="background:var(--pb-mint);">
    <div class="container-xl">
        <h2 style="color:var(--pb-primary);">Can't Make <em style="color:var(--pb-navy);font-style:italic;">an Event?</em></h2>
        <p style="color:var(--pb-dark-gray);">You can still make a life-changing difference from anywhere in the world. Adopt, foster, donate, or volunteer today.</p>
        <div class="donate-cta-buttons">
            <a href="<?= url('animals.php') ?>" class="btn btn-lg" style="background:var(--pb-gradient);color:white;">
                <i class="fas fa-paw"></i> Meet Adoptable Pets
            </a>
            <a href="<?= url('donate.php') ?>" class="btn btn-accent btn-lg">
                <i class="fas fa-heart"></i> Make a Donation
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
