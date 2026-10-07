<?php
require_once __DIR__.'/config/security.php';
require_permission('health.view');
$page_title='Network Diagnostics';
$devices=$pdo->query('SELECT id,device_name,ip_address,status FROM devices ORDER BY device_name')->fetchAll();
$history=$pdo->query('SELECT r.*,d.device_name,d.ip_address,u.name AS requested_by_name FROM diagnostic_runs r JOIN devices d ON d.id=r.device_id LEFT JOIN users u ON u.id=r.requested_by ORDER BY r.created_at DESC LIMIT 40')->fetchAll();
include __DIR__.'/partials/header.php';
?>
<div class="hero"><div><div class="eyebrow">TROUBLESHOOTING</div><h1>Network diagnostics</h1><p>Run checks only against devices registered in this campus inventory.</p></div></div>
<div class="panel" style="margin-bottom:18px"><div class="panel-head"><div><h3>TCP reachability test</h3><p>Checks whether a selected device accepts a TCP connection on a specified port.</p></div><span class="pill blue">AUTHORIZED DEVICES</span></div>
<div style="padding:20px"><form id="diagForm" class="settings-form"><div class="form-grid no-pad">
<label>Registered device<select name="device_id" required><?php foreach($devices as $d): ?><option value="<?= (int)$d['id'] ?>"><?=e($d['device_name'])?> — <?=e($d['ip_address'])?> (<?=e($d['status'])?>)</option><?php endforeach; ?></select></label>
<label>TCP port<input name="port" type="number" min="1" max="65535" value="443" required></label>
</div><button class="btn primary" type="submit">Run TCP test</button> <a class="btn ghost" href="health.php">Open ICMP ping checks</a><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"></form><div id="diagResult" role="status" style="margin-top:14px;color:var(--muted)"></div></div></div>
<div class="panel"><div class="panel-head"><div><h3>Recent diagnostic history</h3><p>Latest 40 tests performed by signed-in users.</p></div></div><div class="table-wrap"><table><thead><tr><th>Time</th><th>Device</th><th>Check</th><th>Result</th><th>Latency</th><th>Requested by</th><th>Details</th></tr></thead><tbody>
<?php if(!$history): ?><tr><td colspan="7">No diagnostic tests recorded yet.</td></tr><?php endif; ?>
<?php foreach($history as $r): ?><tr><td><?=e($r['created_at'])?></td><td><b><?=e($r['device_name'])?></b><small><?=e($r['ip_address'])?></small></td><td><?=strtoupper(e($r['check_type']))?><?= $r['target_port']?' · '.(int)$r['target_port']:'' ?></td><td><span class="status <?=strtolower($r['status'])?>"><?=e($r['status'])?></span></td><td><?= $r['latency_ms']!==null?number_format((float)$r['latency_ms'],2).' ms':'—' ?></td><td><?=e($r['requested_by_name']??'System')?></td><td><?=e($r['result_message']??'')?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<script>
document.getElementById('diagForm').addEventListener('submit',async e=>{
 e.preventDefault();const form=e.currentTarget,box=document.getElementById('diagResult'),btn=form.querySelector('button');
 btn.disabled=true;btn.textContent='Testing…';box.textContent='Running TCP connection test…';
 try{const r=await fetch('api/diagnostic_run.php',{method:'POST',body:new FormData(form)});const j=await r.json();box.textContent=(j.status||'Error')+' — '+(j.message||'Request failed')+(j.latency_ms!==null&&j.latency_ms!==undefined?' ('+j.latency_ms+' ms)':'');}
 catch(err){box.textContent='Could not complete the diagnostic request.';}
 finally{btn.disabled=false;btn.textContent='Run TCP test';}
});
</script>
<?php include __DIR__.'/partials/footer.php'; ?>
