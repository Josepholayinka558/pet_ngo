<?php
$pageTitle = 'Animals';
require_once __DIR__ . '/includes/header.php';

$species = $_GET['species'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = ['1=1'];
$params = [];
if ($species) { $where[] = 'species = ?'; $params[] = $species; }
if ($status) { $where[] = 'status = ?'; $params[] = $status; }
if ($search) { $where[] = '(name LIKE ? OR breed LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereSql = implode(' AND ', $where);

$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM animals WHERE $whereSql", $params)['c'] ?? 0);
$animals = pdo_fetch_all("SELECT a.*, (SELECT image_path FROM animal_images WHERE animal_id = a.id AND is_primary = 1 LIMIT 1) AS image FROM animals a WHERE $whereSql ORDER BY a.featured DESC, a.id DESC LIMIT $perPage OFFSET $offset", $params);

$speciesOptions = pdo_fetch_all("SELECT DISTINCT species FROM animals WHERE species IS NOT NULL");
?>
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-dog" style="color:var(--pb-secondary);margin-right:8px;"></i> Manage Animals</h3>
        <a href="<?= url('admin/animal-add.php') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Animal</a>
    </div>
    <div class="card-body">
        <div class="filter-bar">
            <input type="text" class="form-control" placeholder="Search name/breed..." value="<?= e($search) ?>" id="searchInput" onkeyup="if(event.key==='Enter')applyFilter()">
            <select class="form-select" id="speciesSel" onchange="applyFilter()">
                <option value="">All Species</option>
                <?php foreach(['Dog','Cat','Rabbit','Bird','Other'] as $s): ?>
                <option value="<?= $s ?>" <?= $species === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <select class="form-select" id="statusSel" onchange="applyFilter()">
                <option value="">All Statuses</option>
                <?php foreach(['Available','Adoption Pending','Adopted','Fostered','Medical Hold','Rescued'] as $s): ?>
                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-secondary" onclick="resetFilter()"><i class="fas fa-refresh"></i> Reset</button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr><th></th><th>Name</th><th>Species</th><th>Breed</th><th>Age</th><th>Status</th><th>Featured</th><th>Created</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($animals as $a): ?>
                    <tr>
                        <td><img src="<?= $a['image'] ? asset_url('uploads/'.$a['image']) : 'https://placehold.co/50x50?text='.urlencode($a['name'][0]) ?>" class="thumbnail-sm"></td>
                        <td><strong><?= e($a['name']) ?></strong></td>
                        <td><?= e($a['species']) ?></td>
                        <td><?= e($a['breed'] ?? '-') ?></td>
                        <td><?= ($a['age_years']?:"0").'y '.($a['age_months']?:"0").'m' ?></td>
                        <td>
                            <?php $bm = ['Available'=>'success','Adoption Pending'=>'warning','Adopted'=>'info','Fostered'=>'primary','Medical Hold'=>'danger','Rescued'=>'secondary']; ?>
                            <span class="badge badge-<?= $bm[$a['status']] ?? 'secondary' ?>"><?= e($a['status']) ?></span>
                        </td>
                        <td><?= $a['featured'] ? '<i class="fas fa-star" style="color:#F59E42"></i>' : '' ?></td>
                        <td style="font-size:12px;color:var(--pb-gray);"><?= format_date($a['created_at'],'M j, Y') ?></td>
                        <td>
                            <a href="<?= url('animal.php?id='.$a['id']) ?>" target="_blank" class="btn btn-sm btn-secondary" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('admin/animal-edit.php?id='.$a['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Delete this animal?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="delete_id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginate($page, $perPage, $total, 'animals.php') ?>
    </div>
</div>

<?php
if (is_post() && isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error', 'Invalid token'); redirect(url('admin/animals.php')); }
    $id = (int)$_POST['delete_id'];
    $imgs = pdo_fetch_all("SELECT image_path FROM animal_images WHERE animal_id = ?", [$id]);
    foreach($imgs as $i) delete_file($i['image_path']);
    pdo_delete('animal_images', 'animal_id = ?', [$id]);
    pdo_delete('animals', 'id = ?', [$id]);
    flash('success', 'Animal deleted successfully');
    redirect(url('admin/animals.php'));
}
?>

<script>
function applyFilter() {
    const sp = document.getElementById('speciesSel').value;
    const st = document.getElementById('statusSel').value;
    const q = document.getElementById('searchInput').value;
    window.location = '?species=' + encodeURIComponent(sp) + '&status=' + encodeURIComponent(st) + '&search=' + encodeURIComponent(q);
}
function resetFilter() {
    window.location = 'animals.php';
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
