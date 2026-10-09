<?php
$pageTitle = 'Add Blog Post';
require_once __DIR__ . '/includes/header.php';
$cats = pdo_fetch_all("SELECT * FROM blog_categories ORDER BY name");
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/blog-post-add.php')); }
    $title = clean_input($_POST['title'] ?? '');
    $slug = clean_input($_POST['slug'] ?? '') ?: slugify($title);
    $baseSlug = $slug; $i = 1;
    while (pdo_fetch("SELECT id FROM blog_posts WHERE slug = ?", [$slug])) { $slug = $baseSlug . '-' . $i++; }
    $featImg = '';
    if (!empty($_FILES['featured_image']['name'])) {
        $r = secure_upload($_FILES['featured_image'], 'blog');
        if (!empty($r['success'])) $featImg = $r['path'];
    }
    $data = ['title'=>$title,'slug'=>$slug,'category_id'=>!empty($_POST['category_id'])?(int)$_POST['category_id']:null,'author_id'=>current_admin_id(),'featured_image'=>$featImg,'excerpt'=>clean_input($_POST['excerpt'] ?? ''),'content'=>$_POST['content'] ?? '','tags'=>clean_input($_POST['tags'] ?? ''),'status'=>clean_input($_POST['status'] ?? 'Draft'),'featured'=>(int)($_POST['featured'] ?? 0),'seo_title'=>clean_input($_POST['seo_title'] ?? ''),'seo_description'=>clean_input($_POST['seo_description'] ?? ''),'published_at' => ($_POST['status'] ?? '') === 'Published' ? date('Y-m-d H:i:s') : null];
    $id = pdo_insert('blog_posts', $data);
    flash('success','Post created!'); redirect(url('admin/blog-post-edit.php?id='.$id));
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-plus" style="color:#102238"></i> New Blog Post</h3>
<a href="<?= url('admin/blog.php') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
<div class="card-body"><form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate><?= csrf_field() ?>
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required id="bpTitle" oninput="document.getElementById('bpSlug').placeholder=slugify(this.value)"></div>
<div class="col-md-4"><label class="form-label">URL Slug</label><input type="text" name="slug" class="form-control" id="bpSlug" placeholder="auto-generated"></div>
<div class="col-md-3"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">-- None --</option><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option>Draft</option><option>Published</option><option>Unpublished</option><option>Archived</option></select></div>
<div class="col-md-3"><label class="form-label">Tags</label><input type="text" name="tags" class="form-control" placeholder="comma separated"></div>
<div class="col-md-3"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="featured" value="1" id="bpFeat"><label class="form-check-label fw-bold" for="bpFeat">⭐ Featured</label></div></div>
<div class="col-md-12"><label class="form-label">Featured Image</label><input type="file" name="featured_image" class="form-control" accept="image/*"></div>
<div class="col-md-12"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2"></textarea></div>
<div class="col-md-12"><label class="form-label">Content *</label><textarea name="content" class="form-control ckeditor" rows="12" required></textarea></div>
<fieldset class="col-md-12 border rounded p-3"><legend class="float-none w-auto px-2 fs-6 fw-bold">SEO</legend><div class="row g-2">
<div class="col-md-12"><label class="form-label">SEO Title</label><input type="text" name="seo_title" class="form-control"></div>
<div class="col-md-12"><label class="form-label">SEO Description</label><textarea name="seo_description" class="form-control" rows="2"></textarea></div>
</div></fieldset>
</div>
<div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Post</button>
<a href="<?= url('admin/blog.php') ?>" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<script>function slugify(t){return t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'')}</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
