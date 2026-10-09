<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$stats = [
    ['label' => 'Total Animals', 'value' => (int)(pdo_fetch("SELECT COUNT(*) AS c FROM animals")['c'] ?? 0), 'icon' => 'fa-dog', 'color' => '#0B632A', 'bg' => '#DDF7E2'],
    ['label' => 'Available', 'value' => (int)(pdo_fetch("SELECT COUNT(*) AS c FROM animals WHERE status = 'Available'")['c'] ?? 0), 'icon' => 'fa-heart', 'color' => '#10B981', 'bg' => '#D1FAE5'],
    ['label' => 'Adopted', 'value' => (int)(pdo_fetch("SELECT COUNT(*) AS c FROM animals WHERE status = 'Adopted'")['c'] ?? 0), 'icon' => 'fa-house-heart', 'color' => '#59B9B0', 'bg' => '#DBFEFE'],
    ['label' => 'Rescue Stories', 'value' => (int)(pdo_fetch("SELECT COUNT(*) AS c FROM rescue_stories WHERE status = 'Published'")['c'] ?? 0), 'icon' => 'fa-book-heart', 'color' => '#F59E42', 'bg' => '#FEF3C7'],
    ['label' => 'Adoption Apps', 'value' => (int)(pdo_fetch("SELECT COUNT(*) AS c FROM adoption_applications")['c'] ?? 0), 'icon' => 'fa-file-heart', 'color' => '#102238', 'bg' => '#DBEAFE'],
    ['label' => 'Foster Apps', 'value' => (int)(pdo_fetch("SELECT COUNT(*) AS c FROM foster_applications")['c'] ?? 0), 'icon' => 'fa-house-chimney', 'color' => '#064B20', 'bg' => '#DDF7E2'],
    ['label' => 'Volunteers', 'value' => (int)(pdo_fetch("SELECT COUNT(*) AS c FROM volunteers")['c'] ?? 0), 'icon' => 'fa-users', 'color' => '#0EA5E9', 'bg' => '#E0F2FE'],
    ['label' => 'Total Donations', 'value' => '$' . number_format((float)(pdo_fetch("SELECT COALESCE(SUM(amount),0) AS s FROM donations WHERE status = 'Completed'")['s'] ?? 0), 2), 'icon' => 'fa-hand-holding-dollar', 'color' => '#F59E42', 'bg' => '#FFEDD5'],
];

