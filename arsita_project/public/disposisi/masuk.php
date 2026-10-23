<?php
// public/disposisi/masuk.php
$page_title = 'Disposisi Masuk';
require_once __DIR__.'/../../includes/header.php';

$id_user = $_SESSION['id_user'];
$id_unit = $_SESSION['id_unit'];

// Ambil semua disposisi yang ditugaskan ke user atau unit login
$sql = "
    SELECT dt.*, d.instruksi, d.sifat, d.tenggat_waktu, d.tanggal_kirim, d.catatan as catatan_pimpinan,
           sm.id_surat_masuk, sm.no_surat_asal, sm.perihal, sm.asal_surat, sm.file_surat,
           u_dari.nama as dari_nama, u_dari.id_unit as dari_id_unit, un_dari.nama_unit as dari_unit,
           tl.catatan as catatan_tl, tl.lampiran as lampiran_tl
    FROM disposisi_tujuan dt
    JOIN disposisi d ON dt.id_disposisi = d.id_disposisi
    JOIN surat_masuk sm ON d.id_surat_masuk = sm.id_surat_masuk
    LEFT JOIN users u_dari ON d.dari_user_id = u_dari.id_user
    LEFT JOIN units un_dari ON u_dari.id_unit = un_dari.id_unit
    LEFT JOIN tindak_lanjut tl ON dt.id_disposisi_tujuan = tl.id_disposisi_tujuan
    WHERE (dt.ke_user_id = ? OR (dt.tujuan_tipe = 'unit' AND dt.ke_unit_id = ?))
    ORDER BY dt.status ASC, d.tanggal_kirim DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_user, $id_unit]);
$tugas_list = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Disposisi & Tugas Masuk</div>
            <div class="card-subtitle">
                Daftar instruksi dan arahan dinas dari pimpinan yang perlu ditindaklanjuti
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Perihal & Asal Surat</th>
                    <th>Pemberi Disposisi</th>
                    <th>Sifat</th>
                    <th>Tenggat Waktu</th>
                    <th>Instruksi</th>
                    <th>Status</th>
                    <th width="140">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tugas_list)) { ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            Tidak ada tugas disposisi yang perlu diproses saat ini.
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php $no = 1;
                    foreach ($tugas_list as $t) { ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <b><?= htmlspecialchars($t['perihal']) ?></b><br>
                                <small class="text-muted">
                                    No: <?= htmlspecialchars($t['no_surat_asal'] ?: '-') ?> &bull; Dari: <?= htmlspecialchars($t['asal_surat'] ?: '-') ?>
                                </small>
                            </td>
                            <td>
                                <b><?= htmlspecialchars($t['dari_nama']) ?></b><br>
                                <small class="text-muted"><?= htmlspecialchars($t['dari_unit'] ?: '-') ?></small>
                            </td>
                            <td><?= badge_status($t['sifat']) ?></td>
                            <td><?= tgl_indo($t['tenggat_waktu']) ?></td>
                            <td>
                                <?= htmlspecialchars(mb_strimwidth($t['instruksi'], 0, 60, '...')) ?>
                                <?php if (! empty($t['catatan_tl'])) { ?>
                                    <div style="margin-top: 6px; padding: 4px 8px; background: #f0fdf4; border-left: 3px solid #22c55e; border-radius: 4px; font-size: 11.5px; color: #166534;">
                                        <b>Laporan:</b> <?= htmlspecialchars(mb_strimwidth($t['catatan_tl'], 0, 50, '...')) ?>
                                    </div>
                                <?php } ?>
                            </td>
                            <td>
                                <?= badge_status($t['status']) ?>
                                <?php if ($t['status'] === 'selesai') { ?>
                                    <br><small style="color: var(--success); font-weight: 600; font-size: 11px;"><i class="bi bi-check-circle-fill"></i> Tuntas</small>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($t['status'] === 'selesai') { ?>
                                    <a href="<?= BASE_URL ?>/disposisi/tindak_lanjut.php?id=<?= $t['id_disposisi_tujuan'] ?>" class="btn btn-secondary btn-sm" title="Lihat atau edit hasil laporan">
                                        <i class="bi bi-pencil-square"></i> Ubah Laporan
                                    </a>
                                <?php } elseif ($t['status'] === 'diproses') { ?>
                                    <a href="<?= BASE_URL ?>/disposisi/tindak_lanjut.php?id=<?= $t['id_disposisi_tujuan'] ?>" class="btn btn-primary btn-sm" style="background:#d97706; border-color:#d97706;" title="Lanjutkan progres tugas">
                                        <i class="bi bi-arrow-repeat"></i> Lanjutkan
                                    </a>
                                <?php } else { ?>
                                    <a href="<?= BASE_URL ?>/disposisi/tindak_lanjut.php?id=<?= $t['id_disposisi_tujuan'] ?>" class="btn btn-primary btn-sm" title="Tindak lanjuti instruksi">
                                        <i class="bi bi-play-fill"></i> Tindak Lanjut
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
