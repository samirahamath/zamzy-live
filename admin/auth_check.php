<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminAuth() {
    if (!isset($_SESSION['zamzy_admin_logged']) || $_SESSION['zamzy_admin_logged'] !== true) {
        $loginUrl = (strpos($_SERVER['REQUEST_URI'] ?? '', '/zz/') !== false) ? '/zz/admin/login.php' : '/admin/login.php';
        header('Location: ' . $loginUrl);
        exit;
    }
}
?>
