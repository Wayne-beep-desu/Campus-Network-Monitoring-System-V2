<?php require_once __DIR__.'/config/security.php'; if(!empty($_SESSION['user_id'])) audit('Logout','User signed out'); session_unset();session_destroy();header('Location: login.php');exit;
