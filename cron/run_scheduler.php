<?php
// Run from CLI every 5 minutes. Example: */5 * * * * php /path/to/cron/run_scheduler.php
require_once __DIR__.'/../config/database.php'; require_once __DIR__.'/../config/helpers.php';
function s($k,$f=''){global $pdo;$st=$pdo->prepare('SELECT setting_value FROM settings WHERE setting_key=?');$st->execute([$k]);return (string)($st->fetchColumn()??$f);}
if(s('email_alerts_enabled','0')!=='1') exit("Email alerts disabled\n");
$recipients=$pdo->query("SELECT DISTINCT email FROM users WHERE is_active=1 AND email IS NOT NULL AND role IN ('Admin','IT Staff')")->fetchAll(PDO::FETCH_COLUMN);
$alerts=$pdo->query("SELECT a.*,d.device_name FROM alerts a LEFT JOIN devices d ON d.id=a.device_id WHERE a.is_resolved=0 AND a.created_at>=NOW()-INTERVAL 10 MINUTE")->fetchAll();
foreach($alerts as $a){foreach($recipients as $to){$subject='Campus Network Alert: '.$a['severity'].' - '.$a['title'];$body=$a['message'].'\nDevice: '.($a['device_name']??'—').'\nTime: '.$a['created_at'];$headers='From: '.s('smtp_from','no-reply@campus.local');$ok=@mail($to,$subject,$body,$headers);$pdo->prepare('INSERT INTO notification_queue(recipient_email,subject,body,status,attempts,sent_at) VALUES(?,?,?,?,?,?)')->execute([$to,$subject,$body,$ok?'Sent':'Failed',1,$ok?date('Y-m-d H:i:s'):null]);}}
$pdo->query("UPDATE report_schedules SET last_run=NOW() WHERE enabled=1 AND (last_run IS NULL OR last_run < NOW()-INTERVAL 23 HOUR)");
echo "Scheduler completed\n";
