<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Surat — {{ $startDate }} s/d {{ $endDate }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11.5px;
            color: #1e293b;
            background: #f1f5f9;
        }

        /* ── Screen wrapper (A4 simulation) ── */
        .page-wrapper {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            min-height: 297mm;
            padding: 16mm 14mm 12mm;
            box-shadow: 0 4px 32px rgba(0,0,0,0.12);
        }

        /* ── Toolbar (hidden when printing) ── */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #0f172a;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }
        .toolbar-title {
            flex: 1;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
        }
        .toolbar-title span {
            color: #f8fafc;
            font-weight: 700;
        }
        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: opacity .15s;
        }
        .btn-toolbar:hover { opacity: .85; }
        .btn-print  { background: #2563eb; color: #fff; }
        .btn-back   { background: #334155; color: #e2e8f0; }

        /* ── Report Header ── */
        .report-header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 2.5px solid #1d4ed8;
        }
        .report-header .logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 6px;
        }
        .report-header .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }
        .report-header .org-name {
            font-size: 15px;
            font-weight: 700;
            color: #1d4ed8;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .report-header h1 {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 4px 0 3px;
            letter-spacing: -0.3px;
        }
        .report-header .periode {
            font-size: 12px;
            color: #475569;
            font-weight: 500;
        }
        .report-header .cetak-info {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* ── Summary Cards ── */
        .summary-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }
        .summary-card {
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .summary-card .icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .summary-card .info .num {
            font-size: 24px;
            font-weight: 800;
            line-height: 1;
        }
        .summary-card .info .lbl {
            font-size: 10.5px;
            font-weight: 600;
            margin-top: 2px;
        }
        .summary-card.masuk  { background: #eff6ff; border: 1px solid #bfdbfe; }
        .summary-card.masuk  .icon { background: #dbeafe; color: #1d4ed8; }
        .summary-card.masuk  .num  { color: #1d4ed8; }
        .summary-card.masuk  .lbl  { color: #3b82f6; }
        .summary-card.keluar { background: #ecfdf5; border: 1px solid #a7f3d0; }
        .summary-card.keluar .icon { background: #d1fae5; color: #059669; }
        .summary-card.keluar .num  { color: #059669; }
        .summary-card.keluar .lbl  { color: #10b981; }
        .summary-card.total  { background: #faf5ff; border: 1px solid #e9d5ff; }
        .summary-card.total  .icon { background: #ede9fe; color: #7c3aed; }
        .summary-card.total  .num  { color: #7c3aed; }
        .summary-card.total  .lbl  { color: #a855f7; }

        /* ── Section Title ── */
        .section-title {
            font-size: 12.5px;
            font-weight: 700;
            padding: 7px 12px;
            border-radius: 6px 6px 0 0;
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .section-title.masuk  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-bottom: none; }
        .section-title.keluar { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-bottom: none; }
        .section-title .count-badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
        }
        .section-title.masuk  .count-badge { background: #dbeafe; color: #1d4ed8; }
        .section-title.keluar .count-badge { background: #d1fae5; color: #059669; }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        table.masuk  { border: 1px solid #bfdbfe; border-top: none; }
        table.keluar { border: 1px solid #a7f3d0; border-top: none; }
        table.masuk  thead tr { background: #1d4ed8; }
        table.keluar thead tr { background: #059669; }
        thead tr { color: #fff; }
        th {
            padding: 7px 9px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            letter-spacing: 0.3px;
        }
        td {
            padding: 6px 9px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            line-height: 1.4;
        }
        tbody tr:nth-child(even).masuk-row  { background: #f0f7ff; }
        tbody tr:nth-child(even).keluar-row { background: #f0fdf7; }
        tbody tr:last-child td { border-bottom: none; }
        .no-col { width: 26px; text-align: center; color: #94a3b8; font-size: 10px; }
        .no-surat { font-weight: 700; }
        .no-surat-keluar { font-weight: 700; color: #2563eb; }
        .kosong-row td { text-align: center; color: #94a3b8; font-style: italic; padding: 16px; }

        /* ── Footer ── */
        .report-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            color: #94a3b8;
        }
        .report-footer .left-info {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Print styles ── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 14mm 12mm 12mm;
            }
            body { background: #fff; }
            .toolbar { display: none !important; }
            .page-wrapper {
                max-width: 100%;
                box-shadow: none;
                padding: 0;
                min-height: auto;
            }
            thead { display: table-header-group; }
            tr    { page-break-inside: avoid; }
            .section-title { page-break-after: avoid; }
            /* Force color printing */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

{{-- ── Toolbar ── --}}
<div class="toolbar no-print">
    <div class="toolbar-title">
        <span>Laporan Surat</span> &nbsp;—&nbsp; Preview PDF
    </div>
    <a href="{{ route('laporan.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
       class="btn-toolbar btn-back">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <button onclick="window.print()" class="btn-toolbar btn-print">
        <i class="bi bi-printer-fill"></i> Cetak / Simpan PDF
    </button>
</div>

{{-- ── A4 Page Wrapper ── --}}
<div class="page-wrapper">

    {{-- ── Report Header ── --}}
    <div class="report-header">
        <div class="logo-row">
            <div class="logo-icon">
                <i class="bi bi-building-fill"></i>
            </div>
            <div>
                <div class="org-name">Sistem Informasi Manajemen Surat</div>
            </div>
        </div>
        <h1>Rekap Laporan Surat Masuk &amp; Keluar</h1>
        <div class="periode">
            <i class="bi bi-calendar-range" style="color:#3b82f6;"></i>
            Periode: {{ \App\Services\ArsitaService::tglIndo($startDate) }} &mdash; {{ \App\Services\ArsitaService::tglIndo($endDate) }}
        </div>
        <div class="cetak-info">
            <i class="bi bi-clock"></i>
            Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}, pukul {{ \Carbon\Carbon::now()->format('H:i') }} WIB
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="summary-row">
        <div class="summary-card masuk">
            <div class="icon"><i class="bi bi-inbox-fill"></i></div>
            <div class="info">
                <div class="num">{{ $listMasuk->count() }}</div>
                <div class="lbl">Surat Masuk</div>
            </div>
        </div>
        <div class="summary-card keluar">
            <div class="icon"><i class="bi bi-send-fill"></i></div>
            <div class="info">
                <div class="num">{{ $listKeluar->count() }}</div>
                <div class="lbl">Surat Keluar</div>
            </div>
        </div>
        <div class="summary-card total">
            <div class="icon"><i class="bi bi-files"></i></div>
            <div class="info">
                <div class="num">{{ $listMasuk->count() + $listKeluar->count() }}</div>
                <div class="lbl">Total Surat</div>
            </div>
        </div>
    </div>

    {{-- ── Tabel Surat Masuk ── --}}
    <div class="section-title masuk">
        <i class="bi bi-inbox-fill"></i>
        Surat Masuk
        <span class="count-badge">{{ $listMasuk->count() }} surat</span>
    </div>
    <table class="masuk">
        <thead>
            <tr>
                <th class="no-col">#</th>
                <th>No. Surat Asal</th>
                <th>Dari / Pengirim</th>
                <th>Perihal</th>
                <th>Tgl. Terima</th>
                <th>Kategori</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listMasuk as $i => $sm)
            <tr class="masuk-row">
                <td class="no-col">{{ $i + 1 }}</td>
                <td class="no-surat">{{ $sm->no_surat_asal }}</td>
                <td>{{ $sm->asal_surat }}</td>
                <td>{{ $sm->perihal }}</td>
                <td style="white-space:nowrap;">{{ \App\Services\ArsitaService::tglIndo($sm->tanggal_terima) }}</td>
                <td>{{ $sm->kategori?->nama_kategori ?? 'Umum' }}</td>
                <td>{!! \App\Services\ArsitaService::badgeStatus($sm->status_arsip) !!}</td>
            </tr>
            @empty
            <tr class="kosong-row"><td colspan="7">Tidak ada data surat masuk pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Tabel Surat Keluar ── --}}
    <div class="section-title keluar">
        <i class="bi bi-send-fill"></i>
        Surat Keluar
        <span class="count-badge">{{ $listKeluar->count() }} surat</span>
    </div>
    <table class="keluar">
        <thead>
            <tr>
                <th class="no-col">#</th>
                <th>Nomor Surat</th>
                <th>Tujuan</th>
                <th>Perihal</th>
                <th>Tgl. Draft</th>
                <th>Kategori</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listKeluar as $i => $sk)
            <tr class="keluar-row">
                <td class="no-col">{{ $i + 1 }}</td>
                <td class="no-surat-keluar">{{ $sk->nomorSurat?->nomor_surat ?? '(Draft #' . $sk->id_surat_keluar . ')' }}</td>
                <td>{{ $sk->tujuan }}</td>
                <td>{{ $sk->perihal }}</td>
                <td style="white-space:nowrap;">{{ \App\Services\ArsitaService::tglIndo($sk->tanggal_draft) }}</td>
                <td>{{ $sk->kategori?->nama_kategori ?? 'Umum' }}</td>
                <td>{!! \App\Services\ArsitaService::badgeStatus($sk->status_surat) !!}</td>
            </tr>
            @empty
            <tr class="kosong-row"><td colspan="7">Tidak ada data surat keluar pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Footer ── --}}
    <div class="report-footer">
        <div class="left-info">
            <i class="bi bi-shield-check" style="color:#10b981;"></i>
            Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi Manajemen Surat
        </div>
        <div>
            <i class="bi bi-file-earmark-text" style="color:#94a3b8;"></i>
            Halaman 1
        </div>
    </div>

</div>{{-- end .page-wrapper --}}

</body>
</html>
