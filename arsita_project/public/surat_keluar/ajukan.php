<?php

// public/surat_keluar/ajukan.php - Pengajuan draft surat keluar untuk ACC atasan
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_login();

$id_sk = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;
$role = $_SESSION['role'] ?? '';

$stmt = $pdo->prepare('
    SELECT sk.*, u.level as level_unit
    FROM surat_keluar sk
    JOIN units u ON sk.id_unit = u.id_unit
    WHERE sk.id_surat_keluar = ?
');
$stmt->execute([$id_sk]);
$surat = $stmt->fetch();

if (! $surat) {
    set_flash('danger', 'Surat keluar tidak ditemukan!');
    header('Location: '.BASE_URL.'/surat_keluar/index.php');
    exit;
}

if ($role !== 'admin_rektorat' && $surat['id_unit'] != $id_unit) {
    set_flash('danger', 'Akses ditolak: Anda tidak memiliki akses ke surat ini.');
    header('Location: '.BASE_URL.'/surat_keluar/index.php');
    exit;
}

if (! in_array($surat['status_surat'], ['draft', 'revisi'])) {
    set_flash('danger', 'Surat tidak dalam status Draft atau Revisi.');
    header('Location: '.BASE_URL.'/surat_keluar/detail.php?id='.$id_sk);
    exit;
}

try {
    $target_unit_id = $surat['id_unit'];
    $levelUnit = $surat['level_unit'];

    if ($levelUnit === 'rektorat') {
        $stmtKasub = $pdo->prepare("
            SELECT id_user FROM users 
            WHERE id_unit = ? AND (username = 'kasubbag' OR id_role = (SELECT id_role FROM roles WHERE nama_role = 'pimpinan'))
            ORDER BY (username = 'kasubbag') DESC LIMIT 1
        ");
        $stmtKasub->execute([$target_unit_id]);
        $pejabat_acc = $stmtKasub->fetchColumn();
    } else {
        $stmtPimp = $pdo->prepare("
            SELECT id_user FROM users 
            WHERE id_unit = ? AND id_role = (SELECT id_role FROM roles WHERE nama_role = 'pimpinan')
            LIMIT 1
        ");
        $stmtPimp->execute([$target_unit_id]);
        $pejabat_acc = $stmtPimp->fetchColumn();
    }

    $pdo->beginTransaction();

    $pdo->prepare("UPDATE surat_keluar SET status_surat = 'menunggu_acc' WHERE id_surat_keluar = ?")->execute([$id_sk]);

    // Hapus draft ACC sebelumnya jika ada revisi
    $pdo->prepare("DELETE FROM acc_surat_keluar WHERE id_surat_keluar = ? AND status = 'menunggu'")->execute([$id_sk]);

    $pdo->prepare("INSERT INTO acc_surat_keluar (id_surat_keluar, tahap_ke, disetujui_oleh, status) VALUES (?, 1, ?, 'menunggu')")
        ->execute([$id_sk, $pejabat_acc]);

    $pdo->commit();
    set_flash('success', 'Draft surat telah diajukan ke atasan untuk verifikasi & persetujuan (ACC).');
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('danger', 'Gagal mengajukan persetujuan: '.$e->getMessage());
}

header('Location: '.BASE_URL.'/surat_keluar/detail.php?id='.$id_sk);
exit;
