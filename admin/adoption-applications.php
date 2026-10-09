<?php
$pageTitle = 'Adoption Applications';
require_once __DIR__ . '/includes/header.php';

$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = ['1=1'];
$params = [];
if ($status) { $where[] = 'aa.status = ?'; $params[] = $status; }
$whereSql = implode(' AND ', $where);

$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM adoption_applications aa WHERE $whereSql", $params)['c'] ?? 0);
$apps = pdo_fetch_all("SELECT aa.*, a.name AS animal_name, a.species FROM adoption_applications aa
    LEFT JOIN animals a ON a.id = aa.animal_id
    WHERE $whereSql ORDER BY aa.created_at DESC LIMIT $perPage OFFSET $offset", $params);

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/adoption-applications.php')); }
    if (isset($_POST['update_status'])) {
        $id = (int)$_POST['app_id'];
        $newStatus = clean_input($_POST['new_status']);
        $notes = clean_input($_POST['review_notes'] ?? '');
        pdo_update('adoption_applications', [
            'status' => $newStatus,
            'reviewed_by' => current_admin_id(),
            'review_notes' => $notes,
        ], 'id = ?', [$id]);
        flash('success', 'Application updated!');
        redirect(url('admin/adoption-applications.php'));
    }
    if (isset($_POST['delete_id'])) {
        pdo_delete('adoption_applications', 'id = ?', [(int)$_POST['delete_id']]);
        flash('success', 'Application deleted');
        redirect(url('admin/adoption-applications.php'));
    }
}
?>
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-file-heart" style="color:var(--pb-accent);margin-right:8px;"></i> Adoption Applications</h3>
        <div>
            <select class="form-select d-inline-block" style="width:auto;" onchange="window.location='?status='+this.value">
                <option value="">All Statuses</option>
                <?php foreach(['New','Reviewing','Interview','Approved','Rejected','Completed'] as $s): ?>
                <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Applicant</th><th>For Animal</th><th>Contact</th><th>Location</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($apps as $a): ?>
                    <tr>
                        <td><strong><?= e($a['full_name']) ?></strong></td>
                        <td><?= $a['animal_name'] ? e($a['animal_name']).' <small class="text-muted">('.e($a['species']).')</small>' : '<span class="text-muted">'.e($a['preferred_animal'] ?? 'Any').'</span>' ?></td>
                        <td><div style="font-size:12px;"><?= e($a['email']) ?><br><?= e($a['phone']) ?></div></td>
                        <td><?= e(trim(($a['city'] ?? '').', '.($a['country'] ?? ''), ', ')) ?></td>
                        <td style="font-size:12px;"><?= format_date($a['created_at'],'M j, Y') ?></td>
                        <td><?php $bm=['New'=>'danger','Reviewing'=>'warning','Interview'=>'info','Approved'=>'success','Rejected'=>'secondary','Completed'=>'primary']; ?>
                            <span class="badge badge-<?= $bm[$a['status']] ?? 'secondary' ?>"><?= e($a['status']) ?></span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?= $a['id'] ?>"><i class="fas fa-eye"></i></button>
                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $a['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    <div class="modal fade" id="viewModal<?= $a['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header">
                            <h5 class="modal-title">Application from <?= e($a['full_name']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div><div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-6"><div class="data-row"><span class="label">Animal</span><span class="value"><?= e($a['animal_name'] ?? $a['preferred_animal'] ?? 'Any') ?></span></div></div>
                                <div class="col-md-6"><div class="data-row"><span class="label">Full Name</span><span class="value"><?= e($a['full_name']) ?></span></div></div>
                                <div class="col-md-6"><div class="data-row"><span class="label">Email</span><span class="value"><?= e($a['email']) ?></span></div></div>
                                <div class="col-md-6"><div class="data-row"><span class="label">Phone</span><span class="value"><?= e($a['phone']) ?></span></div></div>
                                <div class="col-md-6"><div class="data-row"><span class="label">Address</span><span class="value"><?= e($a['address'] ?? '-') ?></span></div></div>
                                <div class="col-md-3"><div class="data-row"><span class="label">City</span><span class="value"><?= e($a['city'] ?? '-') ?></span></div></div>
                                <div class="col-md-3"><div class="data-row"><span class="label">Country</span><span class="value"><?= e($a['country'] ?? '-') ?></span></div></div>
                                <div class="col-md-3"><div class="data-row"><span class="label">Housing</span><span class="value"><?= e($a['housing_type'] ?? '-') ?></span></div></div>
                                <div class="col-md-3"><div class="data-row"><span class="label">Own/Rent</span><span class="value"><?= e($a['own_rent'] ?? '-') ?></span></div></div>
                                <div class="col-md-3"><div class="data-row"><span class="label">Household</span><span class="value"><?= $a['household_members'] ?? 0 ?> people</span></div></div>
                                <div class="col-md-3"><div class="data-row"><span class="label">Children</span><span class="value"><?= !empty($a['household_children']) ? 'Yes - '.e($a['children_ages'] ?? '') : 'No' ?></span></div></div>
                                <div class="col-md-6"><div class="data-row"><span class="label">Landlord Permission</span><span class="value"><?= $a['landlord_permission']==='1'?'Yes':($a['landlord_permission']==='0'?'No':'N/A') ?></span></div></div>
                                <div class="col-md-6"><div class="data-row"><span class="label">Existing Pets</span><span class="value"><?= !empty($a['existing_pets']) ? 'Yes - '.e($a['existing_pets_details'] ?? '') : 'No' ?></span></div></div>
                                <div class="col-md-6"><div class="data-row"><span class="label">Hours Alone</span><span class="value"><?= e($a['hours_alone'] ?? '-') ?></span></div></div>
                                <div class="col-md-12"><div class="data-row"><span class="label">Previous Experience</span><span class="value"><?= nl2br(e($a['previous_experience'] ?? '-')) ?></span></div></div>
                                <div class="col-md-12"><div class="data-row"><span class="label">Reason for Adoption</span><span class="value"><?= nl2br(e($a['reason_for_adoption'] ?? '-')) ?></span></div></div>
                                <div class="col-md-12"><div class="data-row"><span class="label">Additional Info</span><span class="value"><?= nl2br(e($a['additional_info'] ?? '-')) ?></span></div></div>
                                <?php if ($a['review_notes']): ?>
                                <div class="col-md-12"><div class="data-row"><span class="label">Admin Notes</span><span class="value"><?= nl2br(e($a['review_notes'])) ?></span></div></div>
                                <?php endif; ?>
                            </div>
                            <form method="POST" action="" class="mt-3 border-top pt-3">
                                <?= csrf_field() ?>
                                <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Update Status</label>
                                        <select name="new_status" class="form-select">
                                            <?php foreach(['New','Reviewing','Interview','Approved','Rejected','Completed'] as $s): ?><option <?= $a['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Review Notes</label><input type="text" name="review_notes" class="form-control" placeholder="Optional notes..."></div>
                                    <div class="col-md-2 align-self-end"><button type="submit" name="update_status" class="btn btn-primary w-100">Update</button></div>
                                </div>
                            </form>
                        </div></div></div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginate($page, $perPage, $total, 'adoption-applications.php') ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
