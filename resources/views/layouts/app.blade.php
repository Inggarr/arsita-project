<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ARSITA - Sistem Informasi Persuratan dan Disposisi UINSSC">
    <title>@yield('title', 'Dashboard') — ARSITA</title>
    <!-- Bootstrap Icons (Lokal) -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <!-- ARSITA Design System -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- Toast & Modal Notifications -->
    <link rel="stylesheet" href="{{ asset('assets/css/notifications.css') }}">
    @stack('styles')
</head>
<body>

@php
    $user = Auth::user();
    $role = $user->role ? $user->role->nama_role : '';
    $id_user = $user->id_user;
    $id_unit = $user->id_unit;
    $user_unit = $user->unit ? $user->unit->nama_unit : 'UINSSC';
    $kode_unit = $user->unit ? $user->unit->kode_unit : '-';
    $user_name = $user->nama ?: $user->name;

    // Badges count
    $badge_acc = 0;
    if (in_array($role, ['pimpinan', 'admin_rektorat'])) {
        if ($role === 'admin_rektorat') {
            $badge_acc = \App\Models\AccSuratKeluar::where('status', 'menunggu')->count();
        } else {
            $badge_acc = \App\Models\AccSuratKeluar::where('status', 'menunggu')
                ->where(function ($q) use ($id_user, $id_unit) {
                    $q->where('disetujui_oleh', $id_user)
                        ->orWhere(function ($sub) use ($id_unit) {
                            $sub->whereNull('disetujui_oleh')
                                ->whereHas('suratKeluar', function ($sk) use ($id_unit) {
                                    $sk->where('id_unit', $id_unit);
                                });
                        });
                })->count();
        }
    }

    $badge_disp_masuk = 0;
    if (in_array($role, ['staff_tu', 'pimpinan', 'admin_rektorat', 'admin_unit'])) {
        $badge_disp_masuk = \App\Models\DisposisiTujuan::where(function($q) use ($id_user, $id_unit) {
            $q->where('ke_user_id', $id_user)
              ->orWhere(function($sub) use ($id_unit) {
                  $sub->where('tujuan_tipe', 'unit')->where('ke_unit_id', $id_unit);
              });
        })->where('status', 'belum_diproses')->count();
    }

    $badge_sm_baru = 0;
    if (in_array($role, ['admin_rektorat', 'staff_tu', 'pimpinan'])) {
        if ($role === 'admin_rektorat') {
            $badge_sm_baru = \App\Models\SuratMasuk::where('status_arsip', 'belum_ditinjau')->count();
        } else {
            $badge_sm_baru = \App\Models\SuratMasuk::where('id_unit', $id_unit)->where('status_arsip', 'belum_ditinjau')->count();
        }
    }
@endphp

