<?php
// public/persetujuan/proses.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_role(['pimpinan', 'admin_rektorat']);

$id_acc = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'] ?? 0;
$role = $_SESSION['role'] ?? '';

// Query data ACC
$stmt = $pdo->prepare('
    SELECT ask.*, sk.id_surat_keluar, sk.perihal, sk.tujuan, sk.sifat, sk.lampiran,
           sk.isi_surat, sk.tembusan, sk.tanggal_draft, sk.dibuat_oleh, sk.id_unit, sk.jenis_surat_id, sk.kategori_id,
           js.nama_jenis, js.kode_jenis, ks.nama_kategori,
           u.nama_unit, u.kode_unit, u.level as level_unit,
           us.nama as nama_pembuat
    FROM acc_surat_keluar ask
    JOIN surat_keluar sk ON ask.id_surat_keluar = sk.id_surat_keluar
    LEFT JOIN jenis_surat js ON sk.jenis_surat_id = js.id_jenis_surat
    LEFT JOIN kategori_surat ks ON sk.kategori_id = ks.id_kategori
    LEFT JOIN units u ON sk.id_unit = u.id_unit
    LEFT JOIN users us ON sk.dibuat_oleh = us.id_user
    WHERE ask.id_acc = ?
');
$stmt->execute([$id_acc]);
$data = $stmt->fetch();

if (! $data) {
    set_flash('danger', 'Data persetujuan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// Hak akses
if ($role !== 'admin_rektorat' && $data['disetujui_oleh'] != $id_user) {
    set_flash('danger', 'Akses ditolak: Surat ini bukan kewenangan persetujuan Anda.');
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keputusan = $_POST['keputusan'] ?? '';
    $catatan = trim($_POST['catatan_penolakan'] ?? '');

    if ($keputusan === 'tolak' && empty($catatan)) {
        $error = 'Alasan penolakan / catatan revisi wajib diisi!';
    } elseif (in_array($keputusan, ['setuju', 'tolak'])) {
        try {
            $pdo->beginTransaction();

            $id_sk = $data['id_surat_keluar'];
            $id_unit_surat = $data['id_unit'];
            $id_jenis_s = $data['jenis_surat_id'];
            $levelUnit = $data['level_unit'];
            $tahap_ke = (int) $data['tahap_ke'];

            if ($keputusan === 'tolak') {
                // Update ACC menjadi ditolak
                $stmtAccUp = $pdo->prepare("
                    UPDATE acc_surat_keluar 
                    SET status = 'ditolak', catatan_penolakan = ?, tanggal = CURRENT_TIMESTAMP 
                    WHERE id_acc = ?
                ");
                $stmtAccUp->execute([$catatan, $id_acc]);

                // Update surat keluar jadi status 'revisi'
                $pdo->prepare("UPDATE surat_keluar SET status_surat = 'revisi' WHERE id_surat_keluar = ?")
                    ->execute([$id_sk]);

                $pdo->commit();
                set_flash('success', 'Surat telah ditolak dengan catatan revisi untuk staf pembuat.');
                header('Location: '.BASE_URL.'/persetujuan/index.php');
                exit;
            } else {
                // KEPUTUSAN SETUJU
                $stmtAccUp = $pdo->prepare("
                    UPDATE acc_surat_keluar 
                    SET status = 'disetujui', tanggal = CURRENT_TIMESTAMP 
                    WHERE id_acc = ?
                ");
                $stmtAccUp->execute([$id_acc]);

                $is_final = false;

                // Logika Berjenjang (Readme Bagian 6 & 9.4)
                if ($levelUnit === 'rektorat') {
                    if ($tahap_ke === 1) {
                        // Lanjut ke Tahap 2 (Kabag)
                        $stmtKabag = $pdo->prepare("
                            SELECT id_user FROM users 
                            WHERE id_unit = ? AND username = 'kabag' LIMIT 1
                        ");
                        $stmtKabag->execute([$id_unit_surat]);
                        $next_pejabat = $stmtKabag->fetchColumn();

                        // Fallback jika tidak ada username 'kabag'
                        if (! $next_pejabat) {
                            $next_pejabat = $pdo->query("SELECT id_user FROM users WHERE id_role = (SELECT id_role FROM roles WHERE nama_role = 'pimpinan') AND id_user != $id_user LIMIT 1")->fetchColumn();
                        }

                        $pdo->prepare("INSERT INTO acc_surat_keluar (id_surat_keluar, tahap_ke, disetujui_oleh, status) VALUES (?, 2, ?, 'menunggu')")
                            ->execute([$id_sk, $next_pejabat]);

                        set_flash('success', 'Surat disetujui Tahap 1 (Kasubbag). Surat otomatis diteruskan ke Tahap 2 (Kabag).');
                    } elseif ($tahap_ke === 2) {
                        // Lanjut ke Tahap 3 (Rektor)
                        $stmtRektor = $pdo->prepare("
                            SELECT id_user FROM users 
                            WHERE id_unit = ? AND username = 'rektor' LIMIT 1
                        ");
                        $stmtRektor->execute([$id_unit_surat]);
                        $next_pejabat = $stmtRektor->fetchColumn();

                        if (! $next_pejabat) {
                            $next_pejabat = $pdo->query("SELECT id_user FROM users WHERE id_role = (SELECT id_role FROM roles WHERE nama_role = 'pimpinan') AND id_user != $id_user LIMIT 1")->fetchColumn();
                        }

                        $pdo->prepare("INSERT INTO acc_surat_keluar (id_surat_keluar, tahap_ke, disetujui_oleh, status) VALUES (?, 3, ?, 'menunggu')")
                            ->execute([$id_sk, $next_pejabat]);

                        set_flash('success', 'Surat disetujui Tahap 2 (Kabag). Surat otomatis diteruskan ke Tahap 3 (Rektor) untuk persetujuan akhir.');
                    } else {
                        // Tahap 3 (Rektor) = TAHAP AKHIR
                        $is_final = true;
                    }
                } else {
                    // Fakultas atau Prodi: 1 Tahap langsung final
                    $is_final = true;
                }

                // JIKA TAHAP AKHIR: GENERATE NOMOR OTOMATIS
                if ($is_final) {
                    $id_kategori_s = $data['kategori_id'] ?? null;
                    $nomor_resmi = generate_nomor_surat($pdo, $id_sk, $id_unit_surat, $id_jenis_s, null, $id_kategori_s);
                    $pdo->prepare("UPDATE surat_keluar SET status_surat = 'disetujui' WHERE id_surat_keluar = ?")
                        ->execute([$id_sk]);

                    set_flash('success', "Surat resmi telah DISETUJUI FINAL! Nomor surat otomatis berhasil digenerate: <b>$nomor_resmi</b>");
                }

                $pdo->commit();
                header('Location: '.BASE_URL.'/persetujuan/index.php');
                exit;
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal memproses persetujuan: '.$e->getMessage();
        }
    }
}

$page_title = 'Tinjauan Persetujuan Surat';
require_once __DIR__.'/../../includes/header.php';
?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; max-width: 1000px; margin: 0 auto;">
    <!-- Bagian Kiri: Tinjauan Isi Naskah Surat -->
    <div>
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Tinjauan Naskah Surat</div>
                    <div class="card-subtitle">
                        Persetujuan Tahap <?= $data['tahap_ke'] ?> &bull; Unit: <?= htmlspecialchars($data['nama_unit']) ?>
                    </div>
                </div>
                <a href="index.php" class="btn btn-secondary btn-sm">Kembali</a>
            </div>

            <?php if ($error) { ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php } ?>

            <table style="margin-bottom: 20px;">
                <tr>
                    <td width="150" class="text-muted">Perihal</td>
                    <td><b><?= htmlspecialchars($data['perihal']) ?></b></td>
                </tr>
                <tr>
                    <td class="text-muted">Tujuan</td>
                    <td><b><?= htmlspecialchars($data['tujuan']) ?></b></td>
                </tr>
                <tr>
                    <td class="text-muted">Jenis / Kategori</td>
                    <td><?= htmlspecialchars($data['nama_jenis']) ?> / <?= htmlspecialchars($data['nama_kategori'] ?: 'Umum') ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Sifat / Lampiran</td>
                    <td><?= badge_status($data['sifat']) ?> &bull; Lampiran: <?= htmlspecialchars($data['lampiran']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal Draft</td>
                    <td><?= tgl_indo($data['tanggal_draft']) ?> oleh <?= htmlspecialchars($data['nama_pembuat']) ?></td>
                </tr>
            </table>

            <div style="margin-top: 16px;">
                <div style="font-size: 13px; font-weight: 700; margin-bottom: 8px;">Naskah Surat:</div>
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; font-size: 14px; line-height: 1.7; white-space: pre-wrap; font-family: 'Times New Roman', serif;">
<?= htmlspecialchars($data['isi_surat']) ?>
                </div>

                <?php if ($data['tembusan']) { ?>
                    <div style="margin-top: 12px; font-size: 12.5px; color: var(--text-secondary);">
                        <b>Tembusan:</b><br><?= nl2br(htmlspecialchars($data['tembusan'])) ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Bagian Kanan: Form Eksekusi ACC -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Keputusan Pimpinan</div>
            </div>

            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 16px;">
                        Silakan periksa naskah dinas di samping. Jika disetujui pada tahap final, sistem akan langsung <b>menerbitkan nomor surat otomatis secara permanen</b>.
                    </p>

                    <div style="margin-bottom: 16px;">
                        <label for="catatan_penolakan">Catatan Revisi / Alasan Penolakan (Wajib jika Ditolak):</label>
                        <textarea name="catatan_penolakan" id="catatan_penolakan" rows="3" placeholder="Contoh: Perihal mohon disesuaikan dengan nomenklatur baru..."></textarea>
                    </div>

                    <div class="action-panel">
                        <button type="submit" name="keputusan" value="setuju" class="btn btn-success" style="width: 100%; padding: 12px;" onclick="return confirm('Apakah Anda yakin menyetujui surat ini?')">
                            <i class="bi bi-check-circle-fill"></i> Setujui Surat Keluar
                        </button>

                        <button type="submit" name="keputusan" value="tolak" class="btn btn-danger" style="width: 100%; padding: 12px;" onclick="return confirm('Apakah Anda yakin menolak surat ini dan meminta revisi?')">
                            <i class="bi bi-x-circle-fill"></i> Tolak & Minta Revisi
                        </button>
                    </div>
                </div>
            </form>

            <div style="border-top: 1px solid var(--border-color); padding-top: 14px;">
                <a href="<?= BASE_URL ?>/surat_keluar/cetak.php?id=<?= $data['id_surat_keluar'] ?>" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%;">
                    <i class="bi bi-printer-fill"></i> Pratinjau Dokumen Format A4
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
