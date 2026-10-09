<?php
$pageTitle = 'Add Rescue Story';
require_once __DIR__ . '/includes/header.php';

$animals = pdo_fetch_all("SELECT id, name, species FROM animals ORDER BY name");

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/rescue-story-add.php')); }
    $title = clean_input($_POST['title'] ?? '');
    $slug = clean_input($_POST['slug'] ?? '') ?: slugify($title);
    $baseSlug = $slug; $i = 1;
    while (pdo_fetch("SELECT id FROM rescue_stories WHERE slug = ?", [$slug])) { $slug = $baseSlug . '-' . $i++; }
    
    $featuredImage = '';
    if (!empty($_FILES['featured_image']['name'])) {
        $r = secure_upload($_FILES['featured_image'], 'stories');
        if (!empty($r['success'])) $featuredImage = $r['path'];
    }
    
    $data = [
        'title' => $title, 'slug' => $slug,
        'animal_id' => !empty($_POST['animal_id']) ? (int)$_POST['animal_id'] : null,
        'category' => clean_input($_POST['category'] ?? ''),
        'rescue_date' => !empty($_POST['rescue_date']) ? $_POST['rescue_date'] : null,
        'location' => clean_input($_POST['location'] ?? ''),
        'author_id' => current_admin_id(),
        'featured_image' => $featuredImage,
        'excerpt' => clean_input($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'status' => clean_input($_POST['status'] ?? 'Draft'),
        'featured' => (int)($_POST['featured'] ?? 0),
        'seo_title' => clean_input($_POST['seo_title'] ?? ''),
        'seo_description' => clean_input($_POST['seo_description'] ?? ''),
        'published_at' => ($_POST['status'] ?? '') === 'Published' ? date('Y-m-d H:i:s') : null,
    ];
    $id = pdo_insert('rescue_stories', $data);
    
    if (!empty($_FILES['gallery']['name'][0])) {
        $count = count($_FILES['gallery']['name']);
        for ($i = 0; $i < $count; $i++) {
            $f = ['name'=>$_FILES['gallery']['name'][$i],'type'=>$_FILES['gallery']['type'][$i],'tmp_name'=>$_FILES['gallery']['tmp_name'][$i],'error'=>$_FILES['gallery']['error'][$i],'size'=>$_FILES['gallery']['size'][$i]];
            $r = secure_upload($f, 'stories');
            if (!empty($r['success'])) pdo_insert('rescue_story_images', ['story_id'=>$id,'image_path'=>$r['path'],'sort_order'=>$i]);
        }
    }
    
    flash('success','Story created!');
    redirect(url('admin/rescue-story-edit.php?id='.$id));
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-plus" style="color:var(--pb-teal)"></i> New Rescue Story</h3>
<a href="<?= url('admin/rescue-stories.php') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
<div class="card-body"><form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate><?= csrf_field() ?>
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required id="storyTitle" oninput="document.getElementById('storySlug').placeholder=slugify(this.value)"></div>
<div class="col-md-4"><label class="form-label">URL Slug</label><input type="text" name="slug" class="form-control" id="storySlug" placeholder="auto-generated"></div>
<div class="col-md-4"><label class="form-label">Animal</label><select name="animal_id" class="form-select"><option value="">-- None --</option><?php foreach($animals as $a): ?><option value="<?= $a['id'] ?>"><?= e($a['name'].' ('.$a['species'].')') ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option>Draft</option><option>Published</option><option>Unpublished</option><option>Archived</option></select></div>
<div class="col-md-3"><label class="form-label">Rescue Date</label><input type="date" name="rescue_date" class="form-control"></div>
<div class="col-md-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control" placeholder="City, State"></div>
<div class="col-md-6"><label class="form-label">Category</label><input type="text" name="category" class="form-control" placeholder="e.g. Dog Rescue, Happy Ending"></div>
<div class="col-md-6"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="featured" value="1" id="storyFeat"><label class="form-check-label fw-bold" for="storyFeat">⭐ Featured Story</label></div></div>
<div class="col-md-12"><label class="form-label">Featured Image</label><input type="file" name="featured_image" class="form-control" accept="image/*"></div>
<div class="col-md-12"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2" placeholder="Short preview..."></textarea></div>
<div class="col-md-12"><label class="form-label">Content *</label><textarea name="content" class="form-control ckeditor" rows="12" required></textarea></div>
<div class="col-md-12"><label class="form-label">Gallery Images</label><input type="file" name="gallery[]" class="form-control" multiple accept="image/*"></div>
<fieldset class="col-md-12 border rounded p-3"><legend class="float-none w-auto px-2 fs-6 fw-bold">SEO</legend><div class="row g-2">
<div class="col-md-12"><label class="form-label">SEO Title</label><input type="text" name="seo_title" class="form-control" placeholder="Optional"></div>
<div class="col-md-12"><label class="form-label">SEO Description</label><textarea name="seo_description" class="form-control" rows="2" placeholder="Optional"></textarea></div>
</div></fieldset>
</div>
<div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Story</button>
<a href="<?= url('admin/rescue-stories.php') ?>" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<script>function slugify(t){return t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'')}</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
