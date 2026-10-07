<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/helpers.php';
require_login();
$page_title = 'Wi-Fi Connected Devices';
$aps = $pdo->query("SELECT * FROM access_points WHERE enabled=1 ORDER BY ap_name")->fetchAll();
$clients = $pdo->query("SELECT c.*, a.ap_name, a.ssid AS ap_ssid FROM wifi_clients c LEFT JOIN access_points a ON a.id=c.access_point_id ORDER BY c.status='Online' DESC, c.last_seen DESC")->fetchAll();
include __DIR__ . '/partials/header.php';
?>
<div class="hero compact">
  <div><div class="eyebrow">NETWORK DISCOVERY</div><h1>Connected Wi-Fi Devices</h1><p>View LAN clients discovered by the monitoring server and their assigned Access Point.</p></div>
  <button class="btn primary" id="scanBtn" onclick="scanWifi()">⌁ Scan network</button>
</div>
<div class="panel" style="margin-bottom:18px">
  <div class="panel-head"><div><h3>Discovery source</h3><p>Current V2 implementation reads the monitoring server's ARP table. It does not pretend ARP data is the AP association table.</p></div><span class="mode-badge real">AUTHORIZED LAN DISCOVERY</span></div>
  <div class="form-grid">
    <label>Access Point<select id="apSelect"><option value="0">Unassigned / detect from controller later</option><?php foreach($aps as $ap): ?><option value="<?= (int)$ap['id'] ?>" data-ssid="<?= e($ap['ssid'] ?? '') ?>" data-location="<?= e($ap['location'] ?? '') ?>"><?= e($ap['ap_name']) ?> — <?= e($ap['location'] ?? 'No location') ?></option><?php endforeach; ?></select></label>
    <label>SSID <input id="ssidInput" placeholder="Campus-WiFi"></label>
  </div>
</div>
<div class="stat-grid">
  <div class="stat-card"><span class="stat-icon blue">⌁</span><div><small>Discovered Clients</small><strong id="clientCount"><?= count($clients) ?></strong><span class="muted">ARP/LAN view</span></div></div>
  <div class="stat-card"><span class="stat-icon green">●</span><div><small>Online</small><strong id="onlineCount"><?= count(array_filter($clients, fn($c)=>$c['status']==='Online')) ?></strong><span class="muted">Last seen ≤ 5 min</span></div></div>
  <div class="stat-card"><span class="stat-icon purple">⌁</span><div><small>Access Points</small><strong><?= count($aps) ?></strong><span class="muted">Configured</span></div></div>
</div>
<div class="panel">
  <div class="panel-head"><div><h3>Detected Clients</h3><p>IP and MAC information visible to the monitoring host.</p></div><span class="pill blue">LIVE SCAN</span></div>
  <div class="table-wrap"><table><thead><tr><th>Device</th><th>IP</th><th>MAC</th><th>Access Point / SSID</th><th>Status</th><th>Last Seen</th></tr></thead><tbody id="clientRows">
  <?php foreach($clients as $c): ?><tr><td><b><?= e($c['hostname'] ?: 'Unknown device') ?></b><small><?= e($c['device_type']) ?></small></td><td class="mono"><?= e($c['ip_address']) ?></td><td class="mono"><?= e($c['mac_address'] ?: '—') ?></td><td><?= e($c['ap_name'] ?: 'Not mapped') ?><small><?= e($c['ssid'] ?: ($c['ap_ssid'] ?: '—')) ?></small></td><td><span class="status <?= strtolower($c['status']) ?>"><?= e($c['status']) ?></span></td><td><?= e($c['last_seen']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <?php if(!$clients): ?><div class="empty" id="emptyState">Wala pang discovered clients. I-click ang <b>Scan network</b>.</div><?php endif; ?>
</div>
<div class="panel" style="margin-top:18px"><div class="panel-head"><div><h3>Important deployment note</h3><p>Para maging exact ang “kung saang AP nakakonekta” requirement, kailangan nating ikonekta ang page na ito sa actual router/AP controller (hal. MikroTik, UniFi, Omada, etc.). Ang generic LAN scan ay nakakakita ng reachable clients pero hindi garantisadong alam ang exact AP association.</p></div></div></div>
<script>
async function scanWifi(){
 const btn=document.getElementById('scanBtn'); btn.disabled=true; btn.textContent='Scanning…';
 const ap=document.getElementById('apSelect'); const opt=ap.options[ap.selectedIndex];
 try{
  const r=await fetch('api/wifi_discovery.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({access_point_id:ap.value,ssid:document.getElementById('ssidInput').value || opt.dataset.ssid || '',location:opt.dataset.location || ''})});
  const d=await r.json(); if(!d.ok) throw new Error(d.message||'Scan failed');
  renderClients(d.clients||[]); document.getElementById('clientCount').textContent=d.count||0; document.getElementById('onlineCount').textContent=(d.clients||[]).filter(x=>x.status==='Online').length; showToast(d.message||'Scan complete');
 }catch(e){showToast(e.message||'Scan failed',true)} finally{btn.disabled=false;btn.textContent='⌁ Scan network'}
}
function renderClients(rows){const body=document.getElementById('clientRows'); body.innerHTML=rows.map(c=>`<tr><td><b>${esc(c.hostname||'Unknown device')}</b><small>${esc(c.device_type||'Wi-Fi/LAN Client')}</small></td><td class="mono">${esc(c.ip_address)}</td><td class="mono">${esc(c.mac_address||'—')}</td><td>Assigned during scan<small>${esc(c.ssid||'—')}</small></td><td><span class="status online">Online</span></td><td>Now</td></tr>`).join('');}
function esc(s){return String(s).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))}
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
