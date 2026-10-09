<?php
$pageTitle = 'Blog Posts';
require_once __DIR__ . '/includes/header.php';

$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;
$where = ['1=1']; $params = [];
if ($status) { $where[] = 'status = ?'; $params[] = $status; }
$whereSql = implode(' AND ', $where);
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM blog_posts WHERE $whereSql", $params)['c'] ?? 0);
$posts = pdo_fetch_all("SELECT bp.*, bc.name AS cat_name, ad.full_name AS author_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bc.id = bp.category_id LEFT JOIN admins ad ON ad.id = bp.author_id WHERE $whereSql ORDER BY COALESCE(published_at, created_at) DESC LIMIT $perPage OFFSET $offset", $params);

if (is_post() && isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/blog.php')); }
    $id = (int)$_POST['delete_id'];
    $p = pdo_fetch("SELECT featured_image FROM blog_posts WHERE id = ?", [$id]);
    if ($p && $p['featured_image']) delete_file($p['featured_image']);
    pdo_delete('blog_posts', 'id = ?', [$id]);
    flash('success', 'Post deleted'); redirect(url('admin/blog.php'));
}
?>
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-blog" style="color:#102238;margin-right:8px;"></i> Blog Posts</h3>
        <div>
            <select class="form-select d-inline-block" style="width:auto;" onchange="window.location='?status='+this.value">
                <option value="">All Statuses</option>
                <?php foreach(['Draft','Published','Unpublished','Archived'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
            </select>
            <a href="<?= url('admin/blog-post-add.php') ?>" class="btn btn-primary ms-2"><i class="fas fa-plus"></i> New Post</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th></th><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Views</th><th>Published</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($posts as $p): ?>
                    <tr>
                        <td><img src="<?= $p['featured_image'] ? asset_url('uploads/'.$p['featured_image']) : 'https://placehold.co/50x50?text=📝' ?>" class="thumbnail-sm"></td>
                        <td><strong><?= e($p['title']) ?></strong><br><small style="color:var(--pb-gray);"><?= e($p['slug']) ?></small></td>
                        <td><?= $p['cat_name'] ? '<span class="badge badge-info">'.e($p['cat_name']).'</span>' : '-' ?></td>
                        <td><?= e($p['author_name'] ?? '-') ?></td>
                        <td><span class="badge badge-<?= $p['status']==='Published'?'success':'secondary' ?>"><?= e($p['status']) ?></span></td>
                        <td><?= (int)$p['views'] ?></td>
                        <td style="font-size:12px;"><?= $p['published_at'] ? format_date($p['published_at'],'M j, Y') : '<span class="text-muted">Draft</span>' ?></td>
                        <td>
                            <a href="<?= url('post.php?slug='.$p['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('admin/blog-post-edit.php?id='.$p['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $p['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginate($page, $perPage, $total, 'blog.php') ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
