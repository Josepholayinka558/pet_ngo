<?php
$pageTitle = 'Newsletter Subscribers';
require_once __DIR__ . '/includes/header.php';
$isActive = $_GET['active'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30; $offset = ($page-1)*$perPage;
$where = ['1=1']; $params = [];
if ($isActive !== '') { $where[] = 'is_active = ?'; $params[] = (int)$isActive; }
if ($search) { $where[] = '(email LIKE ? OR name LIKE ?)'; $p = "%$search%"; $params[] = $p; $params[] = $p; }
$whereSql = implode(' AND ', $where);
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM newsletter_subscribers WHERE $whereSql", $params)['c'] ?? 0);
$subs = pdo_fetch_all("SELECT * FROM newsletter_subscribers WHERE $whereSql ORDER BY subscribed_at DESC LIMIT $perPage OFFSET $offset", $params);
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/subscribers.php')); }
    if (isset($_POST['delete_id'])) { pdo_delete('newsletter_subscribers', 'id = ?', [(int)$_POST['delete_id']]); flash('success','Deleted'); redirect(url('admin/subscribers.php')); }
    if (isset($_POST['export'])) {
        $all = pdo_fetch_all("SELECT email, name, subscribed_at FROM newsletter_subscribers WHERE is_active = 1 ORDER BY subscribed_at DESC");
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=subscribers-'.date('Y-m-d').'.csv');
        $out = fopen('php://output','w');
        fputcsv($out, ['Email','Name','Subscribed At']);
        foreach($all as $s) fputcsv($out, [$s['email'], $s['name'], $s['subscribed_at']]);
        fclose($out); exit;
    }
    if (isset($_POST['add_sub'])) {
        $email = clean_input($_POST['email']);
        if (!validate_email($email)) { flash('error','Invalid email'); redirect(url('admin/subscribers.php')); }
        $exists = pdo_fetch("SELECT id FROM newsletter_subscribers WHERE email = ?", [$email]);
        if ($exists) { pdo_update('newsletter_subscribers', ['is_active'=>1,'name'=>clean_input($_POST['name'] ?? '')], 'id = ?', [$exists['id']]); flash('success','Updated'); }
        else { pdo_insert('newsletter_subscribers', ['email'=>$email,'name'=>clean_input($_POST['name'] ?? ''),'is_active'=>1,'token'=>bin2hex(random_bytes(16))]); flash('success','Added'); }
        redirect(url('admin/subscribers.php'));
    }
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-bell" style="color:#F59E42;margin-right:8px;"></i> Newsletter Subscribers <small class="text-muted">(<?= $total ?> total)</small></h3>
<div><form method="GET" class="d-inline me-2"><input type="text" name="search" class="form-control d-inline" style="width:200px;" placeholder="Search email/name..." value="<?= e($search) ?>"> <button class="btn btn-sm btn-secondary"><i class="fas fa-search"></i></button></form>
<form method="POST" class="d-inline me-2"><?= csrf_field() ?><button type="submit" name="export" class="btn btn-sm btn-success"><i class="fas fa-download"></i> Export CSV</button></form>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Subscriber</button></div></div>
<div class="card-body"><div class="table-responsive"><table class="table">
<thead><tr><th>Name</th><th>Email</th><th>Source</th><th>Status</th><th>Subscribed</th><th>Actions</th></tr></thead>
<tbody><?php foreach($subs as $s): ?>
<tr><td><?= e($s['name'] ?? '<em class="text-muted">No name</em>') ?></td>
<td><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></td>
<td><span class="badge badge-secondary"><?= e($s['source'] ?? 'website') ?></span></td>
<td><span class="badge badge-<?= !empty($s['is_active'])?'success':'danger' ?>"><?= !empty($s['is_active'])?'Active':'Unsubscribed' ?></span></td>
<td style="font-size:12px;color:var(--pb-gray);"><?= format_date($s['subscribed_at'],'M j, Y') ?></td>
<td><form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $s['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></td>
</tr><?php endforeach; ?></tbody>
</table></div><?= paginate($page, $perPage, $total, 'subscribers.php') ?></div></div>

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title">Add Subscriber</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><?= csrf_field() ?><div class="modal-body">
<div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control"></div>
<div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="add_sub" class="btn btn-primary"><i class="fas fa-save"></i> Add</button></div></form></div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
