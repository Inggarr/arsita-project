<?php
// public/arsip/index.php
$page_title = 'Arsip & Pencarian Terpadu';
require_once __DIR__.'/../../includes/header.php';

$id_user = $_SESSION['id_user'];
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Filter
$q = trim($_GET['q'] ?? '');
$tipe = $_GET['tipe'] ?? 'semua'; // 'semua', 'masuk', 'keluar'
$kategori_id = $_GET['kategori'] ?? '';
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

$results = [];

// 1. Cari Surat Masuk
if ($tipe === 'semua' || $tipe === 'masuk') {
    $sqlSM = "
        SELECT 'masuk' as tipe_surat, sm.id_surat_masuk as id_ref, sm.no_surat_asal as nomor_tampil,
               sm.perihal, sm.asal_surat as pihak_terkait, sm.tanggal_terima as tanggal_dokumen,
               ks.nama_kategori, sm.status_arsip as status_tampil, u.nama_unit, sm.file_surat
        FROM surat_masuk sm
        LEFT JOIN kategori_surat ks ON sm.kategori_id = ks.id_kategori
        LEFT JOIN units u ON sm.id_unit = u.id_unit
        WHERE 1=1
    ";
    $paramsSM = [];

    if ($role !== 'admin_rektorat') {
        $sqlSM .= ' AND sm.id_unit = ?';
        $paramsSM[] = $id_unit;
    }

    if ($q !== '') {
        $sqlSM .= ' AND (sm.no_surat_asal LIKE ? OR sm.perihal LIKE ? OR sm.asal_surat LIKE ?)';
        $paramsSM[] = "%$q%";
        $paramsSM[] = "%$q%";
        $paramsSM[] = "%$q%";
    }

    if ($kategori_id !== '') {
        $sqlSM .= ' AND sm.kategori_id = ?';
        $paramsSM[] = $kategori_id;
    }

    if ($tgl_mulai !== '') {
        $sqlSM .= ' AND sm.tanggal_terima >= ?';
        $paramsSM[] = $tgl_mulai;
    }

    if ($tgl_selesai !== '') {
        $sqlSM .= ' AND sm.tanggal_terima <= ?';
        $paramsSM[] = $tgl_selesai;
    }

    $stmtSM = $pdo->prepare($sqlSM);
    $stmtSM->execute($paramsSM);
    $results = array_merge($results, $stmtSM->fetchAll());
}

// 2. Cari Surat Keluar
if ($tipe === 'semua' || $tipe === 'keluar') {
    $sqlSK = "
        SELECT 'keluar' as tipe_surat, sk.id_surat_keluar as id_ref, 
               COALESCE(ns.nomor_surat, '[Draft / Menunggu ACC]') as nomor_tampil,
               sk.perihal, sk.tujuan as pihak_terkait, sk.tanggal_draft as tanggal_dokumen,
               ks.nama_kategori, sk.status_surat as status_tampil, u.nama_unit, NULL as file_surat
        FROM surat_keluar sk
        LEFT JOIN nomor_surat ns ON sk.id_surat_keluar = ns.id_surat_keluar
        LEFT JOIN kategori_surat ks ON sk.kategori_id = ks.id_kategori
        LEFT JOIN units u ON sk.id_unit = u.id_unit
        WHERE 1=1
    ";
    $paramsSK = [];

    if ($role !== 'admin_rektorat') {
        $sqlSK .= ' AND sk.id_unit = ?';
        $paramsSK[] = $id_unit;
    }

    if ($q !== '') {
        $sqlSK .= ' AND (ns.nomor_surat LIKE ? OR sk.perihal LIKE ? OR sk.tujuan LIKE ?)';
        $paramsSK[] = "%$q%";
        $paramsSK[] = "%$q%";
        $paramsSK[] = "%$q%";
    }

    if ($kategori_id !== '') {
        $sqlSK .= ' AND sk.kategori_id = ?';
        $paramsSK[] = $kategori_id;
    }

    if ($tgl_mulai !== '') {
        $sqlSK .= ' AND sk.tanggal_draft >= ?';
        $paramsSK[] = $tgl_mulai;
    }

    if ($tgl_selesai !== '') {
        $sqlSK .= ' AND sk.tanggal_draft <= ?';
        $paramsSK[] = $tgl_selesai;
    }

    $stmtSK = $pdo->prepare($sqlSK);
    $stmtSK->execute($paramsSK);
    $results = array_merge($results, $stmtSK->fetchAll());
}

