<?php
// public/persetujuan/index.php
$page_title = 'Persetujuan Surat Keluar (ACC)';
require_once __DIR__.'/../../includes/header.php';

wajib_role(['pimpinan', 'admin_rektorat']);

$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'];

// Ambil daftar surat keluar yang menunggu persetujuan dari user login
$sql = "
    SELECT ask.*, sk.perihal, sk.tujuan, sk.sifat, sk.tanggal_draft, sk.dibuat_oleh,
           js.nama_jenis, js.kode_jenis, u.nama_unit, us.nama as nama_pembuat
    FROM acc_surat_keluar ask
    JOIN surat_keluar sk ON ask.id_surat_keluar = sk.id_surat_keluar
    LEFT JOIN jenis_surat js ON sk.jenis_surat_id = js.id_jenis_surat
    LEFT JOIN units u ON sk.id_unit = u.id_unit
    LEFT JOIN users us ON sk.dibuat_oleh = us.id_user
    WHERE ask.status = 'menunggu'
";
$params = [];

if ($role !== 'admin_rektorat') {
    $sql .= ' AND ask.disetujui_oleh = ?';
    $params[] = $id_user;
}

$sql .= ' ORDER BY ask.id_acc ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$menunggu_list = $stmt->fetchAll();

// Riwayat surat yang sudah pernah di-ACC oleh user
$stmtRiwayat = $pdo->prepare("
    SELECT ask.*, sk.perihal, sk.tujuan, sk.status_surat, js.nama_jenis, ns.nomor_surat
    FROM acc_surat_keluar ask
    JOIN surat_keluar sk ON ask.id_surat_keluar = sk.id_surat_keluar
    LEFT JOIN jenis_surat js ON sk.jenis_surat_id = js.id_jenis_surat
    LEFT JOIN nomor_surat ns ON sk.id_surat_keluar = ns.id_surat_keluar
    WHERE ask.disetujui_oleh = ? AND ask.status != 'menunggu'
    ORDER BY ask.tanggal DESC LIMIT 10
");
$stmtRiwayat->execute([$id_user]);
$riwayat_acc = $stmtRiwayat->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Antrean Persetujuan Surat Keluar</div>
            <div class="card-subtitle">
                Tinjau naskah surat dinas yang diajukan staf sebelum diterbitkan nomor surat otomatis
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Tahap ACC</th>
                    <th>Perihal & Jenis Surat</th>
                    <th>Tujuan Penerima</th>
                    <th>Pembuat Draft & Unit</th>
                    <th>Sifat</th>
                    <th>Tgl Pengajuan</th>
                    <th width="140">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($menunggu_list)) { ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            🎉 Tidak ada antrean surat keluar yang membutuhkan persetujuan Anda saat ini.
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php $no = 1;
                    foreach ($menunggu_list as $m) { ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 11px;">
                                    Tahap <?= $m['tahap_ke'] ?>
                                </span>
                            </td>
                            <td>
                                <b><?= htmlspecialchars($m['perihal']) ?></b><br>
                                <small class="text-muted"><?= htmlspecialchars($m['nama_jenis']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($m['tujuan']) ?></td>
                            <td>
                                <b><?= htmlspecialchars($m['nama_pembuat']) ?></b><br>
                                <small class="text-muted"><?= htmlspecialchars($m['nama_unit']) ?></small>
                            </td>
                            <td><?= badge_status($m['sifat']) ?></td>
                            <td><?= tgl_indo($m['tanggal_draft']) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/persetujuan/proses.php?id=<?= $m['id_acc'] ?>" class="btn btn-primary btn-sm">
                                    Tinjau Surat
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Riwayat Persetujuan Terakhir -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Riwayat Persetujuan Terakhir Anda</div>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tgl Keputusan</th>
                    <th>Perihal Surat</th>
                    <th>Keputusan Anda</th>
                    <th>Nomor / Status Surat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayat_acc)) { ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada riwayat persetujuan.</td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($riwayat_acc as $r) { ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td><b><?= htmlspecialchars($r['perihal']) ?></b></td>
                            <td><?= badge_status($r['status']) ?></td>
                            <td>
                                <?= $r['nomor_surat'] ? '<b>'.htmlspecialchars($r['nomor_surat']).'</b>' : badge_status($r['status_surat']) ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
