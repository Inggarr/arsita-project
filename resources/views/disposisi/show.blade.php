@extends('layouts.app')
@section('title', 'Detail Disposisi')
@section('content')
<div style="max-width: 860px;">
<div style="margin-bottom: 16px;">
    <a href="{{ route('disposisi.index') }}" style="color: #64748b; text-decoration: none; font-size: 13px;">← Kembali ke Disposisi Masuk</a>
</div>

@php
    $dt = $disposisiTujuan;
    $d  = $dt->disposisi;
    $sm = $d->suratMasuk;
@endphp

<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px;">
        <div>
            <h1 style="font-size: 19px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">Detail Disposisi</h1>
            <p style="color: #64748b; font-size: 13px; margin: 0;">Surat: <strong>{{ $sm?->perihal ?? '—' }}</strong> ({{ $sm?->no_surat_asal }})</p>
        </div>
        {!! \App\Services\ArsitaService::badgeStatus($dt->status) !!}
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 16px;">
        @php
            $rows = [
                ['Instruksi', $d->instruksi ?? '—'],
                ['Sifat Disposisi', ucfirst(str_replace('_',' ',$d->sifat))],
                ['Dari Pimpinan', $d->pengirim?->name ?? '—'],
                ['Tenggat Waktu', $d->tenggat_waktu ? \App\Services\ArsitaService::tglIndo($d->tenggat_waktu) : '—'],
                ['Tanggal Dikirim', \App\Services\ArsitaService::tglIndo($d->tanggal_kirim)],
                ['Catatan Tambahan', $d->catatan ?? '—'],
            ];
        @endphp
        @foreach($rows as $i => $row)
        <div style="padding: 12px 16px; {{ $i % 2 == 0 ? 'background: #f8fafc;' : '' }} border-bottom: 1px solid #f1f5f9;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 4px;">{{ $row[0] }}</div>
            <div style="font-size: 14px; color: #0f172a; font-weight: 500;">{{ $row[1] }}</div>
        </div>
        @endforeach
    </div>

    <a href="{{ route('surat-masuk.show', $sm?->id_surat_masuk) }}" style="font-size: 13px; color: #2563eb; text-decoration: none; font-weight: 600;">
        <i class="bi bi-file-earmark-text-fill"></i> Lihat Detail Surat Asli →
    </a>
</div>

<!-- Riwayat Tindak Lanjut -->
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 16px 0;">Riwayat Tindak Lanjut</h2>
    @forelse($dt->tindakLanjut as $tl)
    <div style="padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px;">
        <div style="font-size: 12px; color: #64748b; margin-bottom: 6px;">{{ \App\Services\ArsitaService::tglIndo($tl->tanggal_update) }}</div>
        <p style="font-size: 13px; color: #334155; margin: 0 0 8px 0;">{{ $tl->catatan }}</p>
        @if($tl->lampiran)
        <a href="{{ asset('uploads/tindak_lanjut/' . $tl->lampiran) }}" target="_blank" style="font-size: 12px; color: #2563eb; text-decoration: none; font-weight: 600;"><i class="bi bi-paperclip"></i> Lihat Lampiran</a>
        @endif
    </div>
    @empty
    <p style="color: #94a3b8; font-size: 13px; text-align: center; padding: 20px 0;">Belum ada laporan tindak lanjut.</p>
    @endforelse
</div>

<!-- Form Tindak Lanjut Baru -->
@if($dt->status !== 'selesai')
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 16px 0;">Tambah Laporan Tindak Lanjut</h2>
    <form action="{{ route('disposisi.tindak-lanjut', $dt->id_disposisi_tujuan) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; color: #991b1b;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Catatan / Laporan *</label>
            <textarea name="catatan" rows="4" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; resize: vertical;">{{ old('catatan') }}</textarea>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Lampiran (opsional)</label>
                <input type="file" name="lampiran" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
            </div>
            <div>
                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Update Status *</label>
                <select name="status" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
                    <option value="diproses" {{ $dt->status === 'diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                    <option value="selesai">Selesai / Ditindaklanjuti</option>
                </select>
            </div>
        </div>
        <button type="submit" style="padding: 11px 24px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;"><i class="bi bi-save-fill"></i> Simpan Laporan</button>
    </form>
</div>
@endif

</div>
@endsection