// Urutkan gabungan berdasarkan tanggal terbaru
usort($results, function ($a, $b) {
    return strtotime($b['tanggal_dokumen']) - strtotime($a['tanggal_dokumen']);
});

$kategori_list = $pdo->query('SELECT * FROM kategori_surat ORDER BY nama_kategori ASC')->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Pusat Arsip Surat Digital Terpadu</div>
            <div class="card-subtitle">
                Telusuri seluruh riwayat arsip naskah dinas masuk dan keluar secara cepat dan akurat
            </div>
        </div>
    </div>

    <!-- Filter Pencarian Lengkap -->
    <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 24px;">
        <div>
            <label style="font-size: 12px;">Kata Kunci Pencarian</label>
            <input type="text" name="q" placeholder="Nomor surat, perihal, pihak terkait..." value="<?= htmlspecialchars($q) ?>">
        </div>

        <div>
            <label style="font-size: 12px;">Tipe Dokumen</label>
            <select name="tipe">
                <option value="semua" <?= $tipe === 'semua' ? 'selected' : '' ?>>Semua Dokumen</option>
                <option value="masuk" <?= $tipe === 'masuk' ? 'selected' : '' ?>>Surat Masuk Saja</option>
                <option value="keluar" <?= $tipe === 'keluar' ? 'selected' : '' ?>>Surat Keluar Saja</option>
            </select>
        </div>

        <div>
            <label style="font-size: 12px;">Kategori Bidang</label>
            <select name="kategori">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategori_list as $k) { ?>
                    <option value="<?= $k['id_kategori'] ?>" <?= $kategori_id == $k['id_kategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kategori']) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div>
            <label style="font-size: 12px;">Rentang Tanggal Mulai</label>
            <input type="date" name="tgl_mulai" value="<?= htmlspecialchars($tgl_mulai) ?>">
        </div>

        <div>
            <label style="font-size: 12px;">Rentang Tanggal Sampai</label>
            <input type="date" name="tgl_selesai" value="<?= htmlspecialchars($tgl_selesai) ?>">
        </div>

        <div style="display: flex; align-items: flex-end; gap: 8px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="bi bi-search"></i> Cari Arsip</button>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Tipe</th>
                    <th>Nomor Surat</th>
                    <th>Tanggal</th>
                    <th>Perihal Surat</th>
                    <th>Pihak Terkait (Asal / Tujuan)</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)) { ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding:32px">
                            Tidak ditemukan arsip surat yang sesuai kriteria pencarian Anda.
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php $no = 1;
                    foreach ($results as $r) { ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?php if ($r['tipe_surat'] === 'masuk') { ?>
                                    <span style="background: #dbeafe; color: #1e40af; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-inbox-fill"></i> MASUK
                                    </span>
                                <?php } else { ?>
                                    <span style="background: #dcfce7; color: #15803d; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-send-fill"></i> KELUAR
                                    </span>
                                <?php } ?>
                            </td>
                            <td><b><?= htmlspecialchars($r['nomor_tampil']) ?></b></td>
                            <td><?= tgl_indo($r['tanggal_dokumen']) ?></td>
                            <td><b><?= htmlspecialchars($r['perihal']) ?></b></td>
                            <td><?= htmlspecialchars($r['pihak_terkait'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($r['nama_kategori'] ?: 'Umum') ?></td>
                            <td><?= badge_status($r['status_tampil']) ?></td>
                            <td>
                                <?php if ($r['tipe_surat'] === 'masuk') { ?>
                                    <a href="<?= BASE_URL ?>/surat_masuk/detail.php?id=<?= $r['id_ref'] ?>" class="btn btn-secondary btn-sm">Buka</a>
                                <?php } else { ?>
                                    <a href="<?= BASE_URL ?>/surat_keluar/detail.php?id=<?= $r['id_ref'] ?>" class="btn btn-secondary btn-sm">Buka</a>
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
