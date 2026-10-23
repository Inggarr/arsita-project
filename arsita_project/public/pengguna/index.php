<?php
// public/pengguna/index.php
$page_title = 'Kelola Pengguna Sistem';
require_once __DIR__.'/../../includes/header.php';
wajib_role(['admin_rektorat']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $id_unit = (int) ($_POST['id_unit'] ?? 0);
    $id_role = (int) ($_POST['id_role'] ?? 0);

    if (empty($nama) || empty($username) || empty($password) || ! $id_unit || ! $id_role) {
        $error = 'Nama, username, password, unit, dan role wajib diisi!';
    } else {
        // Cek username unik
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

// Daftar user
$users_list = $pdo->query('
    SELECT u.*, r.nama_role, un.nama_unit, un.kode_unit
    FROM users u
    JOIN roles r ON u.id_role = r.id_role
    LEFT JOIN units un ON u.id_unit = un.id_unit
    ORDER BY u.id_unit ASC, u.id_role ASC
')->fetchAll();

$roles_list = $pdo->query('SELECT * FROM roles ORDER BY id_role ASC')->fetchAll();
$units_list = $pdo->query("SELECT * FROM units WHERE status = 'aktif' ORDER BY level ASC, nama_unit ASC")->fetchAll();
?>

<div class="detail-grid">
    <!-- Daftar Pengguna -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Daftar Akun Pengguna ARSITA</div>
                <div class="card-subtitle">
                    Hak akses role dan penempatan unit kerja pegawai
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama & Username</th>
                        <th>Role Akses</th>
                        <th>Unit Kampus</th>
                        <th>Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users_list as $u) { ?>
                        <tr>
                            <td>
                                <b><?= htmlspecialchars($u['nama']) ?></b><br>
                                <small class="text-muted">@<?= htmlspecialchars($u['username']) ?></small>
                            </td>
                            <td>
                                <span style="font-size: 11.5px; font-weight: 600; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">
                                    <?= htmlspecialchars($u['nama_role']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['nama_unit'] ?: '-') ?> (<?= htmlspecialchars($u['kode_unit'] ?: '-') ?>)</td>
                            <td>
                                <?php if ($u['status'] === 'aktif') { ?>
                                    <span style="color: var(--success); font-weight: 600; font-size: 12px;">Aktif</span>
                                <?php } else { ?>
                                    <span style="color: var(--danger); font-weight: 600; font-size: 12px;">Nonaktif</span>
                                <?php } ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit.php?id=<?= $u['id_user'] ?>" class="btn btn-secondary btn-sm" title="Edit Pengguna">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($u['id_user'] != $_SESSION['id_user']) { ?>
                                        <a href="hapus.php?id=<?= $u['id_user'] ?>" class="btn btn-secondary btn-sm" style="color: var(--danger);" title="Hapus Pengguna" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tambah User Baru -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Tambah Pengguna Baru</div>
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

            <button type="submit" name="tambah_user" value="1" class="btn btn-primary" style="width: 100%;">
                Daftarkan Pengguna
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
