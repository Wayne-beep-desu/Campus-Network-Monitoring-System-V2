<?php
require_once __DIR__.'/../config/security.php'; require_permission('permissions.manage');
if($_SERVER['REQUEST_METHOD']==='GET') json_response(['ok'=>true,'permissions'=>$pdo->query('SELECT * FROM permissions ORDER BY role,permission_key')->fetchAll()]);
$data=json_decode(file_get_contents('php://input'),true)?:$_POST;
$role=trim($data['role']??'');$key=trim($data['permission_key']??'');
if($role===''||$key==='') json_response(['ok'=>false,'message'=>'Role and permission are required.'],422);
if($_SERVER['REQUEST_METHOD']==='POST'){$pdo->prepare('INSERT IGNORE INTO permissions(role,permission_key) VALUES(?,?)')->execute([$role,$key]);audit('Granted permission',$role.' / '.$key);json_response(['ok'=>true]);}
if($_SERVER['REQUEST_METHOD']==='DELETE'){$pdo->prepare('DELETE FROM permissions WHERE role=? AND permission_key=?')->execute([$role,$key]);audit('Revoked permission',$role.' / '.$key);json_response(['ok'=>true]);}
json_response(['ok'=>false,'message'=>'Method not allowed'],405);
