<?php
$pageTitle = 'Homepage Settings';
require_once __DIR__ . '/includes/header.php';
require_admin();

$sections = pdo_fetch_all("SELECT * FROM homepage_sections ORDER BY sort_order");
$stats = pdo_fetch_all("SELECT * FROM statistics ORDER BY sort_order, id");

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/homepage.php')); }
    
    if (isset($_POST['save_hero'])) {
        $hero = [
            'hero_heading_line1' => clean_input($_POST['h1']),
            'hero_heading_line2' => clean_input($_POST['h2']),
            'hero_heading_line3' => clean_input($_POST['h3']),
            'hero_description' => clean_input($_POST['desc']),
            'hero_cta1_text' => clean_input($_POST['cta1t']),
            'hero_cta1_link' => clean_input($_POST['cta1l']),
            'hero_cta2_text' => clean_input($_POST['cta2t']),
            'hero_cta2_link' => clean_input($_POST['cta2l']),
        ];
        foreach ($hero as $k => $v) {
            $exists = pdo_fetch("SELECT id FROM site_settings WHERE setting_key = ?", [$k]);
            if ($exists) pdo_update('site_settings', ['setting_value'=>$v,'setting_group'=>'homepage'], 'setting_key = ?', [$k]);
            else pdo_insert('site_settings', ['setting_key'=>$k,'setting_value'=>$v,'setting_group'=>'homepage']);
        }
        foreach(['hero_image_primary','hero_image_secondary','hero_image_tertiary'] as $imgKey) {
            if (!empty($_FILES[$imgKey]['name'])) {
                $r = secure_upload($_FILES[$imgKey], 'homepage');
                if (!empty($r['success'])) {
                    $old = pdo_fetch("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$imgKey]);
                    if ($old && $old['setting_value']) delete_file($old['setting_value']);
                    $exists = pdo_fetch("SELECT id FROM site_settings WHERE setting_key = ?", [$imgKey]);
                    if ($exists) pdo_update('site_settings', ['setting_value'=>$r['path'],'setting_group'=>'homepage'], 'setting_key = ?', [$imgKey]);
                    else pdo_insert('site_settings', ['setting_key'=>$imgKey,'setting_value'=>$r['path'],'setting_group'=>'homepage']);
                }
            }
        }
        flash('success','Hero section updated!');
    }
    
    if (isset($_POST['save_donation'])) {
        foreach (['donation_cta_title','donation_cta_text'] as $k) {
            $v = clean_input($_POST[$k]);
            $exists = pdo_fetch("SELECT id FROM site_settings WHERE setting_key = ?", [$k]);
            if ($exists) pdo_update('site_settings', ['setting_value'=>$v,'setting_group'=>'homepage'], 'setting_key = ?', [$k]);
            else pdo_insert('site_settings', ['setting_key'=>$k,'setting_value'=>$v,'setting_group'=>'homepage']);
        }
        flash('success','Donation CTA updated!');
    }
    
    if (isset($_POST['save_mission'])) {
        foreach (['mission_title','mission_text'] as $k) {
            $v = clean_input($_POST[$k]);
            $exists = pdo_fetch("SELECT id FROM site_settings WHERE setting_key = ?", [$k]);
            if ($exists) pdo_update('site_settings', ['setting_value'=>$v,'setting_group'=>'homepage'], 'setting_key = ?', [$k]);
            else pdo_insert('site_settings', ['setting_key'=>$k,'setting_value'=>$v,'setting_group'=>'homepage']);
        }
        flash('success','Mission updated!');
    }
    
    if (isset($_POST['save_section'])) {
        $sid = (int)$_POST['section_id'];
        pdo_update('homepage_sections', ['content'=>$_POST['content_json'],'is_active'=>(int)($_POST['active'] ?? 1),'updated_by'=>current_admin_id()], 'id = ?', [$sid]);
        flash('success','Section saved!');
    }
    
    if (isset($_POST['new_stat'])) {
        pdo_insert('statistics', ['stat_key'=>slugify($_POST['stat_label']), 'stat_label'=>clean_input($_POST['stat_label']), 'stat_value'=>clean_input($_POST['stat_value']), 'icon'=>clean_input($_POST['icon'] ?? ''), 'display_on_home'=>(int)($_POST['display'] ?? 1), 'sort_order'=>(int)($_POST['order'] ?? 0)]);
        flash('success','Stat added!');
    }
    
    if (isset($_POST['update_stat'])) {
        pdo_update('statistics', ['stat_label'=>clean_input($_POST['stat_label']), 'stat_value'=>clean_input($_POST['stat_value']), 'icon'=>clean_input($_POST['icon'] ?? ''), 'display_on_home'=>(int)($_POST['display'] ?? 1), 'sort_order'=>(int)($_POST['order'] ?? 0)], 'id = ?', [(int)$_POST['stat_id']]);
        flash('success','Stat updated!');
    }
    
    if (isset($_POST['delete_stat'])) {
        pdo_delete('statistics', 'id = ?', [(int)$_POST['delete_stat']]);
        flash('success','Stat deleted!');
    }
    
    redirect(url('admin/homepage.php'));
}
?>
<ul class="nav nav-tabs mb-4" id="homeTabs" role="tablist">
<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabHero">Hero Section</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMission">Mission</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabStats">Statistics</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSections">Content Sections</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDonation">Donation CTA</button></li>
</ul>

