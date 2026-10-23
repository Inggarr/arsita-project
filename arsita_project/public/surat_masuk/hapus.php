<?php

// public/surat_masuk/hapus.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_role(['admin_rektorat', 'staff_tu']);

$id_surat = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;
$role = $_SESSION['role'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM surat_masuk WHERE id_surat_masuk = ?');
$stmt->execute([$id_surat]);
$surat = $stmt->fetch();

if (! $surat) {
    set_flash('danger', 'Surat masuk tidak ditemukan!');
    header('Location: '.BASE_URL.'/surat_masuk/index.php');
    exit;
}

// Cek hak akses unit
if ($role !== 'admin_rektorat' && $surat['id_unit'] != $id_unit) {
    set_flash('danger', 'Akses ditolak: Anda tidak memiliki izin menghapus surat unit lain.');
    header('Location: '.BASE_URL.'/surat_masuk/index.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Ambil semua id_disposisi terkait
    $stmtDisp = $pdo->prepare('SELECT id_disposisi FROM disposisi WHERE id_surat_masuk = ?');
    $stmtDisp->execute([$id_surat]);
    $dispIds = $stmtDisp->fetchAll(PDO::FETCH_COLUMN);

    if (! empty($dispIds)) {
        // Ambil id_disposisi_tujuan terkait
        $inDisp = implode(',', array_fill(0, count($dispIds), '?'));
        $stmtDt = $pdo->prepare("SELECT id_disposisi_tujuan FROM disposisi_tujuan WHERE id_disposisi IN ($inDisp)");
        $stmtDt->execute($dispIds);
        $dtIds = $stmtDt->fetchAll(PDO::FETCH_COLUMN);

        if (! empty($dtIds)) {
            // Hapus tindak lanjut & file lampirannya
            $inDt = implode(',', array_fill(0, count($dtIds), '?'));
            $stmtTl = $pdo->prepare("SELECT lampiran FROM tindak_lanjut WHERE id_disposisi_tujuan IN ($inDt)");
            $stmtTl->execute($dtIds);
            $lampiranList = $stmtTl->fetchAll(PDO::FETCH_COLUMN);

            foreach ($lampiranList as $lamp) {
                if ($lamp) {
                    $lampPath = UPLOAD_PATH.'tindak_lanjut/'.$lamp;
                    if (file_exists($lampPath)) {
                        @unlink($lampPath);
                    }
                }
            }

            $stmtDelTl = $pdo->prepare("DELETE FROM tindak_lanjut WHERE id_disposisi_tujuan IN ($inDt)");
            $stmtDelTl->execute($dtIds);

            // Hapus disposisi_tujuan
            $stmtDelDt = $pdo->prepare("DELETE FROM disposisi_tujuan WHERE id_disposisi IN ($inDisp)");
            $stmtDelDt->execute($dispIds);
        }

        // Hapus disposisi
        $stmtDelDisp = $pdo->prepare('DELETE FROM disposisi WHERE id_surat_masuk = ?');
        $stmtDelDisp->execute([$id_surat]);
    }

    // Hapus arsip jika ada
    $cekArsip = $pdo->query("SHOW TABLES LIKE 'arsip'")->fetch();
    if ($cekArsip) {
        $stmtDelArsip = $pdo->prepare("DELETE FROM arsip WHERE tipe = 'masuk' AND referensi_id = ?");
        $stmtDelArsip->execute([$id_surat]);
    }

    // Hapus file scan surat fisik jika ada
    if ($surat['file_surat']) {
        $filePath = UPLOAD_PATH.'surat_masuk/'.$surat['file_surat'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    // Hapus surat masuk
    $stmtDel = $pdo->prepare('DELETE FROM surat_masuk WHERE id_surat_masuk = ?');
    $stmtDel->execute([$id_surat]);

    $pdo->commit();
    set_flash('success', 'Surat masuk beserta riwayat terkait berhasil dihapus.');
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('danger', 'Gagal menghapus surat masuk: '.$e->getMessage());
}

header('Location: '.BASE_URL.'/surat_masuk/index.php');
exit;
