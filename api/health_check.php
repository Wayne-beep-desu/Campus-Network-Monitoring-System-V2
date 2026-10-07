<?php
require_once __DIR__.'/../config/security.php'; require_permission('health.view');
$id=(int)($_GET['device_id']??0);
$st=$pdo->prepare('SELECT * FROM devices WHERE id=?');$st->execute([$id]);$d=$st->fetch();
if(!$d) json_response(['ok'=>false,'message'=>'Device not found.'],404);
$host=$d['ip_address'];$start=microtime(true);$os=strtolower(PHP_OS);
$cmd=$os==='darwin' ? 'ping -c 1 -W 1000 ' : 'ping -n 1 -w 1000 ';
exec($cmd.escapeshellarg($host).' 2>&1',$out,$code);$lat=round((microtime(true)-$start)*1000,2);
$status=$code===0?'Pass':'Fail';$msg=substr(implode(' ',array_slice($out,-2)),0,500);
$pdo->prepare('INSERT INTO device_health_checks(device_id,check_type,status,latency_ms,message) VALUES(?,?,?,?,?)')->execute([$id,'ping',$status,$lat,$msg]);
if($code===0){$pdo->prepare("UPDATE devices SET status='Online',last_seen=NOW() WHERE id=?")->execute([$id]);}
else{$pdo->prepare("UPDATE devices SET status='Offline' WHERE id=?")->execute([$id]);security_event('device_health_failure','Warning',$d['device_name'].' ('.$host.') ping failed');}
json_response(['ok'=>true,'status'=>$status,'latency_ms'=>$lat,'message'=>$msg]);
