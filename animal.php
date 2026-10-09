<?php
$id = (int)($_GET['id'] ?? 0);
if (!$id) { redirect('animals.php'); }

$animal = pdo_fetch("SELECT * FROM animals WHERE id = ?", [$id]);
if (!$animal) { redirect('animals.php'); }

$images = pdo_fetch_all("SELECT * FROM animal_images WHERE animal_id = ? ORDER BY is_primary DESC, sort_order, id", [$id]);

$pageMetaTitle = $animal['name'] . ' - ' . ($animal['breed'] ?? $animal['species']);
$pageMetaDesc = mb_substr(strip_tags($animal['description'] ?? $animal['personality'] ?? 'Meet '.$animal['name'].' at PawBridge Rescue.'), 0, 160);
require_once __DIR__ . '/includes/header.php';

$ageStr = trim(($animal['age_years'] ? $animal['age_years'].' years ' : '').($animal['age_months'] ? $animal['age_months'].' months' : ($animal['age_years'] ? '' : 'Young')));

$statusBadge = [
    'Available' => ['class' => 'success', 'bg' => '#D1FAE5', 'fg' => '#065F46'],
    'Adoption Pending' => ['class' => 'warning', 'bg' => '#FEF3C7', 'fg' => '#92400E'],
    'Adopted' => ['class' => 'info', 'bg' => '#DBEAFE', 'fg' => '#1E40AF'],
    'Fostered' => ['class' => 'primary', 'bg' => '#DDF7E2', 'fg' => '#064B20'],
    'Medical Hold' => ['class' => 'danger', 'bg' => '#FEE2E2', 'fg' => '#991B1B'],
    'Rescued' => ['class' => 'secondary', 'bg' => '#E5E7EB', 'fg' => '#374151'],
];
$sb = $statusBadge[$animal['status']] ?? $statusBadge['Available'];

