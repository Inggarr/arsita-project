@extends('layouts.app')

@section('title', 'Dashboard Eksekutif')

@section('content')
@php
    $user = Auth::user();
    $role = $user->role ? $user->role->nama_role : '';
    $unitNama = $user->unit ? $user->unit->nama_unit : 'Unit Kerja';
    $unitKode = $user->unit ? ($user->unit->kode_unit ?? '') : '';
    $userNama = $user->nama ?? $user->name ?? 'Pengguna';
    $hariTanggal = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y');
@endphp

<!-- Hero / Executive Greeting Banner Sesuai Gambar Referensi -->
<div class="dashboard-hero">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
            <span class="hero-tagline">
                <i class="bi bi-shield-check"></i> Sistem Aktif &amp; Terverifikasi BSrE
            </span>
            <span style="font-size: 12.5px; color: #6b7280; font-weight: 500;">
                • {{ $hariTanggal }}
            </span>
        </div>
        <h1 class="hero-title">
            Selamat Datang, {{ $userNama }}
        </h1>
        <p class="hero-desc">
            {{ $unitNama }} {{ $unitKode ? '('.$unitKode.')' : '' }}. 
            @if($role === 'pimpinan' && $pendingAccList->count() > 0)
                Tinjau {{ $pendingAccList->count() }} antrean persetujuan naskah dan {{ $disposisiMenunggu }} disposisi tertunda untuk hari ini.
            @elseif($disposisiMenunggu > 0)
                Terdapat {{ $disposisiMenunggu }} disposisi dan tugas yang memerlukan tindak lanjut Anda.
            @else
                Semua naskah dinas dan disposisi berjalan optimal dan terpantau dalam sistem.
            @endif
        </p>
    </div>

    <div class="hero-actions">
        @if(in_array($role, ['pimpinan', 'admin_rektorat', 'staff_tu', 'admin_unit']))
            <a href="{{ route('disposisi.index') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 13.5px;">
                <i class="bi bi-box-arrow-up-right"></i> Buat Disposisi
            </a>
        @endif

        @if(in_array($role, ['admin_rektorat', 'staff_tu', 'admin_unit']))
            <a href="{{ route('surat-masuk.create') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 13.5px; background: #ffffff;">
                <i class="bi bi-cloud-arrow-up"></i> Unggah Naskah
            </a>
        @elseif($role === 'pimpinan')
            <a href="{{ route('surat-keluar.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 13.5px; background: #ffffff;">
                <i class="bi bi-journal-check"></i> Monitoring Naskah
            </a>
        @endif
    </div>
</div>

<!-- 4 Kartu Statistik Modern Sesuai Referensi -->
<div class="stats-grid">
    <!-- Stat 1: Surat Masuk -->
    <div class="stat-card-modern">
        <div class="stat-card-top">
            <div class="stat-card-icon emerald">
                <i class="bi bi-inbox"></i>
            </div>
            <span class="stat-pill emerald">
                Aktif
            </span>
        </div>
        <div class="stat-card-bottom">
            <div>
                <div class="stat-card-value">{{ number_format($totalSuratMasuk) }}</div>
                <div class="stat-card-label">Surat Masuk Aktif</div>
            </div>
            <a href="{{ route('surat-masuk.index') }}" class="stat-card-link">
                Detail <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Stat 2: Surat Keluar -->
    <div class="stat-card-modern">
        <div class="stat-card-top">
            <div class="stat-card-icon blue">
                <i class="bi bi-send"></i>
            </div>
            <span class="stat-pill gray">
                Total Periode
            </span>
        </div>
        <div class="stat-card-bottom">
            <div>
                <div class="stat-card-value">{{ number_format($totalSuratKeluar) }}</div>
                <div class="stat-card-label">Surat Keluar {{ $user->unit ? $user->unit->level : 'Unit' }}</div>
            </div>
            <a href="{{ route('surat-keluar.index') }}" class="stat-card-link">
                Detail <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Stat 3: Disposisi Pending -->
    <div class="stat-card-modern">
        <div class="stat-card-top">
            <div class="stat-card-icon amber">
                <i class="bi bi-journal-text"></i>
            </div>
            <span class="stat-pill amber">
                Perlu Aksi
            </span>
        </div>
        <div class="stat-card-bottom">
            <div>
                <div class="stat-card-value">{{ number_format($disposisiMenunggu) }}</div>
                <div class="stat-card-label">Disposisi Pending</div>
            </div>
            <a href="{{ route('disposisi.index') }}" class="stat-card-link">
                Proses <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Stat 4: Menunggu ACC (Khusus Pimpinan / Admin) atau Arsip Surat -->
    @if(in_array($role, ['pimpinan', 'admin_rektorat']))
    <div class="stat-card-modern">
        <div class="stat-card-top">
            <div class="stat-card-icon orange">
                <i class="bi bi-pen"></i>
            </div>
            @if($suratKeluarMenungguAcc > 0)
                <span class="stat-pill orange">{{ $suratKeluarMenungguAcc }} Prioritas</span>
            @else
                <span class="stat-pill gray">Selesai</span>
            @endif
        </div>
        <div class="stat-card-bottom">
            <div>
                <div class="stat-card-value">{{ number_format($suratKeluarMenungguAcc) }}</div>
                <div class="stat-card-label">Menunggu ACC</div>
            </div>
            <a href="{{ route('persetujuan.index') }}" class="stat-card-link" style="color: #ea580c;">
                ACC Segera <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
    @else
    <div class="stat-card-modern">
        <div class="stat-card-top">
            <div class="stat-card-icon emerald">
                <i class="bi bi-buildings"></i>
            </div>
            <span class="stat-pill gray">Unit Kerja</span>
        </div>
        <div class="stat-card-bottom">
            <div>
                <div class="stat-card-value" style="font-size: 22px;">{{ $user->unit ? $user->unit->level : 'Unit' }}</div>
                <div class="stat-card-label">{{ Str::limit($unitNama, 20) }}</div>
            </div>
            <a href="{{ route('arsip.index') }}" class="stat-card-link">
                Arsip <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Aksi Cepat Eksekutif Sesuai Referensi Gambar -->
