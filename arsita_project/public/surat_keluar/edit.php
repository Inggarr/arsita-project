<?php
// public/surat_keluar/edit.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';
wajib_login();

$id_sk = (int) ($_GET['id'] ?? 0);
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;
$role = $_SESSION['role'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM surat_keluar WHERE id_surat_keluar = ?');
$stmt->execute([$id_sk]);
$surat = $stmt->fetch();

if (! $surat) {
    set_flash('danger', 'Surat keluar tidak ditemukan!');
    header('Location: index.php');
    exit;
}

if (! in_array($surat['status_surat'], ['draft', 'revisi'])) {
    set_flash('danger', 'Surat yang sudah diajukan atau disetujui tidak dapat diedit!');
    header('Location: detail.php?id='.$id_sk);
    exit;
}

$kategori_list = $pdo->query('SELECT * FROM kategori_surat ORDER BY nama_kategori ASC')->fetchAll();
$jenis_list = $pdo->query('SELECT * FROM jenis_surat ORDER BY nama_jenis ASC')->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_surat_id = (int) ($_POST['jenis_surat_id'] ?? 0);
    $kategori_id = ! empty($_POST['kategori_id']) ? (int) $_POST['kategori_id'] : null;
    $sifat = $_POST['sifat'] ?? 'biasa';
    $tujuan = trim($_POST['tujuan'] ?? '');
    $lampiran = trim($_POST['lampiran'] ?? '-') ?: '-';
    $perihal = trim($_POST['perihal'] ?? '');
    $isi_surat = trim($_POST['isi_surat'] ?? '');
    $tembusan = trim($_POST['tembusan'] ?? '');
    $tanggal_draft = $_POST['tanggal_draft'] ?? date('Y-m-d');
    $aksi = $_POST['aksi'] ?? 'draft';

    if (empty($perihal) || empty($tujuan) || empty($isi_surat) || ! $jenis_surat_id) {
        $error = 'Semua field wajib diisi lengkap!';
    } else {
        try {
            $pdo->beginTransaction();

            $status_baru = ($aksi === 'ajukan') ? 'menunggu_acc' : 'draft';

            $stmtUp = $pdo->prepare('
                UPDATE surat_keluar SET
                    jenis_surat_id = ?, kategori_id = ?, sifat = ?,
                    tujuan = ?, lampiran = ?, perihal = ?,
                    isi_surat = ?, tembusan = ?, tanggal_draft = ?,
                    status_surat = ?
                WHERE id_surat_keluar = ?
            ');
            $stmtUp->execute([
                $jenis_surat_id, $kategori_id, $sifat,
                $tujuan, $lampiran, $perihal,
                $isi_surat, $tembusan, $tanggal_draft,
                $status_baru, $id_sk,
            ]);

            // Jika diajukan ulang dari revisi
            if ($aksi === 'ajukan') {
                $stmtLevel = $pdo->prepare('SELECT level FROM units WHERE id_unit = ?');
                $stmtLevel->execute([$surat['id_unit']]);
                $levelUnit = $stmtLevel->fetchColumn() ?: 'rektorat';

                if ($levelUnit === 'rektorat') {
                    $stmtKasub = $pdo->prepare("
                        SELECT id_user FROM users 
                        WHERE id_unit = ? AND (username = 'kasubbag' OR id_role = (SELECT id_role FROM roles WHERE nama_role = 'pimpinan'))
                        ORDER BY (username = 'kasubbag') DESC LIMIT 1
                    ");
                    $stmtKasub->execute([$surat['id_unit']]);
                    $pejabat_acc = $stmtKasub->fetchColumn();
                } else {
                    $stmtPimp = $pdo->prepare("
                        SELECT id_user FROM users 
                        WHERE id_unit = ? AND id_role = (SELECT id_role FROM roles WHERE nama_role = 'pimpinan')
                        LIMIT 1
                    ");
                    $stmtPimp->execute([$surat['id_unit']]);
                    $pejabat_acc = $stmtPimp->fetchColumn();
                }

                // Reset tahap ACC ke 1
                $pdo->prepare('DELETE FROM acc_surat_keluar WHERE id_surat_keluar = ?')->execute([$id_sk]);
                $pdo->prepare("INSERT INTO acc_surat_keluar (id_surat_keluar, tahap_ke, disetujui_oleh, status) VALUES (?, 1, ?, 'menunggu')")
                    ->execute([$id_sk, $pejabat_acc]);
            }

            $pdo->commit();
            set_flash('success', 'Perubahan surat keluar berhasil disimpan!');
            header('Location: detail.php?id='.$id_sk);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$page_title = 'Edit Surat Keluar';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title">Edit / Revisi Surat Keluar</div>
            <div class="card-subtitle">
                Sesuaikan isi dokumen surat berdasarkan catatan arahan atau revisi pimpinan
            </div>
        </div>
        <a href="detail.php?id=<?= $id_sk ?>" class="btn btn-secondary btn-sm">Batal</a>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label for="jenis_surat_id">Jenis Surat *</label>
                <select name="jenis_surat_id" id="jenis_surat_id" required>
                    <?php foreach ($jenis_list as $j) { ?>
                        <option value="<?= $j['id_jenis_surat'] ?>" <?= $j['id_jenis_surat'] == $surat['jenis_surat_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($j['nama_jenis']) ?> (<?= htmlspecialchars($j['kode_jenis']) ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="kategori_id">Kategori Bidang</label>
                <select name="kategori_id" id="kategori_id">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori_list as $k) { ?>
                        <option value="<?= $k['id_kategori'] ?>" <?= $k['id_kategori'] == $surat['kategori_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kategori']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="sifat">Sifat Surat *</label>
                <select name="sifat" id="sifat" required>
                    <option value="biasa" <?= $surat['sifat'] === 'biasa' ? 'selected' : '' ?>>Biasa</option>
                    <option value="penting" <?= $surat['sifat'] === 'penting' ? 'selected' : '' ?>>Penting</option>
                    <option value="rahasia" <?= $surat['sifat'] === 'rahasia' ? 'selected' : '' ?>>Rahasia</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tanggal_draft">Tanggal Surat Tertulis *</label>
                <input type="date" id="tanggal_draft" name="tanggal_draft" value="<?= htmlspecialchars($surat['tanggal_draft']) ?>" required>
            </div>

            <div class="form-group">
                <label for="tujuan">Tujuan Surat *</label>
                <input type="text" id="tujuan" name="tujuan" value="<?= htmlspecialchars($surat['tujuan']) ?>" required>
            </div>

            <div class="form-group">
                <label for="lampiran">Jumlah Lampiran Dokumen</label>
                <input type="text" id="lampiran" name="lampiran" value="<?= htmlspecialchars($surat['lampiran']) ?>">
            </div>

            <div class="form-group full-width">
                <label for="perihal">Perihal / Hal Surat *</label>
                <input type="text" id="perihal" name="perihal" value="<?= htmlspecialchars($surat['perihal']) ?>" required>
            </div>

            <div class="form-group full-width">
                <label for="isi_surat">Isi Dokumen Surat *</label>
                <textarea id="isi_surat" name="isi_surat" rows="8" required style="line-height: 1.6;"><?= htmlspecialchars($surat['isi_surat']) ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="tembusan">Tembusan</label>
                <textarea id="tembusan" name="tembusan" rows="2"><?= htmlspecialchars($surat['tembusan'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="detail.php?id=<?= $id_sk ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" name="aksi" value="draft" class="btn btn-secondary">
                <i class="bi bi-save"></i> Simpan Draft
            </button>
            <button type="submit" name="aksi" value="ajukan" class="btn btn-primary">
                <i class="bi bi-send-check-fill"></i> Ajukan Ulang Persetujuan
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
