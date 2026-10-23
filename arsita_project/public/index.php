<?php

ob_start(); // Prevent headers already sent
require_once __DIR__.'/../includes/auth.php';

if (isset($_SESSION['id_user'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
