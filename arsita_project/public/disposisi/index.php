<?php

// public/disposisi/index.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_login();

$role = $_SESSION['role'] ?? '';

// Pimpinan diarahkan ke riwayat disposisi yang diterbitkan, role lain ke disposisi masuk
if (in_array($role, ['pimpinan'])) {
    header('Location: '.BASE_URL.'/disposisi/riwayat.php');
} else {
    header('Location: '.BASE_URL.'/disposisi/masuk.php');
}
exit;
