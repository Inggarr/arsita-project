<?php
// public/laporan/export_pdf.php - Pratinjau & Cetak Laporan Rekapitulasi PDF
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_login();

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$id_unit = $_SESSION['id_unit'];
$role = $_SESSION['role'];

$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
];
$bulanStr = $nama_bulan[str_pad($bulan, 2, '0', STR_PAD_LEFT)] ?? $bulan;

// Ambil data surat masuk
$sqlSM = '
    SELECT sm.no_surat_asal, sm.tanggal_terima, sm.asal_surat, sm.perihal,
           ks.nama_kategori, sm.status_arsip
    FROM surat_masuk sm
    LEFT JOIN kategori_surat ks ON sm.kategori_id = ks.id_kategori
    WHERE MONTH(sm.tanggal_terima) = ? AND YEAR(sm.tanggal_terima) = ?
';
$paramsSM = [$bulan, $tahun];
if ($role !== 'admin_rektorat') {
    $sqlSM .= ' AND sm.id_unit = ?';
    $paramsSM[] = $id_unit;
}
$sqlSM .= ' ORDER BY sm.tanggal_terima ASC';
$stmtSM = $pdo->prepare($sqlSM);
$stmtSM->execute($paramsSM);
$listSM = $stmtSM->fetchAll();

// Ambil data surat keluar
$sqlSK = '
    SELECT ns.nomor_surat, sk.tanggal_draft, sk.tujuan, sk.perihal,
           js.nama_jenis, sk.status_surat
    FROM surat_keluar sk
    LEFT JOIN nomor_surat ns ON sk.id_surat_keluar = ns.id_surat_keluar
    LEFT JOIN jenis_surat js ON sk.jenis_surat_id = js.id_jenis_surat
    WHERE MONTH(sk.tanggal_draft) = ? AND YEAR(sk.tanggal_draft) = ?
';
$paramsSK = [$bulan, $tahun];
if ($role !== 'admin_rektorat') {
    $sqlSK .= ' AND sk.id_unit = ?';
    $paramsSK[] = $id_unit;
}
$sqlSK .= ' ORDER BY sk.tanggal_draft ASC';
$stmtSK = $pdo->prepare($sqlSK);
$stmtSK->execute($paramsSK);
$listSK = $stmtSK->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Persuratan — <?= htmlspecialchars($bulanStr) ?> <?= htmlspecialchars($tahun) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cetak.css">
    <style>
        .table-laporan {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
            margin-bottom: 24px;
        }
        .table-laporan th, .table-laporan td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        .table-laporan th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn-print"><i class="bi bi-printer-fill"></i> Cetak / Simpan PDF</button>
    <a href="<?= BASE_URL ?>/laporan/index.php" class="btn-back">Kembali ke Laporan</a>
</div>

<div class="page">
    <div class="kop">
        <div class="kop-kemenag">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
        <div class="kop-univ">UNIVERSITAS ISLAM NEGERI SIBER SYEKH NURJATI CIREBON</div>
        <div class="kop-unit"><?= strtoupper(htmlspecialchars($_SESSION['nama_unit'])) ?></div>
        <div class="kop-alamat">Jl. Perjuangan By Pass Sunyaragi, Cirebon, Jawa Barat 45131</div>
    </div>

    <div style="text-align: center; margin-bottom: 24px;">
        <h3 style="text-transform: uppercase; margin-bottom: 4px;">LAPORAN REKAPITULASI PERSURATAN</h3>
        <p style="font-size: 11pt;">Periode: <b><?= htmlspecialchars($bulanStr) ?> <?= htmlspecialchars($tahun) ?></b></p>
    </div>

    <h4 style="margin-bottom: 8px;">A. Rekap Surat Masuk (Total: <?= count($listSM) ?>)</h4>
    <table class="table-laporan">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>No. Surat Asal</th>
                <th>Tgl Terima</th>
                <th>Pengirim</th>
                <th>Perihal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listSM)) { ?>
                <tr><td colspan="6" style="text-align: center; font-style: italic;">Tidak ada data surat masuk.</td></tr>
            <?php } else { ?>
                <?php $no = 1;
                foreach ($listSM as $sm) { ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($sm['no_surat_asal'] ?: '-') ?></td>
                        <td><?= tgl_indo($sm['tanggal_terima']) ?></td>
                        <td><?= htmlspecialchars($sm['asal_surat']) ?></td>
                        <td><?= htmlspecialchars($sm['perihal']) ?></td>
                        <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $sm['status_arsip']))) ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>

    <h4 style="margin-bottom: 8px;">B. Rekap Surat Keluar (Total: <?= count($listSK) ?>)</h4>
    <table class="table-laporan">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nomor Surat</th>
                <th>Tgl Draft</th>
                <th>Tujuan</th>
                <th>Perihal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listSK)) { ?>
                <tr><td colspan="6" style="text-align: center; font-style: italic;">Tidak ada data surat keluar.</td></tr>
            <?php } else { ?>
                <?php $no = 1;
                foreach ($listSK as $sk) { ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($sk['nomor_surat'] ?: '(Belum ACC/Draft)') ?></td>
                        <td><?= tgl_indo($sk['tanggal_draft']) ?></td>
                        <td><?= htmlspecialchars($sk['tujuan']) ?></td>
                        <td><?= htmlspecialchars($sk['perihal']) ?></td>
                        <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $sk['status_surat']))) ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>

    <div style="margin-top: 40px; float: right; width: 250px; text-align: left; font-size: 11pt;">
        <div>Cirebon, <?= tgl_indo(date('Y-m-d')) ?></div>
        <div style="margin-top: 4px;">Mengetahui,</div>
        <div style="margin-top: 60px; font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($_SESSION['nama']) ?></div>
        <div>NIP. -</div>
    </div>
</div>

</body>
</html>
