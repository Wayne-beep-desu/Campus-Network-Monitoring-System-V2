<?php
require_once __DIR__.'/../config/security.php'; require_permission('monitoring.view');
if(!extension_loaded('snmp')) json_response(['ok'=>false,'real'=>false,'message'=>'PHP SNMP extension is not enabled.'],503);
$id=(int)($_GET['device_id']??0);$st=$pdo->prepare("SELECT * FROM devices WHERE id=? AND monitor_mode='snmp'");$st->execute([$id]);$d=$st->fetch();if(!$d)json_response(['ok'=>false,'message'=>'Device is not configured for SNMP.'],404);
$community=$d['snmp_community']?:setting('snmp_default_community','public');$host=$d['ip_address'];
function sn($v){$v=preg_replace('/^[A-Z\-]+:\s*/','',(string)$v);return is_numeric($v)?(float)$v:null;}
$in=@snmp2_walk($host,$community,'1.3.6.1.2.1.31.1.1.1.6',1000000,1);$out=@snmp2_walk($host,$community,'1.3.6.1.2.1.31.1.1.1.10',1000000,1);
if($in===false||$out===false)json_response(['ok'=>false,'real'=>true,'message'=>'IF-MIB high-capacity counters unavailable. Check SNMP permissions/device support.'],502);
$now=microtime(true);$totalIn=0;$totalOut=0;foreach($in as $v)$totalIn+=sn($v)??0;foreach($out as $v)$totalOut+=sn($v)??0;
$pdo->prepare('INSERT INTO network_metrics(device_id,source,download_mbps,upload_mbps) VALUES(?,?,?,?)')->execute([$id,'snmp',0,0]);
json_response(['ok'=>true,'real'=>true,'device'=>$d['device_name'],'interface_counters'=>['in_octets'=>$totalIn,'out_octets'=>$totalOut],'sampled_at'=>date('c')]);
