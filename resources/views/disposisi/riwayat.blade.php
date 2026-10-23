@extends('layouts.app')
@section('title', 'Riwayat Disposisi Dibuat')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Monitoring Riwayat Disposisi</div>
            <div class="card-subtitle">
                Pantau real-time progres tindak lanjut dari seluruh instruksi disposisi yang telah Anda terbitkan
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Tgl Kirim</th>
                    <th>Surat Masuk</th>
                    <th>Tujuan Penerima</th>
                    <th>Instruksi</th>
                    <th>Sifat / Tenggat</th>
                    <th>Status Progres</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @forelse($riwayatKirim as $d)
                    @php $sm = $d->suratMasuk; @endphp
                    @foreach($d->tujuan as $dt)
                        @php $tl = $dt->tindakLanjut->first(); @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ date('d/m/Y H:i', strtotime($d->tanggal_kirim)) }}</td>
                            <td>
                                <b>{{ $sm->perihal ?? '-' }}</b><br>
                                <small class="text-muted">No: {{ $sm->no_surat_asal ?? '-' }}</small>
                            </td>
                            <td>
                                <b>
                                    @if($dt->tujuan_tipe === 'unit')
                                        [UNIT] {{ $dt->penerimaUnit->nama_unit ?? '-' }}
                                    @else
                                        {{ $dt->penerimaUser->nama ?? $dt->penerimaUser->name ?? '-' }}
                                    @endif
                                </b>
                            </td>
                            <td>{{ Str::limit($d->instruksi ?? '-', 45) }}</td>
                            <td>
                                {!! \App\Services\ArsitaService::badgeStatus($d->sifat ?? 'biasa') !!}<br>
                                <small class="text-muted">{{ \App\Services\ArsitaService::tglIndo($d->tenggat_waktu) }}</small>
                            </td>
                            <td>
                                {!! \App\Services\ArsitaService::badgeStatus($dt->status) !!}
                                @if($tl && $tl->catatan)
                                    <br><small style="color: var(--success); font-weight: 500;">Hasil terlapor</small>
                                @endif
                            </td>
                            <td>
                                @if($sm)
                                    <a href="{{ route('surat-masuk.show', $sm->id_surat_masuk) }}" class="btn btn-secondary btn-sm">
                                        Detail Surat
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            Belum ada riwayat disposisi yang diterbitkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($riwayatKirim->hasPages())
        <div style="margin-top: 20px; display: flex; justify-content: center;">
            {{ $riwayatKirim->links() }}
        </div>
    @endif
</div>
@endsection
