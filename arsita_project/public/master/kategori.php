<?php
ob_start(); // Prevent headers already sent
// public/master/kategori.php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';
wajib_role(['admin_rektorat']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($nama_kategori)) {
        $error = 'Nama kategori wajib diisi!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO kategori_surat (nama_kategori, deskripsi) VALUES (?, ?)');
        $stmt->execute([$nama_kategori, $deskripsi]);
        set_flash('success', 'Kategori surat baru berhasil ditambahkan!');
        header('Location: kategori.php');
        exit;
    }
}

$page_title = 'Master Kategori Surat';
require_once __DIR__.'/../../includes/header.php';

$kategori_list = $pdo->query('SELECT * FROM kategori_surat ORDER BY id_kategori ASC')->fetchAll();
?>

<div class="detail-grid">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Kategori Surat</div>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="40">ID</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kategori_list as $k) { ?>
                        <tr>
                            <td><?= $k['id_kategori'] ?></td>
                            <td><b><?= htmlspecialchars($k['nama_kategori']) ?></b></td>
                            <td><?= htmlspecialchars($k['deskripsi'] ?: '-') ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Tambah Kategori</div>
        </div>
        <?php if ($error) { ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php } ?>
        <form method="POST">
            <div class="form-group">
                <label for="nama_kategori">Nama Kategori *</label>
                <input type="text" name="nama_kategori" id="nama_kategori" placeholder="Misal: Kepegawaian" required>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="3" placeholder="Keterangan singkat cakupan kategori..."></textarea>
            </div>
            <button type="submit" name="tambah_kategori" value="1" class="btn btn-primary" style="width: 100%;">
                Simpan Kategori
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
