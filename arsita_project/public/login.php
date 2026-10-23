<?php
ob_start();
require_once __DIR__.'/../includes/auth.php';

// Kalau sudah login, langsung ke dashboard
if (isset($_SESSION['id_user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT u.*, r.nama_role, un.nama_unit, un.kode_unit, un.level AS level_unit
        FROM users u
        JOIN roles r ON u.id_role = r.id_role
        LEFT JOIN units un ON u.id_unit = un.id_unit
        WHERE u.username = ? AND u.status = 'aktif'
        LIMIT 1
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['id_unit'] = $user['id_unit'];
        $_SESSION['role'] = $user['nama_role'];
        $_SESSION['nama_unit'] = $user['nama_unit'] ?? 'Umum';
        $_SESSION['kode_unit'] = $user['kode_unit'] ?? '-';
        $_SESSION['level_unit'] = $user['level_unit'] ?? 'rektorat';

        header('Location: dashboard.php');
        exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Login ARSITA — Sistem Persuratan dan Disposisi UINSSC">
    <title>Login — <?= APP_NAME ?></title>
    <!-- Bootstrap Icons (Lokal) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons.min.css">
    <!-- ARSITA Design System -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <!-- Halaman Login Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>

<body>
    <div class="login-container">
        <!-- Panel Kiri: Intro & Demo Akun -->
        <div class="intro-panel">
            <div class="intro-heading">
                <h2>ARSITA</h2>
                <p>Arsip Surat Instansi Terintegrasi Antarunit</p>
                <p class="intro-sub">UIN Siber Syekh Nurjati Cirebon</p>
            </div>

            <div class="demo-box">
                <h4><i class="bi bi-lightning-charge-fill"></i> Akun Uji Coba Cepat</h4>
                <p>Password semua: <strong>password123</strong> · Klik untuk isi otomatis</p>
                
                <div class="demo-groups">
                    <!-- Unit Rektorat -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-building"></i> Rektorat & Tata Usaha Pusat
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('admin_rektorat')" title="Admin Global">Admin Rektorat</li>
                            <li onclick="isi('staff_rektorat')" title="Staf TU Pusat">Staff TU Rektorat</li>
                            <li onclick="isi('rektor')" title="Pimpinan Tertinggi">Rektor (Pimpinan)</li>
                            <li onclick="isi('kabag')" title="Kepala Bagian">Kabag (Pimpinan)</li>
                            <li onclick="isi('kasubbag')" title="Kasubbag TU">Kasubbag (Pimpinan)</li>
                        </ul>
                    </div>

                    <!-- Unit Fakultas -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-mortarboard"></i> Fakultas FITK
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('dekan')" title="Dekan Fakultas">Dekan FITK (Pimpinan)</li>
                            <li onclick="isi('staff_fakultas')" title="Staf TU Fakultas">Staff TU Fakultas</li>
                        </ul>
                    </div>

                    <!-- Unit Program Studi -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-diagram-3"></i> Jurusan / Prodi Teknik Informatika
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('kajur')" title="Ketua Jurusan / Kaprodi">Kajur TI (Pimpinan)</li>
                            <li onclick="isi('staff_prodi')" title="Staf TU Prodi">Staff TU Prodi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Form Login -->
        <div class="login-box">
            <div class="login-brand">
                <div class="login-brand-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <div class="login-brand-title">Masuk ke Sistem</div>
                    <div class="login-brand-sub">Silakan gunakan kredensial akun Anda</div>
                </div>
            </div>

            <?php if ($error) { ?>
                <div class="login-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php } ?>

            <form method="POST">
                <div class="login-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                </div>
                <div class="login-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="login-submit-btn">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk Sekarang
                </button>
            </form>
        </div>
    </div>

    <script>
        function isi(username) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = 'password123';
            document.getElementById('username').focus();
        }
    </script>
</body>
</html>