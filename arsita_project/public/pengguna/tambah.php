<?php
// public/pengguna/tambah.php - Tambah Pengguna Baru
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_role(['admin_rektorat']);

$roles_list = $pdo->query('SELECT * FROM roles ORDER BY id_role ASC')->fetchAll();
$units_list = $pdo->query("SELECT * FROM units WHERE status = 'aktif' ORDER BY level ASC, nama_unit ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $id_unit = (int) ($_POST['id_unit'] ?? 0);
    $id_role = (int) ($_POST['id_role'] ?? 0);

    if (empty($nama) || empty($username) || empty($password) || ! $id_unit || ! $id_role) {
        $error = 'Nama, username, password, unit, dan role wajib diisi!';
    } else {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $cek->execute([$username]);
        if ($cek->fetchColumn() > 0) {
            $error = 'Username sudah dipakai oleh pengguna lain!';
        } else {
            $passHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO users (nama, username, password, email, id_unit, id_role, status)
                VALUES (?, ?, ?, ?, ?, ?, 'aktif')
            ");
            $stmt->execute([$nama, $username, $passHash, $email, $id_unit, $id_role]);
            set_flash('success', "Akun pengguna <b>$username</b> berhasil didaftarkan!");
            header('Location: index.php');
            exit;
        }
    }
}

$page_title = 'Tambah Pengguna Baru';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title">Pendaftaran Akun Pegawai</div>
            <div class="card-subtitle">Buat akun pengguna baru dengan role dan unit kerja terkait</div>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST">
        <div class="form-group">
            <label for="nama">Nama Lengkap & Gelar *</label>
            <input type="text" name="nama" id="nama" placeholder="Contoh: Dr. H. Ahmad, M.Ag." required>
        </div>

        <div class="form-group">
            <label for="username">Username Login *</label>
            <input type="text" name="username" id="username" placeholder="Contoh: ahmad_tu" required>
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" name="password" id="password" placeholder="Minimal 6 karakter" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Contoh: ahmad@uinssc.ac.id">
        </div>

        <div class="form-group">
            <label for="id_unit">Unit Kampus *</label>
            <select name="id_unit" id="id_unit" required>
                <option value="">-- Pilih Unit --</option>
                <?php foreach ($units_list as $un) { ?>
                    <option value="<?= $un['id_unit'] ?>"><?= htmlspecialchars($un['nama_unit']) ?> (<?= htmlspecialchars($un['kode_unit']) ?>)</option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_role">Role Pengguna *</label>
            <select name="id_role" id="id_role" required>
                <option value="">-- Pilih Role --</option>
                <?php foreach ($roles_list as $r) { ?>
                    <option value="<?= $r['id_role'] ?>"><?= htmlspecialchars($r['nama_role']) ?> (<?= htmlspecialchars($r['deskripsi']) ?>)</option>
                <?php } ?>
            </select>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-person-plus-fill"></i> Daftarkan Pengguna
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