if (is_post() && isset($_POST['submit_adoption'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid security token.');
    } else {
        $data = [
            'animal_id' => $id,
            'full_name' => clean_input($_POST['full_name']),
            'email' => clean_input($_POST['email']),
            'phone' => clean_input($_POST['phone']),
            'address' => clean_input($_POST['address'] ?? ''),
            'city' => clean_input($_POST['city'] ?? ''),
            'country' => clean_input($_POST['country'] ?? ''),
            'preferred_animal' => $animal['name'],
            'housing_type' => clean_input($_POST['housing_type'] ?? ''),
            'own_rent' => clean_input($_POST['own_rent'] ?? ''),
            'landlord_permission' => isset($_POST['landlord_permission']) ? 1 : 0,
            'household_members' => (int)($_POST['household_members'] ?? 0),
            'household_children' => isset($_POST['household_children']) ? 1 : 0,
            'children_ages' => clean_input($_POST['children_ages'] ?? ''),
            'existing_pets' => isset($_POST['existing_pets']) ? 1 : 0,
            'existing_pets_details' => clean_input($_POST['existing_pets_details'] ?? ''),
            'previous_experience' => clean_input($_POST['previous_experience'] ?? ''),
            'reason_for_adoption' => clean_input($_POST['reason_for_adoption'] ?? ''),
            'hours_alone' => clean_input($_POST['hours_alone'] ?? ''),
            'additional_info' => clean_input($_POST['additional_info'] ?? ''),
            'status' => 'New',
        ];
        if (empty($data['full_name']) || empty($data['email']) || empty($data['phone'])) {
            flash('error', 'Please fill in all required fields.');
        } elseif (!validate_email($data['email'])) {
            flash('error', 'Please enter a valid email address.');
        } else {
            pdo_insert('adoption_applications', $data);
            flash('success', 'Thank you! Your adoption application for ' . $animal['name'] . ' has been submitted. We will contact you soon!');
            redirect('animal.php?id=' . $id . '#applied');
        }
    }
}

$img = function($row) {
    return $row ? asset_url('uploads/'.$row['image_path']) : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=800&q=80';
};
$mainImg = $img($images[0] ?? null);
$otherImgs = array_slice($images, 1, 4);
while (count($otherImgs) < 4) { $otherImgs[] = null; }
?>
<section class="page-hero" style="padding-bottom:40px;">
    <div class="container-xl">
        <div class="breadcrumb" style="justify-content:center;">
            <a href="<?= url('index.php') ?>"><i class="fas fa-house"></i> Home</a>
            <span>/</span>
            <a href="<?= url('animals.php') ?>">Adoptable Animals</a>
            <span>/</span>
            <span style="color:var(--pb-navy);"><?= e($animal['name']) ?></span>
        </div>
        <span class="ph-eyebrow" style="background:<?= $sb['bg'] ?>;color:<?= $sb['fg'] ?>;"><?= e($animal['status']) ?></span>
        <h1><?= e($animal['name']) ?></h1>
        <p><?= e($animal['breed'] ?? $animal['species']) ?> • <?= e($ageStr) ?> • <?= e($animal['gender']) ?> • <?= e($animal['location'] ?? 'In Foster Care') ?></p>
    </div>
</section>

<section class="section section-tight" style="padding-top:30px;">
    <div class="container-xl">
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="animal-detail-gallery">
                    <div class="g-main"><img src="<?= $mainImg ?>" alt="<?= e($animal['name']) ?>" style="border-radius:var(--radius-xl);" loading="eager"></div>
                    <?php foreach ($otherImgs as $i => $im): ?>
                        <div class="g-sm"><img src="<?= $img($im) ?>" alt="<?= e($animal['name']) ?> photo <?= $i+2 ?>" loading="lazy"></div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top:40px;">
                    <h3 style="margin-bottom:18px;">About <?= e($animal['name']) ?></h3>
                    <div style="background:white;border-radius:var(--radius-lg);padding:36px;box-shadow:var(--shadow-sm);">
                        <?php if ($animal['personality']): ?>
                        <h5 style="color:var(--pb-teal);margin-bottom:10px;"><i class="fas fa-heart"></i> Personality</h5>
                        <p style="margin-bottom:28px;line-height:1.8;"><?= nl2br(e($animal['personality'])) ?></p>
                        <?php endif; ?>

                        <?php if ($animal['description']): ?>
                        <h5 style="color:var(--pb-primary);margin-bottom:10px;"><i class="fas fa-circle-info"></i> Description</h5>
                        <p style="margin-bottom:28px;line-height:1.8;"><?= nl2br(e($animal['description'])) ?></p>
                        <?php endif; ?>

                        <?php if ($animal['rescue_story']): ?>
                        <h5 style="color:var(--pb-accent);margin-bottom:10px;"><i class="fas fa-book-heart"></i> Rescue Story</h5>
                        <p style="line-height:1.8;"><?= nl2br(e($animal['rescue_story'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="detail-info-card" style="position:sticky;top:100px;">
                    <h3 style="margin-bottom:6px;"><?= e($animal['name']) ?></h3>
                    <span class="badge mb-4" style="background:<?= $sb['bg'] ?>;color:<?= $sb['fg'] ?>;padding:8px 18px;border-radius:50px;font-weight:700;"><?= e($animal['status']) ?></span>

                    <div class="info-grid">
                        <div class="info-item"><span class="info-label">Species</span><span class="info-value"><?= e($animal['species']) ?></span></div>
                        <div class="info-item"><span class="info-label">Breed</span><span class="info-value"><?= e($animal['breed'] ?? 'Mixed') ?></span></div>
                        <div class="info-item"><span class="info-label">Age</span><span class="info-value"><?= e($ageStr) ?></span></div>
                        <div class="info-item"><span class="info-label">Gender</span><span class="info-value"><?= e($animal['gender']) ?></span></div>
                        <div class="info-item"><span class="info-label">Size</span><span class="info-value"><?= e($animal['size'] ?? 'Medium') ?></span></div>
                        <div class="info-item"><span class="info-label">Weight</span><span class="info-value"><?= $animal['weight'] ? e($animal['weight']).' kg' : 'Unknown' ?></span></div>
                        <div class="info-item"><span class="info-label">Color</span><span class="info-value"><?= e($animal['color'] ?? 'Various') ?></span></div>
                        <div class="info-item"><span class="info-label">Location</span><span class="info-value"><?= e($animal['location'] ?? 'Foster Home') ?></span></div>
                    </div>

                    <div style="margin:8px 0 22px;">
                        <?php if ($animal['vaccinated']): ?><span class="trait-chip"><i class="fas fa-syringe"></i> Vaccinated</span>
                        <?php else: ?><span class="trait-chip no"><i class="fas fa-syringe"></i> Not Vaccinated</span><?php endif; ?>
                        <?php if ($animal['spayed_neutered']): ?><span class="trait-chip"><i class="fas fa-heart"></i> Spayed/Neutered</span>
                        <?php else: ?><span class="trait-chip no"><i class="fas fa-heart"></i> Not Fixed</span><?php endif; ?>
                    </div>
                    <div style="margin-bottom:28px;">
                        <?php if ($animal['good_with_children'] === 1): ?><span class="trait-chip"><i class="fas fa-child"></i> Good with Kids</span>
                        <?php elseif ($animal['good_with_children'] === 0): ?><span class="trait-chip no"><i class="fas fa-child"></i> No Young Kids</span><?php endif; ?>
                        <?php if ($animal['good_with_other_animals'] === 1): ?><span class="trait-chip"><i class="fas fa-cat"></i> Good with Pets</span>
                        <?php elseif ($animal['good_with_other_animals'] === 0): ?><span class="trait-chip no"><i class="fas fa-cat"></i> Only Pet</span><?php endif; ?>
                    </div>

                    <a href="#adopt-form" class="btn btn-primary btn-lg w-100 justify-content-center mb-2">
                        <i class="fas fa-file-heart"></i> Apply to Adopt <?= e($animal['name']) ?>
                    </a>
                    <?php if ($animal['status'] === 'Available'): ?>
                    <button type="button" class="btn btn-secondary btn-lg w-100 justify-content-center" onclick="shareAnimal()">
                        <i class="fas fa-share-nodes"></i> Share <?= e($animal['name']) ?>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($animal['status'] === 'Available' || $animal['status'] === 'Adoption Pending'): ?>
<section class="section" style="background:var(--pb-soft-bg);" id="adopt-form">
    <div class="container-xl" id="applied">
        <?= display_flash() ?>
        <div class="pb-section-title">
            <span class="eyebrow">Make It Official</span>
            <h2>Apply to Adopt <em><?= e($animal['name']) ?></em></h2>
            <p>We're thrilled you're considering <?= e($animal['name']) ?>! Fill out the form below and our adoption team will get back to you within 2-3 business days.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="form-card">
                    <h3 style="margin-bottom:8px;">Adoption Application</h3>
                    <p style="color:var(--pb-gray);margin-bottom:32px;">Fields marked with <span style="color:var(--pb-danger);">*</span> are required</p>
                    <form method="POST" class="needs-validation" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="animal_id" value="<?= $id ?>">

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span style="color:var(--pb-danger);">*</span></label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span style="color:var(--pb-danger);">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span style="color:var(--pb-danger);">*</span></label>
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

                        <h4 style="margin:28px 0 16px;font-size:1.2rem;">Your Home</h4>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Housing Type</label>
                                <select name="housing_type" class="form-select">
                                    <option value="">Select...</option>
                                    <option>House</option><option>Apartment</option><option>Condo</option>
                                    <option>Townhouse</option><option>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Own or Rent?</label>
                                <select name="own_rent" class="form-select">
                                    <option value="">Select...</option>
                                    <option>Own</option><option>Rent</option><option>Other</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="landlord_permission" id="lp">
                                    <label class="form-check-label" for="lp">I have landlord permission to keep pets (if renting)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Number of Household Members</label>
                                <input type="number" name="household_members" class="form-control" min="0" value="1">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="household_children" id="hc">
                                    <label class="form-check-label" for="hc">Children in the household</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Children ages (if applicable)</label>
                                <input type="text" name="children_ages" class="form-control" placeholder="e.g. 4, 7, 12">
                            </div>
                        </div>

                        <h4 style="margin:28px 0 16px;font-size:1.2rem;">Pet Experience</h4>
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="existing_pets" id="ep">
                                    <label class="form-check-label" for="ep">I currently have other pets</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Tell us about your current pets</label>
                                <textarea name="existing_pets_details" class="form-control" rows="2" placeholder="Types, breeds, ages, how long you've had them..."></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Previous animal experience</label>
                                <textarea name="previous_experience" class="form-control" rows="3" placeholder="Past pets, fostering, volunteering, etc."></textarea>
                            </div>
                        </div>

                        <h4 style="margin:28px 0 16px;font-size:1.2rem;">About <?= e($animal['name']) ?> & You</h4>
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Why do you want to adopt <?= e($animal['name']) ?>? <span style="color:var(--pb-danger);">*</span></label>
                                <textarea name="reason_for_adoption" class="form-control" rows="4" required placeholder="Tell us what made you fall in love with this sweet soul..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hours pet will be alone on an average day</label>
                                <input type="text" name="hours_alone" class="form-control" placeholder="e.g. 4-6 hours">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Anything else we should know?</label>
                                <textarea name="additional_info" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div style="padding:22px;background:var(--pb-mint);border-radius:var(--radius-md);margin-bottom:24px;">
                            <p style="margin:0;font-size:0.9rem;color:var(--pb-primary);font-weight:500;">
                                <i class="fas fa-circle-info" style="margin-right:8px;"></i>
                                By submitting this application, you agree that PawBridge Rescue may contact you to discuss your application and may conduct a home visit or reference check as part of the adoption process.
                            </p>
                        </div>

                        <button type="submit" name="submit_adoption" class="btn btn-primary btn-lg w-100 justify-content-center">
                            <i class="fas fa-paper-plane"></i> Submit Adoption Application
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section" style="background:white;">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow">More Friends</span>
            <h2>Other <em>Animals</em> You Might Love</h2>
            <p>These adorable souls are also looking for their forever families.</p>
        </div>
        <?php
        $related = pdo_fetch_all("SELECT a.*, (SELECT image_path FROM animal_images WHERE animal_id = a.id AND is_primary = 1 LIMIT 1) AS image
            FROM animals a WHERE a.id != ? AND a.status IN ('Available','Adoption Pending') ORDER BY RAND() LIMIT 3", [$id]);
        if (!empty($related)): ?>
        <div class="animals-grid">
            <?php foreach ($related as $a):
                $rim = $a['image'] ? asset_url('uploads/'.$a['image']) : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600&q=80';
                $ageS = trim(($a['age_years'] ? $a['age_years'].'yr ' : '').($a['age_months'] ? $a['age_months'].'mo' : ($a['age_years'] ? '' : 'Young')));
            ?>
            <div class="animal-tile" onclick="window.location='<?= url('animal.php?id='.$a['id']) ?>'">
                <div class="animal-tile-img">
                    <img src="<?= $rim ?>" alt="<?= e($a['name']) ?>" loading="lazy">
                    <div class="at-status" style="background:<?= $a['status']==='Available'?'#D1FAE5;color:#065F46;':'#FEF3C7;color:#92400E;' ?>"><?= e($a['status']) ?></div>
                </div>
                <div class="animal-tile-body">
                    <h3><?= e($a['name']) ?></h3>
                    <div class="at-breed"><?= e($a['breed'] ?? $a['species']) ?> • <?= e($a['gender']) ?></div>
                    <div class="at-tags"><span class="at-tag"><i class="fas fa-birthday-cake"></i> <?= $ageS ?></span></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="text-center mt-5">
            <a href="<?= url('animals.php') ?>" class="btn btn-outline btn-lg">See All Animals <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<script>
function shareAnimal() {
    const url = window.location.href;
    const text = "Check out <?= e($animal['name']) ?> at PawBridge Rescue! 🐾";
    if (navigator.share) { navigator.share({title:document.title, text:text, url:url}).catch(()=>{}); }
    else { navigator.clipboard.writeText(url).then(()=>alert('Link copied to clipboard! Share it with your friends 💚')); }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
