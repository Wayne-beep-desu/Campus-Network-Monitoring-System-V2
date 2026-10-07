<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

function arp_clients(): array {
    $lines = [];
    $rc = 0;
    if (function_exists('exec')) {
        @exec('/usr/sbin/arp -an 2>/dev/null', $lines, $rc);
        if (!$lines) @exec('arp -an 2>/dev/null', $lines, $rc);
    }
    $out = [];
    foreach ($lines as $line) {
        if (preg_match('/\((\d{1,3}(?:\.\d{1,3}){3})\)\s+at\s+([0-9a-fA-F:]{12,17})/i', $line, $m)) {
            $ip = $m[1]; $mac = strtoupper($m[2]);
            if (strcasecmp($mac, 'FF:FF:FF:FF:FF:FF') === 0 || stripos($line, 'incomplete') !== false) continue;
            $out[$ip] = ['ip_address'=>$ip,'mac_address'=>$mac];
        }
    }
    return array_values($out);
}

function detect_hostname(string $ip): ?string {
    $name = @gethostbyaddr($ip);
    return ($name && $name !== $ip) ? $name : null;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $rows = $pdo->query("SELECT c.*, a.ap_name, a.ssid AS ap_ssid FROM wifi_clients c LEFT JOIN access_points a ON a.id=c.access_point_id ORDER BY c.status='Online' DESC, c.last_seen DESC")->fetchAll();
    $aps = $pdo->query("SELECT * FROM access_points WHERE enabled=1 ORDER BY ap_name")->fetchAll();
    json_response(['ok'=>true,'clients'=>$rows,'access_points'=>$aps]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $started = date('Y-m-d H:i:s');
    $clients = arp_clients();
    $apId = (int)($data['access_point_id'] ?? 0);
    $ssid = trim((string)($data['ssid'] ?? ''));
    $location = trim((string)($data['location'] ?? ''));
    $source = 'lan_arp';

    if (!$clients) {
        $pdo->prepare("INSERT INTO wifi_discovery_logs(source,client_count,started_at,finished_at,notes) VALUES(?,?,?,?,?)")
            ->execute([$source,0,$started,date('Y-m-d H:i:s'),'No ARP clients returned. The web server may not have LAN visibility or exec/arp may be unavailable.']);
        json_response(['ok'=>true,'clients'=>[],'count'=>0,'message'=>'No LAN clients were visible in the ARP table.']);
    }

    $upsert = $pdo->prepare("INSERT INTO wifi_clients (ip_address,mac_address,hostname,device_type,access_point_id,ssid,location,source,status,first_seen,last_seen) VALUES (?,?,?,?,?,?,?,?, 'Online', NOW(), NOW()) ON DUPLICATE KEY UPDATE mac_address=VALUES(mac_address),hostname=VALUES(hostname),access_point_id=VALUES(access_point_id),ssid=VALUES(ssid),location=VALUES(location),source=VALUES(source),status='Online',last_seen=NOW()");
    foreach ($clients as &$c) {
        $c['hostname'] = detect_hostname($c['ip_address']);
        $c['device_type'] = 'Wi-Fi/LAN Client';
        $upsert->execute([$c['ip_address'],$c['mac_address'],$c['hostname'],$c['device_type'],$apId ?: null,$ssid ?: null,$location ?: null,$source]);
    }
    unset($c);

    $pdo->prepare("UPDATE wifi_clients SET status='Offline' WHERE source=? AND last_seen < DATE_SUB(NOW(), INTERVAL 5 MINUTE)")->execute([$source]);
    $pdo->prepare("INSERT INTO wifi_discovery_logs(source,client_count,started_at,finished_at,notes) VALUES(?,?,?,?,?)")
        ->execute([$source,count($clients),$started,date('Y-m-d H:i:s'),'ARP-based LAN discovery. AP association requires router/AP/controller integration.']);

    json_response(['ok'=>true,'clients'=>$clients,'count'=>count($clients),'message'=>'LAN clients discovered from the server ARP table.']);
}
json_response(['ok'=>false,'message'=>'Method not allowed.'],405);
