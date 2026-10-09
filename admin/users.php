<?php
$pageTitle = 'Admin Users';
require_once __DIR__ . '/includes/header.php';
require_super_admin();

$users = pdo_fetch_all("SELECT id, username, email, full_name, role, status, last_login, created_at FROM admins ORDER BY id");

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/users.php')); }
    
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        if ($id === (int)current_admin_id()) { flash('error','Cannot delete yourself'); redirect(url('admin/users.php')); }
        pdo_delete('admins', 'id = ?', [$id]);
        flash('success','User deleted'); redirect(url('admin/users.php'));
    }
    
    if (isset($_POST['add_user'])) {
        $username = clean_input($_POST['username']);
        $email = clean_input($_POST['email']);
        if (pdo_fetch("SELECT id FROM admins WHERE username = ? OR email = ?", [$username, $email])) { flash('error','Username/email exists'); redirect(url('admin/users.php')); }
        pdo_insert('admins', ['username'=>$username,'email'=>$email,'password'=>password_hash($_POST['password'], PASSWORD_DEFAULT),'full_name'=>clean_input($_POST['full_name']),'role'=>clean_input($_POST['role']),'status'=>(int)($_POST['status'] ?? 1)]);
        flash('success','User created'); redirect(url('admin/users.php'));
    }
    
    if (isset($_POST['edit_user'])) {
        $id = (int)$_POST['user_id'];
        $data = ['username'=>clean_input($_POST['username']),'email'=>clean_input($_POST['email']),'full_name'=>clean_input($_POST['full_name']),'role'=>clean_input($_POST['role']),'status'=>(int)($_POST['status'] ?? 1)];
        if (!empty($_POST['password'])) $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        pdo_update('admins', $data, 'id = ?', [$id]);
        flash('success','User updated'); redirect(url('admin/users.php'));
    }
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-user-shield" style="color:#102238;margin-right:8px;"></i> Admin Users</h3>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-plus"></i> Add Admin</button></div>
<div class="card-body"><div class="table-responsive"><table class="table">
<thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Created</th><th>Actions</th></tr></thead>
<tbody><?php foreach($users as $u): ?>
<tr><td><strong><?= e($u['full_name']) ?></strong></td>
<td><code><?= e($u['username']) ?></code></td>
<td><?= e($u['email']) ?></td>
<td><span class="badge badge-<?= $u['role']==='super_admin'?'danger':'primary' ?>"><?= e(ucwords(str_replace('_',' ',$u['role']))) ?></span></td>
<td><span class="badge badge-<?= !empty($u['status'])?'success':'secondary' ?>"><?= !empty($u['status'])?'Active':'Disabled' ?></span></td>
<td style="font-size:12px;"><?= $u['last_login'] ? format_date($u['last_login'],'M j, Y g:iA') : '<span class="text-muted">Never</span>' ?></td>
<td style="font-size:12px;"><?= format_date($u['created_at'],'M j, Y') ?></td>
<td><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#edit<?= $u['id'] ?>"><i class="fas fa-edit"></i></button>
<?php if ($u['id'] !== (int)current_admin_id()): ?>
<form method="POST" style="display:inline;" onsubmit="return confirm('Delete this admin?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $u['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
<?php endif; ?></td></tr>

<div class="modal fade" id="edit<?= $u['id'] ?>"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title">Edit: <?= e($u['username']) ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><div class="modal-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?= e($u['full_name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" class="form-control" value="<?= e($u['username']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($u['email']) ?>" required></div>
<div class="col-md-3"><label class="form-label">Role</label><select name="role" class="form-select">
<?php foreach(['super_admin','admin','editor','viewer'] as $r): ?><option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= ucwords(str_replace('_',' ',$r)) ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select">
<option value="1" <?= !empty($u['status'])?'selected':'' ?>>Active</option><option value="0" <?= empty($u['status'])?'selected':'' ?>>Disabled</option>
</select></div>
<div class="col-md-12"><label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label><input type="password" name="password" class="form-control"></div>
</div></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="edit_user" class="btn btn-primary">Save</button></div></form></div></div></div>
<?php endforeach; ?></tbody></table></div></div></div>

<div class="modal fade" id="addUserModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title">Add New Admin</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><?= csrf_field() ?><div class="modal-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="full_name" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
<div class="col-md-3"><label class="form-label">Role</label><select name="role" class="form-select">
<?php foreach(['super_admin','admin','editor','viewer'] as $r): ?><option <?= $r==='admin'?'selected':'' ?>><?= $r ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select">
<option value="1" selected>Active</option><option value="0">Disabled</option>
</select></div>
<div class="col-md-12"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required minlength="6"></div>
</div></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="add_user" class="btn btn-primary"><i class="fas fa-save"></i> Create Admin</button></div></form></div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
