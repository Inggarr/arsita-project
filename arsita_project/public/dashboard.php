<?php
// public/dashboard.php
$page_title = 'Dashboard';
require_once __DIR__.'/../includes/header.php';

$id_user = $_SESSION['id_user'];
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Query Statistik
// 1. Total Surat Masuk (sesuai unit atau semua jika admin_rektorat)
if ($role === 'admin_rektorat') {
    $stmtSM = $pdo->query('SELECT COUNT(*) FROM surat_masuk');
    $total_sm = $stmtSM->fetchColumn();

    $stmtSK = $pdo->query('SELECT COUNT(*) FROM surat_keluar');
    $total_sk = $stmtSK->fetchColumn();
} else {
    $stmtSM = $pdo->prepare('SELECT COUNT(*) FROM surat_masuk WHERE id_unit = ?');
    $stmtSM->execute([$id_unit]);
    $total_sm = $stmtSM->fetchColumn();

    $stmtSK = $pdo->prepare('SELECT COUNT(*) FROM surat_keluar WHERE id_unit = ?');
    $stmtSK->execute([$id_unit]);
    $total_sk = $stmtSK->fetchColumn();
}

// 2. Disposisi Tugas yang ditujukan ke user ini atau unit ini dan belum selesai
$stmtDisp = $pdo->prepare("
    SELECT COUNT(*) 
    FROM disposisi_tujuan dt
    WHERE (dt.ke_user_id = ? OR (dt.tujuan_tipe = 'unit' AND dt.ke_unit_id = ?))
      AND dt.status != 'selesai'
");
$stmtDisp->execute([$id_user, $id_unit]);
$total_disp_pending = $stmtDisp->fetchColumn();

// 3. Surat Keluar yang menunggu ACC (untuk Pimpinan)
$total_butuh_acc = 0;
if ($role === 'pimpinan') {
    $stmtAcc = $pdo->prepare("
        SELECT COUNT(*)
        FROM surat_keluar sk
        JOIN acc_surat_keluar ask ON sk.id_surat_keluar = ask.id_surat_keluar
        WHERE ask.disetujui_oleh = ? AND ask.status = 'menunggu'
    ");
    $stmtAcc->execute([$id_user]);
    $total_butuh_acc = $stmtAcc->fetchColumn();
}

// Ambil Surat Masuk Terbaru
if ($role === 'admin_rektorat') {
    $recent_sm = $pdo->query('
        SELECT sm.*, u.nama_unit, ks.nama_kategori
        FROM surat_masuk sm
        LEFT JOIN units u ON sm.id_unit = u.id_unit
        LEFT JOIN kategori_surat ks ON sm.kategori_id = ks.id_kategori
        ORDER BY sm.created_at DESC LIMIT 5
    ')->fetchAll();
} else {
    $stmtRecentSM = $pdo->prepare('
        SELECT sm.*, u.nama_unit, ks.nama_kategori
        FROM surat_masuk sm
        LEFT JOIN units u ON sm.id_unit = u.id_unit
        LEFT JOIN kategori_surat ks ON sm.kategori_id = ks.id_kategori
        WHERE sm.id_unit = ?
        ORDER BY sm.created_at DESC LIMIT 5
    ');
    $stmtRecentSM->execute([$id_unit]);
    $recent_sm = $stmtRecentSM->fetchAll();
}

// Ambil Disposisi Aktif yang perlu ditindaklanjuti oleh user
$stmtMyDisp = $pdo->prepare("
    SELECT dt.*, d.instruksi, d.sifat, d.tenggat_waktu, sm.perihal, sm.no_surat_asal, sm.id_surat_masuk, u.nama AS dari_nama
    FROM disposisi_tujuan dt
    JOIN disposisi d ON dt.id_disposisi = d.id_disposisi
    JOIN surat_masuk sm ON d.id_surat_masuk = sm.id_surat_masuk
    LEFT JOIN users u ON d.dari_user_id = u.id_user
    WHERE (dt.ke_user_id = ? OR (dt.tujuan_tipe = 'unit' AND dt.ke_unit_id = ?))
    ORDER BY dt.id_disposisi_tujuan DESC LIMIT 5
");
$stmtMyDisp->execute([$id_user, $id_unit]);
$my_disposisi = $stmtMyDisp->fetchAll();
?>

<!-- Kartu Ringkasan Statistik -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-inbox-fill"></i></div>
        <div>
            <div class="stat-value"><?= number_format($total_sm) ?></div>
            <div class="stat-label">Surat Masuk</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-send-fill"></i></div>
        <div>
            <div class="stat-value"><?= number_format($total_sk) ?></div>
            <div class="stat-label">Surat Keluar</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="stat-value"><?= number_format($total_disp_pending) ?></div>
            <div class="stat-label">Disposisi Pending</div>
        </div>
    </div>

    <?php if ($role === 'pimpinan') { ?>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-pencil-square"></i></div>
        <div>
            <div class="stat-value"><?= number_format($total_butuh_acc) ?></div>
            <div class="stat-label">Menunggu ACC Anda</div>
        </div>
    </div>
    <?php } else { ?>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-buildings-fill"></i></div>
        <div>
            <div class="stat-value"><?= htmlspecialchars($_SESSION['level_unit'] ?? 'Unit') ?></div>
            <div class="stat-label">Level Wilayah Kerja</div>
        </div>
    </div>
    <?php } ?>
</div>

<!-- Aksi Cepat / Shortcut -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Aksi Cepat</div>
    </div>
    <div class="quick-actions">
        <?php if (in_array($role, ['admin_rektorat', 'staff_tu'])) { ?>
            <a href="<?= BASE_URL ?>/surat_masuk/tambah.php" class="btn btn-primary">
                <i class="bi bi-file-earmark-plus"></i> Catat Surat Masuk
            </a>
            <a href="<?= BASE_URL ?>/surat_keluar/tambah.php" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Buat Surat Keluar
            </a>
        <?php } ?>

        <?php if ($role === 'pimpinan') { ?>
            <a href="<?= BASE_URL ?>/persetujuan/index.php" class="btn btn-success">
                <i class="bi bi-patch-check"></i> Buka Persetujuan (ACC)
            </a>
            <a href="<?= BASE_URL ?>/surat_masuk/index.php" class="btn btn-primary">
                <i class="bi bi-search"></i> Tinjau Surat Masuk & Disposisi
            </a>
        <?php } ?>



        <a href="<?= BASE_URL ?>/arsip/index.php" class="btn btn-secondary">
            <i class="bi bi-archive"></i> Cari Arsip Surat
        </a>
    </div>
</div>

<div class="grid-2">
    <!-- Surat Masuk Terbaru -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Surat Masuk Terbaru</div>
            <a href="<?= BASE_URL ?>/surat_masuk/index.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tgl Terima</th>
                        <th>No. Asal & Pengirim</th>
                        <th>Perihal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_sm)) { ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada surat masuk.</td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($recent_sm as $sm) { ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($sm['tanggal_terima'])) ?></td>
                                <td>
                                    <b><?= htmlspecialchars($sm['no_surat_asal'] ?: '-') ?></b><br>
                                    <small class="text-muted"><?= htmlspecialchars($sm['asal_surat'] ?: '-') ?></small>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/surat_masuk/detail.php?id=<?= $sm['id_surat_masuk'] ?>" class="text-brand font-500">
                                        <?= htmlspecialchars($sm['perihal']) ?>
                                    </a>
                                </td>
                                <td><?= badge_status($sm['status_arsip']) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Disposisi Tugas Anda -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Tugas Disposisi Anda</div>
            <a href="<?= BASE_URL ?>/disposisi/masuk.php" class="btn btn-secondary btn-sm">Buka Detail</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Perihal Surat</th>
                        <th>Instruksi</th>
                        <th>Sifat</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($my_disposisi)) { ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding:24px">Tidak ada disposisi aktif saat ini.</td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($my_disposisi as $dt) { ?>
                            <tr>
                                <td>
                                    <b><?= htmlspecialchars($dt['perihal']) ?></b><br>
                                    <small class="text-muted">Dari: <?= htmlspecialchars($dt['dari_nama']) ?></small>
                                </td>
                                <td><?= htmlspecialchars(mb_strimwidth($dt['instruksi'], 0, 45, '...')) ?></td>
                                <td><?= badge_status($dt['sifat']) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/disposisi/tindak_lanjut.php?id=<?= $dt['id_disposisi_tujuan'] ?>">
                                        <?= badge_status($dt['status']) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>