@extends('layouts.app')
@section('title', 'Pusat Arsip Surat Digital Terpadu')

@section('content')
<div class="card">
    <div class="card-header" style="align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div class="card-title">Pusat Arsip Surat Digital Terpadu</div>
            <div class="card-subtitle">
                Telusuri seluruh riwayat arsip naskah dinas masuk dan keluar secara cepat dan akurat
            </div>
        </div>

        <!-- Tab Skat Pemisah Tipe Surat -->
        <div style="display: inline-flex; background: #f1f5f9; padding: 4px; border-radius: 10px; gap: 4px; border: 1px solid #e2e8f0;">
            <a href="{{ route('arsip.index', array_merge(request()->except('tipe'), ['tipe' => 'semua'])) }}" 
               style="padding: 7px 16px; border-radius: 7px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; {{ $tipe === 'semua' ? 'background: #ffffff; color: var(--color-primary); box-shadow: 0 1px 3px rgba(0,0,0,0.08);' : 'color: #64748b;' }}">
                <i class="bi bi-collection-fill"></i> Semua Arsip
            </a>
            <a href="{{ route('arsip.index', array_merge(request()->except('tipe'), ['tipe' => 'masuk'])) }}" 
               style="padding: 7px 16px; border-radius: 7px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; {{ $tipe === 'masuk' ? 'background: #2563eb; color: #ffffff; box-shadow: 0 1px 3px rgba(37,99,235,0.3);' : 'color: #64748b;' }}">
                <i class="bi bi-inbox-fill"></i> Surat Masuk
            </a>
            <a href="{{ route('arsip.index', array_merge(request()->except('tipe'), ['tipe' => 'keluar'])) }}" 
               style="padding: 7px 16px; border-radius: 7px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; {{ $tipe === 'keluar' ? 'background: #10b981; color: #ffffff; box-shadow: 0 1px 3px rgba(16,185,129,0.3);' : 'color: #64748b;' }}">
                <i class="bi bi-send-fill"></i> Surat Keluar
            </a>
        </div>
    </div>

    <!-- Filter Pencarian Lengkap (Responsive Grid Tanpa Overflow) -->
    <form method="GET" action="{{ route('arsip.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 24px;">
        <input type="hidden" name="tipe" value="{{ $tipe }}">

        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 12px; font-weight: 600; margin-bottom: 6px; display: block; color: var(--text-dark);">Kata Kunci Pencarian</label>
            <input type="text" name="q" placeholder="Nomor, perihal, pihak..." value="{{ $q }}">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 12px; font-weight: 600; margin-bottom: 6px; display: block; color: var(--text-dark);">Kategori Bidang</label>
            <select name="kategori">
                <option value="">Semua Kategori</option>
                @foreach($kategoriList as $k)
                    <option value="{{ $k->id_kategori }}" {{ $kategori_id == $k->id_kategori ? 'selected' : '' }}>
                        {{ $k->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 12px; font-weight: 600; margin-bottom: 6px; display: block; color: var(--text-dark);">Rentang Tanggal Mulai</label>
            <input type="date" name="tgl_mulai" value="{{ $tgl_mulai }}">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 12px; font-weight: 600; margin-bottom: 6px; display: block; color: var(--text-dark);">Rentang Tanggal Sampai</label>
            <input type="date" name="tgl_selesai" value="{{ $tgl_selesai }}">
        </div>

        <div style="display: flex; align-items: flex-end; gap: 8px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="bi bi-search"></i> Cari</button>
            <a href="{{ route('arsip.index', ['tipe' => $tipe]) }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- Tabel Arsip Terpadu Fit Layar (Tanpa Perlu Digeser Horizontal) -->
    <div style="width: 100%;">
        <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 42px; text-align: center;">No</th>
                    <th style="width: 95px;">Tipe</th>
                    <th style="width: 22%;">Nomor & Tanggal</th>
                    <th>Perihal & Pihak Terkait</th>
                    <th style="width: 14%;">Kategori</th>
                    <th style="width: 130px;">Status</th>
                    <th style="width: 80px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $index => $r)
                    <tr>
                        <td style="text-align: center; color: var(--text-light); font-weight: 600;">{{ $index + 1 }}</td>
                        <td>
                            @if($r['tipe_surat'] === 'masuk')
                                <span style="background: #dbeafe; color: #1e40af; font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="bi bi-inbox-fill"></i> MASUK
                                </span>
                            @else
                                <span style="background: #dcfce7; color: #15803d; font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="bi bi-send-fill"></i> KELUAR
                                </span>
                            @endif
                        </td>
                        <td style="word-break: break-word;">
                            <strong style="color: var(--text-dark); display: block; line-height: 1.35; margin-bottom: 2px;">{{ $r['nomor_tampil'] }}</strong>
                            <small style="color: var(--text-light); font-size: 11.5px; display: flex; align-items: center; gap: 4px;">
                                <i class="bi bi-calendar3"></i> {{ \App\Services\ArsitaService::tglIndo($r['tanggal_dokumen']) }}
                            </small>
                        </td>
                        <td style="word-break: break-word;">
                            <strong style="color: var(--text-dark); display: block; line-height: 1.35; margin-bottom: 3px;">{{ $r['perihal'] }}</strong>
                            <div style="font-size: 12px; color: var(--text-medium); display: flex; align-items: center; gap: 5px;">
                                <i class="bi {{ $r['tipe_surat'] === 'masuk' ? 'bi-geo-alt' : 'bi-send' }}" style="font-size: 11px; color: var(--text-light);"></i>
                                <span>{{ $r['pihak_terkait'] ?: '-' }}</span>
                            </div>
                        </td>
                        <td style="word-break: break-word; font-size: 12.5px; color: var(--text-medium);">
                            {{ $r['nama_kategori'] ?: 'Umum' }}
                        </td>
                        <td>
                            {!! \App\Services\ArsitaService::badgeStatus($r['status_tampil']) !!}
                        </td>
                        <td style="text-align: center;">
                            @if($r['tipe_surat'] === 'masuk')
                                <a href="{{ route('surat-masuk.show', $r['id_ref']) }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 12px;"><i class="bi bi-eye"></i> Buka</a>
                            @else
                                <a href="{{ route('surat-keluar.show', $r['id_ref']) }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 12px;"><i class="bi bi-eye"></i> Buka</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding: 40px 16px;">
                            <i class="bi bi-search" style="font-size: 32px; display: block; margin-bottom: 10px; color: var(--text-light);"></i>
                            Tidak ditemukan arsip surat yang sesuai kriteria pencarian Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
