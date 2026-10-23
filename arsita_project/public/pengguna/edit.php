<?php
// public/pengguna/edit.php - Edit Pengguna
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_role(['admin_rektorat']);

$id_target = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM users WHERE id_user = ?');
$stmt->execute([$id_target]);
$user = $stmt->fetch();

if (! $user) {
    set_flash('danger', 'Pengguna tidak ditemukan!');
    header('Location: index.php');
    exit;
}

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
    $status = $_POST['status'] ?? 'aktif';

    if (empty($nama) || empty($username) || ! $id_unit || ! $id_role) {
        $error = 'Nama, username, unit, dan role wajib diisi!';
    } else {
        // Cek username unik untuk user lain
        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id_user != ?');
        $cek->execute([$username, $id_target]);
        if ($cek->fetchColumn() > 0) {
            $error = 'Username sudah digunakan oleh akun lain!';
        } else {
            if (! empty($password)) {
                $passHash = password_hash($password, PASSWORD_BCRYPT);
                $stmtUpdate = $pdo->prepare('
                    UPDATE users SET nama = ?, username = ?, password = ?, email = ?, id_unit = ?, id_role = ?, status = ?
                    WHERE id_user = ?
                ');
                $stmtUpdate->execute([$nama, $username, $passHash, $email, $id_unit, $id_role, $status, $id_target]);
            } else {
                $stmtUpdate = $pdo->prepare('
                    UPDATE users SET nama = ?, username = ?, email = ?, id_unit = ?, id_role = ?, status = ?
                    WHERE id_user = ?
                ');
                $stmtUpdate->execute([$nama, $username, $email, $id_unit, $id_role, $status, $id_target]);
            }

            set_flash('success', "Data pengguna <b>$username</b> berhasil diperbarui!");
            header('Location: index.php');
            exit;
        }
    }
}

$page_title = 'Edit Pengguna';
require_once __DIR__.'/../../includes/header.php';
?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title">Edit Data Pengguna</div>
            <div class="card-subtitle">Perbarui hak akses, unit penempatan, atau reset password</div>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST">
        <div class="form-group">
            <label for="nama">Nama Lengkap & Gelar *</label>
            <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
        </div>

        <div class="form-group">
            <label for="username">Username Login *</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" name="password" id="password" placeholder="Biarkan kosong jika tidak ingin mengubah password">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="id_unit">Unit Kampus *</label>
            <select name="id_unit" id="id_unit" required>
                <?php foreach ($units_list as $un) { ?>
                    <option value="<?= $un['id_unit'] ?>" <?= $un['id_unit'] == $user['id_unit'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($un['nama_unit']) ?> (<?= htmlspecialchars($un['kode_unit']) ?>)
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_role">Role Akses *</label>
            <select name="id_role" id="id_role" required>
                <?php foreach ($roles_list as $r) { ?>
                    <option value="<?= $r['id_role'] ?>" <?= $r['id_role'] == $user['id_role'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['nama_role']) ?> (<?= htmlspecialchars($r['deskripsi']) ?>)
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status Akun</label>
            <select name="status" id="status">
                <option value="aktif" <?= $user['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= $user['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
