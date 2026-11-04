<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat — {{ $surat->nomorSurat?->nomor_surat ?? 'Draft' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 20px 0;
            background: #525659;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .toolbar {
            width: 210mm;
            max-width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding: 8px 16px;
            background: #323639;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
        }
        /* Standar Kertas & Margin Dinamis dari Pengaturan Surat */
        .a4-page {
            width: {{ ($format['ukuran_kertas'] ?? 'A4') === 'F4' ? '215mm' : '210mm' }};
            min-height: {{ ($format['ukuran_kertas'] ?? 'A4') === 'F4' ? '330mm' : '297mm' }};
            padding-top: {{ $format['margin_atas'] ?? 25 }}mm;
            padding-right: {{ $format['margin_kanan'] ?? 25 }}mm;
            padding-bottom: {{ $format['margin_bawah'] ?? 25 }}mm;
            padding-left: {{ $format['margin_kiri'] ?? 25 }}mm;
            background: #ffffff;
            box-shadow: 0 8px 16px rgba(0,0,0,0.35);
            position: relative;
            font-family: {{ $format['jenis_font'] ?? "'Times New Roman', Times, serif" }};
            font-size: {{ $format['ukuran_font'] ?? 12 }}pt;
            line-height: {{ $format['spasi_baris'] ?? 1.5 }};
            color: #000000;
        }
        .kop-surat {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
            gap: 16px;
        }
        .kop-logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-body {
            flex: 1;
            text-align: center;
        }
        .kop-instansi {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-univ {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3a8a;
            margin: 2px 0;
        }
        .kop-unit {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: 9pt;
            color: #334155;
            margin-top: 4px;
            font-style: italic;
            font-weight: normal;
        }
        .surat-header-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12pt;
            margin-bottom: 20px;
        }
        .surat-header-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .isi-surat {
            font-size: 12pt;
            line-height: 1.6;
            text-align: justify;
            white-space: pre-wrap;
            margin-bottom: 30px;
        }
        .ttd-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .ttd-box {
            text-align: center;
            min-width: 240px;
            font-size: 12pt;
        }
        .ttd-jabatan {
            font-weight: bold;
            margin-bottom: 65px;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }
        .tembusan-section {
            margin-top: 30px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 10px;
            font-size: 10pt;
            color: #334155;
            page-break-inside: avoid;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            .toolbar {
                display: none !important;
            }
            .page-wrapper {
                margin: 0;
                padding: 0;
            }
            .a4-page {
                box-shadow: none;
                border: none;
                margin: 0;
                padding-top: {{ $format['margin_atas'] ?? 25 }}mm;
                padding-right: {{ $format['margin_kanan'] ?? 25 }}mm;
                padding-bottom: {{ $format['margin_bawah'] ?? 25 }}mm;
                padding-left: {{ $format['margin_kiri'] ?? 25 }}mm;
                width: {{ ($format['ukuran_kertas'] ?? 'A4') === 'F4' ? '215mm' : '210mm' }};
                min-height: {{ ($format['ukuran_kertas'] ?? 'A4') === 'F4' ? '330mm' : '297mm' }};
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="toolbar">
        <div>
            <strong>Ukuran Kertas:</strong> {{ $format['ukuran_kertas'] ?? 'A4' }} &bull; Margin: {{ $format['margin_atas'] ?? 25 }}mm/{{ $format['margin_kanan'] ?? 25 }}mm/{{ $format['margin_bawah'] ?? 25 }}mm/{{ $format['margin_kiri'] ?? 25 }}mm
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" style="padding: 7px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                🖨️ Cetak / Simpan PDF
            </button>
            <a href="{{ route('surat-keluar.show', $surat->id_surat_keluar) }}" style="padding: 7px 14px; background: #4b5563; color: #fff; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 500;">
                ← Kembali
            </a>
        </div>
    </div>

    <!-- Lembar Fisik Kertas Sesuai Pengaturan -->
    <div class="a4-page">
        <!-- Kop Surat Resmi Dinamis -->
        @include('components.kop_surat', ['kop' => $kop, 'subUnit' => $surat->unit?->nama_unit])

        <!-- Tabel Identitas Surat & Tanggal -->
        <table class="surat-header-table">
            <tr>
                <td style="width: 75px;">Nomor</td>
                <td style="width: 15px;">:</td>
                <td>
                    @if($surat->nomorSurat)
                        <strong>{{ $surat->nomorSurat->nomor_surat }}</strong>
                    @else
                        <span style="color: #dc2626; font-style: italic;">[Draft - Nomor terbit otomatis setelah ACC]</span>
                    @endif
                </td>
                <td style="text-align: right; width: 40%;">
                    Serang, {{ \App\Services\ArsitaService::tglIndo($surat->tanggal_draft) }}
                </td>
            </tr>
            <tr>
                <td>Sifat</td>
                <td>:</td>
                <td>{{ ucfirst($surat->sifat) }}</td>
                <td></td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td>{{ $surat->lampiran ?: '-' }}</td>
                <td></td>
            </tr>
            <tr>
                <td>Hal</td>
                <td>:</td>
                <td><strong>{{ $surat->perihal }}</strong></td>
                <td></td>
            </tr>
        </table>

        <!-- Tujuan Surat -->
        <div style="margin-bottom: 24px; font-size: 12pt;">
            <div>Yth.</div>
            <div style="font-weight: bold;">{{ $surat->tujuan }}</div>
            <div>di</div>
            <div style="padding-left: 20px;">Tempat</div>
        </div>

        <!-- Isi Naskah Surat -->
        <div class="isi-surat">{{ $surat->isi_surat }}</div>

        <!-- Tanda Tangan Resmi Pejabat -->
        @php
            $pejabatTtd = $surat->pejabat_penandatangan;
            $sudahAcc = in_array($surat->status_surat, ['disetujui', 'terkirim', 'diarsipkan']);
        @endphp
        <div class="ttd-section">
            <div class="ttd-box">
                <div>{{ $surat->unit ? $surat->unit->nama_unit : 'UINSSC' }},</div>
                <div class="ttd-jabatan" style="margin-bottom: 0;">
                    @if($surat->unit && $surat->unit->level === 'rektorat')
                        Rektor
                    @elseif($surat->unit && $surat->unit->level === 'fakultas')
                        Dekan
                    @elseif($surat->unit && $surat->unit->level === 'lembaga')
                        Ketua Lembaga / Kepala
                    @elseif($surat->unit && in_array($surat->unit->level, ['upt', 'pusat']))
                        Kepala {{ $surat->unit->nama_unit }}
                    @else
                        Ketua Program Studi
                    @endif
                </div>

                {{-- Gambar TTD Digital Transparan --}}
                <div style="height: 75px; display: flex; align-items: center; justify-content: center; margin: 4px 0;">
                    @if($sudahAcc && $pejabatTtd && $pejabatTtd->tanda_tangan && file_exists(public_path('uploads/tanda_tangan/' . $pejabatTtd->tanda_tangan)))
                        <img src="{{ asset('uploads/tanda_tangan/' . $pejabatTtd->tanda_tangan) }}" alt="Tanda Tangan" style="max-height: 75px; max-width: 190px; object-fit: contain;">
                    @else
                        <div style="height: 65px;"></div>
                    @endif
                </div>

                <div class="ttd-nama">
                    {{ $pejabatTtd ? ($pejabatTtd->nama ?? $pejabatTtd->name) : ($surat->pembuat ? ($surat->pembuat->nama ?? $surat->pembuat->name) : 'Pejabat Berwenang') }}
                </div>
            </div>
        </div>

        <!-- Tembusan -->
        @if($surat->tembusan)
        <div class="tembusan-section">
            <div style="font-weight: bold; margin-bottom: 4px;">Tembusan:</div>
            <div style="white-space: pre-wrap;">{{ $surat->tembusan }}</div>
        </div>
        @endif
    </div>
</div>
</body>
</html>
