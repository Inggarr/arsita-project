<?php
// public/surat_keluar/index.php
$page_title = 'Daftar Surat Keluar';
require_once __DIR__.'/../../includes/header.php';

$id_user = $_SESSION['id_user'];
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Filter
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$jenis_id = $_GET['jenis'] ?? '';

$sql = '
    SELECT sk.*, js.nama_jenis, js.kode_jenis, ks.nama_kategori, u.nama_unit, u.kode_unit,
           us.nama as nama_pembuat, ns.nomor_surat as no_resmi
    FROM surat_keluar sk
    LEFT JOIN jenis_surat js ON sk.jenis_surat_id = js.id_jenis_surat
    LEFT JOIN kategori_surat ks ON sk.kategori_id = ks.id_kategori
    LEFT JOIN units u ON sk.id_unit = u.id_unit
    LEFT JOIN users us ON sk.dibuat_oleh = us.id_user
    LEFT JOIN nomor_surat ns ON sk.id_surat_keluar = ns.id_surat_keluar
    WHERE 1=1
';
$params = [];

if ($role !== 'admin_rektorat') {
    $sql .= ' AND sk.id_unit = ?';
    $params[] = $id_unit;
}

if ($q !== '') {
    $sql .= ' AND (sk.perihal LIKE ? OR sk.tujuan LIKE ? OR ns.nomor_surat LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($status !== '') {
    $sql .= ' AND sk.status_surat = ?';
    $params[] = $status;
}

if ($jenis_id !== '') {
    $sql .= ' AND sk.jenis_surat_id = ?';
    $params[] = $jenis_id;
}

$sql .= ' ORDER BY sk.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$surat_list = $stmt->fetchAll();

$jenis_list = $pdo->query('SELECT * FROM jenis_surat ORDER BY nama_jenis ASC')->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Surat Keluar <?= htmlspecialchars($_SESSION['nama_unit']) ?></div>
            <div class="card-subtitle">Pengelolaan draft, persetujuan (ACC), penomoran otomatis, dan cetak dokumen resmi</div>
        </div>
        <?php if (in_array($role, ['admin_rektorat', 'staff_tu'])) { ?>
            <a href="<?= BASE_URL ?>/surat_keluar/tambah.php" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Buat Surat Keluar Baru
            </a>
        <?php } ?>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="d-flex gap-3 flex-wrap mb-4">
        <input type="text" name="q" placeholder="Cari perihal, tujuan, nomor..." value="<?= htmlspecialchars($q) ?>" class="flex-1" style="min-width:200px">
        <select name="jenis" style="flex:1;min-width:150px">
            <option value="">Semua Jenis Surat</option>
            <?php foreach ($jenis_list as $j) { ?>
                <option value="<?= $j['id_jenis_surat'] ?>" <?= $jenis_id == $j['id_jenis_surat'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($j['nama_jenis']) ?> (<?= htmlspecialchars($j['kode_jenis']) ?>)
                </option>
            <?php } ?>
        </select>
        <select name="status" style="flex:1;min-width:150px">
            <option value="">Semua Status</option>
            <option value="draft"         <?= $status == 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="menunggu_acc"  <?= $status == 'menunggu_acc' ? 'selected' : '' ?>>Menunggu ACC</option>
            <option value="revisi"        <?= $status == 'revisi' ? 'selected' : '' ?>>Revisi Diperlukan</option>
            <option value="disetujui"     <?= $status == 'disetujui' ? 'selected' : '' ?>>Disetujui &amp; Bernomor</option>
            <option value="diarsipkan"    <?= $status == 'diarsipkan' ? 'selected' : '' ?>>Diarsipkan</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if ($q !== '' || $status !== '' || $jenis_id !== '') { ?>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        <?php } ?>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Nomor Surat Resmi</th>
                    <th>Jenis &amp; Kategori</th>
                    <th>Tujuan</th>
                    <th>Perihal</th>
                    <th>Sifat</th>
                    <th>Status</th>
                    <th width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($surat_list)) { ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            Tidak ada surat keluar yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php $no = 1;
                    foreach ($surat_list as $sk) { ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?php if ($sk['no_resmi']) { ?>
                                    <b class="text-brand"><?= htmlspecialchars($sk['no_resmi']) ?></b>
                                <?php } else { ?>
                                    <span class="text-muted text-xs" style="font-style:italic">(Belum digenerate - ACC)</span>
                                <?php } ?>
                                <br><small class="text-muted"><?= tgl_indo($sk['tanggal_draft']) ?></small>
                            </td>
                            <td>
                                <b><?= htmlspecialchars($sk['nama_jenis'] ?: '-') ?></b><br>
                                <small class="text-muted"><?= htmlspecialchars($sk['nama_kategori'] ?: 'Umum') ?></small>
                            </td>
                            <td><?= htmlspecialchars($sk['tujuan'] ?: '-') ?></td>
                            <td>
                                <b><?= htmlspecialchars($sk['perihal']) ?></b><br>
                                <small class="text-muted">Pembuat: <?= htmlspecialchars($sk['nama_pembuat']) ?></small>
                            </td>
                            <td><?= badge_status($sk['sifat']) ?></td>
                            <td><?= badge_status($sk['status_surat']) ?></td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="<?= BASE_URL ?>/surat_keluar/detail.php?id=<?= $sk['id_surat_keluar'] ?>" class="btn btn-secondary btn-sm">Detail</a>
                                    <a href="<?= BASE_URL ?>/surat_keluar/cetak.php?id=<?= $sk['id_surat_keluar'] ?>" target="_blank" class="btn btn-secondary btn-sm" title="Cetak Surat">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <?php if (in_array($sk['status_surat'], ['draft', 'revisi']) && ($sk['dibuat_oleh'] == $id_user || $role === 'admin_rektorat')) { ?>
                                        <a href="<?= BASE_URL ?>/surat_keluar/edit.php?id=<?= $sk['id_surat_keluar'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
