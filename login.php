<?php
require_once __DIR__.'/config/database.php'; require_once __DIR__.'/config/helpers.php'; require_once __DIR__.'/config/security.php';
if(!empty($_SESSION['user_id'])){header('Location: dashboard.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $username=trim($_POST['username']??''); $password=$_POST['password']??'';
 $st=$pdo->prepare('SELECT * FROM users WHERE username=? LIMIT 1');$st->execute([$username]);$u=$st->fetch();
 if($u && !$u['is_active']){$error='Account is disabled.';security_event('login_disabled','Warning','Disabled account login attempt',null);}
 elseif($u && !empty($u['locked_until']) && strtotime($u['locked_until'])>time()){$error='Account temporarily locked. Try again later.';security_event('login_locked','Warning','Locked account login attempt',$u['id']);}
 elseif($u && password_verify($password,$u['password_hash'])){
   $_SESSION['user_id']=(int)$u['id'];$_SESSION['user_name']=$u['name'];$_SESSION['user_role']=$u['role'];$_SESSION['csrf']=bin2hex(random_bytes(32));
   $pdo->prepare('UPDATE users SET failed_logins=0,locked_until=NULL WHERE id=?')->execute([$u['id']]); audit('Login','Successful login'); header('Location: dashboard.php');exit;
 } else {
   if($u){$fails=(int)$u['failed_logins']+1;$lock=$fails>=5?date('Y-m-d H:i:s',time()+900):null;$pdo->prepare('UPDATE users SET failed_logins=?,locked_until=? WHERE id=?')->execute([$fails,$lock,$u['id']]);}
   security_event('failed_login','Warning','Invalid username or password', $u['id']??null); $error='Invalid username or password.';
 }
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>CampusNet Login</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="login-page"><div class="login-card"><div class="brand center"><div class="brand-mark">CN</div><div><b>CampusNet</b><small>Network Control</small></div></div><h1>Welcome back</h1><p class="muted">Sign in to the campus network control center.</p><?php if($error):?><div class="alert-box danger"><?=e($error)?></div><?php endif;?><form method="post"><label>Username<input name="username" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="btn primary full">Sign in</button></form><small class="login-help">Default: admin / admin123</small></div></body></html>
