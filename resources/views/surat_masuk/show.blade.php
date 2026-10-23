@extends('layouts.app')
@section('title', 'Detail Surat Masuk')
@section('content')
<div style="max-width: 900px;">
<div style="margin-bottom: 16px;">
    <a href="{{ route('surat-masuk.index') }}" style="color: #64748b; text-decoration: none; font-size: 13px;">← Kembali ke Daftar Surat Masuk</a>
</div>

<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">{{ $surat->perihal }}</h1>
            <p style="color: #64748b; font-size: 13px; margin: 0;">No. Surat Asal: <strong>{{ $surat->no_surat_asal }}</strong></p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            {!! \App\Services\ArsitaService::badgeStatus($surat->status_arsip) !!}
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        @php
            $rows = [
                ['Pengirim / Asal', $surat->asal_surat ?? '—'],
                ['Perihal', $surat->perihal ?? '—'],
                ['Tanggal Surat', \App\Services\ArsitaService::tglIndo($surat->tanggal_surat)],
                ['Tanggal Diterima', \App\Services\ArsitaService::tglIndo($surat->tanggal_terima)],
                ['Unit Tujuan', $surat->unit ? $surat->unit->nama_unit : '—'],
                ['Kategori', $surat->kategori ? $surat->kategori->nama_kategori : '—'],
                ['Jenis Surat', $surat->jenisSurat ? $surat->jenisSurat->nama_jenis : '—'],
                ['Dicatat Oleh', $surat->pencatat ? $surat->pencatat->name : '—'],
            ];
        @endphp
        @foreach($rows as $i => $row)
        <div style="padding: 12px 16px; {{ $i % 2 == 0 ? 'background: #f8fafc;' : '' }} border-bottom: 1px solid #f1f5f9;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 4px;">{{ $row[0] }}</div>
            <div style="font-size: 14px; color: #0f172a; font-weight: 500;">{{ $row[1] }}</div>
        </div>
        @endforeach
    </div>

    @if($surat->keterangan)
    <div style="margin-top: 16px; padding: 14px 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 6px;">Keterangan</div>
        <p style="font-size: 14px; color: #334155; margin: 0;">{{ $surat->keterangan }}</p>
    </div>
    @endif

    @if($surat->file_surat)
    <div style="margin-top: 16px; padding: 14px 16px; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe;">
        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #3b82f6; margin-bottom: 6px;">Berkas Lampiran / File Dokumen</div>
        <a href="{{ asset('uploads/surat_masuk/' . $surat->file_surat) }}" target="_blank" style="color: #2563eb; font-size: 14px; font-weight: 600; text-decoration: none;">
            <i class="bi bi-file-earmark-arrow-down-fill"></i> Buka / Unduh Berkas Terlampir ({{ $surat->file_surat }})
        </a>
    </div>
    @endif

    @php
        // Ambil data surat keluar jika surat masuk berasal dari pengiriman otomatis internal kampus
        $skAsal = $surat->suratKeluarAsal;
        if (!$skAsal && $surat->no_surat_asal) {
            $skAsal = \App\Models\SuratKeluar::with(['unit', 'nomorSurat', 'pembuat'])->whereHas('nomorSurat', function($q) use ($surat) {
                $q->where('nomor_surat', $surat->no_surat_asal);
            })->first();
        }

        // Teks naskah isi surat
        $isiNaskah = $skAsal ? $skAsal->isi_surat : null;
        if (!$isiNaskah && $surat->keterangan) {
            // Jika ada format "Isi: ..." di keterangan
            if (str_contains($surat->keterangan, 'Isi:')) {
                $parts = explode('Isi:', $surat->keterangan, 2);
                $isiNaskah = trim($parts[1] ?? '');
            } else {
                $isiNaskah = $surat->keterangan;
            }
        }
        $unitPengirim = $skAsal ? $skAsal->unit : null;
        $pejabatTtd = $skAsal && $skAsal->pembuat ? ($skAsal->pembuat->nama ?? $skAsal->pembuat->name) : 'Pejabat Pengirim';
        $tembusanTeks = $skAsal ? $skAsal->tembusan : null;
        $sifatSurat = $skAsal ? $skAsal->sifat : 'biasa';
        $lampiranSurat = $skAsal ? $skAsal->lampiran : '-';
    @endphp

    <!-- Pratinjau Lembar Fisik Surat A4 Standar Word untuk Penerima -->
    <div style="margin-top: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                <i class="bi bi-file-earmark-text-fill" style="color: #2563eb;"></i> Pratinjau Lembar Surat Resmi (Kertas A4)
            </div>
            @if($skAsal)
            <a href="{{ route('surat-keluar.cetak', $skAsal->id_surat_keluar) }}" target="_blank" style="font-size: 12px; color: #2563eb; text-decoration: none; font-weight: 600;">
                <i class="bi bi-arrows-fullscreen"></i> Tampilan Cetak / PDF Fullscreen →
            </a>
            @endif
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
                    @if($unitPengirim)
                    <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; margin-top: 2px;">
                        {{ $unitPengirim->nama_unit }}
                    </div>
                    @elseif($surat->asal_surat)
                    <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; margin-top: 2px;">
                        {{ $surat->asal_surat }}
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
                            <strong>{{ $surat->no_surat_asal ?: '___________' }}</strong>
                        </td>
                        <td style="text-align: right; width: 40%; padding: 2px 0; vertical-align: top;">
                            Serang, {{ \App\Services\ArsitaService::tglIndo($surat->tanggal_surat) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: top;">Sifat</td>
                        <td style="padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;">{{ ucfirst($sifatSurat) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: top;">Lampiran</td>
                        <td style="padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;">{{ $lampiranSurat }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: top;">Hal</td>
                        <td style="padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;"><strong>{{ $surat->perihal }}</strong></td>
                        <td></td>
                    </tr>
                </table>

                <!-- Penerima / Tujuan di Unit Ini -->
                <div style="margin-bottom: 24px; font-size: 12pt;">
                    <div>Yth.</div>
                    <div style="font-weight: bold;">
                        Pimpinan {{ $surat->unit ? $surat->unit->nama_unit : 'Unit Penerima' }}
                    </div>
                    <div>di</div>
                    <div style="padding-left: 20px;">Tempat</div>
                </div>

                <!-- Isi Naskah Surat Sesuai Margin A4 Word -->
                <div style="font-size: 12pt; line-height: 1.6; text-align: justify; margin-bottom: 30px; white-space: pre-wrap;">{{ $isiNaskah ?: 'Surat dinas telah diterima dan tercatat di sistem ARSITA.' }}</div>

                <!-- Tanda Tangan Resmi Pengirim -->
                <div style="display: flex; justify-content: flex-end; margin-top: 40px;">
                    <div style="text-align: center; min-width: 240px; font-size: 12pt;">
                        <div>{{ $unitPengirim ? $unitPengirim->nama_unit : ($surat->asal_surat ?: 'Pengirim') }},</div>
                        <div style="font-weight: bold; margin-bottom: 65px;">
                            @if($unitPengirim && $unitPengirim->level === 'rektorat')
                                Rektor
                            @elseif($unitPengirim && $unitPengirim->level === 'fakultas')
                                Dekan
                            @elseif($unitPengirim && $unitPengirim->level === 'prodi')
                                Ketua Program Studi
                            @else
                                Pimpinan Pengirim
                            @endif
                        </div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $pejabatTtd }}
                        </div>
                    </div>
                </div>

                <!-- Tembusan -->
                @if($tembusanTeks)
                <div style="margin-top: 30px; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-size: 10pt; color: #334155;">
                    <div style="font-weight: bold; margin-bottom: 4px;">Tembusan:</div>
                    <div style="white-space: pre-wrap;">{{ $tembusanTeks }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
        @if(!$surat->asal_disposisi_id)
            <a href="{{ route('surat-masuk.edit', $surat->id_surat_masuk) }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #059669; color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">
                <i class="bi bi-pencil-fill"></i> Edit Data
            </a>
        @else
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; border-radius: 8px; font-size: 12.5px; font-weight: 600;">
                <i class="bi bi-lock-fill"></i> Dokumen Resmi Disposisi (Terkunci dari Edit / Hapus)
            </span>
        @endif

        @if(Auth::user()->isPimpinan() && $surat->status_arsip !== 'diarsipkan')
        <a href="{{ route('disposisi.create', $surat->id_surat_masuk) }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #7c3aed; color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">
            <i class="bi bi-arrow-left-right"></i> Buat Disposisi
        </a>
        @endif

        @if($surat->status_arsip !== 'diarsipkan')
        <form action="{{ route('surat-masuk.arsipkan', $surat->id_surat_masuk) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #334155; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                <i class="bi bi-archive-fill"></i> Arsipkan
            </button>
        </form>
        @endif

        @if(!$surat->asal_disposisi_id)
        <form action="{{ route('surat-masuk.destroy', $surat->id_surat_masuk) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus surat ini?')">
            @csrf @method('DELETE')
            <button type="submit" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #dc2626; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                <i class="bi bi-trash-fill"></i> Hapus
            </button>
        </form>
        @endif
    </div>
</div>

<!-- Riwayat Disposisi -->
@if($surat->disposisi->count())
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 16px 0;">Riwayat Disposisi</h2>
    @foreach($surat->disposisi as $d)
    <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 12px; background: #f8fafc;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div>
                <span style="font-size: 13px; font-weight: 700; color: #0f172a;">Dari: {{ $d->pengirim ? $d->pengirim->name : '—' }}</span>
                <span style="font-size: 12px; color: #64748b; margin-left: 8px;">{{ \App\Services\ArsitaService::tglIndo($d->tanggal_kirim) }}</span>
            </div>
            {!! \App\Services\ArsitaService::badgeStatus($d->sifat) !!}
        </div>
        <p style="font-size: 13px; color: #334155; margin: 0 0 10px 0;"><strong>Instruksi:</strong> {{ $d->instruksi }}</p>
        @if($d->tenggat_waktu)
        <p style="font-size: 12px; color: #dc2626; margin: 0 0 10px 0;"><i class="bi bi-calendar-x-fill"></i> Tenggat: {{ \App\Services\ArsitaService::tglIndo($d->tenggat_waktu) }}</p>
        @endif
        @foreach($d->tujuan as $t)
        <div style="padding: 10px; background: #fff; border-radius: 6px; border: 1px solid #f1f5f9; margin-top: 6px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; color: #334155;">
                <i class="bi bi-person-fill"></i>
                @if($t->tujuan_tipe === 'individu')
                    {{ $t->penerimaUser ? $t->penerimaUser->name : '—' }}
                @else
                    {{ $t->penerimaUnit ? $t->penerimaUnit->nama_unit : '—' }} (Unit)
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                {!! \App\Services\ArsitaService::badgeStatus($t->status) !!}
                <a href="{{ route('disposisi.show', $t->id_disposisi_tujuan) }}" style="font-size: 12px; color: #2563eb; text-decoration: none; font-weight: 600;">Detail →</a>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endif

</div>
@endsection
