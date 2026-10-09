<?php
$pageTitle = 'Events';
require_once __DIR__ . '/includes/header.php';
$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15; $offset = ($page-1)*$perPage;
$where = ['1=1']; $params = [];
if ($status) { $where[] = 'status = ?'; $params[] = $status; }
$whereSql = implode(' AND ', $where);
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM events WHERE $whereSql", $params)['c'] ?? 0);
$events = pdo_fetch_all("SELECT * FROM events WHERE $whereSql ORDER BY event_date DESC LIMIT $perPage OFFSET $offset", $params);
if (is_post() && isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/events.php')); }
    $id = (int)$_POST['delete_id'];
    $e = pdo_fetch("SELECT image FROM events WHERE id = ?", [$id]);
    if ($e && $e['image']) delete_file($e['image']);
    pdo_delete('events', 'id = ?', [$id]);
    flash('success', 'Event deleted'); redirect(url('admin/events.php'));
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-calendar-star" style="color:#F59E42;margin-right:8px;"></i> Events</h3>
<div><select class="form-select d-inline-block" style="width:auto;" onchange="window.location='?status='+this.value">
<option value="">All</option><?php foreach(['Draft','Published','Completed','Cancelled'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
</select><a href="<?= url('admin/event-add.php') ?>" class="btn btn-primary ms-2"><i class="fas fa-plus"></i> New Event</a></div></div>
<div class="card-body"><div class="table-responsive"><table class="table">
<thead><tr><th></th><th>Name</th><th>Date</th><th>Time</th><th>Location</th><th>Status</th><th>Featured</th><th>Actions</th></tr></thead>
<tbody><?php foreach($events as $e): ?>
<tr><td><img src="<?= $e['image'] ? asset_url('uploads/'.$e['image']) : 'https://placehold.co/50x50?text=📅' ?>" class="thumbnail-sm"></td>
<td><strong><?= e($e['event_name']) ?></strong></td>
<td style="font-size:13px;"><?= format_date($e['event_date'],'M j, Y') ?></td>
<td style="font-size:13px;"><?= $e['event_time'] ? date('g:i A', strtotime($e['event_time'])) : '-' ?></td>
<td><?= e($e['location'] ?? '-') ?></td>
<td><?php $bm=['Draft'=>'secondary','Published'=>'success','Completed'=>'info','Cancelled'=>'danger']; ?><span class="badge badge-<?= $bm[$e['status']] ?? 'secondary' ?>"><?= e($e['status']) ?></span></td>
<td><?= $e['featured'] ? '<i class="fas fa-star" style="color:#F59E42"></i>' : '' ?></td>
<td><a href="<?= url('events.php') ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>
<a href="<?= url('admin/event-edit.php?id='.$e['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
<form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $e['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
</td></tr><?php endforeach; ?></tbody>
</table></div><?= paginate($page, $perPage, $total, 'events.php') ?></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
