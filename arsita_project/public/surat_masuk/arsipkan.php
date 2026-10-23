<?php

ob_start(); // Prevent headers already sent
// public/surat_masuk/arsipkan.php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';
wajib_login();

$id_surat = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'];
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

$stmt = $pdo->prepare('SELECT * FROM surat_masuk WHERE id_surat_masuk = ?');
$stmt->execute([$id_surat]);
$surat = $stmt->fetch();

if (! $surat) {
    set_flash('danger', 'Surat masuk tidak ditemukan!');
    header('Location: '.BASE_URL.'/surat_masuk/index.php');
    exit;
}

if ($role !== 'admin_rektorat' && $surat['id_unit'] != $id_unit) {
    set_flash('danger', 'Akses ditolak!');
    header('Location: '.BASE_URL.'/surat_masuk/index.php');
    exit;
}

// Update status arsip
$stmtUp = $pdo->prepare("UPDATE surat_masuk SET status_arsip = 'diarsipkan' WHERE id_surat_masuk = ?");
$stmtUp->execute([$id_surat]);

// Cek apakah tabel arsip ada (sesuai Readme.md)
$cekArsip = $pdo->query("SHOW TABLES LIKE 'arsip'")->fetch();
if ($cekArsip) {
    $stmtArsip = $pdo->prepare("INSERT INTO arsip (tipe, referensi_id, tanggal_arsip) VALUES ('masuk', ?, CURDATE())");
    $stmtArsip->execute([$id_surat]);
}

set_flash('success', 'Surat masuk berhasil diarsipkan!');
header('Location: '.BASE_URL.'/surat_masuk/detail.php?id='.$id_surat);
exit;
