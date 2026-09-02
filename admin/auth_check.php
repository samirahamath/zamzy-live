<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

function checkAdminAuth() {
    if (!isset($_SESSION['zamzy_admin_logged']) || $_SESSION['zamzy_admin_logged'] !== true) {
        $loginUrl = defined('ADMIN_URL') ? ADMIN_URL . '/login.php' : 'login.php';
        header('Location: ' . $loginUrl);
        exit;
    }
}
?>
