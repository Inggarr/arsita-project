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

    // Badge Surat Keluar Perlu Revisi untuk Staf Pembuat
    $badge_sk_revisi = 0;
    if (in_array($role, ['staff_tu', 'admin_unit', 'admin_rektorat'])) {
        if ($role === 'admin_rektorat') {
            $badge_sk_revisi = \App\Models\SuratKeluar::where('status_surat', 'revisi')->count();
        } else {
            $badge_sk_revisi = \App\Models\SuratKeluar::where('status_surat', 'revisi')
                ->where(function($q) use ($id_user, $id_unit) {
                    $q->where('dibuat_oleh', $id_user)
                      ->orWhere('id_unit', $id_unit);
                })->count();
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
                @if($badge_sk_revisi > 0)
                    <span class="menu-badge" style="background: rgba(220, 38, 38, 0.25); color: #f87171; border-color: rgba(220, 38, 38, 0.5);" title="{{ $badge_sk_revisi }} surat butuh revisi">{{ $badge_sk_revisi }}</span>
                @endif
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

        <div class="topbar-right" style="display: flex; align-items: center; gap: 14px;">
            <!-- Notifikasi Lonceng Dropdown -->
            <div class="notif-bell-wrapper" id="notif-wrapper" style="position: relative; display: inline-flex; align-items: center;">
                <button type="button" class="notif-bell-btn" id="notif-toggle-btn" title="Notifikasi Sistem" aria-label="Notifikasi" style="position: relative; width: 40px; height: 40px; border-radius: 10px; background: #f8fafc; border: 1.5px solid #e2e8f0; color: #475569; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; cursor: pointer; transition: all 0.2s ease; outline: none; padding: 0;">
                    <i class="bi bi-bell-fill"></i>
                    <span class="notif-badge-count" id="notif-badge" style="display: none; position: absolute; top: -5px; right: -5px; background: #dc2626; color: #ffffff; font-size: 10px; font-weight: 700; min-width: 18px; height: 18px; padding: 0 4px; border-radius: 9999px; align-items: center; justify-content: center; border: 2px solid #ffffff; line-height: 1; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4); z-index: 2;">0</span>
                </button>

                <!-- Panel Dropdown Notifikasi -->
                <div class="notif-dropdown" id="notif-dropdown" style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 380px; max-width: calc(100vw - 32px); background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.16), 0 8px 16px -6px rgba(15, 23, 42, 0.08); z-index: 9999; flex-direction: column; overflow: hidden;">
                    <!-- Header -->
                    <div class="notif-header" style="display: flex; align-items: center; justify-content: space-between; padding: 13px 18px; background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%); border-bottom: 1px solid #e2e8f0;">
                        <div class="notif-header-title" style="font-size: 13.5px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-bell" style="font-size: 16px; color: #1e40af;"></i> Notifikasi
                        </div>
                        <button type="button" class="notif-header-action" id="notif-mark-all-btn" style="font-size: 11.5px; color: #1e40af; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; cursor: pointer; font-weight: 600; padding: 4px 10px; transition: all 0.15s ease; white-space: nowrap;">
                            <i class="bi bi-check2-all"></i> Tandai semua dibaca
                        </button>
                    </div>

                    <!-- List Notifikasi -->
                    <div class="notif-list" id="notif-items-list" style="max-height: 390px; overflow-y: auto; display: flex; flex-direction: column; background: #ffffff;">
                        <div class="notif-empty" style="padding: 36px 20px; text-align: center; color: #94a3b8; font-size: 13px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: #f8fafc; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #cbd5e1; margin-bottom: 4px;">
                                <i class="bi bi-bell-slash"></i>
                            </div>
                            <div style="font-weight: 600; color: #64748b;">Tidak ada notifikasi baru</div>
                            <div style="font-size: 11.5px; color: #94a3b8;">Notifikasi revisi atau update naskah dinas akan muncul di sini</div>
                        </div>
                    </div>
                </div>
            </div>

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

// Real-time Notifikasi Polling & Dropdown Toggle
(function() {
    var toggleBtn = document.getElementById('notif-toggle-btn');
    var dropdown = document.getElementById('notif-dropdown');
    var badge = document.getElementById('notif-badge');
    var itemsList = document.getElementById('notif-items-list');
    var markAllBtn = document.getElementById('notif-mark-all-btn');

    if (!toggleBtn || !dropdown) return;

    var previousUnreadCount = 0;
    var isFirstLoad = true;

    // Toggle dropdown
    toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = dropdown.style.display === 'flex' || dropdown.classList.contains('show');
        if (isOpen) {
            dropdown.style.display = 'none';
            dropdown.classList.remove('show');
        } else {
            dropdown.style.display = 'flex';
            dropdown.classList.add('show');
        }
    });

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
            dropdown.style.display = 'none';
            dropdown.classList.remove('show');
        }
    });

    // Mark all as read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch("{{ route('notifikasi.mark-all') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(function(res) { return res.json(); })
              .then(function() {
                  loadNotifications();
              }).catch(function(err) { console.error(err); });
        });
    }

    // Function to render notification items
    function renderNotifications(data, unreadCount) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }

        if (!data || data.length === 0) {
            itemsList.innerHTML = '<div class="notif-empty"><i class="bi bi-bell-slash"></i>Tidak ada notifikasi</div>';
            return;
        }

        var html = '';
        data.forEach(function(item) {
            var iconClass = 'icon-info';
            var iconBi = 'bi-info-circle-fill';

            if (item.tipe === 'revisi') {
                iconClass = 'icon-revisi';
                iconBi = 'bi-exclamation-octagon-fill';
            } else if (item.tipe === 'success') {
                iconClass = 'icon-success';
                iconBi = 'bi-check-circle-fill';
            }

            var unreadClass = !item.is_read ? 'unread' : '';
            var readUrl = "{{ url('notifikasi') }}/" + item.id + "/baca";

            html += '<a href="' + readUrl + '" class="notif-item ' + unreadClass + '">';
            html += '  <div class="notif-item-icon ' + iconClass + '"><i class="bi ' + iconBi + '"></i></div>';
            html += '  <div class="notif-item-content">';
            html += '    <div class="notif-item-title">' + escapeHtml(item.judul) + '</div>';
            html += '    <div class="notif-item-msg">' + escapeHtml(item.pesan) + '</div>';
            html += '    <small class="notif-item-time"><i class="bi bi-clock"></i> ' + item.waktu + ' · Oleh: ' + escapeHtml(item.pengirim) + '</small>';
            html += '  </div>';
            html += '</a>';
        });

        itemsList.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function loadNotifications() {
        fetch("{{ route('notifikasi.list') }}", {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            var unreadCount = result.unread_count || 0;
            renderNotifications(result.data, unreadCount);

            // Jika ada notifikasi revisi baru yang masuk secara real-time saat user sedang online
            if (!isFirstLoad && unreadCount > previousUnreadCount) {
                var latest = result.data[0];
                if (latest && !latest.is_read && typeof arsitaToast === 'function') {
                    var toastType = latest.tipe === 'revisi' ? 'danger' : (latest.tipe === 'success' ? 'success' : 'info');
                    arsitaToast(toastType, latest.judul + ': ' + latest.pesan, 7000);
                }
            }

            previousUnreadCount = unreadCount;
            isFirstLoad = false;
        })
        .catch(function(err) {
            console.error('Gagal mengambil notifikasi:', err);
        });
    }

    // Load pertama kali
    loadNotifications();

    // Polling setiap 15 detik agar langsung update tanpa refresh manual
    setInterval(loadNotifications, 15000);
})();
</script>

@stack('scripts')
</body>
</html>
