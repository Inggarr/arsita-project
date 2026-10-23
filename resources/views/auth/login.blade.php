<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Login ARSITA — Sistem Persuratan dan Disposisi UINSSC">
    <title>Login — ARSITA</title>
    <!-- Bootstrap Icons (Lokal) -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <!-- ARSITA Design System -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- Halaman Login Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
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

            @if($errors->any())
                <div class="login-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="login-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" value="{{ old('username', 'admin_rektorat') }}" required autofocus>
                </div>
                <div class="login-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" value="password123" required>
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
