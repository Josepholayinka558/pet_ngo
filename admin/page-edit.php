<?php
$pageTitle = 'Edit Page';
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$p = pdo_fetch("SELECT * FROM pages WHERE id = ?", [$id]);
if (!$p) { flash('error','Not found'); redirect(url('admin/pages.php')); }
$templates = ['default'=>'Default Template','full-width'=>'Full Width','sidebar'=>'With Sidebar'];
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/page-edit.php?id='.$id)); }
    $title = clean_input($_POST['title'] ?? '');
    $slug = clean_input($_POST['slug'] ?? '') ?: slugify($title);
    $baseSlug = $slug; $i = 1;
    while (($e = pdo_fetch("SELECT id FROM pages WHERE slug = ?", [$slug])) && $e['id'] != $id) { $slug = $baseSlug . '-' . $i++; }
    $data = ['title'=>$title,'slug'=>$slug,'content'=>$_POST['content'] ?? '','status'=>clean_input($_POST['status'] ?? 'Draft'),'template'=>clean_input($_POST['template'] ?? 'default'),'seo_title'=>clean_input($_POST['seo_title'] ?? ''),'seo_description'=>clean_input($_POST['seo_description'] ?? ''),'sort_order'=>(int)($_POST['sort_order'] ?? 0)];
    if (($p['status'] !== 'Published') && ($_POST['status'] ?? '') === 'Published') $data['published_at'] = date('Y-m-d H:i:s');
    pdo_update('pages', $data, 'id = ?', [$id]);
    flash('success','Page updated!'); redirect(url('admin/page-edit.php?id='.$id));
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-edit" style="color:var(--pb-secondary)"></i> Edit Page: <?= e($p['title']) ?></h3>
<div><a href="<?= url('page.php?slug='.$p['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary me-2"><i class="fas fa-eye"></i> View</a>
<a href="<?= url('admin/pages.php') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div></div>
<div class="card-body"><form method="POST"><?= csrf_field() ?>
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required value="<?= e($p['title']) ?>"></div>
<div class="col-md-2"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="<?= e($p['slug']) ?>"></div>
<div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach(['Draft','Published','Unpublished'] as $s): ?><option <?= $p['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Template</label><select name="template" class="form-select"><?php foreach($templates as $k=>$v): ?><option value="<?= $k ?>" <?= $p['template']===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= $p['sort_order'] ?? 0 ?>"></div>
<div class="col-md-12"><label class="form-label">Content</label><textarea name="content" class="form-control ckeditor" rows="14"><?= e($p['content']) ?></textarea></div>
<fieldset class="col-md-12 border rounded p-3"><legend class="float-none w-auto px-2 fs-6 fw-bold">SEO</legend><div class="row g-2">
<div class="col-md-12"><label class="form-label">SEO Title</label><input type="text" name="seo_title" class="form-control" value="<?= e($p['seo_title'] ?? '') ?>"></div>
<div class="col-md-12"><label class="form-label">SEO Description</label><textarea name="seo_description" class="form-control" rows="2"><?= e($p['seo_description'] ?? '') ?></textarea></div>
</div></fieldset>
</div>
<div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
<a href="<?= url('admin/pages.php') ?>" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
