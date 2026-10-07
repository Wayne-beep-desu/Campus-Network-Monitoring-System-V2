<?php
require_once __DIR__.'/../config/security.php'; require_permission('monitoring.view');
/* REAL MONITORING: uses PHP SNMP extension when available. Only poll devices you are authorized to monitor. */
if(!extension_loaded('snmp')) json_response(['ok'=>false,'real'=>false,'message'=>'PHP SNMP extension is not enabled. Install/enable SNMP support before using real polling.'],503);
$id=(int)($_GET['device_id']??0);$st=$pdo->prepare("SELECT * FROM devices WHERE id=? AND monitor_mode='snmp'");$st->execute([$id]);$d=$st->fetch();if(!$d)json_response(['ok'=>false,'message'=>'Device is not configured for SNMP monitoring.'],404);
$host=$d['ip_address'];$community=$d['snmp_community']?:setting('snmp_default_community','public');$port=(int)$d['snmp_port']; $timeout=(int)setting('snmp_timeout','1000000');
try{
 $sys=[ 'name'=>@snmp2_get($host,$community,'1.3.6.1.2.1.1.5.0',$timeout,1), 'uptime'=>@snmp2_get($host,$community,'1.3.6.1.2.1.1.3.0',$timeout,1) ];
 if(!$sys['name']) throw new Exception('SNMP request failed. Check IP, community, ACL and firewall.');
 $pdo->prepare("INSERT INTO network_metrics(device_id,source,download_mbps,upload_mbps,uptime_seconds) VALUES(?,?,?,?,?)")->execute([$id,'snmp',0,0,null]);
 audit('SNMP poll',$d['device_name'].' '.$host); json_response(['ok'=>true,'real'=>true,'device'=>$d['device_name'],'system_name'=>$sys['name'],'uptime'=>$sys['uptime']]);
}catch(Throwable $e){security_event('snmp_poll_failure','Warning',$d['device_name'].': '.$e->getMessage());json_response(['ok'=>false,'real'=>true,'message'=>$e->getMessage()],502);}
?>
