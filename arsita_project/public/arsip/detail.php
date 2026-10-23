<?php

// public/arsip/detail.php - Detail arsip terpadu
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_login();

$tipe = $_GET['tipe'] ?? 'masuk';
$id = (int) ($_GET['id'] ?? 0);

if ($tipe === 'keluar') {
    header('Location: '.BASE_URL.'/surat_keluar/detail.php?id='.$id);
} else {
    header('Location: '.BASE_URL.'/surat_masuk/detail.php?id='.$id);
}
exit;
