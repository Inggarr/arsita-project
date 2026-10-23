<?php
ob_start(); // Prevent headers already sent
// public/disposisi/tindak_lanjut.php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';
wajib_login();

$id_dt = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;

// Ambil detail disposisi tujuan
$stmt = $pdo->prepare('
    SELECT dt.*, d.instruksi, d.sifat, d.tenggat_waktu, d.catatan as catatan_pimpinan, d.tanggal_kirim,
           sm.id_surat_masuk, sm.no_surat_asal, sm.perihal, sm.asal_surat, sm.file_surat,
           u_dari.nama as dari_nama, un_dari.nama_unit as dari_unit,
           tl.id_tindak_lanjut, tl.catatan as catatan_tl, tl.lampiran as lampiran_tl, tl.tanggal_update
    FROM disposisi_tujuan dt
    JOIN disposisi d ON dt.id_disposisi = d.id_disposisi
    JOIN surat_masuk sm ON d.id_surat_masuk = sm.id_surat_masuk
    LEFT JOIN users u_dari ON d.dari_user_id = u_dari.id_user
    LEFT JOIN units un_dari ON u_dari.id_unit = un_dari.id_unit
    LEFT JOIN tindak_lanjut tl ON dt.id_disposisi_tujuan = tl.id_disposisi_tujuan
    WHERE dt.id_disposisi_tujuan = ?
');
$stmt->execute([$id_dt]);
$tugas = $stmt->fetch();

if (! $tugas) {
    set_flash('danger', 'Tugas disposisi tidak ditemukan!');
    header('Location: '.BASE_URL.'/disposisi/masuk.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_baru = $_POST['status'] ?? 'diproses';
    $catatan_tl = trim($_POST['catatan_tl'] ?? '');

    try {
        $pdo->beginTransaction();

        // Upload lampiran baru bila ada
        $lampiran_baru = $tugas['lampiran_tl'];
        if (isset($_FILES['lampiran_tl']) && $_FILES['lampiran_tl']['error'] === UPLOAD_ERR_OK) {
            $lampiran_baru = upload_lampiran('lampiran_tl', 'tindak_lanjut');
        }

        // 1. Update status di disposisi_tujuan
        $stmtUp = $pdo->prepare('UPDATE disposisi_tujuan SET status = ? WHERE id_disposisi_tujuan = ?');
        $stmtUp->execute([$status_baru, $id_dt]);

        // 2. Simpan atau update record di tindak_lanjut
        if ($tugas['id_tindak_lanjut']) {
            $stmtTL = $pdo->prepare('
                UPDATE tindak_lanjut 
                SET catatan = ?, lampiran = ?, tanggal_update = CURRENT_TIMESTAMP 
                WHERE id_tindak_lanjut = ?
            ');
            $stmtTL->execute([$catatan_tl, $lampiran_baru, $tugas['id_tindak_lanjut']]);
        } else {
            $stmtTL = $pdo->prepare('
                INSERT INTO tindak_lanjut (id_disposisi_tujuan, catatan, lampiran)
                VALUES (?, ?, ?)
            ');
            $stmtTL->execute([$id_dt, $catatan_tl, $lampiran_baru]);
        }

        $pdo->commit();
        if ($status_baru === 'selesai') {
            set_flash('success', 'Tugas disposisi berhasil diselesaikan dan laporan telah diteruskan ke pimpinan!');
        } elseif ($status_baru === 'diproses') {
            set_flash('success', 'Status disposisi diperbarui menjadi Sedang Diproses.');
        } else {
            set_flash('warning', 'Laporan disimpan, namun status masih Belum Diproses.');
        }
        header('Location: '.BASE_URL.'/disposisi/masuk.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Defaultkan status: Jika masih 'belum_diproses', arahkan default ke 'selesai' agar tidak menggantung
$selected_status = $tugas['status'];
if ($selected_status === 'belum_diproses') {
    $selected_status = 'selesai';
}

$page_title = 'Tindak Lanjut Disposisi';
require_once __DIR__.'/../../includes/header.php';
?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; max-width: 1000px; margin: 0 auto;">
    <!-- Form Tindak Lanjut -->
    <div>
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Tindak Lanjut Instruksi Disposisi</div>
                    <div class="card-subtitle">
                        Perbarui progres pengerjaan dan berikan laporan hasil tindak lanjut
                    </div>
                </div>
                <a href="masuk.php" class="btn btn-secondary btn-sm">Kembali</a>
            </div>

            <?php if ($error) { ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="status">Perbarui Status Pelaksanaan *</label>
                    <select name="status" id="status" required>
                        <option value="selesai" <?= $selected_status === 'selesai' ? 'selected' : '' ?>>[SELESAI] Instruksi Telah Tuntas Dilaksanakan</option>
                        <option value="diproses" <?= $selected_status === 'diproses' ? 'selected' : '' ?>>[PROSES] Sedang Dalam Tahap Pengerjaan</option>
                        <option value="belum_diproses" <?= $selected_status === 'belum_diproses' ? 'selected' : '' ?>>[PENDING] Belum Diproses / Ditunda</option>
                    </select>
                    <small class="text-muted" style="display:block; margin-top: 4px; font-size: 12px;">
                        Pilih <b>Selesai</b> agar tugas dinyatakan tuntas dan pimpinan menerima hasil laporan Anda.
                    </small>
                </div>

                <div class="form-group">
                    <label for="catatan_tl">Laporan Catatan / Keterangan Tindak Lanjut *</label>
                    <textarea name="catatan_tl" id="catatan_tl" rows="4" placeholder="Tuliskan tindakan yang telah diambil atau hasil dari instruksi yang diberikan..." required><?= htmlspecialchars($tugas['catatan_tl'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="lampiran_tl">Unggah Berkas Bukti / Hasil Tindak Lanjut (PDF/JPG/PNG/DOCX)</label>
                    <?php if ($tugas['lampiran_tl']) { ?>
                        <div style="margin-bottom: 8px; font-size: 12.5px;">
                            Lampiran saat ini: 
                            <a href="<?= BASE_URL ?>/../uploads/tindak_lanjut/<?= htmlspecialchars($tugas['lampiran_tl']) ?>" target="_blank" style="color: var(--primary); font-weight: 600;">
                                <?= htmlspecialchars($tugas['lampiran_tl']) ?>
                            </a>
                        </div>
                    <?php } ?>
                    <input type="file" name="lampiran_tl" id="lampiran_tl" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                </div>

                <div class="form-actions">
                    <a href="masuk.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Laporan Tindak Lanjut</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan Arahan Pimpinan & Surat -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Instruksi Pimpinan</div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="font-size: 12px; color: var(--text-muted);">Pemberi Disposisi:</div>
                <div style="font-size: 14px; font-weight: 700;"><?= htmlspecialchars($tugas['dari_nama']) ?></div>
                <div style="font-size: 12px; color: var(--text-secondary);"><?= htmlspecialchars($tugas['dari_unit'] ?: 'UINSSC') ?></div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="font-size: 12px; color: var(--text-muted);">Sifat & Tenggat:</div>
                <div style="margin-top: 4px; display: flex; gap: 6px; align-items: center;">
                    <?= badge_status($tugas['sifat']) ?>
                    <?php if ($tugas['tenggat_waktu']) { ?>
                        <span style="font-size: 12px; color: var(--danger); font-weight: 600;">
                            Batas: <?= tgl_indo($tugas['tenggat_waktu']) ?>
                        </span>
                    <?php } ?>
                </div>
            </div>

            <div style="margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Instruksi:</div>
                <div style="font-size: 13.5px; line-height: 1.5;"><?= nl2br(htmlspecialchars($tugas['instruksi'])) ?></div>
                <?php if ($tugas['catatan_pimpinan']) { ?>
                    <div style="margin-top: 8px; font-size: 12px; color: var(--text-secondary); border-top: 1px dashed var(--border-color); padding-top: 6px;">
                        <i>Catatan: <?= htmlspecialchars($tugas['catatan_pimpinan']) ?></i>
                    </div>
                <?php } ?>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 14px;">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Informasi Surat Masuk:</div>
                <div style="font-size: 13px; font-weight: 700; color: var(--text-primary);"><?= htmlspecialchars($tugas['perihal']) ?></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    No. <?= htmlspecialchars($tugas['no_surat_asal'] ?: '-') ?> (<?= htmlspecialchars($tugas['asal_surat'] ?: '-') ?>)
                </div>
                <?php if ($tugas['file_surat']) { ?>
                    <div style="margin-top: 10px;">
                        <a href="<?= BASE_URL ?>/../uploads/surat_masuk/<?= htmlspecialchars($tugas['file_surat']) ?>" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%;">
                            <i class="bi bi-file-earmark-text"></i> Buka Berkas Surat
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
