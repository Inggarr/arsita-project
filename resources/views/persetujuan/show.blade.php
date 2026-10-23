@extends('layouts.app')
@section('title', 'Tinjau Persetujuan Surat Keluar (ACC)')

@section('content')
<div style="max-width: 900px;">
    <div style="margin-bottom: 16px;">
        <a href="{{ route('persetujuan.index') }}" style="color: #64748b; text-decoration: none; font-size: 13px;">← Kembali ke Antrean Persetujuan</a>
    </div>

    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #991b1b;">
            {{ session('error') }}
        </div>
    @endif

    @php
        $surat = $acc->suratKeluar;
    @endphp

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <div style="display: inline-block; background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; margin-bottom: 8px;">
                    Tahap Persetujuan ke-{{ $acc->tahap_ke }}
                </div>
                <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">{{ $surat?->perihal }}</h1>
                <p style="color: #64748b; font-size: 13px; margin: 0;">
                    Draft diajukan oleh: <strong>{{ $surat?->pembuat?->nama ?? $surat?->pembuat?->name ?? 'Staf' }}</strong> ({{ $surat?->unit?->nama_unit ?? '-' }})
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                {!! \App\Services\ArsitaService::badgeStatus($surat?->status_surat ?? 'draft') !!}
                {!! \App\Services\ArsitaService::badgeStatus($surat?->sifat ?? 'biasa') !!}
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
            @php
                $rows = [
                    ['Kepada (Tujuan)', $surat?->tujuan ?? '—'],
                    ['Unit Tujuan Internal', $surat?->tujuanUnit ? $surat->tujuanUnit->nama_unit . ' (Kirim Otomatis)' : 'Eksternal / Non-Unit'],
                    ['Unit Pembuat', $surat?->unit ? $surat->unit->nama_unit : '—'],
                    ['Jenis Surat', $surat?->jenisSurat ? $surat->jenisSurat->nama_jenis . ' (' . $surat->jenisSurat->kode_jenis . ')' : '—'],
                    ['Kategori', $surat?->kategori ? $surat->kategori->nama_kategori : '—'],
                    ['Lampiran', $surat?->lampiran ?? '—'],
                    ['Tanggal Surat', \App\Services\ArsitaService::tglIndo($surat?->tanggal_draft)],
                    ['File Draft', $surat?->file_draft ? '<a href="' . asset('uploads/surat_keluar/' . $surat->file_draft) . '" target="_blank" style="color: #2563eb; text-decoration: underline;"><i class="bi bi-file-earmark-arrow-down"></i> Unduh / Lihat Berkas</a>' : 'Tidak ada berkas terlampir'],
                ];
            @endphp
            @foreach($rows as $i => $row)
            <div style="padding: 12px 16px; {{ $i % 2 == 0 ? 'background: #f8fafc;' : '' }} border-bottom: 1px solid #f1f5f9;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 4px;">{{ $row[0] }}</div>
                <div style="font-size: 14px; color: #0f172a; font-weight: 500;">{!! $row[1] !!}</div>
            </div>
            @endforeach
        </div>

        <!-- Preview Lembar Naskah Dinas Resmi (Kop, Format Dinas, TTD) -->
        <div style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                    <i class="bi bi-file-earmark-text-fill" style="color: #2563eb;"></i> Pratinjau Naskah Dinas Lengkap
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
                    @if($surat?->unit)
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
                            @if($surat?->nomorSurat)
                                <strong>{{ $surat->nomorSurat->nomor_surat }}</strong>
                            @else
                                <span style="color: #dc2626; font-style: italic;">[Draft - Nomor terbit otomatis setelah ACC]</span>
                            @endif
                        </td>
                        <td style="text-align: right; width: 40%; padding: 2px 0; vertical-align: top;">
                            Serang, {{ \App\Services\ArsitaService::tglIndo($surat?->tanggal_draft) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: top;">Sifat</td>
                        <td style="padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;">{{ ucfirst($surat?->sifat ?? 'biasa') }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: top;">Lampiran</td>
                        <td style="padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;">{{ $surat?->lampiran ?: '-' }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: top;">Hal</td>
                        <td style="padding: 2px 0; vertical-align: top;">:</td>
                        <td style="padding: 2px 0; vertical-align: top;"><strong>{{ $surat?->perihal }}</strong></td>
                        <td></td>
                    </tr>
                </table>

                <!-- Penerima Tujuan -->
                <div style="margin-bottom: 24px; font-size: 12pt;">
                    <div>Yth.</div>
                    <div style="font-weight: bold;">{{ $surat?->tujuan }}</div>
                    <div>di</div>
                    <div style="padding-left: 20px;">Tempat</div>
                </div>

                <!-- Isi Naskah Surat Sesuai Margin A4 Word -->
                <div style="font-size: 12pt; line-height: 1.6; text-align: justify; margin-bottom: 30px; white-space: pre-wrap;">{{ $surat?->isi_surat }}</div>

                <!-- Tanda Tangan Resmi Pejabat -->
                <div style="display: flex; justify-content: flex-end; margin-top: 40px;">
                    <div style="text-align: center; min-width: 240px; font-size: 12pt;">
                        <div>{{ $surat?->unit ? $surat->unit->nama_unit : 'UINSSC' }},</div>
                        <div style="font-weight: bold; margin-bottom: 65px;">
                            @if($surat?->unit && $surat->unit->level === 'rektorat')
                                Rektor
                            @elseif($surat?->unit && $surat->unit->level === 'fakultas')
                                Dekan
                            @else
                                Ketua Program Studi
                            @endif
                        </div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $surat?->pembuat ? ($surat->pembuat->nama ?? $surat->pembuat->name) : 'Pejabat Berwenang' }}
                        </div>
                    </div>
                </div>

                <!-- Tembusan -->
                @if($surat?->tembusan)
                <div style="margin-top: 30px; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-size: 10pt; color: #334155;">
                    <div style="font-weight: bold; margin-bottom: 4px;">Tembusan:</div>
                    <div style="white-space: pre-wrap;">{{ $surat->tembusan }}</div>
                </div>
                @endif
            </div>
        </div>
        </div>

        <!-- Form Keputusan Persetujuan -->
        <div style="margin-top: 28px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Keputusan Pimpinan (ACC)</h3>
                    <p style="font-size: 13px; color: #64748b; margin: 0;">Pilih persetujuan untuk menerbitkan nomor surat resmi atau tolak jika membutuhkan revisi.</p>
                </div>
            </div>

            <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                <!-- Tombol Setuju dengan Modal Konfirmasi Profesional -->
                <form id="form-acc-setuju" action="{{ route('persetujuan.proses', $surat->id_surat_keluar) }}" method="POST" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="aksi" value="setuju">
                    <button type="button" id="btn-trigger-acc-setuju" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 26px; background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25); transition: all 0.2s ease;">
                        <i class="bi bi-patch-check-fill" style="font-size: 18px;"></i> Setujui Surat (ACC)
                    </button>
                </form>

                <!-- Tombol Tolak / Minta Revisi -->
                <button type="button" onclick="bukaModalTolak()" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 22px; background: #fff; color: #dc2626; border: 1.5px solid #fecaca; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                    <i class="bi bi-x-circle-fill" style="font-size: 17px; color: #dc2626;"></i> Tolak / Minta Revisi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Catatan Tolak / Revisi Profesional -->
<div id="modal-tolak" class="arsita-modal-overlay" style="display: none;">
    <div class="arsita-modal-card" style="max-width: 500px; text-align: left; padding: 28px;">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
                <i class="bi bi-arrow-counterclockwise"></i>
            </div>
            <div>
                <h3 style="font-size: 17px; font-weight: 700; margin: 0; color: #0f172a;">Kembalikan Draft untuk Revisi</h3>
                <p style="font-size: 12.5px; color: #64748b; margin: 2px 0 0 0;">Staf pembuat akan menerima notifikasi beserta catatan Anda.</p>
            </div>
        </div>

        <form action="{{ route('persetujuan.proses', $surat->id_surat_keluar) }}" method="POST">
            @csrf
            <input type="hidden" name="aksi" value="tolak">
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Catatan Perbaikan / Alasan Penolakan <span style="color: #dc2626;">*</span></label>
                <textarea name="catatan_penolakan" rows="4" required placeholder="Contoh: Perbaiki bagian format nomor, tanggal pelaksanaan, atau lampiran pendukung..." style="width: 100%; padding: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; font-family: inherit; box-sizing: border-box; resize: vertical; line-height: 1.5;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="tutupModalTolak()" class="btn btn-secondary" style="padding: 10px 18px;">Batal</button>
                <button type="submit" class="btn btn-danger" style="padding: 10px 20px; font-weight: 600;">
                    <i class="bi bi-send-fill"></i> Kirim Catatan Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btnSetuju = document.getElementById('btn-trigger-acc-setuju');
    if (btnSetuju) {
        btnSetuju.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof arsitaConfirm === 'function') {
                arsitaConfirm({
                    title: 'Konfirmasi Persetujuan Surat',
                    message: 'Apakah Anda yakin menyetujui surat keluar ini? Sistem akan langsung menerbitkan nomor surat resmi dan meneruskan dokumen ke unit tujuan internal (jika ada).',
                    type: 'success',
                    confirmText: 'Ya, Setujui Sekarang (ACC)',
                    cancelText: 'Periksa Kembali',
                    confirmClass: 'btn-success',
                    onConfirm: function() {
                        document.getElementById('form-acc-setuju').submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin menyetujui surat ini?')) {
                    document.getElementById('form-acc-setuju').submit();
                }
            }
        });
    }
});

function bukaModalTolak() {
    var modal = document.getElementById('modal-tolak');
    if (modal) {
        modal.style.display = 'flex';
        requestAnimationFrame(function() {
            modal.classList.add('modal-visible');
            var ta = modal.querySelector('textarea');
            if (ta) ta.focus();
        });
    }
}

function tutupModalTolak() {
    var modal = document.getElementById('modal-tolak');
    if (modal) {
        modal.classList.remove('modal-visible');
        modal.classList.add('modal-closing');
        setTimeout(function() {
            modal.style.display = 'none';
            modal.classList.remove('modal-closing');
        }, 250);
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        tutupModalTolak();
    }
});
</script>
@endpush
@endsection