<!-- Sidebar Identik Asli Native -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo">
            <i class="bi bi-shield-check"></i>
        </div>
        <div>
            <div class="sidebar-title">ARSITA</div>
            <div class="sidebar-sub">Disposisi &amp; Persuratan</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <!-- Menu Utama -->
        <div class="menu-category">Utama</div>
        <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="menu-label">Dashboard</span>
        </a>

        <!-- Persuratan (Surat Masuk & Keluar) -->
        @if(in_array($role, ['admin_rektorat', 'staff_tu', 'pimpinan']))
            <div class="menu-category">Persuratan</div>
            <a href="{{ route('surat-masuk.index') }}" class="menu-item {{ request()->routeIs('surat-masuk.*') ? 'active' : '' }}" title="Surat Masuk">
                <i class="bi bi-inbox-fill"></i>
                <span class="menu-label">Surat Masuk</span>
                @if($badge_sm_baru > 0)
                    <span class="menu-badge" title="{{ $badge_sm_baru }} belum ditinjau">{{ $badge_sm_baru }}</span>
                @endif
            </a>
            <a href="{{ route('surat-keluar.index') }}" class="menu-item {{ request()->routeIs('surat-keluar.*') ? 'active' : '' }}" title="Surat Keluar">
                <i class="bi bi-send-fill"></i>
                <span class="menu-label">Surat Keluar</span>
            </a>
        @endif

        <!-- Disposisi -->
        @php 
            $can_see_disp_masuk = in_array($role, ['staff_tu', 'pimpinan', 'admin_rektorat', 'admin_unit']);
            $can_see_disp_riwayat = in_array($role, ['pimpinan', 'admin_rektorat']);
        @endphp
        @if($can_see_disp_masuk || $can_see_disp_riwayat)
            <div class="menu-category">Disposisi</div>
            @if($can_see_disp_masuk)
                <a href="{{ route('disposisi.index') }}" class="menu-item {{ request()->routeIs('disposisi.index') || request()->routeIs('disposisi.tindak-lanjut') ? 'active' : '' }}" title="Disposisi Masuk">
                    <i class="bi bi-card-checklist"></i>
                    <span class="menu-label">Disposisi Masuk</span>
                    @if($badge_disp_masuk > 0)
                        <span class="menu-badge" title="{{ $badge_disp_masuk }} belum diproses">{{ $badge_disp_masuk }}</span>
                    @endif
                </a>
            @endif
            @if($can_see_disp_riwayat)
                <a href="{{ route('disposisi.riwayat') }}" class="menu-item {{ request()->routeIs('disposisi.riwayat') || request()->routeIs('disposisi.create') ? 'active' : '' }}" title="Riwayat Disposisi Dibuat">
                    <i class="bi bi-send-check-fill"></i>
                    <span class="menu-label">Riwayat Disposisi</span>
                </a>
            @endif
        @endif

        <!-- Persetujuan ACC -->
        @if(in_array($role, ['pimpinan', 'admin_rektorat']))
            <div class="menu-category">Persetujuan</div>
            <a href="{{ route('persetujuan.index') }}" class="menu-item {{ request()->routeIs('persetujuan.*') ? 'active' : '' }}" title="Persetujuan Surat (ACC)">
                <i class="bi bi-patch-check-fill"></i>
                <span class="menu-label">Persetujuan ACC</span>
                @if($badge_acc > 0)
                    <span class="menu-badge" title="{{ $badge_acc }} menunggu ACC">{{ $badge_acc }}</span>
                @endif
            </a>
        @endif

        <!-- Arsip & Laporan -->
        <div class="menu-category">Arsip &amp; Laporan</div>
        <a href="{{ route('arsip.index') }}" class="menu-item {{ request()->routeIs('arsip.*') ? 'active' : '' }}" title="Arsip &amp; Pencarian">
            <i class="bi bi-archive-fill"></i>
            <span class="menu-label">Arsip &amp; Pencarian</span>
        </a>
        <a href="{{ route('laporan.index') }}" class="menu-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}" title="Rekap &amp; Laporan">
            <i class="bi bi-file-earmark-bar-graph-fill"></i>
            <span class="menu-label">Rekap &amp; Laporan</span>
        </a>
        <a href="{{ route('master.format-nomor') }}" class="menu-item {{ request()->routeIs('master.format-nomor*') ? 'active' : '' }}" title="Pengaturan Penomoran Surat">
            <i class="bi bi-hash"></i>
            <span class="menu-label">Penomoran Surat</span>
        </a>

        <!-- Master Data (Admin Rektorat) -->
        @if($role === 'admin_rektorat')
            <div class="menu-category">Master Data</div>
            <a href="{{ route('master.unit') }}" class="menu-item {{ request()->routeIs('master.unit*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i>
                <span class="menu-label">Data Unit Kampus</span>
            </a>
            <a href="{{ route('master.kategori') }}" class="menu-item {{ request()->routeIs('master.kategori*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i>
                <span class="menu-label">Kategori Surat</span>
            </a>
            <a href="{{ route('master.jenis') }}" class="menu-item {{ request()->routeIs('master.jenis*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text-fill"></i>
                <span class="menu-label">Jenis Surat</span>
            </a>
            <a href="{{ route('master.format-nomor') }}" class="menu-item {{ request()->routeIs('master.format-nomor*') ? 'active' : '' }}">
                <i class="bi bi-hash"></i>
                <span class="menu-label">Format Nomor Otomatis</span>
            </a>
            <a href="{{ route('pengguna.index') }}" class="menu-item {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span class="menu-label">Kelola Pengguna</span>
            </a>
        @endif
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="user-badge">
            <div class="user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="user-info">
                <div class="user-name" title="{{ $user_name }}">{{ $user_name }}</div>
                <div class="user-role">{{ ucwords(str_replace('_', ' ', $role)) }}</div>
            </div>
        </div>
    </div>
</aside>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left">
            <div class="page-title">@yield('title', 'Dashboard')</div>
        </div>

        <div class="topbar-right">
            <div class="unit-tag">
                <i class="bi bi-building"></i>
                <span>{{ $user_unit }} ({{ $kode_unit }})</span>
            </div>

            <form action="{{ route('logout') }}" method="POST" id="form-logout" style="display:inline; margin:0;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm"
                   data-confirm="Anda akan keluar dari sistem ARSITA. Pastikan semua pekerjaan sudah tersimpan."
                   data-confirm-title="Keluar dari Sistem?"
                   data-confirm-type="logout"
                   data-confirm-btn="Ya, Keluar" style="cursor: pointer; border: none;">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
            </form>
        </div>
    </header>

    <main class="content-area">
        @if(session('success'))
            <div id="arsita-flash-data" data-type="success" data-message="{{ session('success') }}" style="display:none;"></div>
        @endif
        @if(session('error'))
            <div id="arsita-flash-data" data-type="error" data-message="{{ session('error') }}" style="display:none;"></div>
        @endif

        @yield('content')
    </main>
</div>

<!-- ARSITA Notification System -->
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script>
// Proses flash message dari session → toast
(function() {
    var flashEl = document.getElementById('arsita-flash-data');
    if (flashEl) {
        var type = flashEl.getAttribute('data-type');
        var msg  = flashEl.getAttribute('data-message');
        if (type && msg) {
            setTimeout(function() { 
                if (typeof arsitaToast === 'function') {
                    arsitaToast(type, msg);
                }
            }, 300);
        }
    }
})();

// Menjaga posisi scroll sidebar
(function() {
    var sidebar = document.querySelector('.sidebar-menu');
    if (!sidebar) return;
    var savedPos = sessionStorage.getItem('arsita_sidebar_scroll_pos');
    if (savedPos !== null) {
        sidebar.scrollTop = parseInt(savedPos, 10);
    }
    sidebar.addEventListener('scroll', function() {
        sessionStorage.setItem('arsita_sidebar_scroll_pos', sidebar.scrollTop);
    });
})();
</script>

@stack('scripts')
</body>
</html>
