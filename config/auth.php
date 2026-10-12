<?php
declare(strict_types=1);
session_start();

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/'))
            ;
        $base = preg_replace('~/(?:api|config)/[^/]+$~', '', $script);
        $base = preg_replace('~/[^/]+\\.php$~', '', (string)$base);
        $base = rtrim((string)$base, '/');
        header('Location: ' . $base . '/login.php');
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
