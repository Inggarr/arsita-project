@extends('layouts.app')
@section('title', 'Daftar Surat Keluar')

@section('content')
@php
    $user = Auth::user();
    $role = $user->role ? $user->role->nama_role : '';
    $unit_nama = $user->unit ? $user->unit->nama_unit : '';
@endphp

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Surat Keluar {{ $unit_nama }}</div>
            <div class="card-subtitle">Pengelolaan draft, persetujuan (ACC), penomoran otomatis, dan cetak dokumen resmi</div>
        </div>
        @if(in_array($role, ['admin_rektorat', 'staff_tu']))
            <a href="{{ route('surat-keluar.create') }}" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Buat Surat Keluar Baru
            </a>
        @endif
    </div>

    <!-- Filter Form Identik Asli -->
    <form method="GET" action="{{ route('surat-keluar.index') }}" class="d-flex gap-3 flex-wrap mb-4">
        <input type="text" name="q" placeholder="Cari perihal, tujuan, nomor..." value="{{ request('q') }}" class="flex-1" style="min-width:200px">
        <select name="jenis" style="flex:1;min-width:150px">
            <option value="">Semua Jenis Surat</option>
            @foreach($jenisList as $j)
                <option value="{{ $j->id_jenis_surat }}" {{ request('jenis') == $j->id_jenis_surat ? 'selected' : '' }}>
                    {{ $j->nama_jenis }} ({{ $j->kode_jenis }})
                </option>
            @endforeach
        </select>
        <select name="status" style="flex:1;min-width:150px">
            <option value="">Semua Status</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="menunggu_acc" {{ request('status') == 'menunggu_acc' ? 'selected' : '' }}>Menunggu ACC</option>
            <option value="revisi" {{ request('status') == 'revisi' ? 'selected' : '' }}>Revisi Diperlukan</option>
            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui &amp; Bernomor</option>
            <option value="diarsipkan" {{ request('status') == 'diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request('q') || request('status') || request('jenis'))
            <a href="{{ route('surat-keluar.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Nomor Surat Resmi</th>
                    <th>Jenis &amp; Kategori</th>
                    <th>Tujuan</th>
                    <th>Perihal</th>
                    <th>Sifat</th>
                    <th>Status</th>
                    <th width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suratKeluar as $sk)
                    <tr>
                        <td>{{ $loop->iteration + ($suratKeluar->currentPage() - 1) * $suratKeluar->perPage() }}</td>
                        <td>
                            @if($sk->nomorSurat && $sk->nomorSurat->nomor_surat)
                                <b class="text-brand">{{ $sk->nomorSurat->nomor_surat }}</b>
                            @else
                                <span class="text-muted text-xs" style="font-style:italic">(Belum digenerate - ACC)</span>
                            @endif
                            <br><small class="text-muted">{{ \App\Services\ArsitaService::tglIndo($sk->tanggal_draft ?? $sk->created_at) }}</small>
                        </td>
                        <td>
                            <b>{{ $sk->jenisSurat->nama_jenis ?? '-' }}</b><br>
                            <small class="text-muted">{{ $sk->kategori->nama_kategori ?? 'Umum' }}</small>
                        </td>
                        <td>{{ $sk->tujuan ?: '-' }}</td>
                        <td>
                            <b>{{ $sk->perihal }}</b><br>
                            <small class="text-muted">Pembuat: {{ $sk->pembuat->nama ?? $sk->pembuat->name ?? '-' }}</small>
                        </td>
                        <td>{!! \App\Services\ArsitaService::badgeStatus($sk->sifat ?? 'biasa') !!}</td>
                        <td>{!! \App\Services\ArsitaService::badgeStatus($sk->status_surat) !!}</td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('surat-keluar.show', $sk->id_surat_keluar) }}" class="btn btn-secondary btn-sm">Detail</a>
                                <a href="{{ route('surat-keluar.cetak', $sk->id_surat_keluar) }}" target="_blank" class="btn btn-secondary btn-sm" title="Cetak Surat">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @if(in_array($sk->status_surat, ['draft', 'revisi']) && ($sk->dibuat_oleh == $user->id_user || $role === 'admin_rektorat'))
                                    <form action="{{ route('surat-keluar.ajukan', $sk->id_surat_keluar) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm" style="background: #d97706; color: #fff; border: none;" title="Ajukan ACC ke Pimpinan"
                                            data-confirm="Ajukan draft surat keluar ini ke pimpinan untuk proses persetujuan (ACC)? Setelah diajukan, draf akan diperiksa sebelum nomor resmi terbit."
                                            data-confirm-title="Ajukan Persetujuan (ACC)"
                                            data-confirm-type="info"
                                            data-confirm-btn="Ya, Ajukan Surat"
                                            data-confirm-class="btn-primary">
                                            <i class="bi bi-send"></i> Ajukan
                                        </button>
                                    </form>
                                    <a href="{{ route('surat-keluar.edit', $sk->id_surat_keluar) }}" class="btn btn-primary btn-sm">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            Tidak ada surat keluar yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suratKeluar->hasPages())
        <div style="margin-top: 20px; display: flex; justify-content: center;">
            {{ $suratKeluar->links() }}
        </div>
    @endif
</div>
@endsection
