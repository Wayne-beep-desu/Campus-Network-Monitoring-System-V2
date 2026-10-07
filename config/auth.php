<?php
declare(strict_types=1);
session_start();

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /campus-network/login.php');
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
