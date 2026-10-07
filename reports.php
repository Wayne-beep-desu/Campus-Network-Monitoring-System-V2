<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';
require_login(); $page_title='Network Reports';
$stats=[
'devices'=>(int)$pdo->query("SELECT COUNT(*) FROM devices")->fetchColumn(),
'online'=>(int)$pdo->query("SELECT COUNT(*) FROM devices WHERE status='Online'")->fetchColumn(),
'alerts'=>(int)$pdo->query("SELECT COUNT(*) FROM alerts")->fetchColumn(),
'logs'=>(int)$pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn()
];
$top=$pdo->query("SELECT device_name,ip_address,download_mbps,upload_mbps,status FROM devices ORDER BY download_mbps DESC LIMIT 10")->fetchAll();
include __DIR__ . '/partials/header.php';
?>
<div class="hero compact"><div><div class="eyebrow">ANALYTICS</div><h1>Network reports</h1><p>Summary information for IT management and project documentation.</p></div><a class="btn ghost" href="api/export_csv.php">⇩ CSV</a> <button class="btn primary" onclick="window.print()">🖨 Print report / PDF</button></div>
<div class="report-header"><div><b>Campus Network Monitoring and Bandwidth Management System</b><small>Generated <?= date('F d, Y h:i A') ?></small></div><div class="report-badge">IT DEPARTMENT</div></div>
<div class="stat-grid">
<?php foreach([['Registered Devices',$stats['devices']],['Online Devices',$stats['online']],['Recorded Alerts',$stats['alerts']],['Activity Entries',$stats['logs']]] as $s): ?><div class="stat-card"><div><small><?= $s[0] ?></small><strong><?= $s[1] ?></strong></div></div><?php endforeach; ?>
</div>
<div class="panel"><div class="panel-head"><div><h3>Highest current bandwidth consumers</h3><p>Based on current stored monitoring data</p></div></div><div class="table-wrap"><table><thead><tr><th>Device</th><th>IP</th><th>Download</th><th>Upload</th><th>Status</th></tr></thead><tbody><?php foreach($top as $d): ?><tr><td><b><?= e($d['device_name']) ?></b></td><td class="mono"><?= e($d['ip_address']) ?></td><td><?= number_format($d['download_mbps'],1) ?> Mbps</td><td><?= number_format($d['upload_mbps'],1) ?> Mbps</td><td><span class="status <?= strtolower($d['status']) ?>"><?= e($d['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php include __DIR__ . '/partials/footer.php'; ?>
