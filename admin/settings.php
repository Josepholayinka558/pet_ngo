<?php
$pageTitle = 'Website Settings';
require_once __DIR__ . '/includes/header.php';

$groups = ['general'=>'General','branding'=>'Branding','colors'=>'Colors','contact'=>'Contact','social'=>'Social Media','footer'=>'Footer','seo'=>'SEO'];

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/settings.php')); }
    
    if (isset($_POST['save_settings'])) {
        $group = clean_input($_POST['group']);
        foreach ($_POST as $k => $v) {
            if (in_array($k, ['csrf_token','save_settings','group'])) continue;
            if (is_string($v)) $v = clean_input($v);
            $exists = pdo_fetch("SELECT id FROM site_settings WHERE setting_key = ?", [$k]);
            if ($exists) pdo_update('site_settings', ['setting_value' => $v, 'setting_group' => $group], 'setting_key = ?', [$k]);
            else pdo_insert('site_settings', ['setting_key' => $k, 'setting_value' => $v, 'setting_group' => $group]);
        }
        foreach (['site_logo', 'site_favicon'] as $imgKey) {
            if (!empty($_FILES[$imgKey]['name'])) {
                $r = secure_upload($_FILES[$imgKey], 'branding');
                if (!empty($r['success'])) {
                    $old = pdo_fetch("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$imgKey]);
                    if ($old && $old['setting_value']) delete_file($old['setting_value']);
                    $exists = pdo_fetch("SELECT id FROM site_settings WHERE setting_key = ?", [$imgKey]);
                    if ($exists) pdo_update('site_settings', ['setting_value' => $r['path'], 'setting_group' => 'branding'], 'setting_key = ?', [$imgKey]);
                    else pdo_insert('site_settings', ['setting_key' => $imgKey, 'setting_value' => $r['path'], 'setting_group' => 'branding']);
                }
            }
        }
        flash('success', 'Settings saved!');
        redirect(url('admin/settings.php'));
    }
}

function s($k, $d='') { return e(setting($k, $d)); }
?>
<ul class="nav nav-tabs mb-4" id="setTabs" role="tablist">
<?php $first = true; foreach ($groups as $g => $label): ?>
<li class="nav-item"><button class="nav-link <?= $first?'active':'' ?>" data-bs-toggle="tab" data-bs-target="#tab<?= ucfirst($g) ?>"><?= $label ?></button></li>
<?php $first = false; endforeach; ?>
</ul>
<div class="tab-content">

<div class="tab-pane fade show active" id="tabGeneral">
<div class="card"><div class="card-header"><h3>General Settings</h3></div>
<div class="card-body"><form method="POST" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="group" value="general"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Organization Name</label><input type="text" name="site_name" class="form-control" value="<?= s('site_name','PawBridge Rescue') ?>"></div>
<div class="col-md-6"><label class="form-label">Tagline</label><input type="text" name="site_tagline" class="form-control" value="<?= s('site_tagline','HELPING ANIMALS MOVE FROM DANGER TO SAFETY') ?>"></div>
<div class="col-md-4"><label class="form-label">Primary Email</label><input type="email" name="site_email" class="form-control" value="<?= s('site_email','hello@pawbridge.org') ?>"></div>
<div class="col-md-4"><label class="form-label">Phone Number</label><input type="text" name="site_phone" class="form-control" value="<?= s('site_phone','+1 (555) 123-4567') ?>"></div>
<div class="col-md-4"><label class="form-label">Address</label><input type="text" name="site_address" class="form-control" value="<?= s('site_address','123 Rescue Lane') ?>"></div>
</div><div class="mt-4"><button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Save General Settings</button></div></form></div></div>
</div>

<div class="tab-pane fade" id="tabBranding">
<div class="card"><div class="card-header"><h3>Branding</h3></div>
<div class="card-body"><form method="POST" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="group" value="branding"><div class="row g-4">
<div class="col-md-6">
<label class="form-label">Logo</label>
<?php if (setting('site_logo')): ?><div class="mb-2 p-3 bg-light rounded-3 text-center"><img src="<?= asset_url('uploads/'.setting('site_logo')) ?>" style="max-height:60px;"></div><?php endif; ?>
<input type="file" name="site_logo" class="form-control" accept="image/*">
<div class="form-text">Upload your logo image (PNG with transparency recommended).</div>
</div>
<div class="col-md-6">
<label class="form-label">Favicon</label>
<?php if (setting('site_favicon')): ?><div class="mb-2 p-3 bg-light rounded-3"><img src="<?= asset_url('uploads/'.setting('site_favicon')) ?>" style="height:32px;width:32px;"></div><?php endif; ?>
<input type="file" name="site_favicon" class="form-control" accept="image/png,image/x-icon,image/svg+xml">
<div class="form-text">32x32 pixel PNG or ICO recommended.</div>
</div>
</div><div class="mt-4"><button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Save Branding</button></div></form></div></div>
</div>

