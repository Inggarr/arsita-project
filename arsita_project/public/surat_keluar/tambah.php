<?php
// public/surat_keluar/tambah.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

$role = $_SESSION['role'] ?? '';
$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;

wajib_role(['admin_rektorat', 'staff_tu']);

// Ambil list kategori dan jenis surat
$kategori_list = $pdo->query('SELECT * FROM kategori_surat ORDER BY nama_kategori ASC')->fetchAll();
$jenis_list = $pdo->query('SELECT * FROM jenis_surat ORDER BY nama_jenis ASC')->fetchAll();

// Unit pemilihan (jika admin_rektorat)
$units_list = [];
if ($role === 'admin_rektorat') {
    $units_list = $pdo->query("SELECT * FROM units WHERE status = 'aktif' ORDER BY level ASC, nama_unit ASC")->fetchAll();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_unit_id = ($role === 'admin_rektorat' && ! empty($_POST['id_unit'])) ? (int) $_POST['id_unit'] : $id_unit;
    $jenis_surat_id = (int) ($_POST['jenis_surat_id'] ?? 0);
    $kategori_id = ! empty($_POST['kategori_id']) ? (int) $_POST['kategori_id'] : null;
    $sifat = $_POST['sifat'] ?? 'biasa';
    $tujuan = trim($_POST['tujuan'] ?? '');
    $lampiran = trim($_POST['lampiran'] ?? '-') ?: '-';
    $perihal = trim($_POST['perihal'] ?? '');
    $isi_surat = trim($_POST['isi_surat'] ?? '');
    $tembusan = trim($_POST['tembusan'] ?? '');
    $tanggal_draft = $_POST['tanggal_draft'] ?? date('Y-m-d');
    $aksi = $_POST['aksi'] ?? 'draft'; // 'draft' atau 'ajukan'

    if (empty($perihal) || empty($tujuan) || empty($isi_surat) || ! $jenis_surat_id) {
        $error = 'Jenis surat, tujuan, perihal, dan isi surat wajib diisi!';
    } else {
        try {
            $pdo->beginTransaction();

            // Status awal
            $status_surat = ($aksi === 'ajukan') ? 'menunggu_acc' : 'draft';

            // Simpan record surat keluar
            $stmt = $pdo->prepare('
                INSERT INTO surat_keluar (
                    id_unit, kategori_id, jenis_surat_id, sifat, tujuan,
                    lampiran, perihal, isi_surat, tembusan, tanggal_draft,
                    status_surat, dibuat_oleh
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $target_unit_id, $kategori_id, $jenis_surat_id, $sifat, $tujuan,
                $lampiran, $perihal, $isi_surat, $tembusan, $tanggal_draft,
                $status_surat, $id_user,
            ]);
            $id_surat_keluar = $pdo->lastInsertId();

            // Jika langsung ajukan persetujuan, inisialisasi tahap ACC
            if ($aksi === 'ajukan') {
                // Cek level unit
                $stmtLevel = $pdo->prepare('SELECT level FROM units WHERE id_unit = ?');
                $stmtLevel->execute([$target_unit_id]);
                $levelUnit = $stmtLevel->fetchColumn() ?: 'rektorat';

                // Tentukan pimpinan yang harus ACC sesuai alur unit
                if ($levelUnit === 'rektorat') {
                    // Tahap 1 di Rektorat: Kasubbag
                    $stmtKasub = $pdo->prepare("
                        SELECT id_user FROM users 
                        WHERE id_unit = ? AND (username = 'kasubbag' OR id_role = (SELECT id_role FROM roles WHERE nama_role = 'pimpinan'))
                        ORDER BY (username = 'kasubbag') DESC LIMIT 1
                    ");
                    $stmtKasub->execute([$target_unit_id]);
                    $pejabat_acc = $stmtKasub->fetchColumn();
                } else {
                    // Fakultas (Dekan) atau Prodi (Kajur)
                    $stmtPimp = $pdo->prepare("
                        SELECT id_user FROM users 
                        WHERE id_unit = ? AND id_role = (SELECT id_role FROM roles WHERE nama_role = 'pimpinan')
                        LIMIT 1
                    ");
                    $stmtPimp->execute([$target_unit_id]);
                    $pejabat_acc = $stmtPimp->fetchColumn();
                }

                // Masukkan ke acc_surat_keluar
                $stmtAcc = $pdo->prepare("
                    INSERT INTO acc_surat_keluar (id_surat_keluar, tahap_ke, disetujui_oleh, status)
                    VALUES (?, 1, ?, 'menunggu')
                ");
                $stmtAcc->execute([$id_surat_keluar, $pejabat_acc]);
            }

            $pdo->commit();

            if ($aksi === 'ajukan') {
                set_flash('success', 'Surat keluar berhasil dibuat dan langsung diajukan ke atasan untuk persetujuan (ACC)!');
            } else {
                set_flash('success', 'Draft surat keluar berhasil disimpan!');
            }

            header('Location: '.BASE_URL.'/surat_keluar/detail.php?id='.$id_surat_keluar);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan: '.$e->getMessage();
        }
    }
}

$page_title = 'Buat Surat Keluar Baru';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title">Form Pembuatan Surat Keluar</div>
            <div class="card-subtitle">
                Isi form dengan struktur baku surat dinas. Nomor surat resmi akan digenerate otomatis setelah persetujuan pimpinan.
            </div>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST">
        <div class="form-grid">
            <?php if ($role === 'admin_rektorat') { ?>
                <div class="form-group full-width">
                    <label for="id_unit">Unit Penerbit Surat *</label>
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
                <label for="jenis_surat_id">Jenis Surat Dinas *</label>
                <select name="jenis_surat_id" id="jenis_surat_id" required>
                    <option value="">-- Pilih Jenis Surat --</option>
                    <?php foreach ($jenis_list as $j) { ?>
                        <option value="<?= $j['id_jenis_surat'] ?>">
                            <?= htmlspecialchars($j['nama_jenis']) ?> (<?= htmlspecialchars($j['kode_jenis']) ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="kategori_id">Kategori Urusan Surat</label>
                <select name="kategori_id" id="kategori_id">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori_list as $k) { ?>
                        <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                    <?php } ?>
                </select>
                <small class="text-muted" style="display: block; margin-top: 4px; font-size: 11.5px;">
                    Nomor surat otomatis akan menyesuaikan nomor terakhir dari jenis & kategori ini. <a href="<?= BASE_URL ?>/penomoran/index.php" target="_blank" style="color: var(--primary); text-decoration: underline;">Cek/Atur Nomor Terakhir</a>
                </small>
            </div>

            <div class="form-group">
                <label for="sifat">Sifat Surat *</label>
                <select name="sifat" id="sifat" required>
                    <option value="biasa">Biasa</option>
                    <option value="penting">Penting</option>
                    <option value="rahasia">Rahasia</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tanggal_draft">Tanggal Surat Tertulis *</label>
                <input type="date" id="tanggal_draft" name="tanggal_draft" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label for="tujuan">Tujuan Surat (Kepada Yth.) *</label>
                <input type="text" id="tujuan" name="tujuan" placeholder="Contoh: Kepala Biro AUAK UINSSC / Seluruh Dosen FITK" required>
            </div>

            <div class="form-group">
                <label for="lampiran">Jumlah Lampiran Dokumen</label>
                <input type="text" id="lampiran" name="lampiran" value="-" placeholder="Contoh: 1 (satu) Berkas / -">
            </div>

            <div class="form-group full-width">
                <label for="perihal">Perihal / Hal Surat *</label>
                <input type="text" id="perihal" name="perihal" placeholder="Contoh: Undangan Rapat Koordinasi Kurikulum OBE TA 2026/2027" required>
            </div>

            <div class="form-group full-width">
                <label for="isi_surat">Isi Dokumen Surat (Pembuka, Pokok Masalah, Waktu/Tempat, Penutup) *</label>
                <textarea id="isi_surat" name="isi_surat" rows="8" placeholder="Tuliskan format isi surat resmi..." required style="line-height: 1.6;">Dengan hormat,

Sehubungan dengan agenda evaluasi akademik semester ganjil, kami mengundang Bapak/Ibu untuk hadir pada rapat koordinasi yang akan dilaksanakan pada:

Hari/Tanggal : Senin, 19 Oktober 2026
Waktu        : 09.00 WIB s.d Selesai
Tempat       : Ruang Sidang Utama Kampus UIN Siber Syekh Nurjati Cirebon

Demikian surat ini kami sampaikan, atas perhatian dan kehadiran Bapak/Ibu diucapkan terima kasih.</textarea>
            </div>

            <div class="form-group full-width">
                <label for="tembusan">Tembusan (Opsional)</label>
                <textarea id="tembusan" name="tembusan" rows="2" placeholder="1. Rektor UINSSC&#10;2. Wakil Rektor Bidang Akademik&#10;3. Arsip"></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" name="aksi" value="draft" class="btn btn-secondary">
                <i class="bi bi-save"></i> Simpan sebagai Draft
            </button>
            <button type="submit" name="aksi" value="ajukan" class="btn btn-primary">
                <i class="bi bi-send-check-fill"></i> Ajukan Persetujuan (Kirim ke Pimpinan)
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
