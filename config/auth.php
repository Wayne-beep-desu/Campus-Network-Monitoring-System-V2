<?php
declare(strict_types=1);
session_start();

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /Campus_Network_Monitoring_System/login.php');
        exit;
    }
}

function current_user(): array {
    return [
        'id' => $_SESSION['user_id'] ?? 0,
        'name' => $_SESSION['user_name'] ?? 'Guest',
        'role' => $_SESSION['user_role'] ?? 'Viewer',
    ];
}
?>
