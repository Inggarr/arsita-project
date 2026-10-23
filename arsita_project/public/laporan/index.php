<?php
// public/laporan/index.php
$page_title = 'Rekapitulasi & Laporan Surat';
require_once __DIR__.'/../../includes/header.php';

$id_user = $_SESSION['id_user'];
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

// Filter periode
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Hitung rekap surat masuk per kategori
$sqlKategoriSM = '
    SELECT ks.nama_kategori, COUNT(sm.id_surat_masuk) as jumlah
    FROM kategori_surat ks
    LEFT JOIN surat_masuk sm ON ks.id_kategori = sm.kategori_id 
        AND MONTH(sm.tanggal_terima) = ? AND YEAR(sm.tanggal_terima) = ?
        '.($role !== 'admin_rektorat' ? ' AND sm.id_unit = ?' : '').'
    GROUP BY ks.id_kategori
    ORDER BY ks.nama_kategori ASC
';
$paramsSM = [$bulan, $tahun];
if ($role !== 'admin_rektorat') {
    $paramsSM[] = $id_unit;
}
$stmtKSM = $pdo->prepare($sqlKategoriSM);
$stmtKSM->execute($paramsSM);
$rekap_sm_kategori = $stmtKSM->fetchAll();

// Hitung rekap surat keluar per jenis surat
$sqlJenisSK = '
    SELECT js.nama_jenis, js.kode_jenis, COUNT(sk.id_surat_keluar) as jumlah
    FROM jenis_surat js
    LEFT JOIN surat_keluar sk ON js.id_jenis_surat = sk.jenis_surat_id 
        AND MONTH(sk.tanggal_draft) = ? AND YEAR(sk.tanggal_draft) = ?
        '.($role !== 'admin_rektorat' ? ' AND sk.id_unit = ?' : '').'
    GROUP BY js.id_jenis_surat
    ORDER BY js.nama_jenis ASC
';
$paramsSK = [$bulan, $tahun];
if ($role !== 'admin_rektorat') {
    $paramsSK[] = $id_unit;
}
$stmtJSK = $pdo->prepare($sqlJenisSK);
$stmtJSK->execute($paramsSK);
$rekap_sk_jenis = $stmtJSK->fetchAll();

// Hitung status surat keluar
$sqlStatusSK = '
    SELECT sk.status_surat, COUNT(*) as jumlah
    FROM surat_keluar sk
    WHERE MONTH(sk.tanggal_draft) = ? AND YEAR(sk.tanggal_draft) = ?
    '.($role !== 'admin_rektorat' ? ' AND sk.id_unit = ?' : '').'
    GROUP BY sk.status_surat
';
$stmtSSK = $pdo->prepare($sqlStatusSK);
$stmtSSK->execute($paramsSK);
$rekap_status_sk = $stmtSSK->fetchAll();

// Hitung total surat masuk & keluar di periode ini
$total_sm_periode = array_sum(array_column($rekap_sm_kategori, 'jumlah'));
$total_sk_periode = array_sum(array_column($rekap_sk_jenis, 'jumlah'));

$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
];
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Rekapitulasi Persuratan Periode <?= $nama_bulan[str_pad($bulan, 2, '0', STR_PAD_LEFT)] ?> <?= $tahun ?></div>
            <div class="card-subtitle">
                Laporan statistik volume naskah dinas masuk dan keluar <?= htmlspecialchars($_SESSION['nama_unit']) ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="export_excel.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-secondary btn-sm" target="_blank">
                <i class="bi bi-file-earmark-excel-fill" style="color: #16a34a;"></i> Unduh Excel/CSV
            </a>
            <a href="export_pdf.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-secondary btn-sm" target="_blank">
                <i class="bi bi-file-earmark-pdf-fill" style="color: #dc2626;"></i> Cetak / Simpan PDF
            </a>
        </div>
    </div>

    <!-- Filter Periode -->
    <form method="GET" style="display: flex; gap: 12px; align-items: flex-end; margin-bottom: 24px;">
        <div style="flex: 1; max-width: 200px;">
            <label style="font-size: 12px;">Pilih Bulan</label>
            <select name="bulan">
                <?php foreach ($nama_bulan as $num => $b) { ?>
                    <option value="<?= $num ?>" <?= (int) $bulan == (int) $num ? 'selected' : '' ?>><?= $b ?></option>
                <?php } ?>
            </select>
        </div>

        <div style="flex: 1; max-width: 150px;">
            <label style="font-size: 12px;">Pilih Tahun</label>
            <select name="tahun">
                <?php for ($y = date('Y'); $y >= 2024; $y--) { ?>
                    <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php } ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Tampilkan Rekap</button>
    </form>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-inbox-fill"></i></div>
            <div>
                <div class="stat-value"><?= number_format($total_sm_periode) ?></div>
                <div class="stat-label">Total Surat Masuk (Periode Ini)</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-send-fill"></i></div>
            <div>
                <div class="stat-value"><?= number_format($total_sk_periode) ?></div>
                <div class="stat-label">Total Surat Keluar (Periode Ini)</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-top: 10px;">
        <!-- Rekap Surat Masuk per Kategori -->
        <div>
            <div style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">Distribusi Surat Masuk per Kategori</div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Kategori Surat</th>
                            <th width="120" style="text-align: right;">Jumlah Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekap_sm_kategori as $r) { ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
                                <td style="text-align: right; font-weight: 700;"><?= number_format($r['jumlah']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rekap Surat Keluar per Jenis Surat -->
        <div>
            <div style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">Distribusi Surat Keluar per Jenis Dokumen</div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Jenis Surat Resmi</th>
                            <th width="120" style="text-align: right;">Jumlah Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekap_sk_jenis as $r) { ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nama_jenis']) ?> (<?= htmlspecialchars($r['kode_jenis']) ?>)</td>
                                <td style="text-align: right; font-weight: 700;"><?= number_format($r['jumlah']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
