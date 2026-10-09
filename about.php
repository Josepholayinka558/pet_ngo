<?php
$pageMetaTitle = 'About Us';
$pageMetaDesc = 'Learn about PawBridge Rescue - our story, mission, values, team, and the impact we make in the lives of animals.';
require_once __DIR__ . '/includes/header.php';

$team = pdo_fetch_all("SELECT * FROM team_members WHERE status = 1 ORDER BY is_founder DESC, sort_order, id LIMIT 12");
$stats = pdo_fetch_all("SELECT * FROM statistics ORDER BY sort_order, id LIMIT 6");
$values = [
    ['icon' => 'fa-heart', 'title' => 'Compassion First', 'desc' => 'Every decision we make puts the welfare and happiness of animals first.'],
    ['icon' => 'fa-shield-halved', 'title' => 'Commitment to Safety', 'desc' => 'Rigorous protocols ensure every animal receives proper care, vetting, and placement.'],
    ['icon' => 'fa-handshake', 'title' => 'Transparency', 'desc' => 'Open about our operations, finances, and how donations save lives.'],
    ['icon' => 'fa-users', 'title' => 'Community Focused', 'desc' => 'Building a network of fosters, volunteers, and adopters who share our vision.'],
    ['icon' => 'fa-lightbulb', 'title' => 'Continuous Learning', 'desc' => 'Staying current on best practices for animal welfare and rescue.'],
    ['icon' => 'fa-seedling', 'title' => 'Sustainable Impact', 'desc' => 'Creating long-term solutions, not just temporary fixes for animals in need.'],
];
?>
<section class="page-hero">
    <span class="ph-eyebrow">Our Story</span>
    <h1>Who We <em style="color:var(--pb-teal);font-style:italic;">Are</em></h1>
    <p>PawBridge Rescue is a modern, volunteer-powered animal welfare organization dedicated to bridging the gap between animals in danger and loving, forever homes.</p>
</section>

