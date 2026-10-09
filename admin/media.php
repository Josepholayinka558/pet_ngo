<?php
$pageTitle = 'Media Library';
require_once __DIR__ . '/includes/header.php';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24; $offset = ($page-1)*$perPage;
$where = ['1=1']; $params = [];
if ($search) { $where[] = '(file_name LIKE ? OR alt_text LIKE ? OR caption LIKE ?)'; $p = "%$search%"; $params = [$p,$p,$p]; }
$whereSql = implode(' AND ', $where);
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM media WHERE $whereSql", $params)['c'] ?? 0);
$media = pdo_fetch_all("SELECT * FROM media WHERE $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/media.php')); }
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $m = pdo_fetch("SELECT file_path FROM media WHERE id = ?", [$id]);
        if ($m) delete_file($m['file_path']);
        pdo_delete('media', 'id = ?', [$id]);
        flash('success','Deleted'); redirect(url('admin/media.php'));
    }
    if (isset($_POST['upload'])) {
        $files = $_FILES['files'];
        $count = count($files['name']);
        $uploaded = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $f = ['name'=>$files['name'][$i],'type'=>$files['type'][$i],'tmp_name'=>$files['tmp_name'][$i],'error'=>$files['error'][$i],'size'=>$files['size'][$i]];
            $r = secure_upload($f, 'media');
            if (!empty($r['success'])) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                pdo_insert('media', ['file_name'=>$r['name'],'file_path'=>$r['path'],'file_type'=>'image','file_size'=>$f['size'],'mime_type'=>$finfo->file(UPLOAD_PATH.'/'.$r['path']),'alt_text'=>pathinfo($f['name'],PATHINFO_FILENAME),'uploaded_by'=>current_admin_id()]);
                $uploaded++;
            }
        }
        flash('success', "Uploaded $uploaded file(s)!");
        redirect(url('admin/media.php'));
    }
    if (isset($_POST['update_meta'])) {
        $id = (int)$_POST['media_id'];
        pdo_update('media', ['alt_text'=>clean_input($_POST['alt_text'] ?? ''),'caption'=>clean_input($_POST['caption'] ?? '')], 'id = ?', [$id]);
        flash('success','Meta updated');
        redirect(url('admin/media.php'));
    }
}
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-images" style="color:var(--pb-teal);margin-right:8px;"></i> Media Library</h3>
<div><form method="GET" class="d-inline me-2"><input type="text" name="search" class="form-control d-inline" style="width:220px;display:inline-block;" value="<?= e($search) ?>" placeholder="Search files..."> <button class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button></form>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fas fa-upload"></i> Upload</button></div></div>
<div class="card-body">
<?php if (empty($media)): ?><div class="text-center py-5" style="color:var(--pb-gray);"><i class="fas fa-folder-open fa-4x mb-3" style="opacity:0.2;"></i>
<p>No media yet. Upload your first image!</p></div><?php else: ?>
<div class="media-grid">
<?php foreach($media as $m): ?>
<div class="media-item">
<img src="<?= asset_url('uploads/'.$m['file_path']) ?>" alt="<?= e($m['alt_text'] ?? '') ?>" loading="lazy">
<div class="media-overlay">
<button class="btn btn-sm btn-light" onclick="copyUrl('<?= asset_url('uploads/'.$m['file_path']) ?>')" title="Copy URL"><i class="fas fa-link"></i></button>
<button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#meta<?= $m['id'] ?>" title="Edit"><i class="fas fa-edit"></i></button>
<form method="POST" style="display:inline;" onsubmit="return confirm('Delete file?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $m['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
</div>
</div>
<div class="modal fade" id="meta<?= $m['id'] ?>"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title">File Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<img src="<?= asset_url('uploads/'.$m['file_path']) ?>" class="img-fluid rounded mb-3 d-block mx-auto" style="max-height:260px;">
<div class="data-row"><span class="label">URL</span><span class="value small"><input type="text" class="form-control form-control-sm" value="<?= asset_url('uploads/'.$m['file_path']) ?>" readonly onclick="this.select()"></span></div>
<div class="data-row"><span class="label">Filename</span><span class="value small"><?= e($m['file_name']) ?></span></div>
<div class="data-row"><span class="label">Size</span><span class="value small"><?= round($m['file_size']/1024,1) ?> KB</span></div>
<div class="data-row"><span class="label">Type</span><span class="value small"><?= e($m['mime_type']) ?></span></div>
<form method="POST"><?= csrf_field() ?><input type="hidden" name="media_id" value="<?= $m['id'] ?>">
<div class="mb-2"><label class="form-label mt-2">Alt Text (SEO)</label><input type="text" name="alt_text" class="form-control" value="<?= e($m['alt_text'] ?? '') ?>"></div>
<div class="mb-3"><label class="form-label">Caption</label><input type="text" name="caption" class="form-control" value="<?= e($m['caption'] ?? '') ?>"></div>
<button type="submit" name="update_meta" class="btn btn-primary w-100">Save</button></form>
</div></div></div></div>
<?php endforeach; ?>
</div>
<?= paginate($page, $perPage, $total, 'media.php') ?>
<?php endif; ?>
</div></div>

<div class="modal fade" id="uploadModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title">Upload Files</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="modal-body">
<div class="mb-3"><label class="form-label">Select Images</label><input type="file" name="files[]" class="form-control" multiple accept="image/jpeg,image/png,image/webp" required></div>
<div class="form-text">Allowed: JPG, PNG, WEBP. Max 5MB per file.</div>
</div>
<div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="upload" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button></div></form></div></div></div>
<script>function copyUrl(u){navigator.clipboard.writeText(u).then(()=>{alert('URL copied!')}).catch(()=>{prompt('Copy URL:',u)})}</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
