<?php
require_once __DIR__.'/config/security.php';
require_login();
$page_title='Network Assistant';
$total=(int)$pdo->query('SELECT COUNT(*) FROM devices')->fetchColumn();
$offline=(int)$pdo->query("SELECT COUNT(*) FROM devices WHERE status='Offline'")->fetchColumn();
$warning=(int)$pdo->query("SELECT COUNT(*) FROM devices WHERE status='Warning'")->fetchColumn();
$open=(int)$pdo->query('SELECT COUNT(*) FROM alerts WHERE is_resolved=0')->fetchColumn();
$recent=$pdo->query("SELECT device_name,ip_address,status,download_mbps,upload_mbps,bandwidth_limit FROM devices ORDER BY status='Offline' DESC,status='Warning' DESC LIMIT 8")->fetchAll();
$answer='Ask about offline devices, high bandwidth, alerts, or troubleshooting. This built-in assistant uses live database records and fixed diagnostic rules; it is not a generative AI service.';
if($_SERVER['REQUEST_METHOD']==='POST'){
 require_csrf(); $q=trim(mb_substr($_POST['question']??'',0,500)); $l=strtolower($q);
 if(str_contains($l,'offline')||str_contains($l,'down')){$answer="There are $offline offline device(s) out of $total registered devices. Open Devices or Device Health, then run an authorized ping test. Check power, cabling, VLAN, DHCP, firewall rules, and the switch port before changing configurations.";}
 elseif(str_contains($l,'bandwidth')||str_contains($l,'slow')||str_contains($l,'speed')){$answer="There are $warning device(s) currently marked Warning. Review Bandwidth and Analytics, compare measured values with each device's configured limit, and verify that the source is marked REAL (SNMP/API) rather than SIMULATED before treating it as measured traffic.";}
 elseif(str_contains($l,'alert')){$answer="There are $open unresolved alert(s). Open Alerts, review severity and timestamps, verify the affected device, record the incident, and resolve the alert only after confirming recovery.";}
 elseif(str_contains($l,'security')||str_contains($l,'unknown')||str_contains($l,'login')){$answer="Open Security and Activity Logs. Review failed logins, unfamiliar source IP addresses, account status, and recent permission changes. Enable HTTPS, use unique passwords, and restrict the admin interface to the campus IT network.";}
 elseif(str_contains($l,'backup')||str_contains($l,'restore')){$answer="Open Backup & Restore and create a fresh database backup before maintenance. Store an encrypted copy outside the public web root and periodically test restoration on a separate environment.";}
 else{$answer="Current snapshot: $total registered devices, $offline offline, $warning warning, and $open unresolved alerts. Try asking about offline devices, bandwidth, alerts, security, or backups.";}
 $pdo->prepare('INSERT INTO assistant_sessions(user_id,prompt,response) VALUES(?,?,?)')->execute([$_SESSION['user_id'],$q,$answer]);
}
include __DIR__.'/partials/header.php';
?>
<div class="hero"><div><div class="eyebrow">OPERATIONS SUPPORT</div><h1>Network assistant</h1><p>Rule-based guidance generated from current campus records.</p></div></div>
<div class="grid-2"><div class="panel"><div class="panel-head"><div><h3>Ask the assistant</h3><p>Examples: “Which devices are offline?” or “How do I investigate slow bandwidth?”</p></div><span class="pill blue">LOCAL RULES</span></div><div style="padding:20px"><form method="post" class="settings-form"><label>Your question<textarea name="question" rows="3" maxlength="500" required placeholder="Describe the network issue…"></textarea></label><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button class="btn primary">Get guidance</button></form><div class="alert-box" style="margin-top:18px;white-space:normal;line-height:1.7"><?=e($answer)?></div></div></div>
<div class="panel"><div class="panel-head"><div><h3>Current network snapshot</h3><p>Read-only counts from the database.</p></div></div><div style="padding:20px;display:grid;gap:14px"><div><small class="muted">REGISTERED DEVICES</small><h2><?=$total?></h2></div><div><small class="muted">OFFLINE</small><h2><?=$offline?></h2></div><div><small class="muted">WARNING</small><h2><?=$warning?></h2></div><div><small class="muted">OPEN ALERTS</small><h2><?=$open?></h2></div><a class="link" href="diagnostics.php">Open Network Diagnostics →</a></div></div></div>
<div class="panel"><div class="panel-head"><div><h3>Devices requiring attention</h3><p>Based on saved device status.</p></div></div><div class="table-wrap"><table><thead><tr><th>Device</th><th>IP</th><th>Status</th><th>Download</th><th>Limit</th></tr></thead><tbody><?php foreach($recent as $d): ?><tr><td><?=e($d['device_name'])?></td><td><?=e($d['ip_address'])?></td><td><?=e($d['status'])?></td><td><?=number_format((float)$d['download_mbps'],2)?> Mbps</td><td><?=number_format((float)$d['bandwidth_limit'],2)?> Mbps</td></tr><?php endforeach; ?></tbody></table></div></div>
<?php include __DIR__.'/partials/footer.php'; ?>
