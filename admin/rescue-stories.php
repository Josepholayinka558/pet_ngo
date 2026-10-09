<?php
$pageTitle = 'Rescue Stories';
require_once __DIR__ . '/includes/header.php';

$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;
$where = ['1=1']; $params = [];
if ($status) { $where[] = 'status = ?'; $params[] = $status; }
$whereSql = implode(' AND ', $where);
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM rescue_stories WHERE $whereSql", $params)['c'] ?? 0);
$stories = pdo_fetch_all("SELECT rs.*, a.name AS animal_name FROM rescue_stories rs LEFT JOIN animals a ON a.id = rs.animal_id WHERE $whereSql ORDER BY COALESCE(published_at, created_at) DESC LIMIT $perPage OFFSET $offset", $params);
if (is_post() && isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/rescue-stories.php')); }
    $id = (int)$_POST['delete_id'];
    $story = pdo_fetch("SELECT featured_image FROM rescue_stories WHERE id = ?", [$id]);
    if ($story && $story['featured_image']) delete_file($story['featured_image']);
    pdo_delete('rescue_story_images', 'story_id = ?', [$id]);
    pdo_delete('rescue_stories', 'id = ?', [$id]);
    flash('success', 'Story deleted'); redirect(url('admin/rescue-stories.php'));
}
?>
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-book-heart" style="color:var(--pb-teal);margin-right:8px;"></i> Rescue Stories</h3>
        <div>
            <select class="form-select d-inline-block" style="width:auto;" onchange="window.location='?status='+this.value">
                <option value="">All Statuses</option>
                <?php foreach(['Draft','Published','Unpublished','Archived'] as $opt_status): ?>
                <option value="<?= $opt_status ?>" <?= $status===$opt_status?'selected':'' ?>><?= $opt_status ?></option>
                <?php endforeach; ?>
            </select>
            <a href="<?= url('admin/rescue-story-add.php') ?>" class="btn btn-primary ms-2"><i class="fas fa-plus"></i> New Story</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th></th><th>Title</th><th>Animal</th><th>Status</th><th>Featured</th><th>Views</th><th>Published</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($stories as $story_row): ?>
                    <tr>
                        <td><img src="<?= $story_row['featured_image'] ? asset_url('uploads/'.$story_row['featured_image']) : 'https://placehold.co/50x50?text=📖' ?>" class="thumbnail-sm"></td>
                        <td>
                          <strong><?php echo e($story_row['title']); ?></strong><br>
                          <small style="color:var(--pb-gray);">
                            <?php
                            if (isset($story_row['slug']) && $story_row['slug'] !== '') {
                                echo e($story_row['slug']);
                            } else {
                                echo '';
                            }
                            ?>
                          </small>
                        </td>
                        <td>
                          <?php
                          if (isset($story_row['animal_name']) && !empty($story_row['animal_name'])) {
                              echo e($story_row['animal_name']);
                          } else {
                              echo '-';
                          }
                          ?>
                        </td>
                        <td><span class="badge badge-<?= $story_row['status']==='Published'?'success':'secondary' ?>"><?php echo e($story_row['status']); ?></span></td>
                        <td><?= $story_row['featured'] ? '<i class="fas fa-star" style="color:#F59E42"></i>' : '' ?></td>
                        <td><?= (int)$story_row['views'] ?></td>
                        <td style="font-size:12px;"><?= $story_row['published_at'] ? format_date($story_row['published_at'],'M j, Y') : '<span class="text-muted">Draft</span>' ?></td>
                        <td>
                            <a href="<?= url('story.php?slug='.$story_row['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('admin/rescue-story-edit.php?id='.$story_row['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $story_row['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginate($page, $perPage, $total, 'rescue-stories.php') ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
