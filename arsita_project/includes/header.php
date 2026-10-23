<?php
// includes/header.php
ob_start(); // Mulai output buffering agar header() masih bisa dikirim
require_once __DIR__.'/auth.php';
require_once __DIR__.'/functions.php';
wajib_login();

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ARSITA - Sistem Informasi Persuratan dan Disposisi UINSSC">
    <title><?= isset($page_title) ? htmlspecialchars($page_title).' — ' : '' ?><?= APP_NAME ?></title>
    <!-- Bootstrap Icons (Lokal) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons.min.css">
    <!-- ARSITA Design System -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <!-- Toast & Modal Notifications -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
</head>
<body>

<?php require_once __DIR__.'/sidebar.php'; ?>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left">
            <div class="page-title"><?= htmlspecialchars($page_title ?? 'Dashboard') ?></div>
        </div>

        <div class="topbar-right">
            <div class="unit-tag">
                <i class="bi bi-building"></i>
                <span><?= htmlspecialchars($_SESSION['nama_unit'] ?? 'Unit') ?> (<?= htmlspecialchars($_SESSION['kode_unit'] ?? '-') ?>)</span>
            </div>

            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-secondary btn-sm"
               data-confirm="Anda akan keluar dari sistem ARSITA. Pastikan semua pekerjaan sudah tersimpan."
               data-confirm-title="Keluar dari Sistem?"
               data-confirm-type="logout"
               data-confirm-btn="Ya, Keluar">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </a>
        </div>
    </header>

    <main class="content-area">
        <?php if ($flash) { ?>
            <!-- Flash message via toast (JS akan memproses ini) -->
            <div id="arsita-flash-data" data-type="<?= htmlspecialchars($flash['tipe']) ?>" data-message="<?= htmlspecialchars($flash['pesan']) ?>" style="display:none;"></div>
        <?php } ?>

