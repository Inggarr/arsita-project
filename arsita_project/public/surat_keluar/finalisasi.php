<?php

// public/surat_keluar/finalisasi.php - Finalisasi / Kirim / Arsipkan surat keluar yang telah di-ACC
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_login();

$id_sk = (int) ($_GET['id'] ?? 0);
$aksi = $_GET['aksi'] ?? 'terkirim'; // 'terkirim' atau 'diarsipkan'
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

if (! in_array($surat['status_surat'], ['disetujui', 'terkirim'])) {
    set_flash('danger', 'Surat belum disetujui (ACC lengkap) sehingga belum dapat difinalisasi.');
    header('Location: '.BASE_URL.'/surat_keluar/detail.php?id='.$id_sk);
    exit;
}

try {
    $newStatus = ($aksi === 'diarsipkan') ? 'diarsipkan' : 'terkirim';
    $stmtUp = $pdo->prepare('UPDATE surat_keluar SET status_surat = ? WHERE id_surat_keluar = ?');
    $stmtUp->execute([$newStatus, $id_sk]);

    // Jika diarsipkan, catat juga di tabel arsip jika tersedia
    if ($newStatus === 'diarsipkan') {
        $cekArsip = $pdo->query("SHOW TABLES LIKE 'arsip'")->fetch();
        if ($cekArsip) {
            $stmtArsip = $pdo->prepare("INSERT INTO arsip (tipe, referensi_id, tanggal_arsip) VALUES ('keluar', ?, CURDATE())");
            $stmtArsip->execute([$id_sk]);
        }
        set_flash('success', 'Surat keluar resmi telah berhasil diarsipkan.');
    } else {
        set_flash('success', 'Status surat keluar diubah menjadi Terkirim.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Gagal memfinalisasi surat: '.$e->getMessage());
}

header('Location: '.BASE_URL.'/surat_keluar/detail.php?id='.$id_sk);
exit;
