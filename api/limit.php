<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();
$data=json_decode(file_get_contents('php://input'),true) ?: [];
$id=(int)($data['id']??0); $limit=(float)($data['limit']??0);
if($id<=0||$limit<=0) json_response(['ok'=>false,'message'=>'Invalid values.'],422);
$pdo->prepare("UPDATE devices SET bandwidth_limit=? WHERE id=?")->execute([$limit,$id]);
$pdo->prepare("INSERT INTO activity_logs(user_id,action,details) VALUES(?,?,?)")->execute([$_SESSION['user_id'],'Changed bandwidth limit','Device #'.$id.' → '.$limit.' Mbps']);
json_response(['ok'=>true]);
?>
