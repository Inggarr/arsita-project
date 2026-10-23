@extends('layouts.app')
@section('title', 'Laporan Surat')
@section('content')
<div style="margin-bottom: 20px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Rekap Laporan Surat</h1>
    <p style="color: #64748b; font-size: 13px; margin: 0;">Rekap surat masuk & keluar per periode waktu.</p>
</div>

<form method="GET" style="display: flex; gap: 10px; margin-bottom: 24px; align-items: center; background: #ffffff; padding: 16px 20px; border: 1px solid #e2e8f0; border-radius: 10px; flex-wrap: wrap;">
    <label style="font-size: 13px; font-weight: 600; color: #334155;">Periode:</label>
    <input type="date" name="start_date" value="{{ $startDate }}" style="padding: 9px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
    <span style="font-size: 13px; color: #64748b;">s/d</span>
    <input type="date" name="end_date" value="{{ $endDate }}" style="padding: 9px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
    <button type="submit" style="padding: 9px 18px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600;"><i class="bi bi-filter"></i> Tampilkan</button>
    <button type="button" onclick="window.print()" style="padding: 9px 16px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600;"><i class="bi bi-printer-fill"></i> Cetak</button>
</form>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px;">
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px; text-align: center;">
        <div style="font-size: 28px; font-weight: 800; color: #1d4ed8;">{{ $listMasuk->count() }}</div>
        <div style="font-size: 13px; color: #3b82f6; font-weight: 600; margin-top: 4px;">Total Surat Masuk</div>
    </div>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 16px; text-align: center;">
        <div style="font-size: 28px; font-weight: 800; color: #059669;">{{ $listKeluar->count() }}</div>
        <div style="font-size: 13px; color: #10b981; font-weight: 600; margin-top: 4px;">Total Surat Keluar</div>
    </div>
    <div style="background: #fdf2f8; border: 1px solid #f5d0fe; border-radius: 10px; padding: 16px; text-align: center;">
        <div style="font-size: 28px; font-weight: 800; color: #7c3aed;">{{ $listKeluar->whereNotNull('nomorSurat')->count() }}</div>
        <div style="font-size: 13px; color: #a855f7; font-weight: 600; margin-top: 4px;">Terbit Nomor Otomatis</div>
    </div>
</div>

<!-- Surat Masuk Table -->
@if($listMasuk->count())
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
    <div style="padding: 14px 20px; background: #eff6ff; border-bottom: 1px solid #bfdbfe; font-size: 14px; font-weight: 700; color: #1d4ed8;">
        <i class="bi bi-inbox-fill"></i> Surat Masuk ({{ \App\Services\ArsitaService::tglIndo($startDate) }} — {{ \App\Services\ArsitaService::tglIndo($endDate) }})
    </div>
    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead>
            <tr style="background: #f8fafc;">
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">#</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">No. Surat Asal</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Dari</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Perihal</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Tanggal Terima</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($listMasuk as $i => $sm)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 16px; color: #94a3b8;">{{ $i+1 }}</td>
                <td style="padding: 10px 16px; font-weight: 600;">{{ $sm->no_surat_asal }}</td>
                <td style="padding: 10px 16px; color: #334155;">{{ $sm->asal_surat }}</td>
                <td style="padding: 10px 16px; color: #475569;">{{ Str::limit($sm->perihal, 45) }}</td>
                <td style="padding: 10px 16px; color: #334155;">{{ \App\Services\ArsitaService::tglIndo($sm->tanggal_terima) }}</td>
                <td style="padding: 10px 16px;">{!! \App\Services\ArsitaService::badgeStatus($sm->status_arsip) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- Surat Keluar Table -->
@if($listKeluar->count())
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
    <div style="padding: 14px 20px; background: #ecfdf5; border-bottom: 1px solid #a7f3d0; font-size: 14px; font-weight: 700; color: #059669;">
        <i class="bi bi-send-fill"></i> Surat Keluar
    </div>
    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead>
            <tr style="background: #f8fafc;">
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">#</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Nomor Surat</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Tujuan</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Perihal</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Tanggal</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($listKeluar as $i => $sk)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 16px; color: #94a3b8;">{{ $i+1 }}</td>
                <td style="padding: 10px 16px; font-weight: 600; color: #2563eb;">{{ $sk->nomorSurat?->nomor_surat ?? '(Draft #' . $sk->id_surat_keluar . ')' }}</td>
                <td style="padding: 10px 16px; color: #334155;">{{ $sk->tujuan }}</td>
                <td style="padding: 10px 16px; color: #475569;">{{ Str::limit($sk->perihal, 45) }}</td>
                <td style="padding: 10px 16px; color: #334155;">{{ \App\Services\ArsitaService::tglIndo($sk->tanggal_draft) }}</td>
                <td style="padding: 10px 16px;">{!! \App\Services\ArsitaService::badgeStatus($sk->status_surat) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
