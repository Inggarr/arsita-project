@extends('layouts.app')
@section('title', 'Detail Surat Keluar')
@section('content')
<div style="max-width: 900px;">
<div style="margin-bottom: 16px;">
    <a href="{{ route('surat-keluar.index') }}" style="color: #64748b; text-decoration: none; font-size: 13px;">← Kembali ke Daftar Surat Keluar</a>
</div>

<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">{{ $surat->perihal }}</h1>
            <p style="color: #64748b; font-size: 13px; margin: 0;">
                @if($surat->nomorSurat)
                    Nomor: <strong style="color: #2563eb;">{{ $surat->nomorSurat->nomor_surat }}</strong>
                @else
                    Draft #{{ $surat->id_surat_keluar }} — <em>Nomor belum diterbitkan</em>
                @endif
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            {!! \App\Services\ArsitaService::badgeStatus($surat->status_surat) !!}
            {!! \App\Services\ArsitaService::badgeStatus($surat->sifat) !!}
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        @php
            $rows = [
                ['Tujuan', $surat->tujuan ?? '—'],
                ['Unit Tujuan Internal', $surat->tujuanUnit ? $surat->tujuanUnit->nama_unit . ' (Otomatis Masuk saat Disetujui)' : 'Eksternal / Non-Unit'],
                ['Unit Pembuat', $surat->unit ? $surat->unit->nama_unit : '—'],
                ['Jenis Surat', $surat->jenisSurat ? $surat->jenisSurat->nama_jenis . ' (' . $surat->jenisSurat->kode_jenis . ')' : '—'],
                ['Kategori', $surat->kategori ? $surat->kategori->nama_kategori : '—'],
                ['Lampiran', $surat->lampiran ?? '—'],
                ['Tanggal Draft', \App\Services\ArsitaService::tglIndo($surat->tanggal_draft)],
                ['Dibuat Oleh', $surat->pembuat ? $surat->pembuat->name : '—'],
            ];
        @endphp
        @foreach($rows as $i => $row)
        <div style="padding: 12px 16px; {{ $i % 2 == 0 ? 'background: #f8fafc;' : '' }} border-bottom: 1px solid #f1f5f9;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 4px;">{{ $row[0] }}</div>
            <div style="font-size: 14px; color: #0f172a; font-weight: 500;">{{ $row[1] }}</div>
        </div>
        @endforeach
    </div>

    <!-- Preview Naskah Dokumen Surat Resmi (Kop, Format Dinas, TTD) -->
    <div style="margin-top: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                <i class="bi bi-file-earmark-text-fill" style="color: #2563eb;"></i> Pratinjau Lembar Surat Resmi
            </div>
            <a href="{{ route('surat-keluar.cetak', $surat->id_surat_keluar) }}" target="_blank" style="font-size: 12px; color: #2563eb; text-decoration: none; font-weight: 600;">
                <i class="bi bi-arrows-fullscreen"></i> Buka Tampilan Cetak / PDF Fullscreen →
            </a>
        </div>

        <div style="background: #e2e8f0; border-radius: 12px; padding: 24px 16px; display: flex; justify-content: center; overflow-x: auto;">
            <!-- Lembar Fisik Kertas A4 Word: Lebar 210mm, Min-Tinggi 297mm, Margin Normal Word 2.54cm (25.4mm) -->
            <div style="width: 210mm; min-height: 297mm; padding: 25.4mm; background: #ffffff; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1); border-radius: 2px; font-family: 'Times New Roman', Times, serif; color: #000000; font-size: 12pt; line-height: 1.5; box-sizing: border-box; flex-shrink: 0;">
                <!-- Kop Surat Resmi -->
                <div style="text-align: center; border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 20px;">
                    <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                        KEMENTERIAN AGAMA REPUBLIK INDONESIA
                    </div>
                    <div style="font-size: 15pt; font-weight: bold; text-transform: uppercase; color: #1e3a8a; margin: 2px 0;">
                        UIN SUNAN SUDJATI SERANG CILEGON
                    </div>
                    @if($surat->unit)
                    <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; margin-top: 2px;">
                        {{ $surat->unit->nama_unit }}
                    </div>
                    @endif
                    <div style="font-size: 9pt; color: #334155; margin-top: 4px; font-style: italic;">
                        Jl. Raya Serang-Cilegon KM.3 Cinanggung Serang Banten 42116 · Telp: (0254) 200323 · Website: www.uinssc.ac.id
                    </div>
                </div>

                <!-- Tabel Identitas Nomor, Sifat, Lampiran, Hal & Tanggal -->
                <table style="width: 100%; border-collapse: collapse; font-size: 12pt; margin-bottom: 20px;">
                    <tr>
                        <td style="width: 80px; padding: 2px 0; vertical-align: top;">Nomor</td>
                        <td style="width: 15px; padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;">
                            @if($surat->nomorSurat)
                                <strong>{{ $surat->nomorSurat->nomor_surat }}</strong>
                            @else
                                <span style="color: #dc2626; font-style: italic;">[Draft - Nomor terbit otomatis setelah ACC]</span>
                            @endif
                        </td>
                        <td style="text-align: right; width: 40%; padding: 2px 0; vertical-align: top;">
                            Serang, {{ \App\Services\ArsitaService::tglIndo($surat->tanggal_draft) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: top;">Sifat</td>
                        <td style="padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;">{{ ucfirst($surat->sifat) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: top;">Lampiran</td>
                        <td style="padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;">{{ $surat->lampiran ?: '-' }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: top;">Hal</td>
                        <td style="padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;"><strong>{{ $surat->perihal }}</strong></td>
                        <td></td>
                    </tr>
                </table>

                <!-- Penerima Tujuan -->
                <div style="margin-bottom: 24px; font-size: 12pt;">
                    <div>Yth.</div>
                    <div style="font-weight: bold;">{{ $surat->tujuan }}</div>
                    <div>di</div>
                    <div style="padding-left: 20px;">Tempat</div>
                </div>

                <!-- Isi Naskah Surat Sesuai Margin A4 Word -->
                <div style="font-size: 12pt; line-height: 1.6; text-align: justify; margin-bottom: 30px; white-space: pre-wrap;">{{ $surat->isi_surat }}</div>

                <!-- Tanda Tangan Resmi (Posisi Kanan Bawah Standar Word) -->
                <div style="display: flex; justify-content: flex-end; margin-top: 40px;">
                    <div style="text-align: center; min-width: 240px; font-size: 12pt;">
                        <div>{{ $surat->unit ? $surat->unit->nama_unit : 'UINSSC' }},</div>
                        <div style="font-weight: bold; margin-bottom: 65px;">
                            @if($surat->unit && $surat->unit->level === 'rektorat')
                                Rektor
                            @elseif($surat->unit && $surat->unit->level === 'fakultas')
                                Dekan
                            @else
                                Ketua Program Studi
                            @endif
                        </div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $surat->pembuat ? ($surat->pembuat->nama ?? $surat->pembuat->name) : 'Pejabat Berwenang' }}
                        </div>
                    </div>
                </div>

                <!-- Tembusan -->
                @if($surat->tembusan)
                <div style="margin-top: 30px; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-size: 10pt; color: #334155;">
                    <div style="font-weight: bold; margin-bottom: 4px;">Tembusan:</div>
                    <div style="white-space: pre-wrap;">{{ $surat->tembusan }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Aksi -->
    <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
        @if(in_array($surat->status_surat, ['draft','revisi']))
        <a href="{{ route('surat-keluar.edit', $surat->id_surat_keluar) }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #059669; color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;"><i class="bi bi-pencil-fill"></i> Edit Draft</a>
        <form action="{{ route('surat-keluar.ajukan', $surat->id_surat_keluar) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #d97706; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;"
                data-confirm="Ajukan draft surat keluar ini ke pimpinan untuk proses persetujuan (ACC)? Setelah disetujui, nomor surat resmi akan terbit otomatis."
                data-confirm-title="Ajukan Persetujuan (ACC)"
                data-confirm-type="info"
                data-confirm-btn="Ya, Ajukan Surat"
                data-confirm-class="btn-primary">
                <i class="bi bi-send-fill"></i> Ajukan untuk ACC
            </button>
        </form>
        @endif

        <a href="{{ route('surat-keluar.cetak', $surat->id_surat_keluar) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #7c3aed; color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">
            <i class="bi bi-printer-fill"></i> Cetak Dokumen / PDF
        </a>

        @if(in_array($surat->status_surat, ['draft','revisi']))
        <form action="{{ route('surat-keluar.destroy', $surat->id_surat_keluar) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus draft surat ini?')">
            @csrf @method('DELETE')
            <button type="submit" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #dc2626; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;"><i class="bi bi-trash-fill"></i> Hapus Draft</button>
        </form>
        @endif
    </div>
</div>

<!-- Riwayat Persetujuan -->
@if($surat->persetujuan->count())
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 16px 0;">
        <i class="bi bi-check-circle-fill" style="color: #2563eb;"></i> Riwayat ACC / Persetujuan
    </h2>
    <div style="display: flex; flex-direction: column; gap: 10px;">
        @foreach($surat->persetujuan as $acc)
        <div style="display: flex; align-items: center; gap: 16px; padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 8px; {{ $acc->status === 'disetujui' ? 'background: #f0fdf4; border-color: #bbf7d0;' : ($acc->status === 'ditolak' ? 'background: #fef2f2; border-color: #fecaca;' : 'background: #fffbeb; border-color: #fde68a;') }}">
            <div style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; {{ $acc->status === 'disetujui' ? 'background: #dcfce7; color: #059669;' : ($acc->status === 'ditolak' ? 'background: #fee2e2; color: #dc2626;' : 'background: #fef3c7; color: #d97706;') }}">
                <i class="bi bi-{{ $acc->status === 'disetujui' ? 'check-lg' : ($acc->status === 'ditolak' ? 'x-lg' : 'clock') }}"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 14px; font-weight: 600; color: #0f172a;">Tahap {{ $acc->tahap_ke }}</div>
                <div style="font-size: 12px; color: #64748b;">
                    @if($acc->pemeriksa) Oleh: {{ $acc->pemeriksa->name }} @endif
                    @if($acc->tanggal) · {{ \App\Services\ArsitaService::tglIndo($acc->tanggal) }} @endif
                </div>
                @if($acc->catatan_penolakan)
                <div style="font-size: 12px; color: #dc2626; margin-top: 4px;"><i class="bi bi-chat-fill"></i> {{ $acc->catatan_penolakan }}</div>
                @endif
            </div>
            {!! \App\Services\ArsitaService::badgeStatus($acc->status) !!}
        </div>
        @endforeach
    </div>
</div>
@endif

</div>
@endsection
