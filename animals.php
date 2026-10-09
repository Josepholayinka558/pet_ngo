<?php
$pageMetaTitle = 'Adoptable Pets';
$pageMetaDesc = 'Browse our adorable adoptable dogs, cats, rabbits and more. Find your perfect forever companion at PawBridge Rescue.';
require_once __DIR__ . '/includes/header.php';

$species = $_GET['species'] ?? '';
$breed = $_GET['breed'] ?? '';
$age = $_GET['age'] ?? '';
$gender = $_GET['gender'] ?? '';
$size = $_GET['size'] ?? '';
$location = $_GET['location'] ?? '';
$status = $_GET['status'] ?? 'Available';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];
if ($species) { $where[] = "species = ?"; $params[] = $species; }
if ($breed) { $where[] = "breed LIKE ?"; $params[] = "%$breed%"; }
if ($gender && $gender !== 'Any') { $where[] = "gender = ?"; $params[] = $gender; }
if ($size && $size !== 'Any') { $where[] = "size = ?"; $params[] = $size; }
if ($status) { $where[] = "status = ?"; $params[] = $status; }
if ($location) { $where[] = "location LIKE ?"; $params[] = "%$location%"; }
if ($search) { $where[] = "(name LIKE ? OR personality LIKE ? OR description LIKE ?)"; $p = "%$search%"; $params[] = $p; $params[] = $p; $params[] = $p; }

if ($age === 'young') { $where[] = "(age_years < 1 OR (age_years = 0 AND age_months > 0))"; }
elseif ($age === 'adult') { $where[] = "age_years BETWEEN 1 AND 7"; }
elseif ($age === 'senior') { $where[] = "age_years >= 8"; }

