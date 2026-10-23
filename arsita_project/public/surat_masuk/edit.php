<?php
// public/surat_masuk/edit.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;

wajib_role(['admin_rektorat', 'staff_tu']);

$id_surat = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT sm.*, u.nama_unit, u.kode_unit
    FROM surat_masuk sm
    LEFT JOIN units u ON sm.id_unit = u.id_unit
    WHERE sm.id_surat_masuk = ?
');
$stmt->execute([$id_surat]);
$surat = $stmt->fetch();

if (! $surat) {
    set_flash('danger', 'Surat masuk tidak ditemukan!');
    header('Location: '.BASE_URL.'/surat_masuk/index.php');
    exit;
}

// Cek hak akses unit
if ($role !== 'admin_rektorat' && $surat['id_unit'] != $id_unit) {
    set_flash('danger', 'Akses ditolak: Anda tidak memiliki izin mengedit surat unit lain.');
    header('Location: '.BASE_URL.'/surat_masuk/index.php');
    exit;
}

$kategori_list = $pdo->query('SELECT * FROM kategori_surat ORDER BY nama_kategori ASC')->fetchAll();
$jenis_list = $pdo->query('SELECT * FROM jenis_surat ORDER BY nama_jenis ASC')->fetchAll();

$units_list = [];
if ($role === 'admin_rektorat') {
    $units_list = $pdo->query("SELECT * FROM units WHERE status = 'aktif' ORDER BY level ASC, nama_unit ASC")->fetchAll();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_surat_asal = trim($_POST['no_surat_asal'] ?? '');
    $tanggal_surat = ! empty($_POST['tanggal_surat']) ? $_POST['tanggal_surat'] : null;
    $tanggal_terima = ! empty($_POST['tanggal_terima']) ? $_POST['tanggal_terima'] : date('Y-m-d');
    $asal_surat = trim($_POST['asal_surat'] ?? '');
    $perihal = trim($_POST['perihal'] ?? '');
    $kategori_id = ! empty($_POST['kategori_id']) ? (int) $_POST['kategori_id'] : null;
    $jenis_surat_id = ! empty($_POST['jenis_surat_id']) ? (int) $_POST['jenis_surat_id'] : null;
    $target_unit_id = ($role === 'admin_rektorat' && ! empty($_POST['id_unit'])) ? (int) $_POST['id_unit'] : $surat['id_unit'];
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (empty($perihal) || empty($asal_surat) || empty($no_surat_asal)) {
        $error = 'Nomor surat, pengirim/asal surat, dan perihal wajib diisi!';
    } else {
        try {
            $new_file = $surat['file_surat'];
            if (isset($_FILES['file_scan']) && $_FILES['file_scan']['error'] === UPLOAD_ERR_OK) {
                $uploaded = upload_lampiran('file_scan', 'surat_masuk');
                if ($uploaded) {
                    // Hapus file lama jika ada
                    if ($surat['file_surat']) {
                        $oldPath = UPLOAD_PATH.'surat_masuk/'.$surat['file_surat'];
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $new_file = $uploaded;
                }
            }

            $stmtUpdate = $pdo->prepare('
                UPDATE surat_masuk SET
                    no_surat_asal   = ?,
                    tanggal_surat   = ?,
                    tanggal_terima  = ?,
                    asal_surat      = ?,
                    perihal         = ?,
                    kategori_id     = ?,
                    jenis_surat_id  = ?,
                    id_unit         = ?,
                    file_surat      = ?,
                    keterangan      = ?
                WHERE id_surat_masuk = ?
            ');
            $stmtUpdate->execute([
                $no_surat_asal,
                $tanggal_surat,
                $tanggal_terima,
                $asal_surat,
                $perihal,
                $kategori_id,
                $jenis_surat_id,
                $target_unit_id,
                $new_file,
                $keterangan,
                $id_surat,
            ]);

            set_flash('success', 'Data surat masuk berhasil diperbarui!');
            header('Location: '.BASE_URL.'/surat_masuk/detail.php?id='.$id_surat);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$page_title = 'Edit Surat Masuk';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="card" style="max-width: 850px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title">Edit Surat Masuk</div>
            <div class="card-subtitle">Perbarui informasi identitas surat atau ganti berkas pindaian</div>
        </div>
        <a href="<?= BASE_URL ?>/surat_masuk/detail.php?id=<?= $id_surat ?>" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Detail
        </a>
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
                            <option value="<?= $u['id_unit'] ?>" <?= $u['id_unit'] == $surat['id_unit'] ? 'selected' : '' ?>>
                                [<?= strtoupper($u['level']) ?>] <?= htmlspecialchars($u['nama_unit']) ?> (<?= htmlspecialchars($u['kode_unit']) ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <div class="form-group">
                <label for="no_surat_asal">Nomor Surat Asal (Pengirim) *</label>
                <input type="text" id="no_surat_asal" name="no_surat_asal" value="<?= htmlspecialchars($surat['no_surat_asal'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="asal_surat">Asal Surat / Nama Instansi Pengirim *</label>
                <input type="text" id="asal_surat" name="asal_surat" value="<?= htmlspecialchars($surat['asal_surat'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="tanggal_surat">Tanggal Surat Tertulis</label>
                <input type="date" id="tanggal_surat" name="tanggal_surat" value="<?= htmlspecialchars($surat['tanggal_surat'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="tanggal_terima">Tanggal Diterima Unit *</label>
                <input type="date" id="tanggal_terima" name="tanggal_terima" value="<?= htmlspecialchars($surat['tanggal_terima'] ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="form-group">
                <label for="kategori_id">Kategori Urusan Surat</label>
                <select name="kategori_id" id="kategori_id">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori_list as $kat) { ?>
                        <option value="<?= $kat['id_kategori'] ?>" <?= ($surat['kategori_id'] == $kat['id_kategori']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="jenis_surat_id">Jenis Surat</label>
                <select name="jenis_surat_id" id="jenis_surat_id">
                    <option value="">-- Pilih Jenis Surat --</option>
                    <?php foreach ($jenis_list as $jen) { ?>
                        <option value="<?= $jen['id_jenis_surat'] ?>" <?= ($surat['jenis_surat_id'] == $jen['id_jenis_surat']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($jen['nama_jenis']) ?> (<?= htmlspecialchars($jen['kode_jenis']) ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="perihal">Perihal / Hal Surat *</label>
                <textarea id="perihal" name="perihal" rows="2" required><?= htmlspecialchars($surat['perihal'] ?? '') ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="keterangan">Keterangan / Ringkasan Isi Surat</label>
                <textarea id="keterangan" name="keterangan" rows="3" placeholder="Catatan opsional mengenai isi ringkas surat..."><?= htmlspecialchars($surat['keterangan'] ?? '') ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="file_scan">Ganti File Scan Surat (PDF / JPG / PNG, Maks. 5MB)</label>
                <?php if ($surat['file_surat']) { ?>
                    <div style="font-size: 13px; margin-bottom: 8px; color: var(--text-secondary);">
                        Berkas saat ini: 
                        <a href="<?= BASE_URL ?>/../uploads/surat_masuk/<?= htmlspecialchars($surat['file_surat']) ?>" target="_blank" style="color: var(--primary); font-weight: 600;">
                            <i class="bi bi-paperclip"></i> <?= htmlspecialchars($surat['file_surat']) ?>
                        </a>
                        <span class="text-muted">(Biarkan kosong jika tidak ingin mengubah berkas)</span>
                    </div>
                <?php } ?>
                <input type="file" id="file_scan" name="file_scan" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
            <a href="<?= BASE_URL ?>/surat_masuk/detail.php?id=<?= $id_surat ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
