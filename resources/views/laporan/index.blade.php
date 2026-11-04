@extends('layouts.app')
@section('title', 'Laporan Surat')
@section('content')
<div style="margin-bottom: 20px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Rekap Laporan Surat</h1>
    <p style="color: #64748b; font-size: 13px; margin: 0;">Rekap surat masuk & keluar per periode waktu.</p>
</div>

<form method="GET" id="form-laporan" style="display: flex; gap: 10px; margin-bottom: 24px; align-items: center; background: #ffffff; padding: 16px 20px; border: 1px solid #e2e8f0; border-radius: 10px; flex-wrap: wrap;">
    <label style="font-size: 13px; font-weight: 600; color: #334155;">Periode:</label>
    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" style="padding: 9px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
    <span style="font-size: 13px; color: #64748b;">s/d</span>
    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" style="padding: 9px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
    <button type="submit" style="padding: 9px 18px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600;"><i class="bi bi-filter"></i> Tampilkan</button>

    <div style="flex:1;"></div>

    <a id="btn-pdf"
       href="{{ route('laporan.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
       target="_blank"
       style="padding: 9px 16px; background: #dc2626; color: #fff; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600; text-decoration: none; display:inline-flex; align-items:center; gap:5px;">
        <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
    </a>

    <a id="btn-excel"
       href="{{ route('laporan.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
       style="padding: 9px 16px; background: #16a34a; color: #fff; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600; text-decoration: none; display:inline-flex; align-items:center; gap:5px;">
        <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
    </a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var startInput = document.getElementById('start_date');
    var endInput   = document.getElementById('end_date');
    var btnPdf     = document.getElementById('btn-pdf');
    var btnExcel   = document.getElementById('btn-excel');
    var pdfBase    = '{{ route('laporan.pdf') }}';
    var excelBase  = '{{ route('laporan.excel') }}';

    function updateExportLinks() {
        var s = startInput.value;
        var e = endInput.value;
        btnPdf.href   = pdfBase   + '?start_date=' + s + '&end_date=' + e;
        btnExcel.href = excelBase + '?start_date=' + s + '&end_date=' + e;
    }

    startInput.addEventListener('change', updateExportLinks);
    endInput.addEventListener('change', updateExportLinks);
});
</script>


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
            <tr onclick="window.location='{{ route('surat-masuk.show', $sm->id_surat_masuk) }}'" style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'" title="Klik untuk membuka detail surat masuk">
                <td style="padding: 10px 16px; color: #94a3b8;">{{ $i+1 }}</td>
                <td style="padding: 10px 16px; font-weight: 600;">
                    <a href="{{ route('surat-masuk.show', $sm->id_surat_masuk) }}" style="color: #2563eb; text-decoration: none;">
                        {{ $sm->no_surat_asal }}
                    </a>
                </td>
                <td style="padding: 10px 16px; color: #334155;">{{ $sm->asal_surat }}</td>
                <td style="padding: 10px 16px; color: #0f172a; font-weight: 500;">
                    <a href="{{ route('surat-masuk.show', $sm->id_surat_masuk) }}" style="color: inherit; text-decoration: none;">
                        {{ Str::limit($sm->perihal, 50) }}
                    </a>
                </td>
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
            <tr onclick="window.location='{{ route('surat-keluar.show', $sk->id_surat_keluar) }}'" style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'" title="Klik untuk membuka detail surat keluar">
                <td style="padding: 10px 16px; color: #94a3b8;">{{ $i+1 }}</td>
                <td style="padding: 10px 16px; font-weight: 600;">
                    <a href="{{ route('surat-keluar.show', $sk->id_surat_keluar) }}" style="color: #2563eb; text-decoration: none;">
                        {{ $sk->nomorSurat?->nomor_surat ?? '(Draft #' . $sk->id_surat_keluar . ')' }}
                    </a>
                </td>
                <td style="padding: 10px 16px; color: #334155;">{{ $sk->tujuan }}</td>
                <td style="padding: 10px 16px; color: #0f172a; font-weight: 500;">
                    <a href="{{ route('surat-keluar.show', $sk->id_surat_keluar) }}" style="color: inherit; text-decoration: none;">
                        {{ Str::limit($sk->perihal, 50) }}
                    </a>
                </td>
                <td style="padding: 10px 16px; color: #334155;">{{ \App\Services\ArsitaService::tglIndo($sk->tanggal_draft) }}</td>
                <td style="padding: 10px 16px;">{!! \App\Services\ArsitaService::badgeStatus($sk->status_surat) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
