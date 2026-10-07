<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = $pdo->query("SELECT a.*, d.device_name FROM alerts a LEFT JOIN devices d ON d.id=a.device_id ORDER BY a.created_at DESC")->fetchAll();
    json_response(['ok'=>true,'alerts'=>$rows]);
}

if ($method === 'PATCH') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = (int)($data['id'] ?? 0);
    $pdo->prepare("UPDATE alerts SET is_resolved=1,resolved_at=NOW() WHERE id=?")->execute([$id]);
    $pdo->prepare("INSERT INTO activity_logs(user_id,action,details) VALUES(?,?,?)")
        ->execute([$_SESSION['user_id'],'Resolved alert','Alert #'.$id]);
    json_response(['ok'=>true]);
}

json_response(['ok'=>false,'message'=>'Unsupported request.'],405);
?>