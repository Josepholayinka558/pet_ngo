<?php
$pageMetaTitle = 'Volunteer with PawBridge Rescue';
$pageMetaDesc = 'Join our volunteer team. Help rescue animals through transport, animal care, events, photography, social media, fundraising and more.';
require_once __DIR__ . '/includes/header.php';

if (is_post() && isset($_POST['submit_volunteer'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid security token.');
    } else {
        $cats = $_POST['category'] ?? [];
        if (is_array($cats)) $cats = implode(', ', $cats); else $cats = '';
        $data = [
            'full_name' => clean_input($_POST['full_name']),
            'email' => clean_input($_POST['email']),
            'phone' => clean_input($_POST['phone']),
            'address' => clean_input($_POST['address'] ?? ''),
            'city' => clean_input($_POST['city'] ?? ''),
            'country' => clean_input($_POST['country'] ?? ''),
            'category' => is_string($cats) ? $cats : clean_input($cats),
            'skills' => clean_input($_POST['skills'] ?? ''),
            'availability' => clean_input($_POST['availability'] ?? ''),
            'hours_per_week' => (int)($_POST['hours_per_week'] ?? 0),
            'previous_experience' => clean_input($_POST['previous_experience'] ?? ''),
            'additional_info' => clean_input($_POST['additional_info'] ?? ''),
            'status' => 'New',
        ];
        if (empty($data['full_name']) || empty($data['email']) || empty($data['phone'])) {
            flash('error', 'Please fill in all required fields.');
        } elseif (!validate_email($data['email'])) {
            flash('error', 'Please enter a valid email address.');
        } else {
            pdo_insert('volunteers', $data);
            flash('success', 'Thank you for volunteering! We\'ve received your application and our volunteer coordinator will contact you soon.');
            redirect('volunteer.php#applied');
        }
    }
}

$categories = [
    'Animal Care' => ['fa-dog', 'Daily feeding, cleaning, grooming, socialization at our rescue center or foster homes.'],
    'Transport' => ['fa-truck-medical', 'Pick up animals from shelters, vet runs, adoption events, airport transports.'],
    'Fundraising' => ['fa-hand-holding-dollar', 'Plan campaigns, peer-to-peer fundraising, grant research, donation drives.'],
    'Social Media' => ['fa-hashtag', 'Content creation, community management, stories, reels, posts.'],
    'Photography' => ['fa-camera', 'Animal portraits, event photos, behind-the-scenes content.'],
    'Events' => ['fa-calendar-check', 'Adoption days, fundraisers, community outreach, volunteer coordination.'],
    'Administration' => ['fa-file-lines', 'Data entry, application review, email support, volunteer tracking.'],
    'Foster Support' => ['fa-house-chimney-heart', 'Mentor new fosters, drop off supplies, check-ins, transition support.'],
];
?>
<section class="page-hero">
    <span class="ph-eyebrow">Give Your Time</span>
    <h1>Volunteer &amp; <em style="color:var(--pb-teal);font-style:italic;">Change Lives</em></h1>
    <p>Every single person who joins our team makes a tangible difference. Whether you have 1 hour a week or 10, your skills and heart can help save animals.</p>
</section>

<section class="section">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow">Find Your Fit</span>
            <h2>Ways to <em>Get Involved</em></h2>
            <p>From hands-on animal care to behind-the-scenes admin work — every role matters.</p>
        </div>
        <div class="row g-4">
            <?php $idx = 0; foreach ($categories as $name => $c):
                $idx++;
                $bgs = ['var(--pb-gradient)','var(--pb-mint)','var(--pb-navy)','linear-gradient(135deg,var(--pb-accent),#FFB86B)','#DBFEFE','#DBEAFE'];
                $bg = $bgs[$idx % 6];
                $isLight = ($idx === 0 || $idx === 2 || $idx === 3) ? true : false;
            ?>
            <div class="col-md-6 col-lg-3">
                <div style="background:<?= $bg ?>;color:<?= $isLight?'white':'var(--pb-navy)' ?>;border-radius:var(--radius-xl);padding:36px 28px;height:100%;">
                    <div style="width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:22px;">
                        <i class="fas <?= $c[0] ?>"></i>
                    </div>
                    <h3 style="color:<?= $isLight?'white':'var(--pb-primary)' ?>;margin-bottom:10px;font-size:1.25rem;"><?= $name ?></h3>
                    <p style="color:<?= $isLight?'rgba(255,255,255,0.85)':'var(--pb-gray)' ?>;font-size:0.9rem;line-height:1.6;margin:0;"><?= $c[1] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" style="background:var(--pb-soft-bg);">
    <div class="container-xl">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=900&q=80" alt="Volunteers with rescue animals" class="img-fluid" style="border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);">
            </div>
            <div class="col-lg-6">
                <span class="eyebrow d-inline-block mb-3 px-4 py-2 rounded-pill" style="background:var(--pb-teal);color:white;font-size:0.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Our Volunteers</span>
                <h2 style="margin-bottom:24px;">The Heart &amp; <em style="color:var(--pb-teal);font-style:italic;">Hands of Rescue</em></h2>
                <p style="font-size:1.05rem;margin-bottom:18px;">PawBridge Rescue is 100% volunteer-powered. Without our incredible team of animal lovers, none of this would be possible.</p>
                <p style="margin-bottom:18px;">Volunteers help us save over 500 animals each year through their time, dedication, and skills. Whether you're a student, professional, or retiree — there's a place for you on our team.</p>
                <div class="row g-3 mt-4">
                    <div class="col-6"><div style="background:white;padding:22px;border-radius:var(--radius-lg);text-align:center;"><div style="font-family:var(--pb-heading);font-size:2.2rem;font-weight:900;color:var(--pb-primary);">120+</div><div style="color:var(--pb-gray);font-size:0.88rem;font-weight:600;">Active Volunteers</div></div></div>
                    <div class="col-6"><div style="background:white;padding:22px;border-radius:var(--radius-lg);text-align:center;"><div style="font-family:var(--pb-heading);font-size:2.2rem;font-weight:900;color:var(--pb-primary);">10K+</div><div style="color:var(--pb-gray);font-size:0.88rem;font-weight:600;">Hours Given Yearly</div></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="applied">
    <div class="container-xl">
        <?= display_flash() ?>
        <div class="pb-section-title">
            <span class="eyebrow">Join the Team</span>
            <h2>Volunteer <em>Application</em></h2>
            <p>Tell us about yourself and how you'd like to help. We'll reach out to schedule a quick orientation call.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="form-card">
                    <form method="POST" class="needs-validation" novalidate>
                        <?= csrf_field() ?>

                        <h3 style="margin-bottom:20px;">Your Information</h3>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span style="color:var(--pb-danger);">*</span></label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span style="color:var(--pb-danger);">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span style="color:var(--pb-danger);">*</span></label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" placeholder="United States">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Street Address</label>
                                <input type="text" name="address" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                        </div>

                        <h3 style="margin:28px 0 20px;">Areas of Interest</h3>
                        <div class="row g-3 mb-4">
                            <?php foreach (array_keys($categories) as $i => $cat): ?>
                                <div class="col-sm-6 col-md-4">
                                    <div class="form-check" style="padding:12px 16px 12px 42px;background:var(--pb-soft-bg);border-radius:12px;">
                                        <input class="form-check-input" type="checkbox" name="category[]" value="<?= $cat ?>" id="vc<?= $i ?>">
                                        <label class="form-check-label fw-bold" for="vc<?= $i ?>" style="font-size:0.92rem;"><?= $cat ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <h3 style="margin:28px 0 20px;">Your Availability</h3>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">General Availability</label>
                                <select name="availability" class="form-select">
                                    <option value="">Select...</option>
                                    <option>Weekdays (9-5)</option><option>Evenings</option>
                                    <option>Weekends</option><option>Weekdays + Evenings</option>
                                    <option>Weekends + Evenings</option><option>Flexible</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Approximate hours per week</label>
                                <input type="number" name="hours_per_week" class="form-control" min="0" max="60" placeholder="e.g. 5">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Special skills or talents</label>
                                <textarea name="skills" class="form-control" rows="2" placeholder="Web design, graphic design, vet tech background, non-profit experience, marketing..."></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Previous volunteer or animal experience</label>
                                <textarea name="previous_experience" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Anything else you'd like to share?</label>
                                <textarea name="additional_info" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <button type="submit" name="submit_volunteer" class="btn btn-primary btn-lg w-100 justify-content-center">
                            <i class="fas fa-hand-holding-heart"></i> Apply to Volunteer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
