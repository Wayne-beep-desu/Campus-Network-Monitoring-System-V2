<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/helpers.php';
require_login();
$page_title = 'Network Dashboard';

$total = (int)$pdo->query("SELECT COUNT(*) FROM devices")->fetchColumn();
$online = (int)$pdo->query("SELECT COUNT(*) FROM devices WHERE status='Online'")->fetchColumn();
$warning = (int)$pdo->query("SELECT COUNT(*) FROM devices WHERE status='Warning'")->fetchColumn();
$offline = (int)$pdo->query("SELECT COUNT(*) FROM devices WHERE status='Offline'")->fetchColumn();
$alerts = (int)$pdo->query("SELECT COUNT(*) FROM alerts WHERE is_resolved=0")->fetchColumn();
$down = (float)$pdo->query("SELECT COALESCE(SUM(download_mbps),0) FROM devices")->fetchColumn();
$up = (float)$pdo->query("SELECT COALESCE(SUM(upload_mbps),0) FROM devices")->fetchColumn();
$devices = $pdo->query("SELECT * FROM devices ORDER BY status='Offline' DESC, status='Warning' DESC, device_name LIMIT 8")->fetchAll();
$recentAlerts = $pdo->query("SELECT a.*, d.device_name FROM alerts a LEFT JOIN devices d ON d.id=a.device_id WHERE a.is_resolved=0 ORDER BY a.created_at DESC LIMIT 5")->fetchAll();
include __DIR__ . '/partials/header.php';
?>
<div class="hero">
  <div><div class="eyebrow">REAL-TIME OVERVIEW</div><h1>Network health at a glance.</h1><p>Monitor devices, bandwidth, incidents and connectivity from one control center.</p></div>
  <button class="btn primary" onclick="refreshDashboard()">↻ Refresh data</button>
</div>

<div class="stat-grid">
  <div class="stat-card"><span class="stat-icon blue">⌁</span><div><small>Total Devices</small><strong><?= $total ?></strong><span class="muted">Registered</span></div></div>
  <div class="stat-card"><span class="stat-icon green">●</span><div><small>Online</small><strong><?= $online ?></strong><span class="muted"><?= $total ? round($online/$total*100) : 0 ?>% available</span></div></div>
  <div class="stat-card"><span class="stat-icon amber">!</span><div><small>Warnings</small><strong><?= $warning ?></strong><span class="muted">Needs attention</span></div></div>
  <div class="stat-card"><span class="stat-icon red">⚠</span><div><small>Open Alerts</small><strong><?= $alerts ?></strong><span class="muted"><?= $offline ?> offline</span></div></div>
</div>

<div class="panel" style="margin-bottom:18px">
  <div class="panel-head"><div><h3>Wi-Fi Connected Devices</h3><p>Discover LAN clients and map them to Access Points when controller data is available.</p></div><a class="link" href="wifi_clients.php">Open discovery →</a></div>
  <div class="alert-box"><b>Network discovery is ready.</b> The current adapter uses the monitoring server ARP table; exact AP association needs the actual AP/router controller integration.</div>
</div>

<div class="grid-2">
  <div class="panel">
    <div class="panel-head"><div><h3>Bandwidth Overview</h3><p>Current aggregate traffic</p></div><span class="pill blue">LIVE</span></div>
    <div class="bandwidth-big"><div><small>DOWNLOAD</small><b><?= number_format($down,1) ?> <i>Mbps</i></b></div><div><small>UPLOAD</small><b><?= number_format($up,1) ?> <i>Mbps</i></b></div></div>
    <canvas id="bandwidthChart" height="145"></canvas>
  </div>
  <div class="panel">
    <div class="panel-head"><div><h3>System Status</h3><p>Infrastructure availability</p></div><span class="pill green">HEALTHY</span></div>
    <div class="health-ring"><div class="ring" style="--p:<?= $total ? round($online/$total*100) : 0 ?>%"><b><?= $total ? round($online/$total*100) : 0 ?>%</b><span>online</span></div><div class="legend"><span><i class="dot green"></i>Online <b><?= $online ?></b></span><span><i class="dot amber"></i>Warning <b><?= $warning ?></b></span><span><i class="dot red"></i>Offline <b><?= $offline ?></b></span></div></div>
  </div>
</div>

<div class="panel" style="margin-bottom:18px">
  <div class="panel-head"><div><h3>Wi-Fi Connected Devices</h3><p>Discover LAN clients and map them to Access Points when controller data is available.</p></div><a class="link" href="wifi_clients.php">Open discovery →</a></div>
  <div class="alert-box"><b>Network discovery is ready.</b> The current adapter uses the monitoring server ARP table; exact AP association needs the actual AP/router controller integration.</div>
</div>

<div class="grid-2">
  <div class="panel">
    <div class="panel-head"><div><h3>Monitored Devices</h3><p>Latest network state</p></div><a class="link" href="devices.php">View all →</a></div>
    <div class="table-wrap"><table><thead><tr><th>Device</th><th>IP</th><th>Status</th><th>Traffic</th></tr></thead><tbody>
    <?php foreach($devices as $d): ?>
      <tr><td><b><?= e($d['device_name']) ?></b><small><?= e($d['device_type']) ?> · <?= e($d['location']) ?></small></td><td class="mono"><?= e($d['ip_address']) ?></td><td><span class="status <?= strtolower($d['status']) ?>"><?= e($d['status']) ?></span></td><td><?= number_format((float)$d['download_mbps'],1) ?> Mbps</td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
  </div>
  <div class="panel">
    <div class="panel-head"><div><h3>Open Alerts</h3><p>Items requiring attention</p></div><a class="link" href="alerts.php">Manage →</a></div>
    <?php if(!$recentAlerts): ?><div class="empty">No active alerts 🎉</div><?php endif; ?>
    <div class="alert-list">
    <?php foreach($recentAlerts as $a): ?>
      <div class="alert-row"><span class="alert-symbol <?= strtolower($a['severity']) ?>">!</span><div><b><?= e($a['title']) ?></b><p><?= e($a['message']) ?></p><small><?= e($a['device_name'] ?? 'Network') ?> · <?= e($a['created_at']) ?></small></div></div>
    <?php endforeach; ?>
    </div>
  </div>
</div>
<script>
window.dashboardData = <?= json_encode(['down'=>$down,'up'=>$up]) ?>;
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
