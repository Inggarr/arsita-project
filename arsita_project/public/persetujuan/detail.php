<?php

// public/persetujuan/detail.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_role(['pimpinan', 'admin_rektorat']);

$id_acc = (int) ($_GET['id'] ?? 0);
$id_surat = (int) ($_GET['id_surat'] ?? 0);

if ($id_acc > 0) {
    header('Location: '.BASE_URL.'/persetujuan/proses.php?id='.$id_acc);
    exit;
} elseif ($id_surat > 0) {
    header('Location: '.BASE_URL.'/surat_keluar/detail.php?id='.$id_surat);
    exit;
}

header('Location: '.BASE_URL.'/persetujuan/index.php');
exit;