$recentAnimals = pdo_fetch_all("SELECT a.id, a.name, a.species, a.breed, a.status, a.created_at,
    (SELECT image_path FROM animal_images WHERE animal_id = a.id AND is_primary = 1 LIMIT 1) AS image
    FROM animals a ORDER BY a.created_at DESC LIMIT 5");

$recentAdoptionApps = pdo_fetch_all("SELECT aa.*, a.name AS animal_name FROM adoption_applications aa
    LEFT JOIN animals a ON a.id = aa.animal_id
    ORDER BY aa.created_at DESC LIMIT 5");

$recentMessages = pdo_fetch_all("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");

$recentStories = pdo_fetch_all("SELECT id, title, slug, status, featured_image, published_at FROM rescue_stories
    ORDER BY COALESCE(published_at, created_at) DESC LIMIT 5");
?>

<div class="stat-grid">
    <?php foreach ($stats as $s): ?>
    <div class="stat-card">
        <div class="stat-icon" style="background:<?= $s['bg'] ?>;color:<?= $s['color'] ?>;"><i class="fas <?= $s['icon'] ?>"></i></div>
        <div class="stat-value"><?= $s['value'] ?></div>
        <div class="stat-label"><?= $s['label'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-paw" style="color:var(--pb-secondary);margin-right:8px;"></i> Recently Added Animals</h3>
                <a href="<?= url('admin/animals.php') ?>" class="btn btn-sm btn-outline-primary">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th></th><th>Name</th><th>Type</th><th>Breed</th><th>Status</th><th>Added</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAnimals as $a): ?>
                            <tr>
                                <td><img src="<?= $a['image'] ? asset_url('uploads/' . $a['image']) : 'https://placehold.co/50x50?text=' . urlencode($a['name'][0]) ?>" class="thumbnail-sm"></td>
                                <td><strong><?= e($a['name']) ?></strong></td>
                                <td><?= e($a['species']) ?></td>
                                <td><?= e($a['breed'] ?? '-') ?></td>
                                <td>
                                    <?php $badgeMap = ['Available'=>'success','Adoption Pending'=>'warning','Adopted'=>'info','Fostered'=>'primary','Medical Hold'=>'danger','Rescued'=>'secondary']; ?>
                                    <span class="badge badge-<?= $badgeMap[$a['status']] ?? 'secondary' ?>"><?= e($a['status']) ?></span>
                                </td>
                                <td style="font-size:12px;color:var(--pb-gray);"><?= format_date($a['created_at'], 'M j') ?></td>
                                <td><a href="<?= url('admin/animal-edit.php?id=' . $a['id']) ?>" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-heart" style="color:var(--pb-accent);margin-right:8px;"></i> Recent Adoption Apps</h3>
                <a href="<?= url('admin/adoption-applications.php') ?>" class="btn btn-sm btn-outline-primary">All Apps</a>
            </div>
            <div class="card-body p-0">
                <?php foreach ($recentAdoptionApps as $app): ?>
                <div style="padding:14px 20px;border-bottom:1px solid #F3F4F6;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <strong style="font-size:14px;"><?= e($app['full_name']) ?></strong>
                            <div style="font-size:12px;color:var(--pb-gray);">for <?= e($app['animal_name'] ?? 'Any') ?></div>
                        </div>
                        <?php $bm = ['New'=>'danger','Reviewing'=>'warning','Interview'=>'info','Approved'=>'success','Rejected'=>'secondary','Completed'=>'primary']; ?>
                        <span class="badge badge-<?= $bm[$app['status']] ?? 'secondary' ?>"><?= e($app['status']) ?></span>
                    </div>
                    <div style="font-size:12px;color:var(--pb-gray);"><?= e($app['email']) ?> • <?= format_date($app['created_at'], 'M j') ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-book-heart" style="color:var(--pb-teal);margin-right:8px;"></i> Rescue Stories</h3>
                <a href="<?= url('admin/rescue-stories.php') ?>" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body p-0">
                <?php foreach ($recentStories as $s): ?>
                <div style="padding:14px 20px;border-bottom:1px solid #F3F4F6;" class="d-flex gap-3">
                    <img src="<?= $s['featured_image'] ? asset_url('uploads/' . $s['featured_image']) : 'https://placehold.co/56x56?text=📖' ?>" style="width:56px;height:56px;border-radius:10px;object-fit:cover;">
                    <div style="flex:1;min-width:0;">
                        <strong style="font-size:14px;"><?= e($s['title']) ?></strong>
                        <div style="font-size:12px;color:var(--pb-gray);">
                            <span class="badge badge-<?= $s['status'] === 'Published' ? 'success' : 'secondary' ?>"><?= e($s['status']) ?></span>
                            • <?= $s['published_at'] ? format_date($s['published_at'], 'M j, Y') : 'Draft' ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-envelope" style="color:#102238;margin-right:8px;"></i> Latest Messages</h3>
                <a href="<?= url('admin/messages.php') ?>" class="btn btn-sm btn-outline-primary">View Inbox</a>
            </div>
            <div class="card-body p-0">
                <?php foreach ($recentMessages as $m): ?>
                <div style="padding:14px 20px;border-bottom:1px solid #F3F4F6;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <strong style="font-size:14px;"><?= e($m['full_name']) ?></strong>
                            <?php if (!$m['is_read']): ?><span class="badge badge-danger" style="margin-left:6px;">NEW</span><?php endif; ?>
                            <div style="font-size:12px;color:var(--pb-gray);"><?= e($m['subject'] ?? 'No subject') ?></div>
                        </div>
                        <div style="font-size:11px;color:var(--pb-gray);"><?= format_date($m['created_at'], 'M j') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
