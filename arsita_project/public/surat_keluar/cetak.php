<?php
ob_start(); // Prevent headers already sent
// public/surat_keluar/cetak.php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';
wajib_login();

$id_sk = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT sk.*, js.nama_jenis, js.kode_jenis, u.nama_unit, u.kode_unit, u.level as level_unit,
           ns.nomor_surat as no_resmi, ns.tanggal_nomor
    FROM surat_keluar sk
    LEFT JOIN jenis_surat js ON sk.jenis_surat_id = js.id_jenis_surat
    LEFT JOIN units u ON sk.id_unit = u.id_unit
    LEFT JOIN nomor_surat ns ON sk.id_surat_keluar = ns.id_surat_keluar
    WHERE sk.id_surat_keluar = ?
');
$stmt->execute([$id_sk]);
$surat = $stmt->fetch();

if (! $surat) {
    exit('Surat keluar tidak ditemukan.');
}

// Pejabat penandatangan (diambil dari ACC tahap akhir)
$stmtPejabat = $pdo->prepare("
    SELECT u.nama, r.nama_role, un.nama_unit
    FROM acc_surat_keluar ask
    JOIN users u ON ask.disetujui_oleh = u.id_user
    JOIN roles r ON u.id_role = r.id_role
    JOIN units un ON u.id_unit = un.id_unit
    WHERE ask.id_surat_keluar = ? AND ask.status = 'disetujui'
    ORDER BY ask.tahap_ke DESC LIMIT 1
");
$stmtPejabat->execute([$id_sk]);
$pejabat = $stmtPejabat->fetch();

$nama_pejabat = $pejabat ? $pejabat['nama'] : 'Pimpinan '.$surat['nama_unit'];
$jabatan_pejabat = ($surat['level_unit'] === 'rektorat') ? 'Rektor UIN Siber Syekh Nurjati Cirebon' : (($surat['level_unit'] === 'fakultas') ? 'Dekan '.$surat['nama_unit'] : 'Ketua '.$surat['nama_unit']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Surat — <?= htmlspecialchars($surat['perihal']) ?></title>
    <!-- Bootstrap Icons (Lokal) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons.min.css">
    <!-- Format Cetak Surat Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cetak.css">
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn-print"><i class="bi bi-printer-fill"></i> Cetak / Simpan PDF</button>
    <a href="<?= BASE_URL ?>/surat_keluar/detail.php?id=<?= $id_sk ?>" class="btn-back">Kembali ke Detail</a>
</div>

<div class="page">
    <?php if (! $surat['no_resmi']) { ?>
        <div class="watermark">DRAFT / BELUM ACC</div>
    <?php } ?>

    <!-- Kop Surat Resmi -->
    <div class="kop">
        <div class="kop-kemenag">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
        <div class="kop-univ">UNIVERSITAS ISLAM NEGERI SIBER SYEKH NURJATI CIREBON</div>
        <div class="kop-unit"><?= strtoupper(htmlspecialchars($surat['nama_unit'])) ?></div>
        <div class="kop-alamat">
            Jalan Perjuangan Bypass Sunyaragi, Cirebon, Jawa Barat 45131<br>
            Website: www.uinssc.ac.id | Email: persuratan@uinssc.ac.id
        </div>
    </div>

    <!-- Metadata Surat -->
    <table class="meta-surat">
        <tr>
            <td width="90">Nomor</td>
            <td width="15">:</td>
            <td>
                <b><?= $surat['no_resmi'] ? htmlspecialchars($surat['no_resmi']) : '...../'.htmlspecialchars($surat['kode_jenis']).'/'.htmlspecialchars($surat['kode_unit']).'/'.date('Y') ?></b>
            </td>
            <td style="text-align: right;" width="220">
                Cirebon, <?= tgl_indo($surat['tanggal_draft']) ?>
            </td>
        </tr>
        <tr>
            <td>Sifat</td>
            <td>:</td>
            <td colspan="2"><?= ucfirst($surat['sifat']) ?></td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td colspan="2"><?= htmlspecialchars($surat['lampiran']) ?></td>
        </tr>
        <tr>
            <td>Hal</td>
            <td>:</td>
            <td colspan="2"><b><?= htmlspecialchars($surat['perihal']) ?></b></td>
        </tr>
    </table>

    <div style="margin-bottom: 20px; font-size: 12pt;">
        Kepada Yth.<br>
        <b><?= nl2br(htmlspecialchars($surat['tujuan'])) ?></b><br>
        di Tempat
    </div>

    <!-- Isi Naskah Surat -->
    <div class="isi-surat"><?= htmlspecialchars($surat['isi_surat']) ?></div>

    <!-- Tanda Tangan -->
    <div class="ttd-box">
        <?= $jabatan_pejabat ?>,
        <br><br><br><br>
        <b><u><?= htmlspecialchars($nama_pejabat) ?></u></b><br>
        <?php if ($surat['no_resmi']) { ?>
            <span style="font-size: 10pt; color: #1e3a8a;">[Tervalidasi Sistem ARSITA]</span>
        <?php } ?>
    </div>

    <!-- Tembusan -->
    <?php if ($surat['tembusan']) { ?>
        <div class="tembusan-box">
            <b>Tembusan Yth.:</b><br>
            <?= nl2br(htmlspecialchars($surat['tembusan'])) ?>
        </div>
    <?php } ?>
</div>

</body>
</html>
