<?php
// public/surat_masuk/index.php
$page_title = 'Daftar Surat Masuk';
require_once __DIR__.'/../../includes/header.php';

$id_user = $_SESSION['id_user'];
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Filter & Pencarian
$q = trim($_GET['q'] ?? '');
$kategori = $_GET['kategori'] ?? '';
$status = $_GET['status'] ?? '';

$sql = '
    SELECT sm.*, ks.nama_kategori, js.nama_jenis, u.nama_unit, us.nama as nama_pencatat
    FROM surat_masuk sm
    LEFT JOIN kategori_surat ks ON sm.kategori_id = ks.id_kategori
    LEFT JOIN jenis_surat js ON sm.jenis_surat_id = js.id_jenis_surat
    LEFT JOIN units u ON sm.id_unit = u.id_unit
    LEFT JOIN users us ON sm.dicatat_oleh = us.id_user
    WHERE 1=1
';
$params = [];

if ($role !== 'admin_rektorat') {
    $sql .= ' AND sm.id_unit = ?';
    $params[] = $id_unit;
}

if ($q !== '') {
    $sql .= ' AND (sm.no_surat_asal LIKE ? OR sm.asal_surat LIKE ? OR sm.perihal LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($kategori !== '') {
    $sql .= ' AND sm.kategori_id = ?';
    $params[] = $kategori;
}

if ($status !== '') {
    $sql .= ' AND sm.status_arsip = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY sm.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$surat_list = $stmt->fetchAll();

// Ambil list kategori untuk filter
$kategori_list = $pdo->query('SELECT * FROM kategori_surat ORDER BY nama_kategori ASC')->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Data Surat Masuk <?= htmlspecialchars($_SESSION['nama_unit']) ?></div>
            <div class="card-subtitle">Kelola dan catat seluruh surat dinas yang diterima unit</div>
        </div>
        <?php if (in_array($role, ['admin_rektorat', 'staff_tu'])) { ?>
            <a href="<?= BASE_URL ?>/surat_masuk/tambah.php" class="btn btn-primary">
                <i class="bi bi-file-earmark-plus"></i> Catat Surat Masuk
            </a>
        <?php } ?>
    </div>

    <!-- Form Filter -->
    <form method="GET" class="d-flex gap-3 flex-wrap mb-4">
        <input type="text" name="q" placeholder="Cari nomor, pengirim, atau perihal..." value="<?= htmlspecialchars($q) ?>" class="flex-1" style="min-width:200px">
        
        <select name="kategori" style="flex:1;min-width:150px">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategori_list as $k) { ?>
                <option value="<?= $k['id_kategori'] ?>" <?= $kategori == $k['id_kategori'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['nama_kategori']) ?>
                </option>
            <?php } ?>
        </select>

        <select name="status" style="flex:1;min-width:150px">
            <option value="">Semua Status</option>
            <option value="belum_ditinjau" <?= $status == 'belum_ditinjau' ? 'selected' : '' ?>>Belum Ditinjau</option>
            <option value="ditinjau" <?= $status == 'ditinjau' ? 'selected' : '' ?>>Ditinjau</option>
            <option value="didisposisikan" <?= $status == 'didisposisikan' ? 'selected' : '' ?>>Didisposisikan</option>
            <option value="diarsipkan" <?= $status == 'diarsipkan' ? 'selected' : '' ?>>Diarsipkan</option>
        </select>

        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if ($q !== '' || $kategori !== '' || $status !== '') { ?>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        <?php } ?>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>No. Surat Asal</th>
                    <th>Tgl Terima</th>
                    <th>Pengirim / Asal</th>
                    <th>Perihal</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($surat_list)) { ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            Tidak ada surat masuk yang cocok dengan kriteria pencarian.
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php $no = 1;
                    foreach ($surat_list as $sm) { ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <b><?= htmlspecialchars($sm['no_surat_asal'] ?: '-') ?></b>
                                <?php if ($sm['asal_disposisi_id']) { ?>
                                    <br><span class="badge badge-purple">Disposisi Atasan</span>
                                <?php } ?>
                            </td>
                            <td><?= tgl_indo($sm['tanggal_terima']) ?></td>
                            <td><?= htmlspecialchars($sm['asal_surat'] ?: '-') ?></td>
                            <td>
                                <b><?= htmlspecialchars($sm['perihal']) ?></b>
                            </td>
                            <td><?= htmlspecialchars($sm['nama_kategori'] ?: 'Umum') ?></td>
                            <td><?= badge_status($sm['status_arsip']) ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?= BASE_URL ?>/surat_masuk/detail.php?id=<?= $sm['id_surat_masuk'] ?>" class="btn btn-secondary btn-sm" title="Lihat Detail">
                                        Detail
                                    </a>
                                    <?php if (in_array($role, ['admin_rektorat', 'staff_tu']) && $sm['status_arsip'] !== 'diarsipkan') { ?>
                                        <a href="<?= BASE_URL ?>/surat_masuk/edit.php?id=<?= $sm['id_surat_masuk'] ?>" class="btn btn-secondary btn-sm" title="Edit Surat">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/surat_masuk/hapus.php?id=<?= $sm['id_surat_masuk'] ?>" class="btn btn-secondary btn-sm" style="color: var(--danger);" title="Hapus Surat"
                                           data-confirm="Surat masuk ini akan dihapus secara permanen dan tidak dapat dikembalikan."
                                           data-confirm-title="Hapus Surat Masuk?"
                                           data-confirm-type="danger"
                                           data-confirm-btn="Ya, Hapus"
                                           data-confirm-class="btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php } ?>
                                    <?php if ($role === 'pimpinan' && $sm['status_arsip'] !== 'diarsipkan') { ?>
                                        <a href="<?= BASE_URL ?>/disposisi/buat.php?id_surat=<?= $sm['id_surat_masuk'] ?>" class="btn btn-primary btn-sm" title="Buat Disposisi">
                                            Disposisi
                                        </a>
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
