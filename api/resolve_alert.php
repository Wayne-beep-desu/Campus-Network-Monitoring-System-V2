<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();
$data=json_decode(file_get_contents('php://input'),true) ?: [];
$id=(int)($data['id']??0);
$pdo->prepare("UPDATE alerts SET is_resolved=1,resolved_at=NOW() WHERE id=?")->execute([$id]);
$pdo->prepare("INSERT INTO activity_logs(user_id,action,details) VALUES(?,?,?)")->execute([$_SESSION['user_id'],'Resolved alert','Alert #'.$id]);
json_response(['ok'=>true]);
?>
