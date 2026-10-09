<?php
$pageTitle = 'Team Members';
require_once __DIR__ . '/includes/header.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20; $offset = ($page-1)*$perPage;
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM team_members")['c'] ?? 0);
$team = pdo_fetch_all("SELECT * FROM team_members ORDER BY sort_order, id DESC LIMIT $perPage OFFSET $offset");
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/team.php')); }
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $m = pdo_fetch("SELECT photo FROM team_members WHERE id = ?", [$id]);
        if ($m && $m['photo']) delete_file($m['photo']);
        pdo_delete('team_members', 'id = ?', [$id]);
        flash('success','Deleted'); redirect(url('admin/team.php'));
    }
    if (isset($_POST['add_member'])) {
        $photo = '';
        if (!empty($_FILES['photo']['name'])) { $r = secure_upload($_FILES['photo'], 'team'); if (!empty($r['success'])) $photo = $r['path']; }
        pdo_insert('team_members', ['name'=>clean_input($_POST['name']),'position'=>clean_input($_POST['position'] ?? ''),'bio'=>$_POST['bio'] ?? '','photo'=>$photo,'email'=>clean_input($_POST['email'] ?? ''),'phone'=>clean_input($_POST['phone'] ?? ''),'facebook'=>clean_input($_POST['facebook'] ?? ''),'twitter'=>clean_input($_POST['twitter'] ?? ''),'instagram'=>clean_input($_POST['instagram'] ?? ''),'linkedin'=>clean_input($_POST['linkedin'] ?? ''),'sort_order'=>(int)($_POST['sort_order'] ?? 0),'is_founder'=>(int)($_POST['is_founder'] ?? 0),'status'=>(int)($_POST['status'] ?? 1)]);
        flash('success','Member added'); redirect(url('admin/team.php'));
    }
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-user-group" style="color:var(--pb-primary);margin-right:8px;"></i> Team Members</h3>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Member</button></div>
<div class="card-body">
<?php if (empty($team)): ?><div class="text-center py-5" style="color:var(--pb-gray);"><i class="fas fa-users fa-4x mb-3" style="opacity:0.2;"></i><p>No team members yet.</p></div>
<?php else: ?><div class="row g-4">
<?php foreach($team as $m): ?>
<div class="col-md-3"><div class="card border"><img src="<?= $m['photo'] ? asset_url('uploads/'.$m['photo']) : 'https://placehold.co/300x300?text='.urlencode($m['name']) ?>" class="card-img-top" style="aspect-ratio:1;object-fit:cover;border-radius:14px 14px 0 0;">
<div class="card-body text-center">
<?php if ($m['is_founder']): ?><span class="badge badge-warning mb-2"><i class="fas fa-crown"></i> Founder</span><?php endif; ?>
<h5 class="card-title mb-1"><?= e($m['name']) ?></h5>
<p class="text-muted mb-2" style="font-size:13px;"><?= e($m['position'] ?? '') ?></p>
<div class="d-flex justify-content-center gap-2 mb-3">
<?php if ($m['facebook']): ?><a href="<?= e($m['facebook']) ?>" class="btn btn-sm btn-secondary"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
<?php if ($m['twitter']): ?><a href="<?= e($m['twitter']) ?>" class="btn btn-sm btn-secondary"><i class="fab fa-twitter"></i></a><?php endif; ?>
<?php if ($m['instagram']): ?><a href="<?= e($m['instagram']) ?>" class="btn btn-sm btn-secondary"><i class="fab fa-instagram"></i></a><?php endif; ?>
<?php if ($m['linkedin']): ?><a href="<?= e($m['linkedin']) ?>" class="btn btn-sm btn-secondary"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
</div>
<form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $m['id'] ?>" class="btn btn-sm btn-danger w-100"><i class="fas fa-trash"></i> Remove</button></form>
</div></div></div>
<?php endforeach; ?>
</div><?= paginate($page, $perPage, $total, 'team.php') ?><?php endif; ?>
</div></div>

<div class="modal fade" id="addModal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title">Add Team Member</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" enctype="multipart/form-data"><?= csrf_field() ?><div class="modal-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Position / Title</label><input type="text" name="position" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
<div class="col-md-12"><label class="form-label">Photo</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
<div class="col-md-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3"></textarea></div>
<div class="col-md-3"><label class="form-label">Facebook</label><input type="url" name="facebook" class="form-control" placeholder="https://"></div>
<div class="col-md-3"><label class="form-label">Twitter</label><input type="url" name="twitter" class="form-control" placeholder="https://"></div>
<div class="col-md-3"><label class="form-label">Instagram</label><input type="url" name="instagram" class="form-control" placeholder="https://"></div>
<div class="col-md-3"><label class="form-label">LinkedIn</label><input type="url" name="linkedin" class="form-control" placeholder="https://"></div>
<div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_founder" value="1" id="fd"><label class="form-check-label" for="fd">Founder / Director</label></div></div>
<div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="status" value="1" id="st" checked><label class="form-check-label" for="st">Active</label></div></div>
</div></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="add_member" class="btn btn-primary"><i class="fas fa-save"></i> Add Member</button></div></form></div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
