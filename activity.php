<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';
require_login(); $page_title='Activity Logs';
$logs=$pdo->query("SELECT l.*,u.name FROM activity_logs l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.created_at DESC LIMIT 100")->fetchAll();
include __DIR__ . '/partials/header.php';
?>
<div class="hero compact"><div><div class="eyebrow">AUDIT TRAIL</div><h1>Activity logs</h1><p>Track changes and monitoring actions performed in the system.</p></div></div>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>Time</th><th>User</th><th>Action</th><th>Details</th></tr></thead><tbody><?php foreach($logs as $l): ?><tr><td><?= e($l['created_at']) ?></td><td><?= e($l['name'] ?? 'System') ?></td><td><b><?= e($l['action']) ?></b></td><td><?= e($l['details']) ?></td></tr><?php endforeach; ?><?php if(!$logs): ?><tr><td colspan="4"><div class="empty">No activity yet. System actions will appear here.</div></td></tr><?php endif; ?></tbody></table></div></div>
<?php include __DIR__ . '/partials/footer.php'; ?>
