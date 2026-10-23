<?php
// public/surat_masuk/detail.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';
wajib_login();

$id_surat = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'];
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Ambil detail surat
$stmt = $pdo->prepare('
    SELECT sm.*, ks.nama_kategori, js.nama_jenis, u.nama_unit, u.kode_unit, us.nama as nama_pencatat
    FROM surat_masuk sm
    LEFT JOIN kategori_surat ks ON sm.kategori_id = ks.id_kategori
    LEFT JOIN jenis_surat js ON sm.jenis_surat_id = js.id_jenis_surat
    LEFT JOIN units u ON sm.id_unit = u.id_unit
    LEFT JOIN users us ON sm.dicatat_oleh = us.id_user
    WHERE sm.id_surat_masuk = ?
');
$stmt->execute([$id_surat]);
$surat = $stmt->fetch();

if (! $surat) {
    set_flash('danger', 'Surat masuk tidak ditemukan!');
    header('Location: '.BASE_URL.'/surat_masuk/index.php');
    exit;
}

// Cek hak akses jika bukan admin_rektorat
if ($role !== 'admin_rektorat' && $surat['id_unit'] != $id_unit) {
    set_flash('danger', 'Akses ditolak: Anda tidak memiliki akses ke surat unit ini.');
    header('Location: '.BASE_URL.'/surat_masuk/index.php');
    exit;
}

// Ambil riwayat disposisi untuk surat ini
$stmtDisp = $pdo->prepare('
    SELECT d.*, u.nama AS pembuat_nama, r.nama_role
    FROM disposisi d
    LEFT JOIN users u ON d.dari_user_id = u.id_user
    LEFT JOIN roles r ON u.id_role = r.id_role
    WHERE d.id_surat_masuk = ?
    ORDER BY d.tanggal_kirim ASC
');
$stmtDisp->execute([$id_surat]);
$disposisi_list = $stmtDisp->fetchAll();

$page_title = 'Detail Surat Masuk';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="detail-grid">
    <!-- Bagian Kiri: Detail Surat & Riwayat Disposisi -->
    <div>
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><?= htmlspecialchars($surat['perihal']) ?></div>
                    <div class="card-subtitle">
                        Nomor Asal: <b><?= htmlspecialchars($surat['no_surat_asal'] ?: '-') ?></b>
                    </div>
                </div>
                <div><?= badge_status($surat['status_arsip']) ?></div>
            </div>

            <table style="margin-bottom: 24px;">
                <tr>
                    <td width="180" class="text-muted">Asal Pengirim</td>
                    <td><b><?= htmlspecialchars($surat['asal_surat']) ?></b></td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal Surat</td>
                    <td><?= tgl_indo($surat['tanggal_surat']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal Diterima</td>
                    <td><?= tgl_indo($surat['tanggal_terima']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Kategori / Jenis</td>
                    <td><?= htmlspecialchars($surat['nama_kategori'] ?: 'Umum') ?> / <?= htmlspecialchars($surat['nama_jenis'] ?: '-') ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Unit Penerima</td>
                    <td><?= htmlspecialchars($surat['nama_unit']) ?> (<?= htmlspecialchars($surat['kode_unit']) ?>)</td>
                </tr>
                <tr>
                    <td class="text-muted">Dicatat Oleh</td>
                    <td><?= htmlspecialchars($surat['nama_pencatat'] ?: '-') ?> pada <?= date('d/m/Y H:i', strtotime($surat['created_at'])) ?></td>
                </tr>
                <?php if ($surat['keterangan']) { ?>
                <tr>
                    <td class="text-muted">Keterangan</td>
                    <td><?= nl2br(htmlspecialchars($surat['keterangan'])) ?></td>
                </tr>
                <?php } ?>
            </table>

            <!-- Riwayat Disposisi Surat -->
            <div style="margin-top: 28px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div style="font-size: 15px; font-weight: 700;">Riwayat Alur Disposisi</div>
                    <?php if ($role === 'pimpinan' && $surat['status_arsip'] !== 'diarsipkan') { ?>
                        <a href="<?= BASE_URL ?>/disposisi/buat.php?id_surat=<?= $surat['id_surat_masuk'] ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Buat Disposisi Baru
                        </a>
                    <?php } ?>
                </div>

                <?php if (empty($disposisi_list)) { ?>
                    <p style="color: var(--text-muted); font-size: 13.5px; font-style: italic;">
                        Belum ada disposisi untuk surat ini. 
                        <?php if ($role === 'pimpinan') { ?>
                            Pimpinan dapat membuat disposisi atau langsung mengarsipkan surat.
                        <?php } ?>
                    </p>
                <?php } else { ?>
                    <div class="timeline">
                        <?php foreach ($disposisi_list as $disp) { ?>
                            <?php
                            // Ambil tujuan penerima disposisi
                            $stmtTujuan = $pdo->prepare('
                                SELECT dt.*, u.nama AS penerima_user, un.nama_unit AS penerima_unit, tl.catatan AS catatan_tl, tl.lampiran AS lampiran_tl, tl.tanggal_update AS tgl_tl
                                FROM disposisi_tujuan dt
                                LEFT JOIN users u ON dt.ke_user_id = u.id_user
                                LEFT JOIN units un ON dt.ke_unit_id = un.id_unit
                                LEFT JOIN tindak_lanjut tl ON dt.id_disposisi_tujuan = tl.id_disposisi_tujuan
                                WHERE dt.id_disposisi = ?
                            ');
                            $stmtTujuan->execute([$disp['id_disposisi']]);
                            $tujuan_list = $stmtTujuan->fetchAll();
                            ?>
                            <div class="timeline-step">
                                <div class="timeline-title">
                                    Disposisi oleh <?= htmlspecialchars($disp['pembuat_nama']) ?>
                                    <span style="font-weight: normal; margin-left: 6px;"><?= badge_status($disp['sifat']) ?></span>
                                </div>
                                <div class="timeline-meta">
                                    Terkirim pada <?= date('d/m/Y H:i', strtotime($disp['tanggal_kirim'])) ?>
                                    <?php if ($disp['tenggat_waktu']) { ?>
                                        &bull; Tenggat: <b><?= tgl_indo($disp['tenggat_waktu']) ?></b>
                                    <?php } ?>
                                </div>
                                <div class="timeline-body">
                                    <div style="font-weight: 600; margin-bottom: 4px; color: var(--text-primary);">Instruksi:</div>
                                    <p style="margin-bottom: 8px;"><?= nl2br(htmlspecialchars($disp['instruksi'])) ?></p>
                                    
                                    <?php if ($disp['catatan']) { ?>
                                        <div style="font-weight: 600; margin-bottom: 2px; color: var(--text-muted); font-size: 12px;">Catatan Tambahan:</div>
                                        <p style="font-size: 12.5px; color: var(--text-secondary); margin-bottom: 12px;"><?= nl2br(htmlspecialchars($disp['catatan'])) ?></p>
                                    <?php } ?>

                                    <div style="font-weight: 600; font-size: 12.5px; margin-bottom: 6px; color: var(--text-primary);">Daftar Penerima & Tindak Lanjut:</div>
                                    <div class="action-panel">
                                        <?php foreach ($tujuan_list as $t) { ?>
                                            <div style="background: #ffffff; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-start;">
                                                <div>
                                                    <b><?= $t['tujuan_tipe'] === 'unit' ? 'Unit: '.htmlspecialchars($t['penerima_unit']) : 'Individu: '.htmlspecialchars($t['penerima_user']) ?></b>
                                                    <?php if ($t['catatan_tl']) { ?>
                                                        <div style="font-size: 12px; color: var(--success); margin-top: 4px;">
                                                            <b>Tindak lanjut (<?= date('d/m/Y H:i', strtotime($t['tgl_tl'])) ?>):</b> <?= htmlspecialchars($t['catatan_tl']) ?>
                                                            <?php if ($t['lampiran_tl']) { ?>
                                                                <br><a href="<?= BASE_URL ?>/../uploads/tindak_lanjut/<?= htmlspecialchars($t['lampiran_tl']) ?>" target="_blank" style="color: var(--primary);">Lihat Bukti Lampiran</a>
                                                            <?php } ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div><?= badge_status($t['status']) ?></div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Bagian Kanan: Dokumen Scan & Tindakan -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Berkas Scan Surat</div>
            </div>
            <?php if ($surat['file_surat']) { ?>
                <div style="text-align: center; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px dashed var(--border-color); margin-bottom: 16px;">
                    <div style="font-size: 40px; margin-bottom: 8px;">
                        <i class="bi bi-file-earmark-pdf-fill" style="color: #dc2626;"></i>
                    </div>
                    <div style="font-size: 13px; font-weight: 600; margin-bottom: 12px; word-break: break-all;">
                        <?= htmlspecialchars($surat['file_surat']) ?>
                    </div>
                    <a href="<?= BASE_URL ?>/../uploads/surat_masuk/<?= htmlspecialchars($surat['file_surat']) ?>" target="_blank" class="btn btn-primary btn-sm" style="width: 100%;">
                        <i class="bi bi-eye-fill"></i> Buka / Unduh File Scan
                    </a>
                </div>
            <?php } else { ?>
                <p style="color: var(--text-muted); font-size: 13px; text-align: center; padding: 20px;">
                    Tidak ada lampiran scan surat yang diunggah.
                </p>
            <?php } ?>

            <div style="border-top: 1px solid var(--border-color); padding-top: 16px; display: flex; flex-direction: column; gap: 8px;">
                <?php if ($role === 'pimpinan' && $surat['status_arsip'] !== 'diarsipkan') { ?>
                    <a href="<?= BASE_URL ?>/disposisi/buat.php?id_surat=<?= $surat['id_surat_masuk'] ?>" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-send-fill"></i> Buat Disposisi
                    </a>
                <?php } ?>

                <?php if (in_array($role, ['admin_rektorat', 'staff_tu'])) { ?>
                    <a href="<?= BASE_URL ?>/surat_masuk/edit.php?id=<?= $surat['id_surat_masuk'] ?>" class="btn btn-secondary" style="width: 100%;">
                        <i class="bi bi-pencil-square"></i> Edit Data Surat
                    </a>
                <?php } ?>

                <?php if (in_array($role, ['admin_rektorat', 'staff_tu', 'pimpinan']) && $surat['status_arsip'] !== 'diarsipkan') { ?>
                    <a href="<?= BASE_URL ?>/surat_masuk/arsipkan.php?id=<?= $surat['id_surat_masuk'] ?>" class="btn btn-secondary" style="width: 100%;"
                       data-confirm="Surat ini akan dipindahkan ke arsip dan statusnya berubah menjadi Diarsipkan."
                       data-confirm-title="Arsipkan Surat Ini?"
                       data-confirm-type="info"
                       data-confirm-btn="Ya, Arsipkan">
                        <i class="bi bi-archive-fill"></i> Arsipkan Surat Ini
                    </a>
                <?php } ?>

                <?php if (in_array($role, ['admin_rektorat', 'staff_tu'])) { ?>
                    <a href="<?= BASE_URL ?>/surat_masuk/hapus.php?id=<?= $surat['id_surat_masuk'] ?>" class="btn btn-secondary" style="width: 100%; color: var(--danger);"
                       data-confirm="Surat masuk ini akan dihapus secara permanen beserta seluruh data terkait. Tindakan ini tidak dapat dibatalkan."
                       data-confirm-title="Hapus Surat Masuk?"
                       data-confirm-type="danger"
                       data-confirm-btn="Ya, Hapus Permanen"
                       data-confirm-class="btn-danger">
                        <i class="bi bi-trash"></i> Hapus Surat
                    </a>
                <?php } ?>

                <a href="<?= BASE_URL ?>/surat_masuk/index.php" class="btn btn-secondary" style="width: 100%;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
