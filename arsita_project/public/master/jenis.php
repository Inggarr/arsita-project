<?php
ob_start(); // Prevent headers already sent
// public/master/jenis.php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';
wajib_role(['admin_rektorat']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_jenis'])) {
    $nama_jenis = trim($_POST['nama_jenis'] ?? '');
    $kode_jenis = trim($_POST['kode_jenis'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (empty($nama_jenis) || empty($kode_jenis)) {
        $error = 'Nama jenis dan kode jenis surat wajib diisi!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO jenis_surat (nama_jenis, kode_jenis, keterangan) VALUES (?, ?, ?)');
        $stmt->execute([$nama_jenis, $kode_jenis, $keterangan]);
        set_flash('success', 'Jenis surat baru berhasil ditambahkan!');
        header('Location: jenis.php');
        exit;
    }
}

$page_title = 'Master Jenis Surat';
require_once __DIR__.'/../../includes/header.php';

$jenis_list = $pdo->query('SELECT * FROM jenis_surat ORDER BY id_jenis_surat ASC')->fetchAll();
?>

<div class="detail-grid">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Jenis Dokumen Surat</div>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="40">ID</th>
                        <th>Kode Jenis</th>
                        <th>Nama Jenis Surat</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jenis_list as $j) { ?>
                        <tr>
                            <td><?= $j['id_jenis_surat'] ?></td>
                            <td><b style="color: var(--primary);"><?= htmlspecialchars($j['kode_jenis']) ?></b></td>
                            <td><b><?= htmlspecialchars($j['nama_jenis']) ?></b></td>
                            <td><?= htmlspecialchars($j['keterangan'] ?: '-') ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Tambah Jenis Surat</div>
        </div>
        <?php if ($error) { ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php } ?>
        <form method="POST">
            <div class="form-group">
                <label for="nama_jenis">Nama Jenis Surat *</label>
                <input type="text" name="nama_jenis" id="nama_jenis" placeholder="Misal: Surat Pengantar" required>
            </div>
            <div class="form-group">
                <label for="kode_jenis">Kode Singkatan Jenis *</label>
                <input type="text" name="kode_jenis" id="kode_jenis" placeholder="Misal: SPeng" required>
            </div>
            <div class="form-group">
                <label for="keterangan">Keterangan Tambahan</label>
                <textarea name="keterangan" id="keterangan" rows="3" placeholder="Deskripsi peruntukan surat..."></textarea>
            </div>
            <button type="submit" name="tambah_jenis" value="1" class="btn btn-primary" style="width: 100%;">
                Simpan Jenis Surat
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
