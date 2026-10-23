@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $user = Auth::user();
    $role = $user->role ? $user->role->nama_role : '';
@endphp

<!-- Kartu Ringkasan Statistik -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-inbox-fill"></i></div>
        <div>
            <div class="stat-value">{{ number_format($totalSuratMasuk) }}</div>
            <div class="stat-label">Surat Masuk</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-send-fill"></i></div>
        <div>
            <div class="stat-value">{{ number_format($totalSuratKeluar) }}</div>
            <div class="stat-label">Surat Keluar</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="stat-value">{{ number_format($disposisiMenunggu) }}</div>
            <div class="stat-label">Disposisi Pending</div>
        </div>
    </div>

    @if($role === 'pimpinan')
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-pencil-square"></i></div>
        <div>
            <div class="stat-value">{{ number_format($suratKeluarMenungguAcc) }}</div>
            <div class="stat-label">Menunggu ACC Anda</div>
        </div>
    </div>
    @else
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-buildings-fill"></i></div>
        <div>
            <div class="stat-value">{{ $user->unit ? $user->unit->level : 'Unit' }}</div>
            <div class="stat-label">Level Wilayah Kerja</div>
        </div>
    </div>
    @endif
</div>

<!-- Aksi Cepat / Shortcut -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Aksi Cepat</div>
    </div>
    <div class="quick-actions">
        @if(in_array($role, ['admin_rektorat', 'staff_tu']))
            <a href="{{ route('surat-masuk.create') }}" class="btn btn-primary">
                <i class="bi bi-file-earmark-plus"></i> Catat Surat Masuk
            </a>
            <a href="{{ route('surat-keluar.create') }}" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Buat Surat Keluar
            </a>
        @endif

        @if($role === 'pimpinan')
            <a href="{{ route('persetujuan.index') }}" class="btn btn-success">
                <i class="bi bi-patch-check"></i> Buka Persetujuan (ACC)
            </a>
            <a href="{{ route('surat-masuk.index') }}" class="btn btn-primary">
                <i class="bi bi-search"></i> Tinjau Surat Masuk & Disposisi
            </a>
        @endif

        <a href="{{ route('arsip.index') }}" class="btn btn-secondary">
            <i class="bi bi-archive"></i> Cari Arsip Surat
        </a>
    </div>
</div>

@if(in_array($role, ['pimpinan', 'admin_rektorat']) && $pendingAccList->count() > 0)
<!-- Surat Keluar Menunggu ACC Pimpinan -->
<div class="card" style="border: 2px solid #fed7aa; margin-bottom: 24px;">
    <div class="card-header" style="background: #fff7ed;">
        <div>
            <div class="card-title" style="color: #9a3412;">
                <i class="bi bi-exclamation-circle-fill text-warning"></i> Surat Keluar Menunggu Persetujuan (ACC) Anda
            </div>
            <div class="card-subtitle">
                Segera tinjau draft naskah dinas berikut agar nomor surat resmi dapat diterbitkan.
            </div>
        </div>
        <a href="{{ route('persetujuan.index') }}" class="btn btn-warning btn-sm" style="background: #ea580c; color: #fff; border: none;">
            Lihat Semua Antrean ({{ $pendingAccList->count() }})
        </a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tahap</th>
                    <th>Perihal &amp; Jenis</th>
                    <th>Tujuan Penerima</th>
                    <th>Pembuat &amp; Unit</th>
                    <th>Sifat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingAccList as $acc)
                    @php $sk = $acc->suratKeluar; @endphp
                    <tr>
                        <td>
                            <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 11px;">
                                Tahap {{ $acc->tahap_ke }}
                            </span>
                        </td>
                        <td>
                            <b>{{ $sk?->perihal ?? '-' }}</b><br>
                            <small class="text-muted">{{ $sk?->jenisSurat?->nama_jenis ?? '-' }}</small>
                        </td>
                        <td>{{ $sk?->tujuan ?? '-' }}</td>
                        <td>
                            <b>{{ $sk?->pembuat?->nama ?? $sk?->pembuat?->name ?? '-' }}</b><br>
                            <small class="text-muted">{{ $sk?->unit?->nama_unit ?? '-' }}</small>
                        </td>
                        <td>{!! \App\Services\ArsitaService::badgeStatus($sk?->sifat ?? 'biasa') !!}</td>
                        <td>
                            <a href="{{ route('persetujuan.show', $acc->id_acc) }}" class="btn btn-primary btn-sm">
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
    <div class="card">
        <div class="card-header">
            <div class="card-title">Surat Masuk Terbaru</div>
            <a href="{{ route('surat-masuk.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tgl Terima</th>
                        <th>No. Asal & Pengirim</th>
                        <th>Perihal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSuratMasuk as $sm)
                        <tr>
                            <td>{{ $sm->tanggal_terima ? date('d/m/Y', strtotime($sm->tanggal_terima)) : '-' }}</td>
                            <td>
                                <b>{{ $sm->no_surat_asal ?: '-' }}</b><br>
                                <small class="text-muted">{{ $sm->asal_surat ?: '-' }}</small>
                            </td>
                            <td>
                                <a href="{{ route('surat-masuk.show', $sm->id_surat_masuk) }}" class="text-brand font-500">
                                    {{ $sm->perihal }}
                                </a>
                            </td>
                            <td>{!! \App\Services\ArsitaService::badgeStatus($sm->status_arsip) !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada surat masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Disposisi Tugas Anda -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Tugas Disposisi Anda</div>
            <a href="{{ route('disposisi.index') }}" class="btn btn-secondary btn-sm">Buka Detail</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Perihal Surat</th>
                        <th>Instruksi</th>
                        <th>Sifat</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeDisposisi as $dt)
                        <tr>
                            <td>
                                <b>{{ $dt->disposisi && $dt->disposisi->suratMasuk ? $dt->disposisi->suratMasuk->perihal : '-' }}</b><br>
                                <small class="text-muted">Dari: {{ $dt->disposisi && $dt->disposisi->pengirim ? $dt->disposisi->pengirim->nama : '-' }}</small>
                            </td>
                            <td>{{ Str::limit($dt->disposisi ? $dt->disposisi->instruksi : '-', 45) }}</td>
                            <td>{!! \App\Services\ArsitaService::badgeStatus($dt->disposisi ? $dt->disposisi->sifat : '-') !!}</td>
                            <td>
                                <a href="{{ route('disposisi.show', $dt->id_disposisi_tujuan) }}">
                                    {!! \App\Services\ArsitaService::badgeStatus($dt->status) !!}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding:24px">Tidak ada disposisi aktif saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
