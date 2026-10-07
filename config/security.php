<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/database.php';
require_once __DIR__.'/helpers.php';
function csrf_token(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function require_csrf(): void { $token=$_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''); if(!hash_equals($_SESSION['csrf']??'', $token)) json_response(['ok'=>false,'message'=>'Invalid CSRF token.'],419); }
function can(string $permission): bool {
 if(($_SESSION['user_role']??'')==='Admin') return true;
 global $pdo; $st=$pdo->prepare('SELECT 1 FROM permissions WHERE role=? AND (permission_key=? OR permission_key=?) LIMIT 1'); $st->execute([$_SESSION['user_role']??'', $permission, '*']); return (bool)$st->fetchColumn();
}
function require_permission(string $permission): void { require_login(); if(!can($permission)) { http_response_code(403); die('Permission denied.'); } }
function audit(string $event,string $details='',?int $userId=null): void { global $pdo; $uid=$userId ?? ($_SESSION['user_id']??null); $pdo->prepare('INSERT INTO activity_logs(user_id,action,details) VALUES(?,?,?)')->execute([$uid,$event,$details]); }
function security_event(string $type,string $severity,string $details='',?int $userId=null): void { global $pdo; $pdo->prepare('INSERT INTO security_events(user_id,event_type,severity,ip_address,user_agent,details) VALUES(?,?,?,?,?,?)')->execute([$userId??($_SESSION['user_id']??null),$type,$severity,$_SERVER['REMOTE_ADDR']??null,substr($_SERVER['HTTP_USER_AGENT']??'',0,500),$details]); }
function setting(string $key,string $fallback=''): string { global $pdo; try{$st=$pdo->prepare('SELECT setting_value FROM settings WHERE setting_key=?');$st->execute([$key]);return (string)($st->fetchColumn()??$fallback);}catch(Throwable $e){return $fallback;} }
?>
