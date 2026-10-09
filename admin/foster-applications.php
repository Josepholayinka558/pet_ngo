<?php
$pageTitle = 'Foster Applications';
require_once __DIR__ . '/includes/header.php';

$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = ['1=1']; $params = [];
if ($status) { $where[] = 'status = ?'; $params[] = $status; }
$whereSql = implode(' AND ', $where);
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM foster_applications WHERE $whereSql", $params)['c'] ?? 0);
$apps = pdo_fetch_all("SELECT * FROM foster_applications WHERE $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/foster-applications.php')); }
    if (isset($_POST['update_status'])) {
        $id = (int)$_POST['app_id'];
        pdo_update('foster_applications', [
            'status' => clean_input($_POST['new_status']),
            'reviewed_by' => current_admin_id(),
            'review_notes' => clean_input($_POST['review_notes'] ?? ''),
        ], 'id = ?', [$id]);
        flash('success', 'Updated!');
        redirect(url('admin/foster-applications.php'));
    }
    if (isset($_POST['delete_id'])) {
        pdo_delete('foster_applications', 'id = ?', [(int)$_POST['delete_id']]);
        flash('success', 'Deleted');
        redirect(url('admin/foster-applications.php'));
    }
}
?>
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-house-heart" style="color:var(--pb-primary);margin-right:8px;"></i> Foster Applications</h3>
        <select class="form-select d-inline-block" style="width:auto;" onchange="window.location='?status='+this.value">
            <option value="">All Statuses</option>
            <?php foreach(['New','Reviewing','Interview','Approved','Rejected','Active'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Name</th><th>Animal Types</th><th>Can Foster</th><th>Availability</th><th>Contact</th><th>Applied</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($apps as $a): ?>
                    <tr>
                        <td><strong><?= e($a['full_name']) ?></strong><br><small style="color:var(--pb-gray);"><?= e($a['city'] ?? '') ?></small></td>
                        <td><?= e($a['animal_types'] ?? '-') ?></td>
                        <td><?= $a['can_foster_count'] ?? 1 ?> animals</td>
                        <td><?= e($a['availability'] ?? '-') ?></td>
                        <td style="font-size:12px;"><?= e($a['email']) ?><br><?= e($a['phone']) ?></td>
                        <td style="font-size:12px;"><?= format_date($a['created_at'],'M j, Y') ?></td>
                        <td><?php $bm=['New'=>'danger','Reviewing'=>'warning','Interview'=>'info','Approved'=>'success','Rejected'=>'secondary','Active'=>'primary']; ?>
                            <span class="badge badge-<?= $bm[$a['status']] ?? 'secondary' ?>"><?= e($a['status']) ?></span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#fm<?= $a['id'] ?>"><i class="fas fa-eye"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $a['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    <div class="modal fade" id="fm<?= $a['id'] ?>"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header">
                        <h5 class="modal-title">Foster: <?= e($a['full_name']) ?></h5><button class="btn-close" data-bs-dismiss="modal"></button>
                    </div><div class="modal-body">
                        <div class="row g-2">
                            <div class="col-md-6"><div class="data-row"><span class="label">Name</span><span class="value"><?= e($a['full_name']) ?></span></div></div>
                            <div class="col-md-3"><div class="data-row"><span class="label">Email</span><span class="value"><?= e($a['email']) ?></span></div></div>
                            <div class="col-md-3"><div class="data-row"><span class="label">Phone</span><span class="value"><?= e($a['phone']) ?></span></div></div>
                            <div class="col-md-6"><div class="data-row"><span class="label">Address</span><span class="value"><?= e($a['address'] ?? '-') ?></span></div></div>
                            <div class="col-md-3"><div class="data-row"><span class="label">City</span><span class="value"><?= e($a['city'] ?? '-') ?></span></div></div>
                            <div class="col-md-3"><div class="data-row"><span class="label">Country</span><span class="value"><?= e($a['country'] ?? '-') ?></span></div></div>
                            <div class="col-md-4"><div class="data-row"><span class="label">Housing</span><span class="value"><?= e($a['housing_type'] ?? '-') ?></span></div></div>
                            <div class="col-md-4"><div class="data-row"><span class="label">Animal Types</span><span class="value"><?= e($a['animal_types'] ?? '-') ?></span></div></div>
                            <div class="col-md-4"><div class="data-row"><span class="label">Can Foster Count</span><span class="value"><?= $a['can_foster_count'] ?? 1 ?></span></div></div>
                            <div class="col-md-4"><div class="data-row"><span class="label">Availability</span><span class="value"><?= e($a['availability'] ?? '-') ?></span></div></div>
                            <div class="col-md-4"><div class="data-row"><span class="label">Duration</span><span class="value"><?= e($a['duration'] ?? '-') ?></span></div></div>
                            <div class="col-md-4"><div class="data-row"><span class="label">Transport</span><span class="value"><?= $a['transport_available']==='1'?'Yes':'No' ?></span></div></div>
                            <div class="col-md-6"><div class="data-row"><span class="label">Has Children</span><span class="value"><?= !empty($a['has_children']) ? 'Yes' : 'No' ?></span></div></div>
                            <div class="col-md-6"><div class="data-row"><span class="label">Existing Pets</span><span class="value"><?= !empty($a['existing_pets']) ? 'Yes - '.e($a['existing_pets_details'] ?? '') : 'No' ?></span></div></div>
                            <div class="col-md-12"><div class="data-row"><span class="label">Housing Details</span><span class="value"><?= nl2br(e($a['housing_details'] ?? '-')) ?></span></div></div>
                            <div class="col-md-12"><div class="data-row"><span class="label">Previous Experience</span><span class="value"><?= nl2br(e($a['previous_experience'] ?? '-')) ?></span></div></div>
                            <div class="col-md-12"><div class="data-row"><span class="label">Additional Info</span><span class="value"><?= nl2br(e($a['additional_info'] ?? '-')) ?></span></div></div>
                            <?php if ($a['review_notes']): ?><div class="col-md-12"><div class="data-row"><span class="label">Admin Notes</span><span class="value"><?= nl2br(e($a['review_notes'])) ?></span></div></div><?php endif; ?>
                        </div>
                        <form method="POST" class="mt-3 border-top pt-3"><?= csrf_field() ?><input type="hidden" name="app_id" value="<?= $a['id'] ?>"><div class="row g-2">
                            <div class="col-md-4"><select name="new_status" class="form-select"><?php foreach(['New','Reviewing','Interview','Approved','Rejected','Active'] as $s): ?><option <?= $a['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-6"><input type="text" name="review_notes" class="form-control" placeholder="Notes..."></div>
                            <div class="col-md-2"><button type="submit" name="update_status" class="btn btn-primary w-100">Update</button></div>
                        </div></form>
                    </div></div></div></div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginate($page, $perPage, $total, 'foster-applications.php') ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