<div class="tab-content">
<div class="tab-pane fade show active" id="tabHero">
<div class="card"><div class="card-header"><h3>Hero Section</h3></div>
<div class="card-body"><form method="POST" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Heading Line 1</label><input type="text" name="h1" class="form-control" value="<?= e(setting('hero_heading_line1','Every Paw')) ?>"></div>
<div class="col-md-4"><label class="form-label">Heading Line 2</label><input type="text" name="h2" class="form-control" value="<?= e(setting('hero_heading_line2','Deserves a')) ?>"></div>
<div class="col-md-4"><label class="form-label">Heading Line 3</label><input type="text" name="h3" class="form-control" value="<?= e(setting('hero_heading_line3','Second Chance')) ?>"></div>
<div class="col-md-12"><label class="form-label">Description</label><textarea name="desc" class="form-control" rows="3"><?= e(setting('hero_description','We rescue, transport and rehome animals at risk.')) ?></textarea></div>
<div class="col-md-3"><label class="form-label">Primary CTA Text</label><input type="text" name="cta1t" class="form-control" value="<?= e(setting('hero_cta1_text','Adopt a Pet')) ?>"></div>
<div class="col-md-3"><label class="form-label">Primary CTA Link</label><input type="text" name="cta1l" class="form-control" value="<?= e(setting('hero_cta1_link','animals.php')) ?>"></div>
<div class="col-md-3"><label class="form-label">Secondary CTA Text</label><input type="text" name="cta2t" class="form-control" value="<?= e(setting('hero_cta2_text','Help Us Rescue')) ?>"></div>
<div class="col-md-3"><label class="form-label">Secondary CTA Link</label><input type="text" name="cta2l" class="form-control" value="<?= e(setting('hero_cta2_link','donate.php')) ?>"></div>
<?php foreach(['hero_image_primary'=>'Primary Image','hero_image_secondary'=>'Secondary Image','hero_image_tertiary'=>'Tertiary Image'] as $k=>$lbl): ?>
<div class="col-md-4"><label class="form-label"><?= $lbl ?></label>
<?php if (setting($k)): ?><img src="<?= asset_url('uploads/'.setting($k)) ?>" class="img-fluid rounded-3 mb-2" style="aspect-ratio:4/3;object-fit:cover;width:100%;max-height:150px;"><?php endif; ?>
<input type="file" name="<?= $k ?>" class="form-control" accept="image/*"></div>
<?php endforeach; ?>
</div>
<div class="mt-4"><button type="submit" name="save_hero" class="btn btn-primary"><i class="fas fa-save"></i> Save Hero Section</button></div>
</form></div></div>
</div>

<div class="tab-pane fade" id="tabMission">
<div class="card"><div class="card-header"><h3>Mission Block</h3></div>
<div class="card-body"><form method="POST"><?= csrf_field() ?>
<div class="mb-3"><label class="form-label">Mission Title</label><input type="text" name="mission_title" class="form-control" value="<?= e(setting('mission_title','Our Mission')) ?>"></div>
<div class="mb-3"><label class="form-label">Mission Text</label><textarea name="mission_text" class="form-control" rows="4"><?= e(setting('mission_text','Every animal deserves safety, care, and a chance to thrive.')) ?></textarea></div>
<button type="submit" name="save_mission" class="btn btn-primary"><i class="fas fa-save"></i> Save Mission</button>
</form></div></div>
</div>

