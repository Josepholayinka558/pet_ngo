<?php
$pageTitle = 'Edit Rescue Story';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$story = pdo_fetch("SELECT * FROM rescue_stories WHERE id = ?", [$id]);
if (!$story) { flash('error', 'Not found'); redirect(url('admin/rescue-stories.php')); }
$gallery = pdo_fetch_all("SELECT * FROM rescue_story_images WHERE story_id = ? ORDER BY sort_order", [$id]);
$animals = pdo_fetch_all("SELECT id, name, species FROM animals ORDER BY name");

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/rescue-story-edit.php?id='.$id)); }
    if (isset($_POST['delete_image'])) {
        $gid = (int)$_POST['delete_image'];
        $gi = pdo_fetch("SELECT * FROM rescue_story_images WHERE id = ?", [$gid]);
        if ($gi) { delete_file($gi['image_path']); pdo_delete('rescue_story_images', 'id = ?', [$gid]); }
        flash('success','Image deleted'); redirect(url('admin/rescue-story-edit.php?id='.$id));
    }
    $title = clean_input($_POST['title'] ?? '');
    $slug = clean_input($_POST['slug'] ?? '') ?: slugify($title);
    $baseSlug = $slug; $i = 1;
    while (($e = pdo_fetch("SELECT id FROM rescue_stories WHERE slug = ?", [$slug])) && $e['id'] != $id) { $slug = $baseSlug . '-' . $i++; }
    
    $data = [
        'title' => $title, 'slug' => $slug,
        'animal_id' => !empty($_POST['animal_id']) ? (int)$_POST['animal_id'] : null,
        'category' => clean_input($_POST['category'] ?? ''),
        'rescue_date' => !empty($_POST['rescue_date']) ? $_POST['rescue_date'] : null,
        'location' => clean_input($_POST['location'] ?? ''),
        'excerpt' => clean_input($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'status' => clean_input($_POST['status'] ?? 'Draft'),
        'featured' => (int)($_POST['featured'] ?? 0),
        'seo_title' => clean_input($_POST['seo_title'] ?? ''),
        'seo_description' => clean_input($_POST['seo_description'] ?? ''),
    ];
    if (($story['status'] !== 'Published') && ($_POST['status'] ?? '') === 'Published') $data['published_at'] = date('Y-m-d H:i:s');
    
    if (!empty($_FILES['featured_image']['name'])) {
        if ($story['featured_image']) delete_file($story['featured_image']);
        $r = secure_upload($_FILES['featured_image'], 'stories');
        if (!empty($r['success'])) $data['featured_image'] = $r['path'];
    }
    pdo_update('rescue_stories', $data, 'id = ?', [$id]);
    
    if (!empty($_FILES['gallery']['name'][0])) {
        $count = count($_FILES['gallery']['name']);
        $maxOrder = (int)(pdo_fetch("SELECT MAX(sort_order) AS m FROM rescue_story_images WHERE story_id = ?", [$id])['m'] ?? 0);
        for ($i = 0; $i < $count; $i++) {
            $f = ['name'=>$_FILES['gallery']['name'][$i],'type'=>$_FILES['gallery']['type'][$i],'tmp_name'=>$_FILES['gallery']['tmp_name'][$i],'error'=>$_FILES['gallery']['error'][$i],'size'=>$_FILES['gallery']['size'][$i]];
            $r = secure_upload($f, 'stories');
            if (!empty($r['success'])) pdo_insert('rescue_story_images', ['story_id'=>$id,'image_path'=>$r['path'],'sort_order'=>$maxOrder + $i + 1]);
        }
    }
    flash('success','Story updated!'); redirect(url('admin/rescue-story-edit.php?id='.$id));
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-edit" style="color:var(--pb-teal)"></i> Edit Story: <?= e($story['title']) ?></h3>
<div><a href="<?= url('story.php?slug='.$story['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary me-2"><i class="fas fa-eye"></i> View</a>
<a href="<?= url('admin/rescue-stories.php') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div></div>
<div class="card-body"><form method="POST" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="row g-3">
<?php if ($story['featured_image']): ?>
<div class="col-md-3"><div class="position-relative"><img src="<?= asset_url('uploads/'.$story['featured_image']) ?>" class="img-fluid rounded-3" style="aspect-ratio:4/3;object-fit:cover;width:100%;"></div></div>
<?php endif; ?>
<?php foreach($gallery as $g): ?>
<div class="col-md-2"><div class="position-relative"><img src="<?= asset_url('uploads/'.$g['image_path']) ?>" class="img-fluid rounded-3" style="aspect-ratio:1;object-fit:cover;width:100%;">
<form method="POST" style="position:absolute;top:4px;right:4px;" onsubmit="return confirm('Delete image?')"><?= csrf_field() ?><button type="submit" name="delete_image" value="<?= $g['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
</div></div>
<?php endforeach; ?>
<div class="col-md-8"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required value="<?= e($story['title']) ?>"></div>
<div class="col-md-4"><label class="form-label">URL Slug</label><input type="text" name="slug" class="form-control" value="<?= e($story['slug']) ?>"></div>
<div class="col-md-4"><label class="form-label">Animal</label><select name="animal_id" class="form-select"><option value="">-- None --</option><?php foreach($animals as $a): ?><option value="<?= $a['id'] ?>" <?= $story['animal_id']==$a['id']?'selected':'' ?>><?= e($a['name'].' ('.$a['species'].')') ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach(['Draft','Published','Unpublished','Archived'] as $s): ?><option <?= $story['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Rescue Date</label><input type="date" name="rescue_date" class="form-control" value="<?= $story['rescue_date'] ?? '' ?>"></div>
<div class="col-md-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= e($story['location'] ?? '') ?>"></div>
<div class="col-md-6"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="<?= e($story['category'] ?? '') ?>"></div>
<div class="col-md-6"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="featured" value="1" <?= $story['featured']?'checked':'' ?> id="featStory"><label class="form-check-label fw-bold" for="featStory">⭐ Featured Story</label></div></div>
<div class="col-md-12"><label class="form-label">Change Featured Image</label><input type="file" name="featured_image" class="form-control" accept="image/*"></div>
<div class="col-md-12"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2"><?= e($story['excerpt'] ?? '') ?></textarea></div>
<div class="col-md-12"><label class="form-label">Content</label><textarea name="content" class="form-control ckeditor" rows="12"><?= e($story['content']) ?></textarea></div>
<div class="col-md-12"><label class="form-label">Add Gallery Images</label><input type="file" name="gallery[]" class="form-control" multiple accept="image/*"></div>
<fieldset class="col-md-12 border rounded p-3"><legend class="float-none w-auto px-2 fs-6 fw-bold">SEO</legend><div class="row g-2">
<div class="col-md-12"><label class="form-label">SEO Title</label><input type="text" name="seo_title" class="form-control" value="<?= e($story['seo_title'] ?? '') ?>"></div>
<div class="col-md-12"><label class="form-label">SEO Description</label><textarea name="seo_description" class="form-control" rows="2"><?= e($story['seo_description'] ?? '') ?></textarea></div>
</div></fieldset>
</div>
<div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
<a href="<?= url('admin/rescue-stories.php') ?>" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
