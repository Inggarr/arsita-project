<?php
// public/disposisi/detail.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_login();

$id_disp = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;
$role = $_SESSION['role'] ?? '';

$stmt = $pdo->prepare('
    SELECT d.*, sm.no_surat_asal, sm.perihal, sm.asal_surat, sm.tanggal_surat, sm.tanggal_terima,
           sm.file_surat, sm.status_arsip, u.nama as nama_pembuat, un.nama_unit as unit_pembuat
    FROM disposisi d
    JOIN surat_masuk sm ON d.id_surat_masuk = sm.id_surat_masuk
    LEFT JOIN users u ON d.dari_user_id = u.id_user
    LEFT JOIN units un ON u.id_unit = un.id_unit
    WHERE d.id_disposisi = ?
');
$stmt->execute([$id_disp]);
$disp = $stmt->fetch();

if (! $disp) {
    set_flash('danger', 'Data disposisi tidak ditemukan!');
    header('Location: '.BASE_URL.'/disposisi/index.php');
    exit;
}

// Ambil daftar penerima dan statusnya
$stmtTujuan = $pdo->prepare('
    SELECT dt.*, u.nama as penerima_user, un.nama_unit as penerima_unit,
           tl.catatan as catatan_tl, tl.lampiran as lampiran_tl, tl.tanggal_update as tgl_tl
    FROM disposisi_tujuan dt
    LEFT JOIN users u ON dt.ke_user_id = u.id_user
    LEFT JOIN units un ON dt.ke_unit_id = un.id_unit
    LEFT JOIN tindak_lanjut tl ON dt.id_disposisi_tujuan = tl.id_disposisi_tujuan
    WHERE dt.id_disposisi = ?
');
$stmtTujuan->execute([$id_disp]);
$tujuan_list = $stmtTujuan->fetchAll();

$page_title = 'Detail Disposisi';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title">Lembar Disposisi Elektronik</div>
            <div class="card-subtitle">
                Surat: <b><?= htmlspecialchars($disp['perihal']) ?></b> (No: <?= htmlspecialchars($disp['no_surat_asal'] ?: '-') ?>)
            </div>
        </div>
        <a href="<?= BASE_URL ?>/surat_masuk/detail.php?id=<?= $disp['id_surat_masuk'] ?>" class="btn btn-secondary btn-sm">
            <i class="bi bi-file-earmark-text"></i> Buka Surat Masuk
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
        <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Pengirim & Surat</div>
            <table style="width: 100%; font-size: 13.5px;">
                <tr>
                    <td class="text-muted" width="120">Pemberi:</td>
                    <td><b><?= htmlspecialchars($disp['nama_pembuat'] ?: '-') ?></b> (<?= htmlspecialchars($disp['unit_pembuat'] ?: '-') ?>)</td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal:</td>
                    <td><?= date('d/m/Y H:i', strtotime($disp['tanggal_kirim'])) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Asal Surat:</td>
                    <td><?= htmlspecialchars($disp['asal_surat']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Tgl Terima:</td>
                    <td><?= tgl_indo($disp['tanggal_terima']) ?></td>
                </tr>
            </table>
        </div>

        <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Karakteristik Instruksi</div>
            <table style="width: 100%; font-size: 13.5px;">
                <tr>
                    <td class="text-muted" width="120">Sifat:</td>
                    <td><?= badge_status($disp['sifat']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Tenggat:</td>
                    <td><b><?= tgl_indo($disp['tenggat_waktu']) ?></b></td>
                </tr>
                <tr>
                    <td class="text-muted">Berkas Scan:</td>
                    <td>
                        <?php if ($disp['file_surat']) { ?>
                            <a href="<?= BASE_URL ?>/../uploads/surat_masuk/<?= htmlspecialchars($disp['file_surat']) ?>" target="_blank" style="color: var(--primary); font-weight: 600;">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File Surat
                            </a>
                        <?php } else { ?>
                            <span class="text-muted">-</span>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div style="margin-bottom: 24px;">
        <div style="font-weight: 700; margin-bottom: 8px;">Instruksi Pimpinan:</div>
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-left: 4px solid var(--primary); padding: 16px; border-radius: 6px; font-size: 14px; line-height: 1.6;">
            <?= nl2br(htmlspecialchars($disp['instruksi'])) ?>
        </div>
        <?php if ($disp['catatan']) { ?>
            <div style="margin-top: 10px; font-size: 13px; color: var(--text-secondary);">
                <b>Catatan Tambahan:</b> <?= nl2br(htmlspecialchars($disp['catatan'])) ?>
            </div>
        <?php } ?>
    </div>

    <div>
        <div style="font-weight: 700; margin-bottom: 12px;">Daftar Penerima & Status Tindak Lanjut:</div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Penerima</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Tindak Lanjut & Keterangan</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tujuan_list as $t) { ?>
                        <tr>
                            <td>
                                <b><?= htmlspecialchars($t['tujuan_tipe'] === 'unit' ? ($t['penerima_unit'] ?? '-') : ($t['penerima_user'] ?? '-')) ?></b>
                            </td>
                            <td><span class="badge"><?= ucfirst($t['tujuan_tipe']) ?></span></td>
                            <td><?= badge_status($t['status']) ?></td>
                            <td>
                                <?php if ($t['catatan_tl']) { ?>
                                    <div style="font-size: 13px;">
                                        <?= nl2br(htmlspecialchars($t['catatan_tl'])) ?>
                                        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">
                                            Diperbarui: <?= date('d/m/Y H:i', strtotime($t['tgl_tl'])) ?>
                                            <?php if ($t['lampiran_tl']) { ?>
                                                &bull; <a href="<?= BASE_URL ?>/../uploads/tindak_lanjut/<?= htmlspecialchars($t['lampiran_tl']) ?>" target="_blank" style="color: var(--primary);">Lampiran Bukti</a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <span class="text-muted" style="font-style: italic; font-size: 13px;">Belum ada laporan tindak lanjut</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($t['ke_user_id'] == $id_user || ($t['tujuan_tipe'] === 'unit' && $t['ke_unit_id'] == $id_unit)) { ?>
                                    <a href="<?= BASE_URL ?>/disposisi/tindak_lanjut.php?id=<?= $t['id_disposisi_tujuan'] ?>" class="btn btn-primary btn-sm">
                                        Proses
                                    </a>
                                <?php } else { ?>
                                    <span class="text-muted">-</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
        <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