<div style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <div style="font-size: 14px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 6px;">
            <i class="bi bi-lightning-charge-fill" style="color: #047857;"></i> Aksi Cepat Eksekutif
        </div>
        <span style="font-size: 12px; color: #6b7280;">Pintasan tugas harian naskah &amp; administrasi dinas</span>
    </div>

    <div class="action-cards-grid">
        @if(in_array($role, ['pimpinan', 'admin_rektorat']))
            <!-- Card 1: Buka Persetujuan (ACC) -->
            <a href="{{ route('persetujuan.index') }}" class="action-card-item">
                <div class="action-card-icon">
                    <i class="bi bi-patch-check"></i>
                </div>
                <div>
                    <div class="action-card-title">Buka Persetujuan (ACC)</div>
                    <div class="action-card-desc">{{ $suratKeluarMenungguAcc }} naskah dinas menunggu tanda tangan</div>
                </div>
                <i class="bi bi-arrow-right" style="margin-left: auto; color: #9ca3af; font-size: 14px;"></i>
            </a>

            <!-- Card 2: Tinjau Surat Masuk & Disposisi (Featured Dark Emerald Card) -->
            <a href="{{ route('surat-masuk.index') }}" class="action-card-item featured">
                <div class="action-card-icon">
                    <i class="bi bi-layout-text-window-reverse"></i>
                </div>
                <div>
                    <div class="action-card-title">Tinjau Surat Masuk &amp; Disposisi</div>
                    <div class="action-card-desc">{{ $disposisiMenunggu }} disposisi surat siap ditindaklanjuti</div>
                </div>
                <i class="bi bi-arrow-right" style="margin-left: auto; color: #ffffff; font-size: 14px;"></i>
            </a>
        @else
            <!-- Untuk Staff TU / Admin Unit -->
            <a href="{{ route('surat-masuk.create') }}" class="action-card-item featured">
                <div class="action-card-icon">
                    <i class="bi bi-file-earmark-plus"></i>
                </div>
                <div>
                    <div class="action-card-title">Registrasi Surat Masuk</div>
                    <div class="action-card-desc">Catat surat masuk baru ke buku agenda</div>
                </div>
                <i class="bi bi-arrow-right" style="margin-left: auto; color: #ffffff; font-size: 14px;"></i>
            </a>

            <a href="{{ route('surat-keluar.create') }}" class="action-card-item">
                <div class="action-card-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <div class="action-card-title">Buat Naskah Dinas Baru</div>
                    <div class="action-card-desc">Draft surat keluar untuk verifikasi &amp; paraf</div>
                </div>
                <i class="bi bi-arrow-right" style="margin-left: auto; color: #9ca3af; font-size: 14px;"></i>
            </a>
        @endif

        <!-- Card 3: Cari Arsip Surat Cepat -->
        <a href="{{ route('arsip.index') }}" class="action-card-item">
            <div class="action-card-icon">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div>
                <div class="action-card-title">Cari Arsip Surat Cepat</div>
                <div class="action-card-desc">Klasifikasi agenda &amp; pelacakan kode naskah</div>
            </div>
            <i class="bi bi-arrow-right" style="margin-left: auto; color: #9ca3af; font-size: 14px;"></i>
        </a>
    </div>
