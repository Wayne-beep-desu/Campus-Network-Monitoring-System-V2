<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();
$me = current_user();
$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title ?? 'Campus Network') ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<script defer src="assets/js/app.js"></script>
</head>
<body>
<div class="app">
<aside class="sidebar">
  <div class="brand"><div class="brand-mark">CN</div><div><b>CampusNet</b><small>Network Control</small></div></div>
  <nav>
    <a class="<?= $current==='dashboard.php'?'active':'' ?>" href="dashboard.php">⌂ <span>Dashboard</span></a>
    <a class="<?= $current==='network_map.php'?'active':'' ?>" href="network_map.php">⌁ <span>Network Map</span></a>
    <a class="<?= $current==='floorplan.php'?'active':'' ?>" href="floorplan.php">▦ <span>Floor Plan</span></a>
    <a class="<?= $current==='devices.php'?'active':'' ?>" href="devices.php">▣ <span>Devices</span></a>
    <a class="<?= $current==='wifi_clients.php'?'active':'' ?>" href="wifi_clients.php">◉ <span>Wi-Fi Clients</span></a>
    <a class="<?= $current==='tickets.php'?'active':'' ?>" href="tickets.php">☷ <span>Help Desk</span></a>
    <a class="<?= $current==='assets.php'?'active':'' ?>" href="assets.php">▤ <span>IT Assets</span></a>
    <a class="<?= $current==='maintenance.php'?'active':'' ?>" href="maintenance.php">🔧 <span>Maintenance</span></a>
    <a class="<?= $current==='bandwidth.php'?'active':'' ?>" href="bandwidth.php">◒ <span>Bandwidth</span></a>
    <a class="<?= $current==='analytics.php'?'active':'' ?>" href="analytics.php">◔ <span>Analytics</span></a>
    <a class="<?= $current==='speed_test.php'?'active':'' ?>" href="speed_test.php">⚡ <span>Speed Test</span></a>
    <a class="<?= $current==='alerts.php'?'active':'' ?>" href="alerts.php">⚠ <span>Alerts</span></a>
    <a class="<?= $current==='users.php'?'active':'' ?>" href="users.php">♙ <span>Users</span></a>
    <a class="<?= $current==='reports.php'?'active':'' ?>" href="reports.php">▤ <span>Reports</span></a>
    <a class="<?= $current==='activity.php'?'active':'' ?>" href="activity.php">◷ <span>Activity Logs</span></a>
    <a class="<?= $current==='security.php'?'active':'' ?>" href="security.php">◈ <span>Security</span></a>
    <a class="<?= $current==='permissions.php'?'active':'' ?>" href="permissions.php">🔐 <span>Permissions</span></a>
    <a class="<?= $current==='notifications.php'?'active':'' ?>" href="notifications.php">✉ <span>Notifications</span></a>
    <a class="<?= $current==='health.php'?'active':'' ?>" href="health.php">♥ <span>Health Checks</span></a>
    <a class="<?= $current==='diagnostics.php'?'active':'' ?>" href="diagnostics.php">⌁ <span>Diagnostics</span></a>
    <a class="<?= $current==='assistant.php'?'active':'' ?>" href="assistant.php">✦ <span>Network Assistant</span></a>
    <a class="<?= $current==='backups.php'?'active':'' ?>" href="backup_restore.php">⤓ <span>Backup & Restore</span></a>
    <a class="<?= $current==='settings.php'?'active':'' ?>" href="settings.php">⚙ <span>Settings</span></a>
  </nav>
  <div class="sidebar-bottom"><button class="theme-toggle" onclick="toggleTheme()">☾ Dark mode</button>
    <div class="user-mini"><div class="avatar"><?= strtoupper(substr($me['name'],0,1)) ?></div><div><b><?= e($me['name']) ?></b><small><?= e($me['role']) ?></small></div></div>
    <a href="logout.php" class="logout">↪ Sign out</a>
  </div>
</aside>
<main class="main">
<header class="topbar">
  <div><span class="mobile-menu" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</span><span class="page-kicker">CAMPUS NETWORK</span><h2><?= e($page_title ?? 'Dashboard') ?></h2></div>
  <div class="top-actions"><button class="notification-btn" onclick="location.href='alerts.php'">🔔 <span id="notifCount">0</span></button><span class="live-dot"></span><span>System Live</span><span class="clock" id="clock"></span></div>
</header>
<section class="content">
