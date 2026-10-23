<?php

// public/surat_keluar/hapus.php - Hapus draft/surat keluar
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_role(['admin_rektorat', 'staff_tu']);

$id_sk = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;
$role = $_SESSION['role'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM surat_keluar WHERE id_surat_keluar = ?');
$stmt->execute([$id_sk]);
$surat = $stmt->fetch();

if (! $surat) {
    set_flash('danger', 'Surat keluar tidak ditemukan!');
    header('Location: '.BASE_URL.'/surat_keluar/index.php');
    exit;
}

if ($role !== 'admin_rektorat' && $surat['id_unit'] != $id_unit) {
    set_flash('danger', 'Akses ditolak: Anda tidak memiliki izin untuk unit ini.');
    header('Location: '.BASE_URL.'/surat_keluar/index.php');
    exit;
}

// Surat yang sudah disetujui / bernomor resmi sebaiknya tidak dihapus sembarangan
if ($surat['status_surat'] === 'disetujui' && $role !== 'admin_rektorat') {
    set_flash('danger', 'Surat keluar yang telah disetujui dan bernomor resmi tidak dapat dihapus.');
    header('Location: '.BASE_URL.'/surat_keluar/detail.php?id='.$id_sk);
    exit;
}

try {
    $pdo->beginTransaction();

    // Hapus nomor surat jika ada
    $pdo->prepare('DELETE FROM nomor_surat WHERE id_surat_keluar = ?')->execute([$id_sk]);

    // Hapus riwayat acc
    $pdo->prepare('DELETE FROM acc_surat_keluar WHERE id_surat_keluar = ?')->execute([$id_sk]);

    // Hapus arsip jika ada
    $cekArsip = $pdo->query("SHOW TABLES LIKE 'arsip'")->fetch();
    if ($cekArsip) {
        $pdo->prepare("DELETE FROM arsip WHERE tipe = 'keluar' AND referensi_id = ?")->execute([$id_sk]);
    }

    // Hapus file draft/final jika ada
    if (! empty($surat['file_draft'])) {
        $path = UPLOAD_PATH.'surat_keluar/'.$surat['file_draft'];
        if (file_exists($path)) {
            @unlink($path);
        }
    }
    if (! empty($surat['file_final'])) {
        $path = UPLOAD_PATH.'surat_keluar/'.$surat['file_final'];
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    // Hapus surat keluar
    $pdo->prepare('DELETE FROM surat_keluar WHERE id_surat_keluar = ?')->execute([$id_sk]);

    $pdo->commit();
    set_flash('success', 'Surat keluar berhasil dihapus.');
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('danger', 'Gagal menghapus surat keluar: '.$e->getMessage());
}

header('Location: '.BASE_URL.'/surat_keluar/index.php');
exit;
