<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$devices=$pdo->query("SELECT * FROM devices")->fetchAll();
$updated=0;
foreach($devices as $d){
    // Prototype monitoring values. Replace this generator with SNMP/router polling in production.
    $down=round(max(0, min($d['bandwidth_limit']*1.15, $d['bandwidth_limit']*(0.15+mt_rand(0,85)/100))),1);
    $up=round(max(0, min($d['bandwidth_limit']*0.5, $d['bandwidth_limit']*(0.05+mt_rand(0,40)/100))),1);
    $status=$down > $d['bandwidth_limit']*0.9 ? 'Warning' : (mt_rand(1,100)<=4 ? 'Offline' : 'Online');
    $pdo->prepare("UPDATE devices SET download_mbps=?,upload_mbps=?,status=?,last_seen=NOW() WHERE id=?")->execute([$down,$up,$status,$d['id']]);
    $pdo->prepare("INSERT INTO bandwidth_logs(device_id,download_mbps,upload_mbps) VALUES(?,?,?)")->execute([$d['id'],$down,$up]);
    if($status==='Warning'){
        $title='High bandwidth usage';
        $message=$d['device_name'].' is using '.number_format($down,1).' Mbps of its '.number_format($d['bandwidth_limit'],1).' Mbps limit.';
        $exists=$pdo->prepare("SELECT id FROM alerts WHERE device_id=? AND title=? AND is_resolved=0 LIMIT 1");
        $exists->execute([$d['id'],$title]);
        if(!$exists->fetch()) $pdo->prepare("INSERT INTO alerts(device_id,title,message,severity) VALUES(?,?,?,?)")->execute([$d['id'],$title,$message,'High']);
    }
    $updated++;
}
json_response(['ok'=>true,'updated'=>$updated]);
?>
