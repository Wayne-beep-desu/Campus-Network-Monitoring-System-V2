<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';
require_login(); $page_title='Network Alerts';
$alerts=$pdo->query("SELECT a.*,d.device_name FROM alerts a LEFT JOIN devices d ON d.id=a.device_id ORDER BY a.is_resolved ASC,a.created_at DESC")->fetchAll();
include __DIR__ . '/partials/header.php';
?>
<div class="hero compact"><div><div class="eyebrow">INCIDENT CENTER</div><h1>Network alerts</h1><p>Review high-traffic, offline and connectivity events.</p></div><button class="btn ghost" onclick="generateAlert()">+ Test alert</button></div>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>Severity</th><th>Alert</th><th>Device</th><th>Created</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($alerts as $a): ?>
<tr><td><span class="severity <?= strtolower($a['severity']) ?>"><?= e($a['severity']) ?></span></td><td><b><?= e($a['title']) ?></b><small><?= e($a['message']) ?></small></td><td><?= e($a['device_name'] ?? 'Network') ?></td><td><?= e($a['created_at']) ?></td><td><?= $a['is_resolved']?'<span class="status online">Resolved</span>':'<span class="status warning">Open</span>' ?></td><td><?php if(!$a['is_resolved']): ?><button class="btn small" onclick="resolveAlert(<?= (int)$a['id'] ?>)">Resolve</button><?php endif; ?></td></tr>
<?php endforeach; ?>
<?php if(!$alerts): ?><tr><td colspan="6"><div class="empty">No alerts recorded.</div></td></tr><?php endif; ?>
</tbody></table></div></div>
<?php include __DIR__ . '/partials/footer.php'; ?>
