<?php

// public/disposisi/update_status.php - Update status disposisi
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_login();

$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_dt = (int) ($_POST['id_disposisi_tujuan'] ?? 0);
    $status = $_POST['status'] ?? 'diproses';

    if (! in_array($status, ['belum_diproses', 'diproses', 'selesai'])) {
        $status = 'diproses';
    }

    $stmt = $pdo->prepare('SELECT * FROM disposisi_tujuan WHERE id_disposisi_tujuan = ?');
    $stmt->execute([$id_dt]);
    $dt = $stmt->fetch();

    if ($dt) {
        // Cek wewenang
        if ($dt['ke_user_id'] == $id_user || ($dt['tujuan_tipe'] === 'unit' && $dt['ke_unit_id'] == $id_unit) || $_SESSION['role'] === 'admin_rektorat') {
            $pdo->prepare('UPDATE disposisi_tujuan SET status = ? WHERE id_disposisi_tujuan = ?')->execute([$status, $id_dt]);
            set_flash('success', 'Status disposisi berhasil diperbarui.');
        } else {
            set_flash('danger', 'Akses ditolak: Anda bukan penerima disposisi ini.');
        }
    } else {
        set_flash('danger', 'Data disposisi tujuan tidak ditemukan.');
    }
}

$redirect = $_SERVER['HTTP_REFERER'] ?? (BASE_URL.'/disposisi/masuk.php');
header('Location: '.$redirect);
exit;
