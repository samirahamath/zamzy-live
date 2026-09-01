<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminAuth() {
    if (!isset($_SESSION['zamzy_admin_logged']) || $_SESSION['zamzy_admin_logged'] !== true) {
        header('Location: login.php');
        exit;
    }
}
?>
