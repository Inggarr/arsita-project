<?php

require_once __DIR__.'/../config/database.php';

$passHash = password_hash('password123', PASSWORD_BCRYPT);

// Ambil id role
$rolesMap = [];
foreach ($pdo->query('SELECT id_role, nama_role FROM roles')->fetchAll() as $r) {
    $rolesMap[$r['nama_role']] = $r['id_role'];
}

// Ambil id unit
$unitMap = [];
foreach ($pdo->query('SELECT id_unit, kode_unit FROM units')->fetchAll() as $u) {
    $unitMap[$u['kode_unit']] = $u['id_unit'];
}

$sampleUsers = [
    ['Admin Rektorat', 'admin_rektorat', $passHash, 'admin@uinssc.ac.id', $unitMap['UN.14'] ?? 1, $rolesMap['admin_rektorat'] ?? 1],
    ['Staff TU Rektorat', 'staff_rektorat', $passHash, 'tu_rektorat@uinssc.ac.id', $unitMap['UN.14'] ?? 1, $rolesMap['staff_tu'] ?? 3],
    ['Prof. Dr. Rektor (Pimpinan)', 'rektor', $passHash, 'rektor@uinssc.ac.id', $unitMap['UN.14'] ?? 1, $rolesMap['pimpinan'] ?? 4],
    ['Kabag Rektorat (Pimpinan)', 'kabag', $passHash, 'kabag@uinssc.ac.id', $unitMap['UN.14'] ?? 1, $rolesMap['pimpinan'] ?? 4],
    ['Kasubbag Rektorat (Pimpinan)', 'kasubbag', $passHash, 'kasubbag@uinssc.ac.id', $unitMap['UN.14'] ?? 1, $rolesMap['pimpinan'] ?? 4],
    ['Staff TU Fakultas', 'staff_fakultas', $passHash, 'tu_fitk@uinssc.ac.id', $unitMap['Fak.01'] ?? 2, $rolesMap['staff_tu'] ?? 3],
    ['Dekan FITK (Pimpinan)', 'dekan', $passHash, 'dekan_fitk@uinssc.ac.id', $unitMap['Fak.01'] ?? 2, $rolesMap['pimpinan'] ?? 4],
    ['Staff TU Prodi', 'staff_prodi', $passHash, 'tu_ti@uinssc.ac.id', $unitMap['Prodi.03'] ?? 3, $rolesMap['staff_tu'] ?? 3],
    ['Kajur Informatika (Pimpinan)', 'kajur', $passHash, 'kajur_ti@uinssc.ac.id', $unitMap['Prodi.03'] ?? 3, $rolesMap['pimpinan'] ?? 4],
];

$stmt = $pdo->prepare("INSERT INTO users (nama, username, password, email, id_unit, id_role, status) 
                       VALUES (?, ?, ?, ?, ?, ?, 'aktif') 
                       ON DUPLICATE KEY UPDATE nama = VALUES(nama), password = VALUES(password), id_unit = VALUES(id_unit), id_role = VALUES(id_role)");

foreach ($sampleUsers as $su) {
    $stmt->execute($su);
}

// Cek dan buat default format_nomor
$cekFormat = $pdo->query('SELECT COUNT(*) FROM format_nomor')->fetchColumn();
if ($cekFormat == 0) {
    $jenisList = $pdo->query('SELECT id_jenis_surat, kode_jenis FROM jenis_surat')->fetchAll();
    $unitsList = $pdo->query('SELECT id_unit, kode_unit FROM units')->fetchAll();

    $stmtFmt = $pdo->prepare('INSERT INTO format_nomor (id_unit, id_jenis_surat, format_nomor, contoh, aktif) VALUES (?, ?, ?, ?, 1)');
    foreach ($unitsList as $u) {
        foreach ($jenisList as $j) {
            $format = '{nomor}/'.$j['kode_jenis'].'/'.$u['kode_unit'].'/{tahun}';
            $contoh = '001/'.$j['kode_jenis'].'/'.$u['kode_unit'].'/'.date('Y');
            $stmtFmt->execute([$u['id_unit'], $j['id_jenis_surat'], $format, $contoh]);
        }
    }
}

echo 'Semua user sampel dan format nomor siap!';