<section class="section">
    <div class="container-xl">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=900&q=80" alt="Team of volunteers with rescue dogs" class="img-fluid" style="border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);" loading="lazy">
            </div>
            <div class="col-lg-6">
                <span class="eyebrow d-inline-block mb-3 px-4 py-2 rounded-pill" style="background:var(--pb-mint);color:var(--pb-primary);font-size:0.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Our Journey</span>
                <h2 style="margin-bottom:24px;">A Passion That Became <em style="color:var(--pb-teal);font-style:italic;">a Mission</em></h2>
                <p style="font-size:1.1rem;margin-bottom:18px;">Founded by a group of animal lovers who saw too many animals left behind, PawBridge Rescue began as a small network of fosters and has grown into the life-changing organization it is today.</p>
                <p style="margin-bottom:18px;">From emergency transports to long-term rehabilitation, adoption matchmaking to community education — every part of what we do is rooted in one simple belief: <strong style="color:var(--pb-primary);">every paw deserves a second chance.</strong></p>
                <p>What started with rescuing a handful of animals each year has grown to helping hundreds annually, with partnerships across vet clinics, transport networks, and foster homes throughout the region.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:var(--pb-soft-bg);">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow">What Drives Us</span>
            <h2>Mission, <em>Vision</em> &amp; Values</h2>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="detail-info-card" style="background:var(--pb-gradient);color:white;height:100%;">
                    <div style="width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:20px;"><i class="fas fa-bullseye"></i></div>
                    <h3 style="color:white;margin-bottom:14px;">Our Mission</h3>
                    <p style="color:rgba(255,255,255,0.88);font-size:1.05rem;line-height:1.8;margin:0;"><?= e(setting('mission_text', 'Every animal deserves safety, care, and a chance to thrive. We rescue, transport, rehabilitate, and rehome animals at risk while advocating for a kinder world.')) ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detail-info-card" style="height:100%;">
                    <div style="width:64px;height:64px;border-radius:18px;background:var(--pb-mint);display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:20px;color:var(--pb-primary);"><i class="fas fa-eye"></i></div>
                    <h3 style="margin-bottom:14px;">Our Vision</h3>
                    <p style="font-size:1.05rem;line-height:1.8;margin:0;color:var(--pb-dark-gray);">A world where no animal is left behind — where every community embraces adoption, fostering, and compassionate care as the norm, and where every shelter pet is simply a pet waiting for their person.</p>
                </div>
            </div>
        </div>

        <div class="values-grid">
            <?php foreach ($values as $v): ?>
            <div class="value-card">
                <div class="value-icon"><i class="fas <?= $v['icon'] ?>"></i></div>
                <h4><?= $v['title'] ?></h4>
                <p><?= $v['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section impact-section" id="impact">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow" style="background:rgba(6,75,32,0.08);color:var(--pb-primary);">Our Impact</span>
            <h2>Together, We've <em>Moved Mountains</em></h2>
            <p>These numbers tell only part of the story. Behind every statistic is a life transformed, a family made complete, and an animal given hope again.</p>
        </div>
        <div class="impact-grid">
            <?php
            if (!empty($stats)) foreach ($stats as $s): ?>
            <div class="impact-stat">
                <div class="impact-icon"><i class="fas <?= e($s['icon'] ?? 'fa-heart') ?>"></i></div>
                <span class="impact-num" data-count="<?= e($s['stat_value']) ?>"><?= e($s['stat_value']) ?></span>
                <div class="impact-label"><?= e($s['stat_label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" id="team">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow">The Humans Behind It All</span>
            <h2>Meet Our <em>Incredible Team</em></h2>
            <p>From directors to fosters, every single person at PawBridge is driven by a shared love for animals and commitment to the cause.</p>
        </div>

        <?php if (!empty($team)): ?>
        <div class="team-grid">
            <?php foreach ($team as $m): ?>
            <div class="team-member">
                <img src="<?= $m['photo'] ? asset_url('uploads/'.$m['photo']) : 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80' ?>" alt="<?= e($m['name']) ?>" class="tm-photo" loading="lazy">
                <?php if (!empty($m['is_founder'])): ?>
                    <span class="badge badge-warning mb-2" style="background:#FEF3C7;color:#92400E;"><i class="fas fa-crown"></i> Founder</span>
                <?php endif; ?>
                <h4><?= e($m['name']) ?></h4>
                <div class="tm-role"><?= e($m['position'] ?? 'Team Member') ?></div>
                <?php if ($m['bio']): ?>
                <p><?= e(mb_substr($m['bio'], 0, 130)) ?><?= strlen($m['bio']) > 130 ? '...' : '' ?></p>
                <?php endif; ?>
                <div class="tm-socials">
                    <?php if ($m['email']): ?><a href="mailto:<?= e($m['email']) ?>"><i class="fas fa-envelope"></i></a><?php endif; ?>
                    <?php if ($m['facebook']): ?><a href="<?= e($m['facebook']) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                    <?php if ($m['linkedin']): ?><a href="<?= e($m['linkedin']) ?>" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                    <?php if ($m['instagram']): ?><a href="<?= e($m['instagram']) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5" style="color:var(--pb-gray);">
            <i class="fas fa-user-group fa-4x mb-3" style="opacity:0.15;"></i>
            <p>Team profiles coming soon.</p>
        </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="<?= url('volunteer.php') ?>" class="btn btn-primary btn-lg">
                Join Our Team <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="section donate-cta" style="background:var(--pb-navy);">
    <div class="container-xl">
        <h2>Ready to Make <em style="color:var(--pb-accent);font-style:italic;">a Difference?</em></h2>
        <p>There are so many ways to support our mission. Whether it's adopting, fostering, volunteering or donating — every action ripples outward.</p>
        <div class="donate-cta-buttons">
            <a href="<?= url('animals.php') ?>" class="btn btn-lg" style="background:var(--pb-teal);color:white;">
                <i class="fas fa-paw"></i> Browse Adoptable Pets
            </a>
            <a href="<?= url('donate.php') ?>" class="btn btn-accent btn-lg">
                <i class="fas fa-heart"></i> Support Our Work
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
