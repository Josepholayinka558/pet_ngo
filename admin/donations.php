<?php
$pageTitle = 'Donations';
require_once __DIR__ . '/includes/header.php';
$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20; $offset = ($page-1)*$perPage;
$where = ['1=1']; $params = [];
if ($status) { $where[] = 'status = ?'; $params[] = $status; }
$whereSql = implode(' AND ', $where);
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM donations WHERE $whereSql", $params)['c'] ?? 0);
$totalAmount = (float)(pdo_fetch("SELECT COALESCE(SUM(amount),0) AS s FROM donations WHERE status = 'Completed'")['s'] ?? 0);
$donations = pdo_fetch_all("SELECT * FROM donations WHERE $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/donations.php')); }
    if (isset($_POST['add_donation'])) {
        $data = ['donor_name'=>clean_input($_POST['donor_name']),'donor_email'=>clean_input($_POST['donor_email'] ?? ''),'donor_phone'=>clean_input($_POST['donor_phone'] ?? ''),'amount'=>(float)$_POST['amount'],'currency'=>clean_input($_POST['currency'] ?? 'USD'),'donation_type'=>clean_input($_POST['donation_type'] ?? 'One Time'),'payment_method'=>clean_input($_POST['payment_method'] ?? 'Manual'),'message'=>clean_input($_POST['message'] ?? ''),'anonymous'=>(int)($_POST['anonymous'] ?? 0),'status'=>clean_input($_POST['status'] ?? 'Completed')];
        if ($data['status'] === 'Completed') {
            pdo_insert('donations', $data + ['transaction_id' => 'MANUAL-'.uniqid()]);
        } else {
            pdo_insert('donations', $data);
        }
        flash('success','Donation recorded!'); redirect(url('admin/donations.php'));
    }
    if (isset($_POST['delete_id'])) {
        pdo_delete('donations', 'id = ?', [(int)$_POST['delete_id']]);
        flash('success', 'Deleted'); redirect(url('admin/donations.php'));
    }
}
$currencies = ['USD','EUR','GBP','CAD','AUD'];
$types = ['One Time','Monthly','Annual','In Memory','Other'];
$statuses = ['Pending','Completed','Failed','Refunded'];
?>
<div class="stat-grid">
<div class="stat-card"><div class="stat-icon" style="background:#FEF3C7;color:#F59E42;"><i class="fas fa-sack-dollar"></i></div><div class="stat-value">$<?= number_format($totalAmount,2) ?></div><div class="stat-label">Total Raised</div></div>
<div class="stat-card"><div class="stat-icon" style="background:#DDF7E2;color:#0B632A;"><i class="fas fa-heart"></i></div><div class="stat-value"><?= $total ?></div><div class="stat-label">Total Donations</div></div>
<div class="stat-card"><div class="stat-icon" style="background:#DBFEFE;color:#59B9B0;"><i class="fas fa-user-friends"></i></div><div class="stat-value"><?= (int)(pdo_fetch("SELECT COUNT(DISTINCT donor_email) AS c FROM donations WHERE status = 'Completed' AND donor_email != ''")['c'] ?? 0) ?></div><div class="stat-label">Unique Donors</div></div>
<div class="stat-card"><div class="stat-icon" style="background:#DBEAFE;color:#102238;"><i class="fas fa-calendar-check"></i></div><div class="stat-value"><?= (int)(pdo_fetch("SELECT COUNT(*) AS c FROM donations WHERE donation_type IN ('Monthly','Annual') AND status = 'Completed'")['c'] ?? 0) ?></div><div class="stat-label">Recurring Donors</div></div>
</div>
<div class="card"><div class="card-header"><h3><i class="fas fa-hand-holding-dollar" style="color:#F59E42;margin-right:8px;"></i> Donations</h3>
<div><select class="form-select d-inline-block" style="width:auto;" onchange="window.location='?status='+this.value">
<option value="">All</option><?php foreach($statuses as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
</select><button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Record Donation</button></div></div>
<div class="card-body"><div class="table-responsive"><table class="table">
<thead><tr><th>Donor</th><th>Amount</th><th>Type</th><th>Payment</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
<tbody><?php foreach($donations as $d): ?>
<tr><td><?php if (!empty($d['anonymous'])): ?><em>Anonymous</em><?php else: ?><strong><?= e($d['donor_name']) ?></strong><br><small style="color:var(--pb-gray);"><?= e($d['donor_email'] ?? '') ?></small><?php endif; ?></td>
<td><strong style="color:var(--pb-secondary);"><?= e($d['currency']) ?> <?= number_format($d['amount'],2) ?></strong></td>
<td><span class="badge badge-<?= in_array($d['donation_type'],['Monthly','Annual'])?'primary':'secondary' ?>"><?= e($d['donation_type']) ?></span></td>
<td style="font-size:12px;"><?= e($d['payment_method'] ?? '-') ?></td>
<td style="font-size:12px;max-width:200px;"><?= $d['message'] ? e(mb_substr($d['message'],0,60)) : '-' ?></td>
<td style="font-size:12px;"><?= format_date($d['created_at'],'M j, Y g:iA') ?></td>
<td><?php $bm=['Pending'=>'warning','Completed'=>'success','Failed'=>'danger','Refunded'=>'secondary']; ?><span class="badge badge-<?= $bm[$d['status']] ?? 'secondary' ?>"><?= e($d['status']) ?></span></td>
<td><form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $d['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></td>
</tr><?php endforeach; ?></tbody>
</table></div><?= paginate($page, $perPage, $total, 'donations.php') ?></div></div>

<div class="modal fade" id="addModal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title">Record New Donation</h5><button class="btn-close" data-bs-dismiss="modal"></button>
</div><form method="POST"><?= csrf_field() ?><div class="modal-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Donor Name *</label><input type="text" name="donor_name" class="form-control" required></div>
<div class="col-md-3"><label class="form-label">Email</label><input type="email" name="donor_email" class="form-control"></div>
<div class="col-md-3"><label class="form-label">Phone</label><input type="text" name="donor_phone" class="form-control"></div>
<div class="col-md-3"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
<div class="col-md-3"><label class="form-label">Currency</label><select name="currency" class="form-select"><?php foreach($currencies as $c): ?><option><?= $c ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Type</label><select name="donation_type" class="form-select"><?php foreach($types as $t): ?><option><?= $t ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach($statuses as $s): ?><option <?= $s==='Completed'?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Payment Method</label><input type="text" name="payment_method" class="form-control" value="Manual"></div>
<div class="col-md-6"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="anonymous" value="1" id="an"><label class="form-check-label" for="an">Anonymous Donation</label></div></div>
<div class="col-md-12"><label class="form-label">Message / Notes</label><textarea name="message" class="form-control" rows="3"></textarea></div>
</div></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="add_donation" class="btn btn-primary"><i class="fas fa-save"></i> Record Donation</button></div></form></div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
