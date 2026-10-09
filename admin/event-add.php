<?php
$pageTitle = 'Add Event';
require_once __DIR__ . '/includes/header.php';
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/event-add.php')); }
    $name = clean_input($_POST['event_name'] ?? '');
    $slug = clean_input($_POST['slug'] ?? '') ?: slugify($name);
    $baseSlug = $slug; $i = 1;
    while (pdo_fetch("SELECT id FROM events WHERE slug = ?", [$slug])) { $slug = $baseSlug . '-' . $i++; }
    $img = '';
    if (!empty($_FILES['image']['name'])) { $r = secure_upload($_FILES['image'], 'events'); if (!empty($r['success'])) $img = $r['path']; }
    $id = pdo_insert('events', ['event_name'=>$name,'slug'=>$slug,'image'=>$img,'description'=>$_POST['description'] ?? '','event_date'=>$_POST['event_date'] ?? date('Y-m-d'),'event_time'=>!empty($_POST['event_time'])?$_POST['event_time']:null,'end_time'=>!empty($_POST['end_time'])?$_POST['end_time']:null,'location'=>clean_input($_POST['location'] ?? ''),'registration_url'=>clean_input($_POST['registration_url'] ?? ''),'category'=>clean_input($_POST['category'] ?? ''),'max_attendees'=>!empty($_POST['max_attendees'])?(int)$_POST['max_attendees']:null,'status'=>clean_input($_POST['status'] ?? 'Draft'),'featured'=>(int)($_POST['featured'] ?? 0),'seo_title'=>clean_input($_POST['seo_title'] ?? ''),'seo_description'=>clean_input($_POST['seo_description'] ?? ''),'created_by'=>current_admin_id()]);
    flash('success','Event created!'); redirect(url('admin/event-edit.php?id='.$id));
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-plus" style="color:#F59E42"></i> New Event</h3>
<a href="<?= url('admin/events.php') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
<div class="card-body"><form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate><?= csrf_field() ?>
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Event Name *</label><input type="text" name="event_name" class="form-control" required id="eName" oninput="document.getElementById('eSlug').placeholder=slugify(this.value)"></div>
<div class="col-md-4"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" id="eSlug" placeholder="auto"></div>
<div class="col-md-3"><label class="form-label">Date *</label><input type="date" name="event_date" class="form-control" required></div>
<div class="col-md-2"><label class="form-label">Start Time</label><input type="time" name="event_time" class="form-control"></div>
<div class="col-md-2"><label class="form-label">End Time</label><input type="time" name="end_time" class="form-control"></div>
<div class="col-md-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control" placeholder="Venue name, address"></div>
<div class="col-md-2"><label class="form-label">Max Attendees</label><input type="number" name="max_attendees" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Category</label><input type="text" name="category" class="form-control" placeholder="Fundraiser, Adoption Day..."></div>
<div class="col-md-4"><label class="form-label">Registration URL</label><input type="url" name="registration_url" class="form-control"></div>
<div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option>Draft</option><option>Published</option><option>Completed</option><option>Cancelled</option></select></div>
<div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="featured" value="1" id="eFeat"><label class="form-check-label fw-bold" for="eFeat">⭐ Featured</label></div></div>
<div class="col-md-12"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
<div class="col-md-12"><label class="form-label">Description</label><textarea name="description" class="form-control ckeditor" rows="8"></textarea></div>
<fieldset class="col-md-12 border rounded p-3"><legend class="float-none w-auto px-2 fs-6 fw-bold">SEO</legend><div class="row g-2">
<div class="col-md-12"><label class="form-label">SEO Title</label><input type="text" name="seo_title" class="form-control"></div>
<div class="col-md-12"><label class="form-label">SEO Description</label><textarea name="seo_description" class="form-control" rows="2"></textarea></div>
</div></fieldset>
</div>
<div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Event</button>
<a href="<?= url('admin/events.php') ?>" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<script>function slugify(t){return t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'')}</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
