@extends('layouts.app')
@section('title', 'Persetujuan Surat Keluar (ACC)')

@section('content')
@php
    $user = Auth::user();
    $role = $user->role ? $user->role->nama_role : '';
@endphp

<!-- Antrean Menunggu ACC -->
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Antrean Persetujuan Surat Keluar</div>
            <div class="card-subtitle">
                Tinjau naskah surat dinas yang diajukan staf sebelum diterbitkan nomor surat otomatis
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Tahap ACC</th>
                    <th>Perihal &amp; Jenis Surat</th>
                    <th>Tujuan Penerima</th>
                    <th>Pembuat Draft &amp; Unit</th>
                    <th>Sifat</th>
                    <th>Tgl Pengajuan</th>
                    <th width="140">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarAcc as $acc)
                    @php $sk = $acc->suratKeluar; @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
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
                        <td>{{ \App\Services\ArsitaService::tglIndo($sk?->tanggal_draft) }}</td>
                        <td>
                            <a href="{{ route('persetujuan.show', $acc->id_acc) }}" class="btn btn-primary btn-sm">
                                Tinjau Surat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            🎉 Tidak ada antrean surat keluar yang membutuhkan persetujuan Anda saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Riwayat Persetujuan Terakhir -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Riwayat Persetujuan Terakhir Anda</div>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tgl Keputusan</th>
                    <th>Perihal Surat</th>
                    <th>Keputusan Anda</th>
                    <th>Nomor / Status Surat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayatAcc as $r)
                    @php $sk = $r->suratKeluar; @endphp
                    <tr>
                        <td>{{ $r->tanggal ? date('d/m/Y H:i', strtotime($r->tanggal)) : '-' }}</td>
                        <td><b>{{ $sk?->perihal ?? '-' }}</b></td>
                        <td>{!! \App\Services\ArsitaService::badgeStatus($r->status) !!}</td>
                        <td>
                            @if($sk?->nomorSurat?->nomor_surat)
                                <b>{{ $sk->nomorSurat->nomor_surat }}</b>
                            @else
                                {!! \App\Services\ArsitaService::badgeStatus($sk?->status_surat ?? 'draft') !!}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada riwayat persetujuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
