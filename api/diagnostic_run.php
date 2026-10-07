<?php
declare(strict_types=1);
require_once __DIR__.'/../config/security.php';
require_permission('health.view');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false,'message'=>'POST required.'],405);
require_csrf();
$id = filter_input(INPUT_POST, 'device_id', FILTER_VALIDATE_INT);
$type = $_POST['check_type'] ?? 'ping';
$port = filter_input(INPUT_POST, 'port', FILTER_VALIDATE_INT);
if (!$id || !in_array($type, ['ping','tcp'], true)) json_response(['ok'=>false,'message'=>'Invalid diagnostic request.'],422);

$stmt=$pdo->prepare('SELECT id,device_name,ip_address FROM devices WHERE id=?');
$stmt->execute([$id]); $device=$stmt->fetch();
if (!$device) json_response(['ok'=>false,'message'=>'Device is not registered.'],404);
$host=$device['ip_address'];
if (!filter_var($host,FILTER_VALIDATE_IP)) json_response(['ok'=>false,'message'=>'Registered device IP is invalid.'],422);

$start=microtime(true); $status='Fail'; $message='No response'; $latency=null;
if ($type==='tcp') {
 if (!$port || $port<1 || $port>65535) json_response(['ok'=>false,'message'=>'Provide a valid TCP port (1–65535).'],422);
 $errno=0; $errstr=''; $sock=@fsockopen($host,$port,$errno,$errstr,2.0);
 $latency=round((microtime(true)-$start)*1000,2);
 if ($sock) { fclose($sock); $status='Pass'; $message='TCP connection accepted on port '.$port.'.'; }
 else { $message='TCP connection failed ('.$errno.').'; }
} else {
 $timeout=1.5; $sock=@fsockopen('udp://'.$host,7,$errno,$errstr,$timeout);
 // UDP echo is not a reliable ping; use the existing ICMP endpoint for ping checks.
 if ($sock) fclose($sock);
 $status='Error'; $message='Use the ICMP Ping action on Device Health for a true ping test. This endpoint does not misreport UDP reachability as ping.';
}
$pdo->prepare('INSERT INTO diagnostic_runs(device_id,requested_by,check_type,target_port,status,latency_ms,result_message) VALUES(?,?,?,?,?,?,?)')
    ->execute([$id,$_SESSION['user_id']??null,$type,$type==='tcp'?$port:null,$status,$latency,$message]);
audit('Network diagnostic',$device['device_name'].' '.$type.' '.$message);
json_response(['ok'=>true,'status'=>$status,'latency_ms'=>$latency,'message'=>$message,'device'=>$device['device_name']]);
