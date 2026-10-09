<?php
$pageTitle = 'Edit Event';
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$e = pdo_fetch("SELECT * FROM events WHERE id = ?", [$id]);
if (!$e) { flash('error','Not found'); redirect(url('admin/events.php')); }
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/event-edit.php?id='.$id)); }
    $name = clean_input($_POST['event_name'] ?? '');
    $slug = clean_input($_POST['slug'] ?? '') ?: slugify($name);
    $baseSlug = $slug; $i = 1;
    while (($x = pdo_fetch("SELECT id FROM events WHERE slug = ?", [$slug])) && $x['id'] != $id) { $slug = $baseSlug . '-' . $i++; }
    $data = ['event_name'=>$name,'slug'=>$slug,'description'=>$_POST['description'] ?? '','event_date'=>$_POST['event_date'] ?? date('Y-m-d'),'event_time'=>!empty($_POST['event_time'])?$_POST['event_time']:null,'end_time'=>!empty($_POST['end_time'])?$_POST['end_time']:null,'location'=>clean_input($_POST['location'] ?? ''),'registration_url'=>clean_input($_POST['registration_url'] ?? ''),'category'=>clean_input($_POST['category'] ?? ''),'max_attendees'=>!empty($_POST['max_attendees'])?(int)$_POST['max_attendees']:null,'status'=>clean_input($_POST['status'] ?? 'Draft'),'featured'=>(int)($_POST['featured'] ?? 0),'seo_title'=>clean_input($_POST['seo_title'] ?? ''),'seo_description'=>clean_input($_POST['seo_description'] ?? '')];
    if (!empty($_FILES['image']['name'])) {
        if ($e['image']) delete_file($e['image']);
        $r = secure_upload($_FILES['image'], 'events');
        if (!empty($r['success'])) $data['image'] = $r['path'];
    }
    pdo_update('events', $data, 'id = ?', [$id]);
    flash('success','Event updated!'); redirect(url('admin/event-edit.php?id='.$id));
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-edit" style="color:#F59E42"></i> Edit: <?= e($e['event_name']) ?></h3>
<div><a href="<?= url('events.php') ?>" target="_blank" class="btn btn-sm btn-secondary me-2"><i class="fas fa-eye"></i> View</a>
<a href="<?= url('admin/events.php') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div></div>
<div class="card-body"><form method="POST" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="row g-3">
<?php if ($e['image']): ?><div class="col-md-3"><img src="<?= asset_url('uploads/'.$e['image']) ?>" class="img-fluid rounded-3" style="aspect-ratio:4/3;object-fit:cover;width:100%;"></div><?php endif; ?>
<div class="col-md-8"><label class="form-label">Event Name *</label><input type="text" name="event_name" class="form-control" required value="<?= e($e['event_name']) ?>"></div>
<div class="col-md-4"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="<?= e($e['slug']) ?>"></div>
<div class="col-md-3"><label class="form-label">Date *</label><input type="date" name="event_date" class="form-control" required value="<?= $e['event_date'] ?>"></div>
<div class="col-md-2"><label class="form-label">Start Time</label><input type="time" name="event_time" class="form-control" value="<?= $e['event_time'] ?? '' ?>"></div>
<div class="col-md-2"><label class="form-label">End Time</label><input type="time" name="end_time" class="form-control" value="<?= $e['end_time'] ?? '' ?>"></div>
<div class="col-md-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= e($e['location'] ?? '') ?>"></div>
<div class="col-md-2"><label class="form-label">Max Attendees</label><input type="number" name="max_attendees" class="form-control" value="<?= $e['max_attendees'] ?? '' ?>"></div>
<div class="col-md-4"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="<?= e($e['category'] ?? '') ?>"></div>
<div class="col-md-4"><label class="form-label">Registration URL</label><input type="url" name="registration_url" class="form-control" value="<?= e($e['registration_url'] ?? '') ?>"></div>
<div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach(['Draft','Published','Completed','Cancelled'] as $s): ?><option <?= $e['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="featured" value="1" <?= $e['featured']?'checked':'' ?> id="eFeat"><label class="form-check-label fw-bold" for="eFeat">⭐ Featured</label></div></div>
<div class="col-md-12"><label class="form-label">Change Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
<div class="col-md-12"><label class="form-label">Description</label><textarea name="description" class="form-control ckeditor" rows="8"><?= e($e['description'] ?? '') ?></textarea></div>
<fieldset class="col-md-12 border rounded p-3"><legend class="float-none w-auto px-2 fs-6 fw-bold">SEO</legend><div class="row g-2">
<div class="col-md-12"><label class="form-label">SEO Title</label><input type="text" name="seo_title" class="form-control" value="<?= e($e['seo_title'] ?? '') ?>"></div>
<div class="col-md-12"><label class="form-label">SEO Description</label><textarea name="seo_description" class="form-control" rows="2"><?= e($e['seo_description'] ?? '') ?></textarea></div>
</div></fieldset>
</div>
<div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
<a href="<?= url('admin/events.php') ?>" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
