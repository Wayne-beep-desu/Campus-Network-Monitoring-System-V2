<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$method=$_SERVER['REQUEST_METHOD'];
if($method==='GET'){
    $rows=$pdo->query("SELECT * FROM devices ORDER BY id DESC")->fetchAll();
    json_response(['ok'=>true,'devices'=>$rows]);
}
if($method==='POST'){
    $data=json_decode(file_get_contents('php://input'),true) ?: $_POST;
    $id=(int)($data['id']??0);
    $name=trim($data['device_name']??'');
    $ip=trim($data['ip_address']??'');
    $mac=trim($data['mac_address']??'');
    $type=trim($data['device_type']??'Computer');
    $location=trim($data['location']??'');
    $limit=(float)($data['bandwidth_limit']??10);
    $mode=in_array(($data['monitor_mode']??'simulated'),['simulated','snmp','api'],true)?$data['monitor_mode']:'simulated';
    $community=trim($data['snmp_community']??''); $snmp_port=(int)($data['snmp_port']??161);
    if($name===''||$ip==='') json_response(['ok'=>false,'message'=>'Device name and IP address are required.'],422);
    try{
        if($id){
            $st=$pdo->prepare("UPDATE devices SET device_name=?,ip_address=?,mac_address=?,device_type=?,location=?,bandwidth_limit=?,monitor_mode=?,snmp_community=?,snmp_port=? WHERE id=?");
            $st->execute([$name,$ip,$mac,$type,$location,$limit,$mode,$community,$snmp_port,$id]);
            $action='Updated device';
        }else{
            $st=$pdo->prepare("INSERT INTO devices(device_name,ip_address,mac_address,device_type,location,bandwidth_limit,status,monitor_mode,snmp_community,snmp_port) VALUES(?,?,?,?,?,?, 'Online',?,?,?)");
            $st->execute([$name,$ip,$mac,$type,$location,$limit,$mode,$community,$snmp_port]);
            $action='Added device';
        }
        $log=$pdo->prepare("INSERT INTO activity_logs(user_id,action,details) VALUES(?,?,?)");
        $log->execute([$_SESSION['user_id'],$action,$name.' ('.$ip.')']);
        json_response(['ok'=>true]);
    }catch(PDOException $e){ json_response(['ok'=>false,'message'=>'IP address may already exist.'],409); }
}
if($method==='DELETE'){
    parse_str(file_get_contents('php://input'),$data);
    $id=(int)($data['id']??($_GET['id']??0));
    $st=$pdo->prepare("SELECT device_name,ip_address FROM devices WHERE id=?");$st->execute([$id]);$d=$st->fetch();
    if(!$d) json_response(['ok'=>false,'message'=>'Device not found.'],404);
    $pdo->prepare("DELETE FROM devices WHERE id=?")->execute([$id]);
    $pdo->prepare("INSERT INTO activity_logs(user_id,action,details) VALUES(?,?,?)")->execute([$_SESSION['user_id'],'Deleted device',$d['device_name'].' ('.$d['ip_address'].')']);
    json_response(['ok'=>true]);
}
json_response(['ok'=>false,'message'=>'Method not allowed.'],405);
?>
