<?php

// public/pengguna/hapus.php - Hapus Pengguna
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_role(['admin_rektorat']);

$id_target = (int) ($_GET['id'] ?? 0);
$id_self = $_SESSION['id_user'] ?? 0;

if ($id_target === $id_self) {
    set_flash('danger', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login!');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id_user = ?');
$stmt->execute([$id_target]);
$user = $stmt->fetch();

if (! $user) {
    set_flash('danger', 'Pengguna tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    // Cek apakah ada riwayat relasi
    $stmtDel = $pdo->prepare('DELETE FROM users WHERE id_user = ?');
    $stmtDel->execute([$id_target]);
    set_flash('success', "Pengguna <b>{$user['username']}</b> berhasil dihapus.");
} catch (PDOException $e) {
    // Jika ada foreign key constraint, ubah status jadi nonaktif
    $stmtNonaktif = $pdo->prepare("UPDATE users SET status = 'nonaktif' WHERE id_user = ?");
    $stmtNonaktif->execute([$id_target]);
    set_flash('warning', "Pengguna <b>{$user['username']}</b> memiliki riwayat data aktif dan telah dinonaktifkan (status: nonaktif).");
}

header('Location: index.php');
exit;
