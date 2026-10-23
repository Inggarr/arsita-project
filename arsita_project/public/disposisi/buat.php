<?php
ob_start(); // Prevent headers already sent
// public/disposisi/buat.php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;

// Hanya pimpinan atau admin_rektorat yang berhak membuat disposisi
wajib_role(['pimpinan', 'admin_rektorat']);

$id_surat = (int) ($_GET['id_surat'] ?? 0);

// Ambil data surat masuk
$stmt = $pdo->prepare('
    SELECT sm.*, u.nama_unit, u.level, ks.nama_kategori
    FROM surat_masuk sm
    LEFT JOIN units u ON sm.id_unit = u.id_unit
    LEFT JOIN kategori_surat ks ON sm.kategori_id = ks.id_kategori
    WHERE sm.id_surat_masuk = ?
');
$stmt->execute([$id_surat]);
$surat = $stmt->fetch();

if (! $surat) {
    set_flash('danger', 'Surat masuk tidak ditemukan!');
    header('Location: '.BASE_URL.'/surat_masuk/index.php');
    exit;
}

// Ambil daftar tujuan yang dapat dipilih:
// 1. Individu / Staff di unit ini atau bawahan
$users_list = $pdo->prepare("
    SELECT u.id_user, u.nama, r.nama_role, un.nama_unit
    FROM users u
    JOIN roles r ON u.id_role = r.id_role
    JOIN units un ON u.id_unit = un.id_unit
    WHERE u.status = 'aktif' AND u.id_user != ?
    ORDER BY un.id_unit ASC, u.nama ASC
");
$users_list->execute([$id_user]);
$penerima_users = $users_list->fetchAll();

// 2. Unit bawahan (contoh: Rektorat bisa disposisi ke Fakultas/Prodi, Fakultas bisa ke Prodi)
$current_level = $surat['level'] ?? 'rektorat';
if ($current_level === 'rektorat') {
    $units_bawahan = $pdo->query("SELECT * FROM units WHERE level IN ('fakultas', 'prodi') AND status = 'aktif' ORDER BY level ASC, nama_unit ASC")->fetchAll();
} elseif ($current_level === 'fakultas') {
    $units_bawahan = $pdo->prepare("SELECT * FROM units WHERE id_parent = ? AND status = 'aktif' ORDER BY nama_unit ASC");
    $units_bawahan->execute([$id_unit]);
    $units_bawahan = $units_bawahan->fetchAll();
} else {
    $units_bawahan = []; // Prodi biasanya langsung ke individu (dosen/staff)
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tujuan_tipe = $_POST['tujuan_tipe'] ?? 'individu';
    $ke_user_id = ! empty($_POST['ke_user_id']) ? (int) $_POST['ke_user_id'] : null;
    $ke_unit_id = ! empty($_POST['ke_unit_id']) ? (int) $_POST['ke_unit_id'] : null;
    $instruksi = trim($_POST['instruksi'] ?? '');
    $sifat = $_POST['sifat'] ?? 'biasa';
    $tenggat_waktu = ! empty($_POST['tenggat_waktu']) ? $_POST['tenggat_waktu'] : null;
    $catatan = trim($_POST['catatan'] ?? '');

    if (empty($instruksi)) {
        $error = 'Instruksi disposisi wajib diisi!';
    } elseif ($tujuan_tipe === 'individu' && ! $ke_user_id) {
        $error = 'Penerima individu wajib dipilih!';
    } elseif ($tujuan_tipe === 'unit' && ! $ke_unit_id) {
        $error = 'Unit bawahan tujuan wajib dipilih!';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Simpan induk disposisi
            $stmtDisp = $pdo->prepare('
                INSERT INTO disposisi (id_surat_masuk, dari_user_id, instruksi, sifat, tenggat_waktu, catatan)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stmtDisp->execute([$id_surat, $id_user, $instruksi, $sifat, $tenggat_waktu, $catatan]);
            $id_disposisi = $pdo->lastInsertId();

            // 2. Simpan disposisi_tujuan
            $stmtTujuan = $pdo->prepare("
                INSERT INTO disposisi_tujuan (id_disposisi, tujuan_tipe, ke_user_id, ke_unit_id, status)
                VALUES (?, ?, ?, ?, 'belum_diproses')
            ");
            $stmtTujuan->execute([
                $id_disposisi,
                $tujuan_tipe,
                ($tujuan_tipe === 'individu' ? $ke_user_id : null),
                ($tujuan_tipe === 'unit' ? $ke_unit_id : null),
            ]);
            $id_disposisi_tujuan = $pdo->lastInsertId();

            // 3. Update status surat masuk asal menjadi 'didisposisikan'
            $stmtUpSm = $pdo->prepare("UPDATE surat_masuk SET status_arsip = 'didisposisikan' WHERE id_surat_masuk = ?");
            $stmtUpSm->execute([$id_surat]);

            // 4. Logika Berjenjang Antarunit (Readme Bagian 8.1):
            // Jika tujuan adalah UNIT, otomatis buat record surat masuk baru di unit bawahan tersebut!
            if ($tujuan_tipe === 'unit' && $ke_unit_id) {
                $stmtNewSM = $pdo->prepare("
                    INSERT INTO surat_masuk (
                        no_surat_asal, tanggal_surat, tanggal_terima, asal_surat,
                        perihal, kategori_id, jenis_surat_id, id_unit, file_surat,
                        keterangan, asal_disposisi_id, status_arsip, dicatat_oleh
                    ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, 'belum_ditinjau', ?)
                ");
                $asal_keterangan = 'Disposisi dari '.($surat['nama_unit'] ?? 'Unit Atasan').'. Instruksi: '.$instruksi;
                $stmtNewSM->execute([
                    $surat['no_surat_asal'],
                    $surat['tanggal_surat'],
                    $surat['asal_surat'].' (via '.$surat['nama_unit'].')',
                    $surat['perihal'],
                    $surat['kategori_id'],
                    $surat['jenis_surat_id'],
                    $ke_unit_id,
                    $surat['file_surat'],
                    $asal_keterangan,
                    $id_disposisi_tujuan,
                    $id_user,
                ]);
            }

            $pdo->commit();
            set_flash('success', 'Disposisi berhasil diterbitkan dan dikirimkan ke tujuan!');
            header('Location: '.BASE_URL.'/surat_masuk/detail.php?id='.$id_surat);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan: '.$e->getMessage();
        }
    }
}

$page_title = 'Buat Disposisi Surat';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="card" style="max-width: 850px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title">Lembar Disposisi Surat Masuk</div>
            <div class="card-subtitle">
                Terbitkan instruksi disposisi kepada pejabat/staf atau delegasikan ke unit bawahan
            </div>
        </div>
        <a href="<?= BASE_URL ?>/surat_masuk/detail.php?id=<?= $id_surat ?>" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <!-- Ringkasan Surat yang didisposisikan -->
    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; margin-bottom: 24px;">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px;">Surat yang Didisposisikan:</div>
        <div style="font-size: 15px; font-weight: 700; color: var(--primary); margin-bottom: 4px;"><?= htmlspecialchars($surat['perihal']) ?></div>
        <div style="font-size: 13px; color: var(--text-secondary);">
            No. Surat: <b><?= htmlspecialchars($surat['no_surat_asal'] ?: '-') ?></b> &bull; 
            Asal: <b><?= htmlspecialchars($surat['asal_surat'] ?: '-') ?></b> &bull; 
            Tgl Terima: <?= tgl_indo($surat['tanggal_terima']) ?>
        </div>
        <?php if ($surat['file_surat']) { ?>
            <div style="margin-top: 8px;">
                <a href="<?= BASE_URL ?>/../uploads/surat_masuk/<?= htmlspecialchars($surat['file_surat']) ?>" target="_blank" style="font-size: 12px; color: var(--primary); font-weight: 600;">
                    👁️ Buka File Scan Surat
                </a>
            </div>
        <?php } ?>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST">
        <div class="form-grid">
            <div class="form-group full-width">
                <label>Pilih Tipe Tujuan Disposisi *</label>
                <div style="display: flex; gap: 24px; margin-top: 6px;">
                    <label style="font-weight: normal; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="tujuan_tipe" value="individu" checked onchange="toggleTujuan(this.value)">
                        <b>Pejabat / Penanggung Jawab Bidang</b> (Pimpinan / Staf Bidang Terkait)
                    </label>
                    <?php if (! empty($units_bawahan)) { ?>
                    <label style="font-weight: normal; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="tujuan_tipe" value="unit" onchange="toggleTujuan(this.value)">
                        <b>Unit Kerja Bawahan</b> (Fakultas / Program Studi)
                    </label>
                    <?php } ?>
                </div>
            </div>

            <!-- Pilihan Pejabat / Staf Bidang -->
            <div class="form-group full-width" id="box_individu">
                <label for="ke_user_id">Pilih Pejabat / Staf Pengelola Bidang *</label>
                <select name="ke_user_id" id="ke_user_id">
                    <option value="">-- Pilih Pejabat / Staf Bidang --</option>
                    <?php foreach ($penerima_users as $u) { ?>
                        <option value="<?= $u['id_user'] ?>">
                            <?= htmlspecialchars($u['nama']) ?> — <?= htmlspecialchars($u['nama_role']) ?> (<?= htmlspecialchars($u['nama_unit']) ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Pilihan Unit Bawahan -->
            <?php if (! empty($units_bawahan)) { ?>
            <div class="form-group full-width" id="box_unit" style="display: none;">
                <label for="ke_unit_id">Pilih Unit Kampus Bawahan *</label>
                <select name="ke_unit_id" id="ke_unit_id">
                    <option value="">-- Pilih Unit Bawahan --</option>
                    <?php foreach ($units_bawahan as $ub) { ?>
                        <option value="<?= $ub['id_unit'] ?>">
                            [<?= strtoupper($ub['level']) ?>] <?= htmlspecialchars($ub['nama_unit']) ?> (<?= htmlspecialchars($ub['kode_unit']) ?>)
                        </option>
                    <?php } ?>
                </select>
                <small style="color: var(--text-muted); display: block; margin-top: 4px;">
                    Surat akan didisposisikan ke unit ini, dan otomatis muncul di daftar Surat Masuk pimpinan unit yang bersangkutan.
                </small>
            </div>
            <?php } ?>

            <div class="form-group">
                <label for="sifat">Sifat Disposisi *</label>
                <select name="sifat" id="sifat" required>
                    <option value="biasa">Biasa</option>
                    <option value="segera">Segera</option>
                    <option value="sangat_segera">Sangat Segera</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tenggat_waktu">Tenggat Waktu Pelaksanaan (Batas Deadline)</label>
                <input type="date" id="tenggat_waktu" name="tenggat_waktu">
            </div>

            <div class="form-group full-width">
                <label for="instruksi">Instruksi Pimpinan *</label>
                <textarea id="instruksi" name="instruksi" rows="3" placeholder="Contoh: Mohon dipelajari dan ditindaklanjuti sesuai ketentuan, laporkan hasilnya..." required></textarea>
            </div>

            <div class="form-group full-width">
                <label for="catatan">Catatan / Arahan Tambahan (Opsional)</label>
                <textarea id="catatan" name="catatan" rows="2" placeholder="Keterangan pendukung instruksi bila ada..."></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>/surat_masuk/detail.php?id=<?= $id_surat ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Terbitkan & Kirim Disposisi</button>
        </div>
    </form>
</div>

<script>
function toggleTujuan(tipe) {
    const boxIndividu = document.getElementById('box_individu');
    const boxUnit = document.getElementById('box_unit');
    if (tipe === 'unit') {
        if (boxIndividu) boxIndividu.style.display = 'none';
        if (boxUnit) boxUnit.style.display = 'block';
    } else {
        if (boxIndividu) boxIndividu.style.display = 'block';
        if (boxUnit) boxUnit.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