<div class="tab-pane fade" id="tabStats">
<div class="card"><div class="card-header"><h3>Impact Statistics</h3></div>
<div class="card-body">
<button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#statModal"><i class="fas fa-plus"></i> Add Statistic</button>
<div class="table-responsive"><table class="table">
<thead><tr><th>Label</th><th>Value</th><th>Icon</th><th>Home Display</th><th>Order</th><th>Actions</th></tr></thead>
<tbody><?php foreach($stats as $s): ?>
<tr><form method="POST" class="d-inline"><?= csrf_field() ?><input type="hidden" name="stat_id" value="<?= $s['id'] ?>">
<td><input type="text" name="stat_label" class="form-control form-control-sm" value="<?= e($s['stat_label']) ?>"></td>
<td><input type="text" name="stat_value" class="form-control form-control-sm" value="<?= e($s['stat_value']) ?>"></td>
<td><input type="text" name="icon" class="form-control form-control-sm" value="<?= e($s['icon'] ?? '') ?>" placeholder="fa-paw"></td>
<td><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="display" value="1" <?= !empty($s['display_on_home'])?'checked':'' ?>></div></td>
<td><input type="number" name="order" class="form-control form-control-sm" value="<?= $s['sort_order'] ?? 0 ?>" style="width:70px;"></td>
<td><button type="submit" name="update_stat" class="btn btn-sm btn-primary">Save</button>
<button type="submit" name="delete_stat" value="<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></button></td>
</form></tr><?php endforeach; ?></tbody></table></div>
</div></div>
<div class="modal fade" id="statModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title">Add Statistic</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><?= csrf_field() ?><div class="modal-body">
<div class="mb-3"><label class="form-label">Label *</label><input type="text" name="stat_label" class="form-control" required placeholder="Animals Rescued"></div>
<div class="mb-3"><label class="form-label">Value *</label><input type="text" name="stat_value" class="form-control" required placeholder="1,200+"></div>
<div class="mb-3"><label class="form-label">FontAwesome Icon</label><input type="text" name="icon" class="form-control" placeholder="fa-paw"></div>
<div class="row">
<div class="col-md-6"><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="display" value="1" checked id="dh"><label class="form-check-label" for="dh">Display on homepage</label></div></div>
<div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" name="order" class="form-control" value="0"></div>
</div></div>
<div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="new_stat" class="btn btn-primary"><i class="fas fa-save"></i> Add</button></div></form></div></div></div>
</div>

<div class="tab-pane fade" id="tabSections">
<?php foreach($sections as $sec): ?>
<div class="card mb-4"><div class="card-header"><h3><?= e($sec['section_name']) ?> <small class="badge <?= !empty($sec['is_active'])?'bg-success':'bg-secondary' ?>"><?= !empty($sec['is_active'])?'Active':'Inactive' ?></small></h3></div>
<div class="card-body"><form method="POST"><?= csrf_field() ?><input type="hidden" name="section_id" value="<?= $sec['id'] ?>">
<div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="active" value="1" <?= !empty($sec['is_active'])?'checked':'' ?> id="a<?= $sec['id'] ?>"><label class="form-check-label fw-bold" for="a<?= $sec['id'] ?>">Section Active</label></div></div>
<div class="mb-3"><label class="form-label">Section Content (JSON)</label>
<textarea name="content_json" class="form-control" rows="10" spellcheck="false"><?= e($sec['content'] ?? '{}') ?></textarea>
<div class="form-text">This JSON is used to render the homepage section. Edit with care.</div></div>
<button type="submit" name="save_section" class="btn btn-primary"><i class="fas fa-save"></i> Save Section</button>
</form></div></div>
<?php endforeach; ?>
</div>

<div class="tab-pane fade" id="tabDonation">
<div class="card"><div class="card-header"><h3>Donation CTA Section</h3></div>
<div class="card-body"><form method="POST"><?= csrf_field() ?>
<div class="mb-3"><label class="form-label">CTA Title</label><input type="text" name="donation_cta_title" class="form-control" value="<?= e(setting('donation_cta_title','A Small Act Can Change a Life')) ?>"></div>
<div class="mb-3"><label class="form-label">CTA Text</label><textarea name="donation_cta_text" class="form-control" rows="4"><?= e(setting('donation_cta_text','Your support helps provide rescue, transport, food, medical care and safe homes for animals in need.')) ?></textarea></div>
<button type="submit" name="save_donation" class="btn btn-primary"><i class="fas fa-save"></i> Save Donation CTA</button>
</form></div></div>
</div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
