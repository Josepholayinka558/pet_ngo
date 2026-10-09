<?php
$pageTitle = 'Custom Pages';
require_once __DIR__ . '/includes/header.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20; $offset = ($page-1)*$perPage;
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM pages")['c'] ?? 0);
$pages = pdo_fetch_all("SELECT p.*, a.full_name AS author FROM pages p LEFT JOIN admins a ON a.id = p.created_by ORDER BY p.sort_order, p.id DESC LIMIT $perPage OFFSET $offset");
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/pages.php')); }
    if (isset($_POST['delete_id'])) {
        pdo_delete('pages', 'id = ?', [(int)$_POST['delete_id']]);
        flash('success','Deleted'); redirect(url('admin/pages.php'));
    }
    if (isset($_POST['add_page'])) {
        $title = clean_input($_POST['title'] ?? '');
        $slug = clean_input($_POST['slug'] ?? '') ?: slugify($title);
        $baseSlug = $slug; $i = 1;
        while (pdo_fetch("SELECT id FROM pages WHERE slug = ?", [$slug])) { $slug = $baseSlug . '-' . $i++; }
        pdo_insert('pages', ['title'=>$title,'slug'=>$slug,'content'=>$_POST['content'] ?? '','featured_image'=>'','status'=>clean_input($_POST['status'] ?? 'Draft'),'template'=>clean_input($_POST['template'] ?? 'default'),'seo_title'=>clean_input($_POST['seo_title'] ?? ''),'seo_description'=>clean_input($_POST['seo_description'] ?? ''),'sort_order'=>(int)($_POST['sort_order'] ?? 0),'created_by'=>current_admin_id(),'published_at' => ($_POST['status'] ?? '') === 'Published' ? date('Y-m-d H:i:s') : null]);
        flash('success','Page created'); redirect(url('admin/pages.php'));
    }
}
$templates = ['default'=>'Default Template','full-width'=>'Full Width','sidebar'=>'With Sidebar'];
$statuses = ['Draft','Published','Unpublished'];
?>
<div class="card"><div class="card-header"><h3><i class="fas fa-file-lines" style="color:var(--pb-secondary);margin-right:8px;"></i> Custom Pages</h3>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> New Page</button></div>
<div class="card-body">
<div class="table-responsive"><table class="table">
<thead><tr><th>Title</th><th>Slug / URL</th><th>Template</th><th>Status</th><th>Author</th><th>Published</th><th>Actions</th></tr></thead>
<tbody><?php foreach($pages as $p): ?>
<tr><td><strong><?= e($p['title']) ?></strong><?php if ($p['sort_order']>0): ?><small class="text-muted ms-2">order: <?= $p['sort_order'] ?></small><?php endif; ?></td>
<td><code style="font-size:12px;">/page.php?slug=<?= e($p['slug']) ?></code></td>
<td><span class="badge badge-secondary"><?= e($templates[$p['template']] ?? $p['template']) ?></span></td>
<td><span class="badge badge-<?= $p['status']==='Published'?'success':'secondary' ?>"><?= e($p['status']) ?></span></td>
<td><?= e($p['author'] ?? '-') ?></td>
<td style="font-size:12px;"><?= $p['published_at'] ? format_date($p['published_at'],'M j, Y') : '<span class="text-muted">Draft</span>' ?></td>
<td><a href="<?= url('page.php?slug='.$p['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>
<a href="<?= url('admin/page-edit.php?id='.$p['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
<form method="POST" style="display:inline;" onsubmit="return confirm('Delete page?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $p['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
</td></tr><?php endforeach; ?></tbody>
</table></div><?= paginate($page, $perPage, $total, 'pages.php') ?>
</div></div>

<div class="modal fade" id="addModal"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title">Create New Page</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" enctype="multipart/form-data"><?= csrf_field() ?><div class="modal-body"><div class="row g-3">
<div class="col-md-8"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required id="pTitle" oninput="document.getElementById('pSlug').placeholder=slugify(this.value)"></div>
<div class="col-md-2"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" id="pSlug" placeholder="auto"></div>
<div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach($statuses as $s): ?><option><?= $s ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Template</label><select name="template" class="form-select"><?php foreach($templates as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
<div class="col-md-12"><label class="form-label">Content</label><textarea name="content" class="form-control ckeditor" rows="10"></textarea></div>
<fieldset class="col-md-12 border rounded p-3"><legend class="float-none w-auto px-2 fs-6 fw-bold">SEO</legend><div class="row g-2">
<div class="col-md-12"><label class="form-label">SEO Title</label><input type="text" name="seo_title" class="form-control"></div>
<div class="col-md-12"><label class="form-label">SEO Description</label><textarea name="seo_description" class="form-control" rows="2"></textarea></div>
</div></fieldset>
</div></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="add_page" class="btn btn-primary"><i class="fas fa-save"></i> Create Page</button></div></form></div></div></div>
<script>function slugify(t){return t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'')}</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
