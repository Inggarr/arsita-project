<?php
// public/master/index.php - Dashboard Master Data
$page_title = 'Kelola Master Data';
require_once __DIR__.'/../../includes/header.php';

wajib_role(['admin_rektorat']);

$countUnits = $pdo->query('SELECT COUNT(*) FROM units')->fetchColumn();
$countJenis = $pdo->query('SELECT COUNT(*) FROM jenis_surat')->fetchColumn();
$countKategori = $pdo->query('SELECT COUNT(*) FROM kategori_surat')->fetchColumn();
$countFormat = $pdo->query('SELECT COUNT(*) FROM format_nomor')->fetchColumn();
?>

<div class="card mb-4">
    <div class="card-header">
        <div>
            <div class="card-title">Pusat Data Master Sistem ARSITA</div>
            <div class="card-subtitle">Pengaturan referensi unit kerja, jenis surat dinas, klasifikasi kategori, dan format penomoran otomatis</div>
        </div>
    </div>
</div>

<div class="stats-grid">
    <a href="<?= BASE_URL ?>/master/unit.php" class="stat-card" style="text-decoration: none; color: inherit;">
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
            <i class="bi bi-buildings"></i>
        </div>
        <div>
            <div class="stat-value"><?= $countUnits ?></div>
            <div class="stat-label">Unit Kerja (Rektorat / Fak / Prodi)</div>
        </div>
    </a>

    <a href="<?= BASE_URL ?>/master/jenis.php" class="stat-card" style="text-decoration: none; color: inherit;">
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
            <i class="bi bi-file-earmark-text"></i>
        </div>
        <div>
            <div class="stat-value"><?= $countJenis ?></div>
            <div class="stat-label">Jenis Dokumen Surat</div>
        </div>
    </a>

    <a href="<?= BASE_URL ?>/master/kategori.php" class="stat-card" style="text-decoration: none; color: inherit;">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
            <i class="bi bi-tags"></i>
        </div>
        <div>
            <div class="stat-value"><?= $countKategori ?></div>
            <div class="stat-label">Kategori Urusan Surat</div>
        </div>
    </a>

    <a href="<?= BASE_URL ?>/master/format_nomor.php" class="stat-card" style="text-decoration: none; color: inherit;">
        <div class="stat-icon" style="background: #f3e8ff; color: #7e22ce;">
            <i class="bi bi-123"></i>
        </div>
        <div>
            <div class="stat-value"><?= $countFormat ?></div>
            <div class="stat-label">Format Penomoran Otomatis</div>
        </div>
    </a>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
