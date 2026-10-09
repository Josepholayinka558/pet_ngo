<?php
$pageTitle = 'Edit Blog Post';
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$post = pdo_fetch("SELECT * FROM blog_posts WHERE id = ?", [$id]);
if (!$post) { flash('error', 'Not found'); redirect(url('admin/blog.php')); }
$cats = pdo_fetch_all("SELECT * FROM blog_categories ORDER BY name");
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/blog-post-edit.php?id='.$id)); }
    $title = clean_input($_POST['title'] ?? '');
    $slug = clean_input($_POST['slug'] ?? '') ?: slugify($title);
    $baseSlug = $slug; $i = 1;
    while (($e = pdo_fetch("SELECT id FROM blog_posts WHERE slug = ?", [$slug])) && $e['id'] != $id) { $slug = $baseSlug . '-' . $i++; }
    $data = ['title'=>$title,'slug'=>$slug,'category_id'=>!empty($_POST['category_id'])?(int)$_POST['category_id']:null,'excerpt'=>clean_input($_POST['excerpt'] ?? ''),'content'=>$_POST['content'] ?? '','tags'=>clean_input($_POST['tags'] ?? ''),'status'=>clean_input($_POST['status'] ?? 'Draft'),'featured'=>(int)($_POST['featured'] ?? 0),'seo_title'=>clean_input($_POST['seo_title'] ?? ''),'seo_description'=>clean_input($_POST['seo_description'] ?? '')];
    if (($post['status'] !== 'Published') && ($_POST['status'] ?? '') === 'Published') $data['published_at'] = date('Y-m-d H:i:s');
    if (!empty($_FILES['featured_image']['name'])) {
        if ($post['featured_image']) delete_file($post['featured_image']);
        $r = secure_upload($_FILES['featured_image'], 'blog');
        if (!empty($r['success'])) $data['featured_image'] = $r['path'];
    }
    pdo_update('blog_posts', $data, 'id = ?', [$id]);
    flash('success','Post updated!'); redirect(url('admin/blog-post-edit.php?id='.$id));
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-edit" style="color:#102238"></i> Edit: <?= e($post['title']) ?></h3>
<div><a href="<?= url('post.php?slug='.$post['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary me-2"><i class="fas fa-eye"></i> View</a>
<a href="<?= url('admin/blog.php') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div></div>
<div class="card-body"><form method="POST" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="row g-3">
<?php if ($post['featured_image']): ?><div class="col-md-3"><img src="<?= asset_url('uploads/'.$post['featured_image']) ?>" class="img-fluid rounded-3" style="aspect-ratio:4/3;object-fit:cover;width:100%;"></div><?php endif; ?>
<div class="col-md-8"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required value="<?= e($post['title']) ?>"></div>
<div class="col-md-4"><label class="form-label">URL Slug</label><input type="text" name="slug" class="form-control" value="<?= e($post['slug']) ?>"></div>
<div class="col-md-3"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">-- None --</option><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $post['category_id']==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach(['Draft','Published','Unpublished','Archived'] as $s): ?><option <?= $post['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Tags</label><input type="text" name="tags" class="form-control" value="<?= e($post['tags'] ?? '') ?>"></div>
<div class="col-md-3"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="featured" value="1" <?= $post['featured']?'checked':'' ?> id="bpFeat"><label class="form-check-label fw-bold" for="bpFeat">⭐ Featured</label></div></div>
<div class="col-md-12"><label class="form-label">Change Featured Image</label><input type="file" name="featured_image" class="form-control" accept="image/*"></div>
<div class="col-md-12"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2"><?= e($post['excerpt'] ?? '') ?></textarea></div>
<div class="col-md-12"><label class="form-label">Content</label><textarea name="content" class="form-control ckeditor" rows="12"><?= e($post['content']) ?></textarea></div>
<fieldset class="col-md-12 border rounded p-3"><legend class="float-none w-auto px-2 fs-6 fw-bold">SEO</legend><div class="row g-2">
<div class="col-md-12"><label class="form-label">SEO Title</label><input type="text" name="seo_title" class="form-control" value="<?= e($post['seo_title'] ?? '') ?>"></div>
<div class="col-md-12"><label class="form-label">SEO Description</label><textarea name="seo_description" class="form-control" rows="2"><?= e($post['seo_description'] ?? '') ?></textarea></div>
</div></fieldset>
</div>
<div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
<a href="<?= url('admin/blog.php') ?>" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
