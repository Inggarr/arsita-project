<?php

// public/laporan/export_excel.php - Export Rekapitulasi Persuratan ke Excel / CSV
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
    SELECT sm.no_surat_asal, sm.tanggal_surat, sm.tanggal_terima, sm.asal_surat, sm.perihal,
           ks.nama_kategori, sm.status_arsip, u.nama_unit
    FROM surat_masuk sm
    LEFT JOIN kategori_surat ks ON sm.kategori_id = ks.id_kategori
    LEFT JOIN units u ON sm.id_unit = u.id_unit
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
    SELECT ns.nomor_surat, sk.tanggal_draft, sk.tujuan, sk.perihal, sk.sifat,
           js.nama_jenis, ks.nama_kategori, sk.status_surat, u.nama_unit
    FROM surat_keluar sk
    LEFT JOIN nomor_surat ns ON sk.id_surat_keluar = ns.id_surat_keluar
    LEFT JOIN jenis_surat js ON sk.jenis_surat_id = js.id_jenis_surat
    LEFT JOIN kategori_surat ks ON sk.kategori_id = ks.id_kategori
    LEFT JOIN units u ON sk.id_unit = u.id_unit
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

$filename = "Rekap_Surat_ARSITA_{$bulanStr}_{$tahun}.csv";
header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

$output = fopen('php://output', 'w');
// UTF-8 BOM
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['REKAPITULASI PERSURATAN - ARSITA UINSSC']);
fputcsv($output, ["Periode: $bulanStr $tahun", 'Unit: '.$_SESSION['nama_unit']]);
fputcsv($output, []);

// Bagian 1: Surat Masuk
fputcsv($output, ['=== DATA SURAT MASUK ===']);
fputcsv($output, ['No', 'No. Surat Asal', 'Tanggal Surat', 'Tanggal Terima', 'Pengirim / Asal', 'Perihal', 'Kategori', 'Unit', 'Status']);
$no = 1;
foreach ($listSM as $r) {
    fputcsv($output, [
        $no++,
        $r['no_surat_asal'] ?: '-',
        $r['tanggal_surat'] ?: '-',
        $r['tanggal_terima'] ?: '-',
        $r['asal_surat'],
        $r['perihal'],
        $r['nama_kategori'] ?: 'Umum',
        $r['nama_unit'],
        $r['status_arsip'],
    ]);
}

fputcsv($output, []);
fputcsv($output, ['=== DATA SURAT KELUAR ===']);
fputcsv($output, ['No', 'Nomor Surat Resmi', 'Tanggal Draft', 'Tujuan', 'Perihal', 'Jenis', 'Kategori', 'Sifat', 'Unit', 'Status']);
$no = 1;
foreach ($listSK as $r) {
    fputcsv($output, [
        $no++,
        $r['nomor_surat'] ?: '(Draft / Belum Final)',
        $r['tanggal_draft'] ?: '-',
        $r['tujuan'],
        $r['perihal'],
        $r['nama_jenis'] ?: '-',
        $r['nama_kategori'] ?: 'Umum',
        $r['sifat'],
        $r['nama_unit'],
        $r['status_surat'],
    ]);
}

fclose($output);
exit;
