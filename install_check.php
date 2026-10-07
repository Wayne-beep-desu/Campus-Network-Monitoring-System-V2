<?php
$checks=[];
$checks['PHP >= 8.0']=version_compare(PHP_VERSION,'8.0.0','>=');
$checks['PDO MySQL']=extension_loaded('pdo_mysql');
$checks['JSON']=extension_loaded('json');
$checks['SNMP (needed for real SNMP)']=extension_loaded('snmp');
$checks['exec() available']=function_exists('exec');
$checks['mysqldump available']=false;$checks['mysql CLI available']=false;
if(function_exists('exec')){exec('command -v mysqldump 2>/dev/null',$o,$c);$checks['mysqldump available']=$c===0;exec('command -v mysql 2>/dev/null',$o2,$c2);$checks['mysql CLI available']=$c2===0;}
include __DIR__.'/partials/header.php';
?>
<div class="hero"><div><div class="eyebrow">SYSTEM DIAGNOSTICS</div><h1>Installation Check</h1><p>Review server requirements before enabling real monitoring or recovery features.</p></div></div>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>Requirement</th><th>Status</th></tr></thead><tbody><?php foreach($checks as $name=>$ok): ?><tr><td><?=e($name)?></td><td><span class="status <?=$ok?'online':'offline'?>"><?=$ok?'Ready':'Needs setup'?></span></td></tr><?php endforeach; ?></tbody></table></div></div>
<div class="backup-note" style="margin-top:18px">For real SNMP monitoring, enable PHP SNMP and configure only devices you are authorized to monitor. For backups, make sure the MySQL CLI tools are available to the PHP process.</div>
<?php include __DIR__.'/partials/footer.php'; ?>