$whereSql = implode(' AND ', $where);
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM animals WHERE $whereSql", $params)['c'] ?? 0);
$animals = pdo_fetch_all("SELECT a.*,
    (SELECT image_path FROM animal_images WHERE animal_id = a.id AND is_primary = 1 LIMIT 1) AS image
    FROM animals a WHERE $whereSql
    ORDER BY a.featured DESC, a.id DESC
    LIMIT $perPage OFFSET $offset", $params);

$speciesList = ['Dog','Cat','Rabbit','Bird','Other'];
?>
<section class="page-hero">
    <span class="ph-eyebrow">Find Your Match</span>
    <h1>Adoptable <em style="color:var(--pb-teal);font-style:italic;">Animals</em></h1>
    <p>Every single one of these sweet souls is waiting for a loving home. Could you be the one to change their life forever?</p>
</section>

<section class="section section-tight" style="padding-top:50px;">
    <div class="container-xl">
        <form method="GET" class="animal-filter-bar">
            <input type="search" name="search" class="form-control" placeholder="Search name or keyword..." value="<?= e($search) ?>">
            <select name="species" class="form-select">
                <option value="">All Species</option>
                <?php foreach ($speciesList as $s): ?>
                <option value="<?= $s ?>" <?= $species === $s ? 'selected' : '' ?>><?= $s.'s' ?></option>
                <?php endforeach; ?>
            </select>
            <select name="age" class="form-select">
                <option value="">Any Age</option>
                <option value="young" <?= $age === 'young' ? 'selected' : '' ?>>Young (under 1yr)</option>
                <option value="adult" <?= $age === 'adult' ? 'selected' : '' ?>>Adult (1-7yrs)</option>
                <option value="senior" <?= $age === 'senior' ? 'selected' : '' ?>>Senior (8+ yrs)</option>
            </select>
            <select name="gender" class="form-select">
                <option value="Any">Any Gender</option>
                <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
            </select>
            <select name="size" class="form-select">
                <option value="Any">Any Size</option>
                <option value="Small" <?= $size === 'Small' ? 'selected' : '' ?>>Small</option>
                <option value="Medium" <?= $size === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="Large" <?= $size === 'Large' ? 'selected' : '' ?>>Large</option>
                <option value="Extra Large" <?= $size === 'Extra Large' ? 'selected' : '' ?>>XL</option>
            </select>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="Available" <?= $status === 'Available' ? 'selected' : '' ?>>Available</option>
                <option value="Adoption Pending" <?= $status === 'Adoption Pending' ? 'selected' : '' ?>>Pending</option>
            </select>
            <input type="text" name="breed" class="form-control" placeholder="Breed..." value="<?= e($breed) ?>" style="max-width:180px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Find</button>
            <a href="<?= url('animals.php') ?>" class="btn btn-secondary">Reset</a>
        </form>

        <?php if (empty($animals)): ?>
            <div class="text-center py-5" style="background:white;border-radius:var(--radius-xl);padding:60px;">
                <i class="fas fa-dog fa-4x mb-3" style="color:var(--pb-teal);opacity:0.4;"></i>
                <h4 style="margin-bottom:12px;">No animals match your filters</h4>
                <p style="max-width:480px;margin:0 auto 20px;">Try adjusting your search or filters, or <a href="<?= url('animals.php') ?>">see all animals</a> ready for adoption.</p>
                <a href="<?= url('foster.php') ?>" class="btn btn-outline">Become a Foster Home</a>
            </div>
        <?php else: ?>

        <div class="animals-grid">
            <?php foreach ($animals as $a):
                $img = $a['image'] ? asset_url('uploads/'.$a['image']) : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600&q=80';
                $ageStr = trim(($a['age_years'] ? $a['age_years'].' yr ' : '').($a['age_months'] ? $a['age_months'].' mo' : ($a['age_years'] ? '' : 'Young')));
                $statusClass = $a['status'] === 'Available' ? 'success' : 'warning';
            ?>
            <div class="animal-tile" onclick="window.location='<?= url('animal.php?id='.$a['id']) ?>'">
                <div class="animal-tile-img">
                    <img src="<?= $img ?>" alt="<?= e($a['name']) ?> - <?= e($a['breed'] ?? $a['species']) ?>" loading="lazy">
                    <div class="at-status" style="background:<?= $statusClass === 'success' ? '#D1FAE5;color:#065F46;' : '#FEF3C7;color:#92400E;' ?>">
                        <?= e($a['status']) ?>
                    </div>
                    <?php if (!empty($a['featured'])): ?>
                    <div class="at-featured" title="Featured Pet"><i class="fas fa-star"></i></div>
                    <?php endif; ?>
                </div>
                <div class="animal-tile-body">
                    <h3><?= e($a['name']) ?></h3>
                    <div class="at-breed">
                        <?= e($a['breed'] ?? $a['species']) ?>
                        • <?= e($a['gender']) ?>
                    </div>
                    <div class="at-tags">
                        <span class="at-tag"><i class="fas fa-birthday-cake"></i> <?= $ageStr ?></span>
                        <span class="at-tag"><i class="fas fa-weight-scale"></i> <?= e($a['size']) ?></span>
                        <?php if (!empty($a['location'])): ?>
                        <span class="at-tag"><i class="fas fa-location-dot"></i> <?= e(mb_substr($a['location'],0,20)) ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= url('animal.php?id='.$a['id']) ?>" class="btn btn-primary btn-sm w-100 justify-content-center mt-2">
                        Meet <?= e($a['name']) ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:50px;">
            <?= paginate($page, $perPage, $total, 'animals.php') ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section donate-cta" style="background:var(--pb-mint);">
    <div class="container-xl">
        <h2 style="color:var(--pb-primary);">Don't See <em style="color:var(--pb-navy);font-style:italic;">Your Match?</em></h2>
        <p style="color:var(--pb-dark-gray);max-width:600px;">New animals arrive every week! Sign up for our adoption alerts, foster a pet while you wait, or consider donating to help more animals arrive safely.</p>
        <div class="donate-cta-buttons">
            <a href="<?= url('foster.php') ?>" class="btn btn-lg" style="background:var(--pb-gradient);color:white;">
                <i class="fas fa-house-heart"></i> Become a Foster
            </a>
            <a href="<?= url('contact.php') ?>" class="btn btn-lg btn-outline" style="border-color:var(--pb-primary);color:var(--pb-primary);">
                Contact Adoption Team
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
