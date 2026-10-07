<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';
require_login();
$page_title='Device Management';
$devices=$pdo->query("SELECT * FROM devices ORDER BY id DESC")->fetchAll();
include __DIR__ . '/partials/header.php';
?>
<div class="hero compact"><div><div class="eyebrow">INFRASTRUCTURE</div><h1>Connected devices</h1><p>Register and manage routers, servers, PCs and access points.</p></div><button class="btn primary" onclick="openModal()">+ Add Device</button></div>
<div class="panel">
<div class="toolbar"><input class="search" id="deviceSearch" oninput="filterTable('deviceTable',this.value)" placeholder="Search device, IP or location…"><button class="btn ghost" onclick="monitorAll()">⚡ Check status</button></div>
<div class="table-wrap"><table id="deviceTable"><thead><tr><th>Device</th><th>IP / MAC</th><th>Type</th><th>Bandwidth</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($devices as $d): ?>
<tr>
<td><b><?= e($d['device_name']) ?></b><small><?= e($d['location']) ?></small></td>
<td class="mono"><?= e($d['ip_address']) ?><small><?= e($d['mac_address']) ?></small></td>
<td><?= e($d['device_type']) ?></td>
<td><b><?= number_format((float)$d['download_mbps'],1) ?></b> / <?= number_format((float)$d['bandwidth_limit'],1) ?> Mbps<small>Limit</small></td>
<td><span class="status <?= strtolower($d['status']) ?>"><?= e($d['status']) ?></span></td>
<td><button class="icon-btn" onclick='editDevice(<?= json_encode($d) ?>)'>✎</button> <button class="icon-btn danger" onclick="deleteDevice(<?= (int)$d['id'] ?>)">×</button></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
</div>

<div class="modal" id="deviceModal"><div class="modal-card"><div class="modal-head"><h3 id="modalTitle">Add Device</h3><button class="close" onclick="closeModal()">×</button></div>
<form id="deviceForm"><input type="hidden" name="id" id="device_id">
<div class="form-grid"><label>Device Name<input name="device_name" id="device_name" required></label><label>IP Address<input name="ip_address" id="ip_address" required></label><label>MAC Address<input name="mac_address" id="mac_address"></label><label>Device Type<select name="device_type" id="device_type"><option>Computer</option><option>Server</option><option>Router</option><option>Switch</option><option>Access Point</option><option>Printer</option></select></label><label>Location<input name="location" id="location"></label><label>Bandwidth Limit (Mbps)<input type="number" step="0.1" name="bandwidth_limit" id="bandwidth_limit" value="10"></label></div>
<div class="modal-actions"><button type="button" class="btn ghost" onclick="closeModal()">Cancel</button><button class="btn primary">Save Device</button></div></form></div></div>
<?php include __DIR__ . '/partials/footer.php'; ?>
