<?php
// public/disposisi/riwayat.php
$page_title = 'Riwayat Disposisi Dibuat';
require_once __DIR__.'/../../includes/header.php';

$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'];

// Query disposisi yang dibuat oleh user atau seluruhnya jika admin_rektorat
$sql = '
    SELECT d.*, sm.no_surat_asal, sm.perihal, sm.asal_surat,
           dt.tujuan_tipe, dt.status as status_tl,
           u_ke.nama as nama_penerima_user, un_ke.nama_unit as nama_penerima_unit,
           tl.catatan as hasil_catatan, tl.tanggal_update as tgl_tl
    FROM disposisi d
    JOIN surat_masuk sm ON d.id_surat_masuk = sm.id_surat_masuk
    JOIN disposisi_tujuan dt ON d.id_disposisi = dt.id_disposisi
    LEFT JOIN users u_ke ON dt.ke_user_id = u_ke.id_user
    LEFT JOIN units un_ke ON dt.ke_unit_id = un_ke.id_unit
    LEFT JOIN tindak_lanjut tl ON dt.id_disposisi_tujuan = tl.id_disposisi_tujuan
    WHERE 1=1
';
$params = [];

if ($role !== 'admin_rektorat') {
    $sql .= ' AND d.dari_user_id = ?';
    $params[] = $id_user;
}

$sql .= ' ORDER BY d.tanggal_kirim DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$riwayat_list = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Monitoring Riwayat Disposisi</div>
            <div class="card-subtitle">
                Pantau real-time progres tindak lanjut dari seluruh instruksi disposisi yang telah Anda terbitkan
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Tgl Kirim</th>
                    <th>Surat Masuk</th>
                    <th>Tujuan Penerima</th>
                    <th>Instruksi</th>
                    <th>Sifat / Tenggat</th>
                    <th>Status Progres</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayat_list)) { ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            Belum ada riwayat disposisi yang diterbitkan.
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php $no = 1;
                    foreach ($riwayat_list as $r) { ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($r['tanggal_kirim'])) ?></td>
                            <td>
                                <b><?= htmlspecialchars($r['perihal']) ?></b><br>
                                <small class="text-muted">No: <?= htmlspecialchars($r['no_surat_asal'] ?: '-') ?></small>
                            </td>
                            <td>
                                <b><?= $r['tujuan_tipe'] === 'unit' ? '[UNIT] '.htmlspecialchars($r['nama_penerima_unit']) : htmlspecialchars($r['nama_penerima_user']) ?></b>
                            </td>
                            <td><?= htmlspecialchars(mb_strimwidth($r['instruksi'], 0, 45, '...')) ?></td>
                            <td>
                                <?= badge_status($r['sifat']) ?><br>
                                <small class="text-muted"><?= tgl_indo($r['tenggat_waktu']) ?></small>
                            </td>
                            <td>
                                <?= badge_status($r['status_tl']) ?>
                                <?php if ($r['hasil_catatan']) { ?>
                                    <br><small style="color: var(--success);">Hasil terlapor</small>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/surat_masuk/detail.php?id=<?= $r['id_surat_masuk'] ?>" class="btn btn-secondary btn-sm">
                                    Detail Surat
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
