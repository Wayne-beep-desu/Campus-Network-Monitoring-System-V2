<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';
require_login(); $page_title='Bandwidth Management';
$devices=$pdo->query("SELECT * FROM devices ORDER BY download_mbps DESC")->fetchAll();
$totalLimit=(float)$pdo->query("SELECT COALESCE(SUM(bandwidth_limit),0) FROM devices")->fetchColumn();
$totalDown=(float)$pdo->query("SELECT COALESCE(SUM(download_mbps),0) FROM devices")->fetchColumn();
$totalUp=(float)$pdo->query("SELECT COALESCE(SUM(upload_mbps),0) FROM devices")->fetchColumn();
include __DIR__ . '/partials/header.php';
?>
<div class="hero compact"><div><div class="eyebrow">TRAFFIC CONTROL</div><h1>Bandwidth management</h1><p>View current utilization and configure per-device limits.</p></div><button class="btn primary" onclick="simulateTraffic()">↻ Simulate traffic</button></div>
<div class="stat-grid">
<div class="stat-card"><span class="stat-icon blue">↓</span><div><small>Total Download</small><strong><?= number_format($totalDown,1) ?> <i>Mbps</i></strong></div></div>
<div class="stat-card"><span class="stat-icon purple">↑</span><div><small>Total Upload</small><strong><?= number_format($totalUp,1) ?> <i>Mbps</i></strong></div></div>
<div class="stat-card"><span class="stat-icon amber">Σ</span><div><small>Configured Capacity</small><strong><?= number_format($totalLimit,1) ?> <i>Mbps</i></strong></div></div>
<div class="stat-card"><span class="stat-icon green">%</span><div><small>Utilization</small><strong><?= $totalLimit ? number_format(($totalDown/$totalLimit)*100,1) : 0 ?>%</strong></div></div>
</div>
<div class="panel"><div class="panel-head"><div><h3>Device bandwidth</h3><p>Download / upload against assigned limit</p></div></div>
<div class="bandwidth-table">
<?php foreach($devices as $d): $pct=min(100,$d['bandwidth_limit']>0?($d['download_mbps']/$d['bandwidth_limit']*100):0); ?>
<div class="bw-row"><div class="bw-info"><b><?= e($d['device_name']) ?></b><small><?= e($d['ip_address']) ?> · <?= e($d['device_type']) ?></small></div><div class="bw-bar"><span style="width:<?= $pct ?>%"></span></div><div class="bw-value"><b><?= number_format($d['download_mbps'],1) ?></b><small>/ <?= number_format($d['bandwidth_limit'],1) ?> Mbps</small></div><button class="icon-btn" onclick="quickLimit(<?= (int)$d['id'] ?>, <?= (float)$d['bandwidth_limit'] ?>)">⚙</button></div>
<?php endforeach; ?>
</div></div>
<?php include __DIR__ . '/partials/footer.php'; ?>
