<?php
$pageTitle = 'Edit Animal';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$animal = pdo_fetch("SELECT * FROM animals WHERE id = ?", [$id]);
if (!$animal) { flash('error', 'Animal not found'); redirect(url('admin/animals.php')); }
$images = pdo_fetch_all("SELECT * FROM animal_images WHERE animal_id = ? ORDER BY is_primary DESC, sort_order ASC", [$id]);

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/animal-edit.php?id='.$id)); }
    
    if (isset($_POST['delete_image'])) {
        $imgId = (int)$_POST['delete_image'];
        $img = pdo_fetch("SELECT * FROM animal_images WHERE id = ?", [$imgId]);
        if ($img) { delete_file($img['image_path']); pdo_delete('animal_images', 'id = ?', [$imgId]); }
        flash('success', 'Image deleted');
        redirect(url('admin/animal-edit.php?id='.$id));
    }
    if (isset($_POST['set_primary'])) {
        $imgId = (int)$_POST['set_primary'];
        pdo_update('animal_images', ['is_primary' => 0], 'animal_id = ?', [$id]);
        pdo_update('animal_images', ['is_primary' => 1], 'id = ?', [$imgId]);
        flash('success', 'Primary image updated');
        redirect(url('admin/animal-edit.php?id='.$id));
    }
    
    $data = [
        'name' => clean_input($_POST['name'] ?? ''),
        'species' => clean_input($_POST['species'] ?? 'Dog'),
        'breed' => clean_input($_POST['breed'] ?? ''),
        'age_years' => (int)($_POST['age_years'] ?? 0),
        'age_months' => (int)($_POST['age_months'] ?? 0),
        'gender' => clean_input($_POST['gender'] ?? 'Unknown'),
        'size' => clean_input($_POST['size'] ?? 'Medium'),
        'location' => clean_input($_POST['location'] ?? ''),
        'color' => clean_input($_POST['color'] ?? ''),
        'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null,
        'personality' => clean_input($_POST['personality'] ?? ''),
        'medical_info' => clean_input($_POST['medical_info'] ?? ''),
        'vaccinated' => (int)($_POST['vaccinated'] ?? 0),
        'spayed_neutered' => (int)($_POST['spayed_neutered'] ?? 0),
        'good_with_children' => isset($_POST['good_with_children']) ? (int)$_POST['good_with_children'] : null,
        'good_with_other_animals' => isset($_POST['good_with_other_animals']) ? (int)$_POST['good_with_other_animals'] : null,
        'description' => $_POST['description'] ?? '',
        'rescue_story' => $_POST['rescue_story'] ?? '',
        'rescue_date' => !empty($_POST['rescue_date']) ? $_POST['rescue_date'] : null,
        'status' => clean_input($_POST['status'] ?? 'Available'),
        'featured' => (int)($_POST['featured'] ?? 0),
    ];
    
    pdo_update('animals', $data, 'id = ?', [$id]);
    
    if (!empty($_FILES['images']['name'][0])) {
        $count = count($_FILES['images']['name']);
        $maxOrder = (int)(pdo_fetch("SELECT MAX(sort_order) AS m FROM animal_images WHERE animal_id = ?", [$id])['m'] ?? 0);
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name' => $_FILES['images']['name'][$i],
                'type' => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i],
            ];
            $result = secure_upload($file, 'animals');
            if (!empty($result['success'])) {
                pdo_insert('animal_images', [
                    'animal_id' => $id,
                    'image_path' => $result['path'],
                    'is_primary' => count($images) === 0 && $i === 0 ? 1 : 0,
                    'sort_order' => $maxOrder + $i + 1,
                ]);
            }
        }
    }
    
    flash('success','Animal updated successfully!');
    redirect(url('admin/animal-edit.php?id='.$id));
}
?>
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-edit" style="color:var(--pb-secondary);margin-right:8px;"></i> Edit: <?= e($animal['name']) ?></h3>
        <div>
            <a href="<?= url('animal.php?id='.$id) ?>" target="_blank" class="btn btn-sm btn-secondary me-2"><i class="fas fa-eye"></i> View Public</a>
            <a href="<?= url('admin/animals.php') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3 mb-4">
                <?php foreach($images as $img): ?>
                <div class="col-md-3">
                    <div class="position-relative">
                        <img src="<?= asset_url('uploads/'.$img['image_path']) ?>" class="img-fluid rounded-3" style="aspect-ratio:1;object-fit:cover;width:100%;">
                        <div class="position-absolute top-0 end-0 m-2 d-flex gap-1">
                            <form method="POST" action="" style="display:inline;"><?= csrf_field() ?><button type="submit" name="set_primary" value="<?= $img['id'] ?>" class="btn btn-sm <?= $img['is_primary'] ? 'btn-success' : 'btn-light' ?>" title="Set primary"><i class="fas fa-star"></i></button></form>
                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Delete this image?')"><?= csrf_field() ?><button type="submit" name="delete_image" value="<?= $img['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                        </div>
                        <?php if ($img['is_primary']): ?><span class="badge badge-success position-absolute bottom-0 start-0 m-2">Primary</span><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="col-md-12">
                    <label class="form-label">Upload More Photos</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" value="<?= e($animal['name']) ?>" required></div>
                <div class="col-md-3">
                    <label class="form-label">Species *</label>
                    <select name="species" class="form-select">
                        <?php foreach(['Dog','Cat','Rabbit','Bird','Other'] as $s): ?><option <?= $animal['species']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Breed</label><input type="text" name="breed" class="form-control" value="<?= e($animal['breed'] ?? '') ?>"></div>
                <div class="col-md-2"><label class="form-label">Years</label><input type="number" name="age_years" class="form-control" value="<?= $animal['age_years'] ?>" min="0" max="30"></div>
                <div class="col-md-2"><label class="form-label">Months</label><input type="number" name="age_months" class="form-control" value="<?= $animal['age_months'] ?>" min="0" max="11"></div>
                <div class="col-md-2">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <?php foreach(['Unknown','Male','Female'] as $s): ?><option <?= $animal['gender']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Size</label>
                    <select name="size" class="form-select">
                        <?php foreach(['Small','Medium','Large','Extra Large'] as $s): ?><option <?= $animal['size']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Weight (kg)</label><input type="number" step="0.1" name="weight" class="form-control" value="<?= $animal['weight'] ?? '' ?>"></div>
                <div class="col-md-2"><label class="form-label">Color</label><input type="text" name="color" class="form-control" value="<?= e($animal['color'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= e($animal['location'] ?? '') ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select">
                        <?php foreach(['Available','Adoption Pending','Adopted','Fostered','Medical Hold','Rescued'] as $s): ?><option <?= $animal['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Rescue Date</label><input type="date" name="rescue_date" class="form-control" value="<?= $animal['rescue_date'] ?? '' ?>"></div>
                <div class="col-md-12"><label class="form-label">Personality</label><textarea name="personality" class="form-control" rows="2"><?= e($animal['personality'] ?? '') ?></textarea></div>
                <div class="col-md-12"><label class="form-label">Medical Information</label><textarea name="medical_info" class="form-control" rows="2"><?= e($animal['medical_info'] ?? '') ?></textarea></div>
                <div class="col-md-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="5"><?= e($animal['description'] ?? '') ?></textarea></div>
                <div class="col-md-12"><label class="form-label">Rescue Story</label><textarea name="rescue_story" class="form-control" rows="4"><?= e($animal['rescue_story'] ?? '') ?></textarea></div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="vaccinated" value="1" <?= $animal['vaccinated']?'checked':'' ?>><label class="form-check-label">Vaccinated</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="spayed_neutered" value="1" <?= $animal['spayed_neutered']?'checked':'' ?>><label class="form-check-label">Spayed / Neutered</label></div></div>
                        <div class="col-md-3">
                            <label class="form-label d-block">Good with children</label>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="good_with_children" id="gwy1" value="1" <?= ($animal['good_with_children']==='1')?'checked':'' ?>><label class="btn btn-outline-success" for="gwy1">Yes</label>
                                <input type="radio" class="btn-check" name="good_with_children" id="gwy2" value="0" <?= ($animal['good_with_children']==='0')?'checked':'' ?>><label class="btn btn-outline-danger" for="gwy2">No</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">Good with animals</label>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="good_with_other_animals" id="gwo1" value="1" <?= ($animal['good_with_other_animals']==='1')?'checked':'' ?>><label class="btn btn-outline-success" for="gwo1">Yes</label>
                                <input type="radio" class="btn-check" name="good_with_other_animals" id="gwo2" value="0" <?= ($animal['good_with_other_animals']==='0')?'checked':'' ?>><label class="btn btn-outline-danger" for="gwo2">No</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="featured" value="1" <?= $animal['featured']?'checked':'' ?> id="feat"><label class="form-check-label fw-bold" for="feat">⭐ Featured on homepage</label></div></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="<?= url('admin/animals.php') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
