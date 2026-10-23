<?php
// public/surat_keluar/detail.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';
wajib_login();

$id_sk = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'];
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Query data surat keluar
$stmt = $pdo->prepare('
    SELECT sk.*, js.nama_jenis, js.kode_jenis, ks.nama_kategori, u.nama_unit, u.kode_unit, u.level as level_unit,
           us.nama as nama_pembuat, ns.nomor_surat as no_resmi, ns.tanggal_nomor
    FROM surat_keluar sk
    LEFT JOIN jenis_surat js ON sk.jenis_surat_id = js.id_jenis_surat
    LEFT JOIN kategori_surat ks ON sk.kategori_id = ks.id_kategori
    LEFT JOIN units u ON sk.id_unit = u.id_unit
    LEFT JOIN users us ON sk.dibuat_oleh = us.id_user
    LEFT JOIN nomor_surat ns ON sk.id_surat_keluar = ns.id_surat_keluar
    WHERE sk.id_surat_keluar = ?
');
$stmt->execute([$id_sk]);
$surat = $stmt->fetch();

if (! $surat) {
    set_flash('danger', 'Surat keluar tidak ditemukan!');
    header('Location: '.BASE_URL.'/surat_keluar/index.php');
    exit;
}

// Cek hak akses
if ($role !== 'admin_rektorat' && $surat['id_unit'] != $id_unit) {
    set_flash('danger', 'Akses ditolak!');
    header('Location: '.BASE_URL.'/surat_keluar/index.php');
    exit;
}

// Alur ACC
$stmtAcc = $pdo->prepare('
    SELECT ask.*, u.nama as nama_pejabat, r.nama_role
    FROM acc_surat_keluar ask
    LEFT JOIN users u ON ask.disetujui_oleh = u.id_user
    LEFT JOIN roles r ON u.id_role = r.id_role
    WHERE ask.id_surat_keluar = ?
    ORDER BY ask.tahap_ke ASC
');
$stmtAcc->execute([$id_sk]);
$acc_history = $stmtAcc->fetchAll();

// Jika user ingin mengajukan dari status draft
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_ajukan']) && $surat['status_surat'] === 'draft') {
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

    $pdo->prepare("UPDATE surat_keluar SET status_surat = 'menunggu_acc' WHERE id_surat_keluar = ?")->execute([$id_sk]);
    $pdo->prepare("INSERT INTO acc_surat_keluar (id_surat_keluar, tahap_ke, disetujui_oleh, status) VALUES (?, 1, ?, 'menunggu')")
        ->execute([$id_sk, $pejabat_acc]);

    set_flash('success', 'Draft surat telah berhasil diajukan untuk persetujuan (ACC)!');
    header('Location: detail.php?id='.$id_sk);
    exit;
}

$page_title = 'Detail Surat Keluar';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="detail-grid">
    <!-- Bagian Kiri: Dokumen Detail & Isi -->
    <div>
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><?= htmlspecialchars($surat['perihal']) ?></div>
                    <div class="card-subtitle">
                        Jenis: <b><?= htmlspecialchars($surat['nama_jenis']) ?></b> &bull; Unit: <b><?= htmlspecialchars($surat['nama_unit']) ?></b>
                    </div>
                </div>
                <div><?= badge_status($surat['status_surat']) ?></div>
            </div>

            <!-- Kartu Status Nomor Resmi -->
            <div class="nomor-surat-box <?= $surat['no_resmi'] ? '' : 'empty' ?>">
                <div class="nomor-surat-label">Nomor Surat Otomatis:</div>
                <div class="nomor-surat-value <?= $surat['no_resmi'] ? '' : 'empty' ?>">
                    <?= $surat['no_resmi'] ? htmlspecialchars($surat['no_resmi']) : '[NOMOR KOSONG - MENUNGGU ACC FINAL]' ?>
                </div>
                <div class="nomor-surat-hint">
                    <?= $surat['no_resmi'] ? 'Nomor otomatis diterbitkan pada '.tgl_indo($surat['tanggal_nomor']) : 'Nomor surat resmi akan dikunci & digenerate otomatis tepat saat pimpinan menyetujui surat ini.' ?>
                </div>
            </div>

            <!-- Metadata Info -->
            <table style="margin-bottom: 24px;">
                <tr>
                    <td width="160" class="text-muted">Tujuan Surat</td>
                    <td><b><?= htmlspecialchars($surat['tujuan']) ?></b></td>
                </tr>
                <tr>
                    <td class="text-muted">Sifat & Lampiran</td>
                    <td><?= badge_status($surat['sifat']) ?> &bull; Lampiran: <?= htmlspecialchars($surat['lampiran']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal Draft</td>
                    <td><?= tgl_indo($surat['tanggal_draft']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Pembuat Draft</td>
                    <td><?= htmlspecialchars($surat['nama_pembuat']) ?> (<?= date('d/m/Y H:i', strtotime($surat['created_at'])) ?>)</td>
                </tr>
            </table>

            <!-- Preview Isi Dokumen -->
            <div class="surat-body-preview">
<?= htmlspecialchars($surat['isi_surat']) ?>
            </div>

                <?php if ($surat['tembusan']) { ?>
                    <div style="margin-top: 16px; font-size: 13px;">
                        <b>Tembusan:</b><br>
                        <div style="color: var(--text-secondary); margin-top: 4px; white-space: pre-wrap;"><?= htmlspecialchars($surat['tembusan']) ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Bagian Kanan: Alur Persetujuan (ACC) & Tindakan -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Alur Persetujuan (ACC)</div>
            </div>

            <?php if (empty($acc_history)) { ?>
                <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">
                    Surat masih dalam status <b>Draft</b>. Silakan ajukan persetujuan ke atasan agar dapat diperiksa dan diproses nomor resminya.
                </div>
            <?php } else { ?>
                <div class="timeline" style="margin-bottom: 20px;">
                    <?php foreach ($acc_history as $acc) { ?>
                        <div class="timeline-step <?= $acc['status'] === 'disetujui' ? 'completed' : ($acc['status'] === 'ditolak' ? 'rejected' : '') ?>">
                            <div class="timeline-title">
                                Tahap <?= $acc['tahap_ke'] ?>: <?= htmlspecialchars($acc['nama_pejabat'] ?: 'Pimpinan') ?>
                            </div>
                            <div class="timeline-meta">
                                <?= badge_status($acc['status']) ?> 
                                <?php if ($acc['tanggal']) { ?>
                                    &bull; <?= date('d/m/Y H:i', strtotime($acc['tanggal'])) ?>
                                <?php } ?>
                            </div>
                            <?php if ($acc['catatan_penolakan']) { ?>
                                <div class="timeline-body" style="background: #fee2e2; border-color: #fecaca; color: #991b1b;">
                                    <b>Catatan Revisi:</b> <?= nl2br(htmlspecialchars($acc['catatan_penolakan'])) ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="action-panel">
                <!-- Tombol Cetak Dokumen Resmi -->
                <a href="<?= BASE_URL ?>/surat_keluar/cetak.php?id=<?= $surat['id_surat_keluar'] ?>" target="_blank" class="btn btn-secondary" style="width: 100%;">
                    <i class="bi bi-printer-fill"></i> Pratinjau / Cetak Format A4
                </a>

                <?php if ($surat['status_surat'] === 'draft') { ?>
                    <form method="POST">
                        <button type="submit" name="action_ajukan" class="btn btn-primary" style="width: 100%;">
                            <i class="bi bi-send-check-fill"></i> Ajukan Persetujuan ke Atasan
                        </button>
                    </form>
                <?php } ?>

                <?php if (in_array($surat['status_surat'], ['draft', 'revisi']) && ($surat['dibuat_oleh'] == $id_user || $role === 'admin_rektorat')) { ?>
                    <a href="<?= BASE_URL ?>/surat_keluar/edit.php?id=<?= $surat['id_surat_keluar'] ?>" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-pencil-fill"></i> Edit Dokumen Surat
                    </a>
                <?php } ?>

                <?php if ($role === 'pimpinan') { ?>
                    <!-- Shortcut jika surat ini sedang menunggu ACC dari pimpinan login -->
                    <?php
                    $stmtCekSaya = $pdo->prepare("SELECT id_acc FROM acc_surat_keluar WHERE id_surat_keluar = ? AND disetujui_oleh = ? AND status = 'menunggu' LIMIT 1");
                    $stmtCekSaya->execute([$id_sk, $id_user]);
                    $menungguSaya = $stmtCekSaya->fetchColumn();
                    if ($menungguSaya) {
                        ?>
                        <a href="<?= BASE_URL ?>/persetujuan/proses.php?id=<?= $menungguSaya ?>" class="btn btn-success" style="width: 100%;">
                            <i class="bi bi-patch-check-fill"></i> Tinjau & Berikan ACC Sekarang
                        </a>
                    <?php } ?>
                <?php } ?>

                <a href="<?= BASE_URL ?>/surat_keluar/index.php" class="btn btn-secondary" style="width: 100%;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
