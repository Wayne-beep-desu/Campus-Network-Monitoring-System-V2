<?php
require_once __DIR__.'/config/database.php'; require_once __DIR__.'/config/auth.php'; require_once __DIR__.'/config/helpers.php'; require_login();
$page_title='Network Map'; $devices=$pdo->query("SELECT * FROM devices ORDER BY id")->fetchAll(); include __DIR__.'/partials/header.php';
?>
<div class="hero compact"><div><div class="eyebrow">VISUAL TOPOLOGY</div><h1>Campus network map</h1><p>Interactive topology view of routers, servers, access points and computers.</p></div><button class="btn primary" onclick="monitorAll()">↻ Refresh topology</button></div>
<div class="map-panel"><div class="map-node cloud">☁<b>Internet</b><small>Campus ISP</small></div><div class="connector v"></div><div class="map-node router online">◉<b>Core Router</b><small>192.168.1.1</small></div><div class="connector v"></div><div class="map-node switch online">▣<b>Core Switch</b><small>Campus Distribution</small></div><div class="connector v"></div><div class="map-branches">
<?php foreach($devices as $d): ?><div class="map-device <?=strtolower($d['status'])?>" onclick='showDeviceInfo(<?=json_encode($d,JSON_HEX_APOS|JSON_HEX_QUOT)?>)'><span>▦</span><div><b><?=e($d['device_name'])?></b><small><?=e($d['ip_address'])?></small></div><i></i></div><?php endforeach; ?>
</div></div><div class="map-legend"><span>🟢 Online</span><span>🟡 Warning</span><span>🔴 Offline</span><span>Click a device for details</span></div>
<div class="modal" id="infoModal"><div class="modal-card"><div class="modal-head"><h3 id="infoTitle">Device</h3><button class="close" onclick="qs('#infoModal').classList.remove('show')">×</button></div><div class="device-detail" id="infoBody"></div></div></div>
<?php include __DIR__.'/partials/footer.php'; ?>