</div>

@if(in_array($role, ['pimpinan', 'admin_rektorat']) && $pendingAccList->count() > 0)
<!-- Surat Keluar Menunggu ACC Pimpinan (Sesuai Referensi Gambar: Kotak Warning Oranye Lembut) -->
<div class="card" style="border: 1px solid #fed7aa; background: #fffcf7; border-radius: 14px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid #fef3c7; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: flex-start; gap: 14px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #ea580c; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; margin-top: 2px;">
                <i class="bi bi-exclamation-lg"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="font-size: 15px; font-weight: 700; color: #111827;">Surat Keluar Menunggu Persetujuan (ACC) Anda</div>
                    <span style="background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 700;">
                        Perlu ACC Segera
                    </span>
                </div>
                <div style="font-size: 12.5px; color: #6b7280; margin-top: 3px;">
                    Segera tinjau draft naskah dinas berikut agar nomor surat resmi dapat otomatis diterbitkan oleh Subbag Tata Usaha.
                </div>
            </div>
        </div>
        <a href="{{ route('persetujuan.index') }}" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #4b5563; text-decoration: none; padding: 6px 14px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;">
            <span>Lihat Semua Antrean</span>
            <span style="background: #ea580c; color: #fff; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">
                {{ $pendingAccList->count() }}
            </span>
        </a>
    </div>
    
    <div class="table-responsive" style="background: #ffffff;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #fafafa; border-bottom: 1px solid #e5e7eb; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">
                    <th style="padding: 12px 18px; text-align: left;">Tahap</th>
                    <th style="padding: 12px 18px; text-align: left;">Perihal &amp; Jenis Naskah</th>
                    <th style="padding: 12px 18px; text-align: left;">Tujuan Penerima</th>
                    <th style="padding: 12px 18px; text-align: left;">Pembuat &amp; Unit Kerja</th>
                    <th style="padding: 12px 18px; text-align: center;">Sifat</th>
                    <th style="padding: 12px 18px; text-align: right;">Aksi Eksekutif</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingAccList as $acc)
                    @php $sk = $acc->suratKeluar; @endphp
                    <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.15s ease;">
                        <td style="padding: 14px 18px;">
                            <span style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="bi bi-hourglass-split"></i> Tahap {{ $acc->tahap_ke }}
                            </span>
                        </td>
                        <td style="padding: 14px 18px;">
                            <div style="font-weight: 700; color: #111827; font-size: 13.5px;">{{ $sk?->perihal ?? '-' }}</div>
                            <small style="color: #6b7280; font-size: 12px;">{{ $sk?->jenisSurat?->nama_jenis ?? 'Surat Dinas' }}</small>
                        </td>
                        <td style="padding: 14px 18px; font-size: 13px; color: #374151;">
                            <div style="font-weight: 600;">{{ $sk?->tujuan ?? '-' }}</div>
                            <small style="color: #9ca3af;">Kampus Terpadu</small>
                        </td>
                        <td style="padding: 14px 18px;">
                            <div style="font-weight: 600; color: #111827; font-size: 13px;">{{ $sk?->pembuat?->nama ?? $sk?->pembuat?->name ?? '-' }}</div>
                            <small style="color: #6b7280; font-size: 12px;">{{ $sk?->unit?->nama_unit ?? '-' }}</small>
                        </td>
                        <td style="padding: 14px 18px; text-align: center;">
                            {!! \App\Services\ArsitaService::badgeStatus($sk?->sifat ?? 'biasa') !!}
                        </td>
                        <td style="padding: 14px 18px; text-align: right;">
                            <a href="{{ route('persetujuan.show', $acc->id_acc) }}" class="btn btn-primary btn-sm" style="background: #047857; border-color: #047857; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="bi bi-pencil-square"></i> Tinjau &amp; ACC
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="grid-2">
    <!-- Surat Masuk Terbaru -->
    <div class="card" style="border-radius: 14px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
        <div class="card-header" style="background: #ffffff; padding: 16px 20px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="card-title" style="font-size: 15px; font-weight: 700; color: #111827;">Surat Masuk Terbaru</div>
                <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">Arsip registrasi surat naskah masuk di {{ $user->unit ? $user->unit->level : 'Unit' }}</div>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <a href="{{ route('surat-masuk.index') }}" class="btn btn-secondary btn-sm" style="font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 6px; background: #f3f4f6; border: none; color: #374151;">
                    Semua ({{ $totalSuratMasuk }})
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #fafafa; border-bottom: 1px solid #e5e7eb; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">
                        <th style="padding: 10px 16px; text-align: left;">Tgl Terima</th>
                        <th style="padding: 10px 16px; text-align: left;">No. Asal &amp; Pengirim</th>
                        <th style="padding: 10px 16px; text-align: left;">Perihal</th>
                        <th style="padding: 10px 16px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSuratMasuk as $sm)
                        <tr onclick="window.location='{{ route('surat-masuk.show', $sm->id_surat_masuk) }}'" style="cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'" title="Klik untuk membuka detail surat masuk">
                            <td style="padding: 12px 16px; font-size: 12.5px; color: #6b7280; white-space: nowrap;">
                                {{ $sm->tanggal_terima ? date('d/m/Y', strtotime($sm->tanggal_terima)) : '-' }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: #111827; font-size: 13px;">{{ $sm->no_surat_asal ?: '-' }}</div>
                                <small style="color: #6b7280; font-size: 11.5px;">{{ $sm->asal_surat ?: '-' }}</small>
                            </td>
                            <td style="padding: 12px 16px;">
                                <a href="{{ route('surat-masuk.show', $sm->id_surat_masuk) }}" style="color: #047857; font-weight: 600; text-decoration: none; font-size: 13px;">
                                    {{ Str::limit($sm->perihal, 38) }}
                                </a>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                {!! \App\Services\ArsitaService::badgeStatus($sm->status_arsip) !!}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding: 24px; font-size: 13px;">Belum ada surat masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Disposisi Tugas Anda -->
    <div class="card" style="border-radius: 14px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
        <div class="card-header" style="background: #ffffff; padding: 16px 20px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="card-title" style="font-size: 15px; font-weight: 700; color: #111827;">Tugas Disposisi Anda</div>
                <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">Instruksi langsung untuk tindak lanjut unit</div>
            </div>
            <a href="{{ route('disposisi.index') }}" class="btn btn-secondary btn-sm" style="font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 6px; background: #f3f4f6; border: none; color: #374151;">
                Buka Detail
            </a>
        </div>
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #fafafa; border-bottom: 1px solid #e5e7eb; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">
                        <th style="padding: 10px 16px; text-align: left;">Perihal Surat</th>
                        <th style="padding: 10px 16px; text-align: left;">Instruksi</th>
                        <th style="padding: 10px 16px; text-align: center;">Sifat</th>
                        <th style="padding: 10px 16px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeDisposisi as $dt)
                        @php
                            $suratMasukId = $dt->disposisi && $dt->disposisi->id_surat_masuk ? $dt->disposisi->id_surat_masuk : null;
                            $targetUrl = $suratMasukId ? route('surat-masuk.show', $suratMasukId) : route('disposisi.show', $dt->id_disposisi_tujuan);
                        @endphp
                        <tr onclick="window.location='{{ $targetUrl }}'" style="cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'" title="Klik untuk membuka surat langsung">
                            <td style="padding: 12px 16px;">
                                <a href="{{ $targetUrl }}" style="color: #047857; text-decoration: none; font-size: 13px; font-weight: 700;">
                                    {{ Str::limit($dt->disposisi && $dt->disposisi->suratMasuk ? $dt->disposisi->suratMasuk->perihal : '-', 30) }}
                                </a><br>
                                <small style="color: #6b7280; font-size: 11.5px;">Dari: {{ $dt->disposisi && $dt->disposisi->pengirim ? $dt->disposisi->pengirim->nama : '-' }}</small>
                            </td>
                            <td style="padding: 12px 16px; font-size: 12.5px; color: #4b5563;">{{ Str::limit($dt->disposisi ? $dt->disposisi->instruksi : '-', 35) }}</td>
                            <td style="padding: 12px 16px; text-align: center;">{!! \App\Services\ArsitaService::badgeStatus($dt->disposisi ? $dt->disposisi->sifat : '-') !!}</td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <a href="{{ route('disposisi.show', $dt->id_disposisi_tujuan) }}" onclick="event.stopPropagation();" title="Buka Lembar Disposisi">
                                    {!! \App\Services\ArsitaService::badgeStatus($dt->status) !!}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding: 24px; font-size: 13px;">Tidak ada disposisi aktif saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

