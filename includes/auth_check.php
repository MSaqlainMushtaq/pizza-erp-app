<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header("Location: /pizza-erp-app/views/auth/login.php");
    exit();
}
?>