<div class="tab-pane fade" id="tabColors">
<div class="card"><div class="card-header"><h3>Color Palette</h3></div>
<div class="card-body"><form method="POST"><?= csrf_field() ?><input type="hidden" name="group" value="colors">
<div class="color-settings">
<?php $colors = [
    'color_primary' => ['Primary (Dark Green)', '#064B20'],
    'color_secondary' => ['Secondary (Forest Green)', '#0B632A'],
    'color_mint' => ['Mint (Soft BG)', '#DDF7E2'],
    'color_teal' => ['Teal (Accent)', '#59B9B0'],
    'color_navy' => ['Navy (Dark Text)', '#102238'],
    'color_accent' => ['Orange (CTA Accent)', '#F59E42'],
    'color_soft_bg' => ['Soft Background', '#F5FAF5'],
];
foreach ($colors as $k => [$lbl, $def]): ?>
<div class="color-setting">
<input type="color" name="<?= $k ?>" value="<?= s($k, $def) ?>">
<div><label><?= $lbl ?></label><div style="font-size:11px;color:var(--pb-gray);font-family:monospace;"><?= s($k, $def) ?></div></div>
</div>
<?php endforeach; ?>
</div>
<div class="mt-4"><button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Save Colors</button></div>
</form></div></div>
</div>

<div class="tab-pane fade" id="tabContact">
<div class="card"><div class="card-header"><h3>Contact Information</h3></div>
<div class="card-body"><form method="POST"><?= csrf_field() ?><input type="hidden" name="group" value="contact"><div class="row g-3">
<div class="col-md-6"><label class="form-label">City</label><input type="text" name="site_city" class="form-control" value="<?= s('site_city','Animal City') ?>"></div>
<div class="col-md-6"><label class="form-label">Country</label><input type="text" name="site_country" class="form-control" value="<?= s('site_country','United States') ?>"></div>
<div class="col-md-12"><label class="form-label">Contact Hours</label><textarea name="contact_hours" class="form-control" rows="3"><?= s('contact_hours',"Monday - Friday: 9:00 AM - 5:00 PM\nSaturday: 10:00 AM - 3:00 PM\nSunday: Closed") ?></textarea></div>
<div class="col-md-12"><label class="form-label">Map Embed URL (or iframe HTML)</label><textarea name="map_embed" class="form-control" rows="4" placeholder="Paste Google Maps embed iframe code or URL"><?= s('map_embed') ?></textarea></div>
</div><div class="mt-4"><button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Save Contact</button></div></form></div></div>
</div>

<div class="tab-pane fade" id="tabSocial">
<div class="card"><div class="card-header"><h3>Social Media Links</h3></div>
<div class="card-body"><form method="POST"><?= csrf_field() ?><input type="hidden" name="group" value="social"><div class="row g-3">
<?php foreach (['facebook_url'=>'Facebook','twitter_url'=>'Twitter / X','instagram_url'=>'Instagram','youtube_url'=>'YouTube','linkedin_url'=>'LinkedIn'] as $k => $lbl): ?>
<div class="col-md-6"><label class="form-label"><?= $lbl ?> URL</label><div class="input-group">
<span class="input-group-text"><i class="fab fa-<?= strstr($k,'_',true) ?>"></i></span>
<input type="url" name="<?= $k ?>" class="form-control" value="<?= s($k) ?>" placeholder="https://"></div></div>
<?php endforeach; ?>
</div><div class="mt-4"><button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Save Social</button></div></form></div></div>
</div>

<div class="tab-pane fade" id="tabFooter">
<div class="card"><div class="card-header"><h3>Footer</h3></div>
<div class="card-body"><form method="POST"><?= csrf_field() ?><input type="hidden" name="group" value="footer"><div class="mb-3">
<label class="form-label">Footer Copyright / Text</label><textarea name="footer_text" class="form-control" rows="3"><?= s('footer_text','© 2026 PawBridge Rescue. All rights reserved.') ?></textarea>
</div><button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Save Footer</button></form></div></div>
</div>

<div class="tab-pane fade" id="tabSeo">
<div class="card"><div class="card-header"><h3>SEO Defaults</h3></div>
<div class="card-body"><form method="POST"><?= csrf_field() ?><input type="hidden" name="group" value="seo"><div class="row g-3">
<div class="col-md-12"><label class="form-label">Default SEO Title</label><input type="text" name="seo_default_title" class="form-control" value="<?= s('seo_default_title','PawBridge Rescue | Helping Animals Find Their Way Home') ?>"></div>
<div class="col-md-12"><label class="form-label">Default SEO Description</label><textarea name="seo_default_description" class="form-control" rows="4"><?= s('seo_default_description','PawBridge Rescue rescues, transports, and rehomes animals at risk. Help us give every animal a second chance at a loving home.') ?></textarea></div>
</div><div class="mt-4"><button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Save SEO</button></div></form></div></div>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
