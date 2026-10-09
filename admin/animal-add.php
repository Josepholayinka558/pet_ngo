<?php
$pageTitle = 'Add Animal';
require_once __DIR__ . '/includes/header.php';

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/animal-add.php')); }
    
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
        'created_by' => current_admin_id(),
    ];
    
    $animalId = pdo_insert('animals', $data);
    
    if (!empty($_FILES['images']['name'][0])) {
        $count = count($_FILES['images']['name']);
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
                    'animal_id' => $animalId,
                    'image_path' => $result['path'],
                    'is_primary' => ($i === 0) ? 1 : 0,
                    'sort_order' => $i,
                ]);
            }
        }
    }
    
    flash('success','Animal added successfully!');
    redirect(url('admin/animal-edit.php?id='.$animalId));
}
?>
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle" style="color:var(--pb-secondary);margin-right:8px;"></i> Add New Animal</h3>
        <a href="<?= url('admin/animals.php') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Animals</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-md-3">
                    <label class="form-label">Species *</label>
                    <select name="species" class="form-select"><option>Dog</option><option>Cat</option><option>Rabbit</option><option>Bird</option><option>Other</option></select>
                </div>
                <div class="col-md-3"><label class="form-label">Breed</label><input type="text" name="breed" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Years</label><input type="number" name="age_years" class="form-control" value="0" min="0" max="30"></div>
                <div class="col-md-2"><label class="form-label">Months</label><input type="number" name="age_months" class="form-control" value="0" min="0" max="11"></div>
                <div class="col-md-2">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select"><option>Unknown</option><option>Male</option><option>Female</option></select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Size</label>
                    <select name="size" class="form-select"><option>Small</option><option selected>Medium</option><option>Large</option><option>Extra Large</option></select>
                </div>
                <div class="col-md-2"><label class="form-label">Weight (kg)</label><input type="number" step="0.1" name="weight" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Color</label><input type="text" name="color" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Location</label><input type="text" name="location" class="form-control" placeholder="City, State"></div>
                <div class="col-md-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select">
                        <option>Available</option><option>Adoption Pending</option><option>Adopted</option>
                        <option>Fostered</option><option>Medical Hold</option><option>Rescued</option>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Rescue Date</label><input type="date" name="rescue_date" class="form-control"></div>
                <div class="col-md-12"><label class="form-label">Personality</label><textarea name="personality" class="form-control" rows="2" placeholder="Friendly, playful, loves cuddles..."></textarea></div>
                <div class="col-md-12"><label class="form-label">Medical Information</label><textarea name="medical_info" class="form-control" rows="2" placeholder="Vaccinations, conditions, medications..."></textarea></div>
                <div class="col-md-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="5" placeholder="Full description of the animal..."></textarea></div>
                <div class="col-md-12"><label class="form-label">Rescue Story</label><textarea name="rescue_story" class="form-control" rows="4" placeholder="The story of how this animal was rescued..."></textarea></div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="vaccinated" value="1"><label class="form-check-label">Vaccinated</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="spayed_neutered" value="1"><label class="form-check-label">Spayed / Neutered</label></div></div>
                        <div class="col-md-3">
                            <label class="form-label d-block">Good with children</label>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="good_with_children" id="gwy1" value="1"><label class="btn btn-outline-success" for="gwy1">Yes</label>
                                <input type="radio" class="btn-check" name="good_with_children" id="gwy2" value="0"><label class="btn btn-outline-danger" for="gwy2">No</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">Good with animals</label>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="good_with_other_animals" id="gwo1" value="1"><label class="btn btn-outline-success" for="gwo1">Yes</label>
                                <input type="radio" class="btn-check" name="good_with_other_animals" id="gwo2" value="0"><label class="btn btn-outline-danger" for="gwo2">No</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="featured" value="1" id="feat"><label class="form-check-label fw-bold" for="feat">⭐ Featured on homepage</label></div></div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">Photos (First will be primary)</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <div class="form-text">Upload JPG, PNG or WEBP. Max 5MB each. First image becomes the featured/primary photo.</div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Animal</button>
                <a href="<?= url('admin/animals.php') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
