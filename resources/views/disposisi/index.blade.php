@extends('layouts.app')
@section('title', 'Disposisi Masuk')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Disposisi & Tugas Masuk</div>
            <div class="card-subtitle">
                Daftar instruksi dan arahan dinas dari pimpinan yang perlu ditindaklanjuti
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Perihal &amp; Asal Surat</th>
                    <th>Pemberi Disposisi</th>
                    <th>Sifat</th>
                    <th>Tenggat Waktu</th>
                    <th>Instruksi</th>
                    <th>Status</th>
                    <th width="140">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disposisiMasuk as $dt)
                    @php 
                        $d = $dt->disposisi; 
                        $sm = $d?->suratMasuk;
                        $tl = $dt->tindakLanjut->first(); // hasMany, ambil terbaru
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration + ($disposisiMasuk->currentPage() - 1) * $disposisiMasuk->perPage() }}</td>
                        <td>
                            <b>{{ $sm->perihal ?? '-' }}</b><br>
                            <small class="text-muted">
                                No: {{ $sm->no_surat_asal ?? '-' }} &bull; Dari: {{ $sm->asal_surat ?? '-' }}
                            </small>
                        </td>
                        <td>
                            <b>{{ $d?->pengirim?->nama ?? $d?->pengirim?->name ?? '-' }}</b><br>
                            <small class="text-muted">{{ $d?->pengirim?->unit?->nama_unit ?? '-' }}</small>
                        </td>
                        <td>{!! \App\Services\ArsitaService::badgeStatus($d->sifat ?? 'biasa') !!}</td>
                        <td>{{ \App\Services\ArsitaService::tglIndo($d->tenggat_waktu) }}</td>
                        <td>
                            {{ Str::limit($d->instruksi ?? '-', 60) }}
                            @if($tl && $tl->catatan)
                                <div style="margin-top: 6px; padding: 4px 8px; background: #f0fdf4; border-left: 3px solid #22c55e; border-radius: 4px; font-size: 11.5px; color: #166534;">
                                    <b>Laporan:</b> {{ Str::limit($tl->catatan, 50) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            {!! \App\Services\ArsitaService::badgeStatus($dt->status) !!}
                            @if($dt->status === 'selesai')
                                <br><small style="color: var(--success); font-weight: 600; font-size: 11px;"><i class="bi bi-check-circle-fill"></i> Tuntas</small>
                            @endif
                        </td>
                        <td>
                            @if($dt->status === 'selesai')
                                <a href="{{ route('disposisi.show', $dt->id_disposisi_tujuan) }}" class="btn btn-secondary btn-sm" title="Lihat atau edit hasil laporan">
                                    <i class="bi bi-pencil-square"></i> Ubah Laporan
                                </a>
                            @elseif($dt->status === 'diproses')
                                <a href="{{ route('disposisi.show', $dt->id_disposisi_tujuan) }}" class="btn btn-primary btn-sm" style="background:#d97706; border-color:#d97706;" title="Lanjutkan progres tugas">
                                    <i class="bi bi-arrow-repeat"></i> Lanjutkan
                                </a>
                            @else
                                <a href="{{ route('disposisi.show', $dt->id_disposisi_tujuan) }}" class="btn btn-primary btn-sm" title="Tindak lanjuti instruksi">
                                    <i class="bi bi-play-fill"></i> Tindak Lanjut
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            Tidak ada tugas disposisi yang perlu diproses saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($disposisiMasuk->hasPages())
        <div style="margin-top: 20px; display: flex; justify-content: center;">
            {{ $disposisiMasuk->links() }}
        </div>
    @endif
</div>
@endsection
