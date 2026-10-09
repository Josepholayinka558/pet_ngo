<?php
$pageTitle = 'Messages';
require_once __DIR__ . '/includes/header.php';
$isRead = $_GET['read'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20; $offset = ($page-1)*$perPage;
$where = ['1=1']; $params = [];
if ($isRead === '0') { $where[] = 'is_read = 0'; }
elseif ($isRead === '1') { $where[] = 'is_read = 1'; }
$whereSql = implode(' AND ', $where);
$total = (int)(pdo_fetch("SELECT COUNT(*) AS c FROM contact_messages WHERE $whereSql", $params)['c'] ?? 0);
$msgs = pdo_fetch_all("SELECT * FROM contact_messages WHERE $whereSql ORDER BY is_read ASC, created_at DESC LIMIT $perPage OFFSET $offset", $params);
$currentMsg = null;
if (isset($_GET['view'])) {
    $currentMsg = pdo_fetch("SELECT * FROM contact_messages WHERE id = ?", [(int)$_GET['view']]);
    if ($currentMsg && !$currentMsg['is_read']) pdo_update('contact_messages', ['is_read'=>1], 'id = ?', [$currentMsg['id']]);
}
if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) { flash('error','Invalid token'); redirect(url('admin/messages.php')); }
    if (isset($_POST['delete_id'])) {
        pdo_delete('contact_messages', 'id = ?', [(int)$_POST['delete_id']]);
        flash('success','Deleted'); redirect(url('admin/messages.php'));
    }
    if (isset($_POST['send_reply'])) {
        $id = (int)$_POST['msg_id'];
        $reply = clean_input($_POST['reply_text'] ?? '');
        pdo_update('contact_messages', ['reply'=>$reply,'replied_by'=>current_admin_id(),'replied_at'=>date('Y-m-d H:i:s')], 'id = ?', [$id]);
        flash('success','Reply saved!');
        redirect(url('admin/messages.php?view='.$id));
    }
    if (isset($_POST['mark_read_bulk'])) {
        $ids = $_POST['ids'] ?? [];
        foreach ($ids as $id) pdo_update('contact_messages', ['is_read'=>1], 'id = ?', [(int)$id]);
        flash('success','Marked as read');
        redirect(url('admin/messages.php'));
    }
}
?>
<div class="row g-3">
<div class="col-md-4">
<div class="card h-100"><div class="card-header"><h5><i class="fas fa-inbox" style="color:var(--pb-secondary)"></i> Inbox</h5>
<div><a href="?read=0" class="btn btn-sm btn-<?= $isRead==='0'?'primary':'secondary' ?>">Unread (<?= (int)(pdo_fetch("SELECT COUNT(*) AS c FROM contact_messages WHERE is_read = 0")['c'] ?? 0) ?>)</a>
<a href="?read=1" class="btn btn-sm btn-<?= $isRead==='1'?'primary':'secondary' ?>">Read</a>
<a href="messages.php" class="btn btn-sm btn-<?= $isRead===''?'primary':'secondary' ?>">All</a></div></div>
<div class="card-body p-0" style="max-height:70vh;overflow-y:auto;">
<?php foreach($msgs as $m): ?>
<a href="?view=<?= $m['id'] ?>" class="d-block p-3 border-bottom text-decoration-none <?= $currentMsg && $currentMsg['id']==$m['id']?'bg-light':'' ?>" style="color:inherit;">
<div class="d-flex justify-content-between align-items-start mb-1">
<?php if (!$m['is_read']): ?><span class="badge badge-danger me-1">NEW</span><?php endif; ?>
<strong class="<?= !$m['is_read']?'fw-bold':'' ?>" style="font-size:14px;flex:1;"><?= e($m['full_name']) ?></strong>
<small style="color:var(--pb-gray);white-space:nowrap;"><?= format_date($m['created_at'],'M j') ?></small>
</div>
<div style="font-size:13px;font-weight:600;color:var(--pb-dark-gray);"><?= e($m['subject'] ?? '(No subject)') ?></div>
<div style="font-size:12px;color:var(--pb-gray);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e(mb_substr($m['message'],0,50)) ?>...</div>
</a>
<?php endforeach; ?>
</div></div></div>
<div class="col-md-8">
<div class="card h-100"><div class="card-header"><h5><i class="fas fa-envelope-open-text" style="color:#102238"></i> Message</h5>
<?php if ($currentMsg): ?><form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button type="submit" name="delete_id" value="<?= $currentMsg['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button></form><?php endif; ?>
</div>
<div class="card-body">
<?php if (!$currentMsg): ?>
<div class="text-center py-5" style="color:var(--pb-gray);"><i class="fas fa-envelope-open fa-4x mb-3" style="opacity:0.2;"></i>
<p>Select a message to view, or click on NEW messages to respond.</p></div>
<?php else: ?>
<h4 style="color:var(--pb-navy);font-weight:700;margin-bottom:6px;"><?= e($currentMsg['subject'] ?? '(No subject)') ?></h4>
<div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
<div><strong><?= e($currentMsg['full_name']) ?></strong> <span style="color:var(--pb-gray);">&lt;<?= e($currentMsg['email']) ?>&gt;</span><br>
<?php if ($currentMsg['phone']): ?><small style="color:var(--pb-gray);"><i class="fas fa-phone"></i> <?= e($currentMsg['phone']) ?></small><?php endif; ?></div>
<div style="color:var(--pb-gray);font-size:13px;"><i class="fas fa-clock"></i> <?= format_date($currentMsg['created_at'],'F j, Y g:i A') ?></div>
</div>
<div style="white-space:pre-wrap;line-height:1.8;font-size:15px;color:#1f2937;" class="mb-4 pb-4 border-bottom"><?= e($currentMsg['message']) ?></div>
<?php if ($currentMsg['reply']): ?>
<div style="background:var(--pb-mint);border-radius:14px;padding:18px;margin-bottom:4px;" class="mb-3">
<div style="font-size:12px;color:var(--pb-secondary);font-weight:700;margin-bottom:6px;"><i class="fas fa-reply"></i> Your Reply • <?= $currentMsg['replied_at'] ? format_date($currentMsg['replied_at'],'F j, Y g:i A') : '' ?></div>
<div style="white-space:pre-wrap;line-height:1.7;"><?= e($currentMsg['reply']) ?></div>
</div>
<?php endif; ?>
<form method="POST" class="mt-3"><?= csrf_field() ?><input type="hidden" name="msg_id" value="<?= $currentMsg['id'] ?>">
<label class="form-label"><i class="fas fa-reply"></i> Reply</label>
<textarea name="reply_text" class="form-control" rows="4" placeholder="Write your reply..."><?= e($currentMsg['reply'] ?? '') ?></textarea>
<div class="mt-2 d-flex justify-content-end gap-2">
<a href="mailto:<?= e($currentMsg['email']) ?>" class="btn btn-secondary"><i class="fas fa-paper-plane"></i> Open in Email</a>
<button type="submit" name="send_reply" class="btn btn-primary"><i class="fas fa-save"></i> Save Reply</button>
</div></form>
<?php endif; ?>
</div></div></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
