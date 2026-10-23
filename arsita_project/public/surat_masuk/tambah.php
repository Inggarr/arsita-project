<?php
ob_start(); // Prevent headers already sent
// public/surat_masuk/tambah.php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;

wajib_role(['admin_rektorat', 'staff_tu']);

$kategori_list = $pdo->query('SELECT * FROM kategori_surat ORDER BY nama_kategori ASC')->fetchAll();
$jenis_list = $pdo->query('SELECT * FROM jenis_surat ORDER BY nama_jenis ASC')->fetchAll();

// Daftar unit (jika admin_rektorat bisa memilih unit tujuan pencatatan)
$units_list = [];
if ($role === 'admin_rektorat') {
    $units_list = $pdo->query("SELECT * FROM units WHERE status = 'aktif' ORDER BY level ASC, nama_unit ASC")->fetchAll();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_surat_asal = trim($_POST['no_surat_asal'] ?? '');
    $tanggal_surat = $_POST['tanggal_surat'] ?? null;
    $tanggal_terima = $_POST['tanggal_terima'] ?? date('Y-m-d');
    $asal_surat = trim($_POST['asal_surat'] ?? '');
    $perihal = trim($_POST['perihal'] ?? '');
    $kategori_id = ! empty($_POST['kategori_id']) ? (int) $_POST['kategori_id'] : null;
    $jenis_surat_id = ! empty($_POST['jenis_surat_id']) ? (int) $_POST['jenis_surat_id'] : null;
    $target_unit_id = ($role === 'admin_rektorat' && ! empty($_POST['id_unit'])) ? (int) $_POST['id_unit'] : $id_unit;
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (empty($perihal) || empty($asal_surat) || empty($no_surat_asal)) {
        $error = 'Nomor surat, pengirim/asal surat, dan perihal wajib diisi!';
    } else {
        try {
            $filename = null;
            if (isset($_FILES['file_scan']) && $_FILES['file_scan']['error'] === UPLOAD_ERR_OK) {
                $filename = upload_lampiran('file_scan', 'surat_masuk');
            }

            $stmt = $pdo->prepare("
                INSERT INTO surat_masuk (
                    no_surat_asal, tanggal_surat, tanggal_terima, asal_surat, 
                    perihal, kategori_id, jenis_surat_id, id_unit, file_surat, 
                    keterangan, status_arsip, dicatat_oleh
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'belum_ditinjau', ?)
            ");
            $stmt->execute([
                $no_surat_asal, $tanggal_surat, $tanggal_terima, $asal_surat,
                $perihal, $kategori_id, $jenis_surat_id, $target_unit_id, $filename,
                $keterangan, $id_user,
            ]);

            set_flash('success', 'Surat masuk berhasil dicatat!');
            header('Location: '.BASE_URL.'/surat_masuk/index.php');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$page_title = 'Pencatatan Surat Masuk';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="card" style="max-width: 850px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title">Form Input Surat Masuk</div>
            <div class="card-subtitle">
                Catat identitas fisik surat masuk dan unggah file hasil scan (PDF/Gambar)
            </div>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <?php if ($role === 'admin_rektorat') { ?>
                <div class="form-group full-width">
                    <label for="id_unit">Unit Penerima Surat *</label>
                    <select name="id_unit" id="id_unit" required>
                        <?php foreach ($units_list as $u) { ?>
                            <option value="<?= $u['id_unit'] ?>" <?= $u['id_unit'] == $id_unit ? 'selected' : '' ?>>
                                [<?= strtoupper($u['level']) ?>] <?= htmlspecialchars($u['nama_unit']) ?> (<?= htmlspecialchars($u['kode_unit']) ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <div class="form-group">
                <label for="no_surat_asal">Nomor Surat Asal (dari Pengirim) *</label>
                <input type="text" id="no_surat_asal" name="no_surat_asal" placeholder="Misal: 104/KEMENAG/IX/2026" required>
            </div>

            <div class="form-group">
                <label for="asal_surat">Asal Surat / Nama Instansi Pengirim *</label>
                <input type="text" id="asal_surat" name="asal_surat" placeholder="Misal: Biro Kepegawaian Kemenag RI" required>
            </div>

            <div class="form-group">
                <label for="tanggal_surat">Tanggal Surat Tertulis</label>
                <input type="date" id="tanggal_surat" name="tanggal_surat" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label for="tanggal_terima">Tanggal Diterima Unit *</label>
                <input type="date" id="tanggal_terima" name="tanggal_terima" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label for="kategori_id">Kategori Surat</label>
                <select name="kategori_id" id="kategori_id">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori_list as $k) { ?>
                        <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="jenis_surat_id">Jenis Surat</label>
                <select name="jenis_surat_id" id="jenis_surat_id">
                    <option value="">-- Pilih Jenis Surat --</option>
                    <?php foreach ($jenis_list as $j) { ?>
                        <option value="<?= $j['id_jenis_surat'] ?>"><?= htmlspecialchars($j['nama_jenis']) ?> (<?= htmlspecialchars($j['kode_jenis']) ?>)</option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="perihal">Perihal / Isi Ringkas Surat *</label>
                <textarea id="perihal" name="perihal" rows="2" placeholder="Tuliskan perihal atau judul surat secara jelas..." required></textarea>
            </div>

            <div class="form-group full-width">
                <label for="file_scan">Upload File Scan Surat (PDF / JPG / PNG, Max 5MB)</label>
                <input type="file" id="file_scan" name="file_scan" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                <small style="color: var(--text-muted); display: block; margin-top: 4px;">
                    File scan akan menjadi lampiran digital resmi yang dapat ditinjau oleh Pimpinan untuk disposisi.
                </small>
            </div>

            <div class="form-group full-width">
                <label for="keterangan">Keterangan / Catatan Tambahan (Opsional)</label>
                <textarea id="keterangan" name="keterangan" rows="2" placeholder="Catatan fisik tempat penyimpanan atau nomor agenda surat..."></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Surat Masuk</button>
        </div>
    </form>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
