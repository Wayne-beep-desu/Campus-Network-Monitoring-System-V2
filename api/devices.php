<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = $pdo->query("SELECT * FROM devices ORDER BY id DESC")->fetchAll();
    json_response(['ok' => true, 'devices' => $rows]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $id = (int)($data['id'] ?? 0);
    $name = trim((string)($data['device_name'] ?? ''));
    $ip = trim((string)($data['ip_address'] ?? ''));
    $mac = trim((string)($data['mac_address'] ?? ''));
    $type = trim((string)($data['device_type'] ?? 'Computer'));
    $location = trim((string)($data['location'] ?? ''));
    $limit = (float)($data['bandwidth_limit'] ?? 10);

    // The form may omit monitor_mode, so safely default to simulated.
    $requestedMode = $data['monitor_mode'] ?? 'simulated';
    $mode = in_array($requestedMode, ['simulated', 'snmp', 'api'], true)
        ? $requestedMode
        : 'simulated';

    $community = trim((string)($data['snmp_community'] ?? ''));
    $snmpPort = (int)($data['snmp_port'] ?? 161);

    if ($name === '' || $ip === '') {
        json_response([
            'ok' => false,
            'message' => 'Device name and IP address are required.'
        ], 422);
    }

    try {
        if ($id) {
            $st = $pdo->prepare(
                "UPDATE devices
                 SET device_name = ?, ip_address = ?, mac_address = ?, device_type = ?,
                     location = ?, bandwidth_limit = ?, monitor_mode = ?,
                     snmp_community = ?, snmp_port = ?
                 WHERE id = ?"
            );
            $st->execute([
                $name, $ip, $mac, $type, $location, $limit,
                $mode, $community, $snmpPort, $id
            ]);
            $action = 'Updated device';
        } else {
            $st = $pdo->prepare(
                "INSERT INTO devices
                 (device_name, ip_address, mac_address, device_type, location,
                  bandwidth_limit, status, monitor_mode, snmp_community, snmp_port)
                 VALUES (?, ?, ?, ?, ?, ?, 'Online', ?, ?, ?)"
            );
            $st->execute([
                $name, $ip, $mac, $type, $location, $limit,
                $mode, $community, $snmpPort
            ]);
            $action = 'Added device';
        }

        $log = $pdo->prepare(
            "INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)"
        );
        $log->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $name . ' (' . $ip . ')'
        ]);

        json_response(['ok' => true]);
    } catch (PDOException $e) {
        if ((string)$e->getCode() === '23000') {
            json_response([
                'ok' => false,
                'message' => 'This IP address is already registered.'
            ], 409);
        }

        error_log('Device API database error: ' . $e->getMessage());
        json_response([
            'ok' => false,
            'message' => 'Unable to save the device. Check the server logs.'
        ], 500);
    }
}

if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $data);
    $id = (int)($data['id'] ?? ($_GET['id'] ?? 0));

    $st = $pdo->prepare("SELECT device_name, ip_address FROM devices WHERE id = ?");
    $st->execute([$id]);
    $device = $st->fetch();

    if (!$device) {
        json_response(['ok' => false, 'message' => 'Device not found.'], 404);
    }

    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM devices WHERE id = ?")->execute([$id]);
        $log = $pdo->prepare(
            "INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)"
        );
        $log->execute([
            $_SESSION['user_id'] ?? null,
            'Deleted device',
            $device['device_name'] . ' (' . $device['ip_address'] . ')'
        ]);
        $pdo->commit();
        json_response(['ok' => true]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Device API delete error: ' . $e->getMessage());
        json_response([
            'ok' => false,
            'message' => 'Unable to delete the device. Check the server logs.'
        ], 500);
    }
}

json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
