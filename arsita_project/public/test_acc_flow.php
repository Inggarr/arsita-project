<?php

// test_acc_flow.php - Otomatisasi pengujian alur ACC Rektorat 3 tahap
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';

echo '<h2>Simulasi Alur Persetujuan (ACC) 3 Tahap Rektorat</h2>';

// Cari surat keluar yang sedang menunggu ACC
$sk = $pdo->query("
    SELECT sk.*, ask.id_acc, ask.tahap_ke, ask.disetujui_oleh, u.nama as nama_pejabat, u.username
    FROM surat_keluar sk
    JOIN acc_surat_keluar ask ON sk.id_surat_keluar = ask.id_surat_keluar
    LEFT JOIN users u ON ask.disetujui_oleh = u.id_user
    WHERE sk.status_surat = 'menunggu_acc' AND ask.status = 'menunggu'
    ORDER BY sk.id_surat_keluar DESC, ask.tahap_ke ASC
    LIMIT 1
")->fetch();

if (! $sk) {
    exit('Tidak ada surat keluar yang sedang menunggu ACC.');
}

echo "Surat ditemukan: <b>{$sk['perihal']}</b> (ID: {$sk['id_surat_keluar']})<br>";
echo "Tahap saat ini: <b>Tahap {$sk['tahap_ke']}</b> oleh {$sk['nama_pejabat']} (@{$sk['username']})<br><hr>";

// Eksekusi Tahap 1: Kasubbag ACC
$id_sk = $sk['id_surat_keluar'];
$id_acc = $sk['id_acc'];

$pdo->beginTransaction();
$pdo->prepare("UPDATE acc_surat_keluar SET status = 'disetujui', tanggal = NOW() WHERE id_acc = ?")->execute([$id_acc]);

// Masuk Tahap 2: Kabag
$id_kabag = $pdo->query("SELECT id_user FROM users WHERE username = 'kabag'")->fetchColumn();
$pdo->prepare("INSERT INTO acc_surat_keluar (id_surat_keluar, tahap_ke, disetujui_oleh, status) VALUES (?, 2, ?, 'menunggu')")
    ->execute([$id_sk, $id_kabag]);
$id_acc_kabag = $pdo->lastInsertId();
$pdo->commit();
echo '✅ <b>Tahap 1 (Kasubbag)</b> berhasil disetujui! Diteruskan ke Tahap 2 (Kabag).<br>';

// Eksekusi Tahap 2: Kabag ACC
$pdo->beginTransaction();
$pdo->prepare("UPDATE acc_surat_keluar SET status = 'disetujui', tanggal = NOW() WHERE id_acc = ?")->execute([$id_acc_kabag]);

// Masuk Tahap 3: Rektor
$id_rektor = $pdo->query("SELECT id_user FROM users WHERE username = 'rektor'")->fetchColumn();
$pdo->prepare("INSERT INTO acc_surat_keluar (id_surat_keluar, tahap_ke, disetujui_oleh, status) VALUES (?, 3, ?, 'menunggu')")
    ->execute([$id_sk, $id_rektor]);
$id_acc_rektor = $pdo->lastInsertId();
$pdo->commit();
echo '✅ <b>Tahap 2 (Kabag)</b> berhasil disetujui! Diteruskan ke Tahap 3 (Rektor).<br>';

// Eksekusi Tahap 3 (Final): Rektor ACC -> GENERATE NOMOR OTOMATIS
$pdo->beginTransaction();
$pdo->prepare("UPDATE acc_surat_keluar SET status = 'disetujui', tanggal = NOW() WHERE id_acc = ?")->execute([$id_acc_rektor]);

// Panggil logika inti penomoran otomatis ARSITA
$nomor_resmi = generate_nomor_surat($pdo, $id_sk, $sk['id_unit'], $sk['jenis_surat_id'], null, $sk['kategori_id'] ?? null);
$pdo->prepare("UPDATE surat_keluar SET status_surat = 'disetujui' WHERE id_surat_keluar = ?")->execute([$id_sk]);
$pdo->commit();

echo '🎉 <b>Tahap 3 (Rektor) DISETUJUI FINAL!</b><br>';
echo "🔥 <b>Nomor Surat Resmi Otomatis Terbit:</b> <span style='font-size: 20px; color: blue;'>{$nomor_resmi}</span><br>";
