@extends('layouts.app')
@section('title', 'Pengaturan Sistem')

@section('content')
@php
    $user = Auth::user();
    $tabs = [
        'format-nomor'       => ['label' => 'Penomoran Surat',       'icon' => 'bi-hash'],
        'klasifikasi-sifat'  => ['label' => 'Sifat / Keamanan Surat','icon' => 'bi-shield-check'],
        'kategori-jenis'     => ['label' => 'Kategori & Jenis Surat', 'icon' => 'bi-tags-fill'],
    ];

    if ($user->isAdminRektorat()) {
        $tabs['unit-organisasi'] = ['label' => 'Unit Organisasi', 'icon' => 'bi-diagram-3-fill'];
        $tabs['kop-surat']       = ['label' => 'Kop Surat',       'icon' => 'bi-building'];
        $tabs['format-surat']    = ['label' => 'Format Dokumen',  'icon' => 'bi-file-text'];
    }
@endphp

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0; display: flex; align-items: center; gap: 10px;">
        <span style="width: 36px; height: 36px; background: linear-gradient(135deg, #2563eb, #7c3aed); border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;">
            <i class="bi bi-gear-fill" style="color: #fff; font-size: 17px;"></i>
        </span>
        Pengaturan Sistem
    </h1>
    <p style="color: #64748b; font-size: 13px; margin: 0 0 0 46px;">Kelola pola penomoran dinas, kategori &amp; jenis surat, kode unit kampus, kop surat, dan format dokumen ARSITA.</p>
</div>

{{-- Tab Navigation --}}
<div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 28px; gap: 0; overflow-x: auto;">
    @foreach($tabs as $key => $tab)
    <a href="{{ route('pengaturan.index', ['tab' => $key]) }}"
       id="tab-btn-{{ $key }}"
       style="display: inline-flex; align-items: center; gap: 7px; padding: 11px 22px; font-size: 13px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $activeTab === $key ? '#2563eb' : 'transparent' }}; margin-bottom: -2px; color: {{ $activeTab === $key ? '#2563eb' : '#64748b' }}; white-space: nowrap; transition: all 0.15s ease;">
        <i class="bi {{ $tab['icon'] }}"></i>
        {{ $tab['label'] }}
    </a>
    @endforeach
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB 1 — FORMAT PENOMORAN SURAT
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'format-nomor')
<div>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 3px 0;">Format Penomoran Otomatis</h2>
            <p style="margin: 0; font-size: 12.5px; color: #64748b;">Template nomor dinas per unit dan jenis surat</p>
        </div>
        <button type="button" onclick="openCreateModal()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bi bi-plus-circle-fill"></i> Tambah Format
        </button>
    </div>

    {{-- Panduan token --}}
    <div style="background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 18px; margin-bottom: 18px; font-size: 12.5px; color: #166534; display: flex; gap: 12px; align-items: flex-start;">
        <i class="bi bi-info-circle-fill" style="font-size: 18px; color: #15803d; margin-top: 1px; flex-shrink: 0;"></i>
        <div>
            <strong>Pedoman Token Template Penomoran Surat (Standar kode.md Kemenag &amp; ARSITA):</strong>
            <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px;">
                <code style="background: #dcfce7; padding: 2px 8px; border-radius: 5px; color: #14532d; font-weight: 700; border: 1px solid #bbf7d0;">{sifat_kode}</code>
                <span style="align-self: center;">kode klasifikasi keamanan surat (B/T/R/SR)</span>
                <code style="background: #dcfce7; padding: 2px 8px; border-radius: 5px; color: #14532d; font-weight: 700; border: 1px solid #bbf7d0;">{nomor}</code>
                <span style="align-self: center;">nomor urut surat (0001 / 001)</span>
                <code style="background: #dcfce7; padding: 2px 8px; border-radius: 5px; color: #14532d; font-weight: 700; border: 1px solid #bbf7d0;">{kode_unit}</code>
                <span style="align-self: center;">kode unit pengirim (Un.30 / Fak.01 / INF-S1)</span>
                <code style="background: #dcfce7; padding: 2px 8px; border-radius: 5px; color: #14532d; font-weight: 700; border: 1px solid #bbf7d0;">{kode_jabatan}</code>
                <span style="align-self: center;">kode pejabat penandatangan (R / R.III / F.I / J.I.16)</span>
                <code style="background: #dcfce7; padding: 2px 8px; border-radius: 5px; color: #14532d; font-weight: 700; border: 1px solid #bbf7d0;">{kode_arsip}</code>
                <span style="align-self: center;">kode klasifikasi arsip surat (PP.04 / OT.01)</span>
                <code style="background: #dcfce7; padding: 2px 8px; border-radius: 5px; color: #14532d; font-weight: 700; border: 1px solid #bbf7d0;">{bulan}</code>
                <span style="align-self: center;">bulan 2 digit (10)</span>
                <code style="background: #dcfce7; padding: 2px 8px; border-radius: 5px; color: #14532d; font-weight: 700; border: 1px solid #bbf7d0;">{tahun}</code>
                <span style="align-self: center;">tahun ({{ date('Y') }})</span>
            </div>
            <div style="margin-top: 8px;">
                Format Standar Resmi Kemenag: <code style="background: #dcfce7; padding: 2px 7px; border-radius: 4px; font-weight: 700;">{sifat_kode}-{nomor}/{kode_unit}/{kode_jabatan}/{kode_arsip}/{bulan}/{tahun}</code>
                → <strong>B-0001/Un.30/R.III/PP.04/{{ date('m/Y') }}</strong>
            </div>
        </div>
    </div>

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0; width: 40px;">No</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Unit Kampus</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Jenis Surat</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Kategori</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Template Format</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Contoh Hasil</th>
                        <th style="padding: 12px 16px; text-align: center; color: #64748b; border-bottom: 1px solid #e2e8f0; width: 80px;">Status</th>
                        <th style="padding: 12px 16px; text-align: center; color: #64748b; border-bottom: 1px solid #e2e8f0; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($formatList as $index => $f)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        <td style="padding: 12px 16px; color: #94a3b8;">{{ $index + 1 }}</td>
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 600; color: #0f172a;">{{ $f->unit?->nama_unit ?? 'Semua Unit' }}</div>
                            @if($f->unit)<small style="color: #64748b; font-family: monospace;">({{ $f->unit->kode_unit }})</small>@endif
                        </td>
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 600; color: #1e40af;">{{ $f->jenisSurat?->nama_jenis ?? 'Semua Jenis' }}</div>
                            @if($f->jenisSurat)<small style="color: #64748b; font-family: monospace;">Kode: {{ $f->jenisSurat->kode_jenis }}</small>@endif
                        </td>
                        <td style="padding: 12px 16px; color: #475569;">{{ $f->kategori?->nama_kategori ?? 'Semua Kategori' }}</td>
                        <td style="padding: 12px 16px;">
                            <code style="background: #f1f5f9; padding: 3px 8px; border-radius: 5px; font-size: 12px; color: #7c3aed; font-weight: 700; border: 1px solid #e2e8f0;">{{ $f->format_nomor }}</code>
                        </td>
                        <td style="padding: 12px 16px; font-family: monospace; font-size: 12px; color: #059669; font-weight: 600;">{{ $f->contoh ?? '—' }}</td>
                        <td style="padding: 12px 16px; text-align: center;">
                            @if($f->aktif)
                                <span style="background: #ecfdf5; color: #059669; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;">Aktif</span>
                            @else
                                <span style="background: #fef2f2; color: #dc2626; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;">Nonaktif</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button type="button"
                                    onclick="openEditModal(this)"
                                    data-format="{{ json_encode($f->only(['id_format','id_unit','id_jenis_surat','kategori_id','format_nomor','contoh','aktif'])) }}"
                                    class="btn btn-secondary btn-sm" style="padding: 5px 9px;" title="Edit">
                                    <i class="bi bi-pencil-square" style="color: #2563eb;"></i>
                                </button>
                                <form action="{{ route('pengaturan.format-nomor.destroy', $f->id_format) }}" method="POST"
                                      style="display: inline; margin: 0;"
                                      onsubmit="return confirm('Hapus format penomoran ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 5px 9px;" title="Hapus">
                                        <i class="bi bi-trash" style="color: #dc2626;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 40px 20px; text-align: center; color: #94a3b8;">
                            <i class="bi bi-hash" style="font-size: 36px; display: block; margin-bottom: 10px; color: #cbd5e1;"></i>
                            Belum ada format nomor. Klik <strong>Tambah Format</strong> untuk memulai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Format Nomor --}}
<div id="modal-create-format" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 580px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #eff6ff, #f8fafc);">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-plus-circle" style="color: #2563eb;"></i> Tambah Format Penomoran
            </div>
            <button type="button" onclick="closeCreateModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('pengaturan.format-nomor.store') }}" method="POST" style="padding: 22px;">
            @csrf
            @include('pengaturan._form_format_nomor', ['prefix' => 'create', 'data' => null])
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 4px;">
                <button type="button" onclick="closeCreateModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle"></i> Simpan Format</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Format Nomor --}}
<div id="modal-edit-format" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 580px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #eff6ff, #f8fafc);">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-pencil-square" style="color: #2563eb;"></i> Edit Format Penomoran
            </div>
            <button type="button" onclick="closeEditModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form id="form-edit-format" action="" method="POST" style="padding: 22px;">
            @csrf @method('PUT')
            @include('pengaturan._form_format_nomor', ['prefix' => 'edit', 'data' => null])
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 4px;">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2-circle"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>

@endif {{-- end format-nomor --}}

{{-- ═══════════════════════════════════════════════════════════
     TAB — KLASIFIKASI KEAMANAN & SIFAT SURAT
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'klasifikasi-sifat')
<div>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 3px 0;">Klasifikasi Keamanan / Sifat Surat</h2>
            <p style="margin: 0; font-size: 12.5px; color: #64748b;">Kelola kode awal nomor surat (B, T, R, SR), warna tinta, dan peruntukan keamanan surat dinas.</p>
        </div>
        <button type="button" onclick="openCreateSifatModal()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bi bi-plus-circle-fill"></i> Tambah Sifat Surat
        </button>
    </div>

    {{-- Panduan --}}
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 12.5px; color: #334155;">
        <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 7px;">
            <i class="bi bi-info-circle-fill" style="color: #2563eb;"></i> Ketentuan Klasifikasi Keamanan Surat (Kemenag &amp; kode.md):
        </div>
        <ul style="margin: 4px 0 0 18px; padding: 0; line-height: 1.6;">
            <li><strong>B (Biasa):</strong> Untuk urusan dinas umum tanpa kerahasiaan khusus, menggunakan <em>tinta hitam</em>.</li>
            <li><strong>T (Terbatas):</strong> Untuk urusan dinas tertentu, menggunakan <em>tinta hitam</em>.</li>
            <li><strong>R (Rahasia):</strong> Untuk urusan yang bila bocor merugikan instansi, kode ditulis dengan <em>tinta merah</em>.</li>
            <li><strong>SR (Sangat Rahasia):</strong> Untuk urusan rahasia tingkat tinggi, kode ditulis dengan <em>tinta merah</em>.</li>
        </ul>
    </div>

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; text-align: left;">
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; width: 60px;">No</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; width: 120px;">Kode Surat</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569;">Nama Sifat</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; width: 140px;">Warna Tinta</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569;">Keterangan</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; width: 90px; text-align: center;">Status</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; width: 110px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sifatList as $idx => $s)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 12px 16px; color: #64748b;">{{ $idx + 1 }}</td>
                        <td style="padding: 12px 16px;">
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-family: monospace; font-size: 13px; background: {{ $s->badge_bg }}; color: {{ $s->badge_text }}; border: 1px solid rgba(0,0,0,0.06);">
                                {{ $s->kode_sifat }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">{{ $s->nama_sifat }}</td>
                        <td style="padding: 12px 16px;">
                            @if(strtolower($s->warna_tinta) === 'merah')
                                <span style="display: inline-flex; align-items: center; gap: 6px; color: #dc2626; font-weight: 600; font-size: 12px;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #dc2626;"></span> Merah
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 6px; color: #334155; font-weight: 600; font-size: 12px;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #334155;"></span> Hitam
                                </span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; color: #64748b; font-size: 12px;">{{ $s->keterangan ?: '-' }}</td>
                        <td style="padding: 12px 16px; text-align: center;">
                            @if($s->aktif)
                                <span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">Aktif</span>
                            @else
                                <span style="background: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">Nonaktif</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button type="button"
                                        onclick="openEditSifatModal({{ json_encode($s) }})"
                                        class="btn btn-secondary btn-sm"
                                        style="padding: 4px 8px; font-size: 12px;"
                                        title="Edit Sifat Surat">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('pengaturan.sifat.destroy', $s->id_sifat) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus sifat surat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px; font-size: 12px;" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 30px; text-align: center; color: #94a3b8;">
                            Belum ada data klasifikasi / sifat surat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Sifat Surat --}}
    <div id="modalCreateSifat" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #fff; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">Tambah Sifat Surat Baru</h3>
                <button type="button" onclick="closeCreateSifatModal()" style="border: none; background: transparent; font-size: 18px; cursor: pointer; color: #64748b;">&times;</button>
            </div>
            <form action="{{ route('pengaturan.sifat.store') }}" method="POST" style="padding: 20px;">
                @csrf
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kode Sifat Surat (Awalan Nomor) *</label>
                    <input type="text" name="kode_sifat" required placeholder="Contoh: B, T, R, SR" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; text-transform: uppercase;">
                    <small style="color: #64748b; font-size: 11px;">Akan digunakan pada awalan format nomor surat, misal B-0001</small>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Nama Sifat Surat *</label>
                    <input type="text" name="nama_sifat" required placeholder="Contoh: Biasa, Terbatas, Rahasia, Sangat Rahasia" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Warna Tinta Cetak *</label>
                    <select name="warna_tinta" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        <option value="hitam">Tinta Hitam (Standar: Biasa &amp; Terbatas)</option>
                        <option value="merah">Tinta Merah (Standar: Rahasia &amp; Sangat Rahasia)</option>
                    </select>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Keterangan singkat" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
                    <button type="button" onclick="closeCreateSifatModal()" class="btn btn-secondary btn-sm">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Sifat Surat --}}
    <div id="modalEditSifat" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #fff; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">Edit Sifat Surat</h3>
                <button type="button" onclick="closeEditSifatModal()" style="border: none; background: transparent; font-size: 18px; cursor: pointer; color: #64748b;">&times;</button>
            </div>
            <form id="formEditSifat" method="POST" style="padding: 20px;">
                @csrf
                @method('PUT')
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kode Sifat Surat *</label>
                    <input type="text" name="kode_sifat" id="edit_kode_sifat" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; text-transform: uppercase;">
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Nama Sifat Surat *</label>
                    <input type="text" name="nama_sifat" id="edit_nama_sifat" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Warna Tinta Cetak *</label>
                    <select name="warna_tinta" id="edit_warna_tinta" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        <option value="hitam">Tinta Hitam</option>
                        <option value="merah">Tinta Merah</option>
                    </select>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Keterangan</label>
                    <input type="text" name="keterangan" id="edit_keterangan_sifat" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
                    <button type="button" onclick="closeEditSifatModal()" class="btn btn-secondary btn-sm">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif {{-- end klasifikasi-sifat --}}

{{-- ═══════════════════════════════════════════════════════════
     TAB 2 — KATEGORI & JENIS SURAT
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'kategori-jenis')
<div>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 3px 0;">Kategori &amp; Jenis Surat</h2>
            <p style="margin: 0; font-size: 12.5px; color: #64748b;">Kelola klasifikasi arsip surat (kode kategori) dan jenis surat dinas yang dikelompokkan per kategori.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="openCreateKategoriModal()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-tag-fill" style="color: #2563eb;"></i> Tambah Kategori
            </button>
            <button type="button" onclick="openCreateJenisModal()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-plus-circle-fill"></i> Tambah Jenis Surat
            </button>
        </div>
    </div>

    {{-- Info Kode Klasifikasi Arsip --}}
    <div style="background: linear-gradient(135deg, #f0fdf4, #eff6ff); border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 12.5px; color: #1e3a8a; display: flex; gap: 12px; align-items: flex-start;">
        <i class="bi bi-info-circle-fill" style="font-size: 18px; color: #2563eb; margin-top: 1px; flex-shrink: 0;"></i>
        <div>
            <strong>Kode Klasifikasi Arsip (Sesuai Tata Naskah Dinas Kemenag):</strong>
            <div style="margin-top: 4px; color: #334155; line-height: 1.5;">
                Kode kategori surat digunakan secara otomatis sebagai token <code>{kode_arsip}</code> dalam penomoran surat resmi (misal: <code>PP.04</code> untuk Akademik, <code>KM.01</code> untuk Kemahasiswaan, <code>KP.02</code> untuk Kepegawaian, <code>OT.01</code> untuk Umum).
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 20px; align-items: start;">
        {{-- Daftar Kategori Surat --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
            <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-tags-fill" style="color: #2563eb;"></i> Kategori Surat (Klasifikasi Arsip)
                </div>
                <span style="font-size: 11px; background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 999px; font-weight: 700;">{{ $kategoriList->count() }} Kategori</span>
            </div>
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 10px 14px; text-align: left; color: #64748b;">Nama &amp; Kode Arsip</th>
                            <th style="padding: 10px 14px; text-align: center; color: #64748b; width: 70px;">Jenis</th>
                            <th style="padding: 10px 14px; text-align: center; color: #64748b; width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoriList as $k)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px 14px;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $k->nama_kategori }}</div>
                                <div style="display: flex; gap: 6px; align-items: center; margin-top: 2px;">
                                    <code style="background: #eff6ff; color: #1d4ed8; padding: 1px 6px; border-radius: 4px; font-weight: 700; font-size: 11px; border: 1px solid #bfdbfe;">
                                        {{ $k->kode_kategori ?: '-' }}
                                    </code>
                                    <span style="color: #64748b; font-size: 11px;">{{ $k->deskripsi ?: '—' }}</span>
                                </div>
                            </td>
                            <td style="padding: 10px 14px; text-align: center;">
                                <span style="background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;">
                                    {{ $k->jenis_surat_count }}
                                </span>
                            </td>
                            <td style="padding: 10px 14px; text-align: center;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" onclick="openEditKategoriModal(this)"
                                        data-kategori="{{ json_encode($k->only(['id_kategori', 'nama_kategori', 'kode_kategori', 'deskripsi'])) }}"
                                        class="btn btn-secondary btn-sm" style="padding: 3px 7px;" title="Edit">
                                        <i class="bi bi-pencil" style="color: #2563eb; font-size: 11px;"></i>
                                    </button>
                                    <form action="{{ route('pengaturan.kategori.destroy', $k->id_kategori) }}" method="POST"
                                          style="display: inline; margin: 0;" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 3px 7px;" title="Hapus">
                                            <i class="bi bi-trash" style="color: #dc2626; font-size: 11px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="padding: 24px; text-align: center; color: #94a3b8;">Belum ada kategori surat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Daftar Jenis Surat yang Dikelompokkan per Kategori --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
            <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-file-earmark-text-fill" style="color: #7c3aed;"></i> Jenis Surat Dinas
                </div>
                <span style="font-size: 11px; background: #f3e8ff; color: #6b21a8; padding: 2px 8px; border-radius: 999px; font-weight: 700;">{{ $jenisList->count() }} Jenis</span>
            </div>
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 10px 14px; text-align: left; color: #64748b;">Jenis &amp; Kode</th>
                            <th style="padding: 10px 14px; text-align: left; color: #64748b;">Kelompok Kategori</th>
                            <th style="padding: 10px 14px; text-align: left; color: #64748b;">Keterangan</th>
                            <th style="padding: 10px 14px; text-align: center; color: #64748b; width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jenisList as $j)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px 14px;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $j->nama_jenis }}</div>
                                <code style="background: #f8fafc; color: #7c3aed; padding: 1px 6px; border-radius: 4px; font-weight: 700; font-size: 11px; border: 1px solid #e2e8f0;">
                                    {{ $j->kode_jenis }}
                                </code>
                            </td>
                            <td style="padding: 10px 14px;">
                                @if($j->kategori)
                                    <span style="background: #ecfdf5; color: #065f46; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; border: 1px solid #a7f3d0;">
                                        {{ $j->kategori->nama_kategori }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8; font-style: italic; font-size: 11px;">(Semua / Umum)</span>
                                @endif
                            </td>
                            <td style="padding: 10px 14px; color: #64748b;">{{ $j->keterangan ?: '—' }}</td>
                            <td style="padding: 10px 14px; text-align: center;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" onclick="openEditJenisModal(this)"
                                        data-jenis="{{ json_encode($j->only(['id_jenis_surat', 'nama_jenis', 'kode_jenis', 'kategori_id', 'keterangan'])) }}"
                                        class="btn btn-secondary btn-sm" style="padding: 3px 7px;" title="Edit">
                                        <i class="bi bi-pencil" style="color: #2563eb; font-size: 11px;"></i>
                                    </button>
                                    <form action="{{ route('pengaturan.jenis.destroy', $j->id_jenis_surat) }}" method="POST"
                                          style="display: inline; margin: 0;" onsubmit="return confirm('Hapus jenis surat ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 3px 7px;" title="Hapus">
                                            <i class="bi bi-trash" style="color: #dc2626; font-size: 11px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="padding: 24px; text-align: center; color: #94a3b8;">Belum ada jenis surat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Kategori --}}
<div id="modal-create-kategori" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #eff6ff, #f8fafc);">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-tag-fill" style="color: #2563eb;"></i> Tambah Kategori Surat
            </div>
            <button type="button" onclick="closeCreateKategoriModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('pengaturan.kategori.store') }}" method="POST" style="padding: 22px;">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Nama Kategori *</label>
                <input type="text" name="nama_kategori" required placeholder="cth: Akademik, Keuangan, dll."
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kode Arsip Kemenag (Opsional)</label>
                <input type="text" name="kode_kategori" placeholder="cth: PP.04, KM.01, KU.03"
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
                <small style="color: #64748b; font-size: 11px;">Digunakan otomatis sebagai token <code>{kode_arsip}</code> pada penomoran surat.</small>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Deskripsi</label>
                <textarea name="deskripsi" rows="2" placeholder="Keterangan kategori..."
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box; resize: vertical;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                <button type="button" onclick="closeCreateKategoriModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Kategori --}}
<div id="modal-edit-kategori" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #eff6ff, #f8fafc);">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-pencil-square" style="color: #2563eb;"></i> Edit Kategori Surat
            </div>
            <button type="button" onclick="closeEditKategoriModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form id="form-edit-kategori" action="" method="POST" style="padding: 22px;">
            @csrf @method('PUT')
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Nama Kategori *</label>
                <input type="text" name="nama_kategori" id="edit_kategori_nama" required
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kode Arsip Kemenag</label>
                <input type="text" name="kode_kategori" id="edit_kategori_kode"
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Deskripsi</label>
                <textarea name="deskripsi" id="edit_kategori_deskripsi" rows="2"
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box; resize: vertical;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                <button type="button" onclick="closeEditKategoriModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2-circle"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Jenis Surat --}}
<div id="modal-create-jenis" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #eff6ff, #f8fafc);">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-file-earmark-plus-fill" style="color: #2563eb;"></i> Tambah Jenis Surat
            </div>
            <button type="button" onclick="closeCreateJenisModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('pengaturan.jenis.store') }}" method="POST" style="padding: 22px;">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Nama Jenis Surat *</label>
                <input type="text" name="nama_jenis" required placeholder="cth: Surat Keputusan, Surat Edaran"
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kode Singkatan Jenis *</label>
                <input type="text" name="kode_jenis" required placeholder="cth: SK, SE, ST, SU, SP"
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kelompokkan ke Kategori (Opsional)</label>
                <select name="kategori_id" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Umum / Semua Kategori --</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }} ({{ $k->kode_kategori ?: '-' }})</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Keterangan</label>
                <input type="text" name="keterangan" placeholder="Keterangan singkat..."
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                <button type="button" onclick="closeCreateJenisModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Jenis Surat --}}
<div id="modal-edit-jenis" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #eff6ff, #f8fafc);">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-pencil-square" style="color: #2563eb;"></i> Edit Jenis Surat
            </div>
            <button type="button" onclick="closeEditJenisModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form id="form-edit-jenis" action="" method="POST" style="padding: 22px;">
            @csrf @method('PUT')
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Nama Jenis Surat *</label>
                <input type="text" name="nama_jenis" id="edit_jenis_nama" required
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kode Singkatan Jenis *</label>
                <input type="text" name="kode_jenis" id="edit_jenis_kode" required
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kelompokkan ke Kategori</label>
                <select name="kategori_id" id="edit_jenis_kategori_id" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Umum / Semua Kategori --</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Keterangan</label>
                <input type="text" name="keterangan" id="edit_jenis_keterangan"
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                <button type="button" onclick="closeEditJenisModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2-circle"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>

@endif {{-- end kategori-jenis --}}


{{-- ═══════════════════════════════════════════════════════════
     TAB 3 — UNIT ORGANISASI & KODE PENOMORAN UNIT
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'unit-organisasi')
<div>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 3px 0;">Daftar Unit Kampus &amp; Kode Penomoran</h2>
            <p style="margin: 0; font-size: 12.5px; color: #64748b;">Setiap unit kampus memiliki kode penomoran resmi tersendiri (token <code>{kode_unit}</code>) pada nomor surat dinas.</p>
        </div>
        <button type="button" onclick="openCreateUnitModal()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bi bi-plus-circle-fill"></i> Tambah Unit Organisasi
        </button>
    </div>

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; width: 40px;">No</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b;">Nama Unit</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b;">Kode Unit (Nomor Surat)</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b;">Level</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b;">Induk (Parent)</th>
                        <th style="padding: 12px 16px; text-align: center; color: #64748b; width: 80px;">Status</th>
                        <th style="padding: 12px 16px; text-align: center; color: #64748b; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $idx => $u)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        <td style="padding: 12px 16px; color: #94a3b8;">{{ $idx + 1 }}</td>
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 700; color: #0f172a;">{{ $u->nama_unit }}</div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <code style="background: #eff6ff; color: #1e40af; padding: 2px 8px; border-radius: 5px; font-weight: 700; font-size: 12px; border: 1px solid #bfdbfe;">
                                {{ $u->kode_unit }}
                            </code>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="background: #f1f5f9; color: #334155; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                {{ $u->level }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px; color: #64748b;">{{ $u->parent?->nama_unit ?? '—' }}</td>
                        <td style="padding: 12px 16px; text-align: center;">
                            @if($u->status === 'aktif')
                                <span style="background: #ecfdf5; color: #059669; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;">Aktif</span>
                            @else
                                <span style="background: #fef2f2; color: #dc2626; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;">Nonaktif</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button type="button" onclick="openEditUnitModal(this)"
                                    data-unit="{{ json_encode($u->only(['id_unit', 'nama_unit', 'kode_unit', 'level', 'id_parent', 'status'])) }}"
                                    class="btn btn-secondary btn-sm" style="padding: 5px 9px;" title="Edit">
                                    <i class="bi bi-pencil-square" style="color: #2563eb;"></i>
                                </button>
                                <form action="{{ route('pengaturan.unit.destroy', $u->id_unit) }}" method="POST"
                                      style="display: inline; margin: 0;" onsubmit="return confirm('Hapus unit ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 5px 9px;" title="Hapus">
                                        <i class="bi bi-trash" style="color: #dc2626;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 30px; text-align: center; color: #94a3b8;">Belum ada data unit.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Unit --}}
<div id="modal-create-unit" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #eff6ff, #f8fafc);">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-diagram-3-fill" style="color: #2563eb;"></i> Tambah Unit Kampus
            </div>
            <button type="button" onclick="closeCreateUnitModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('pengaturan.unit.store') }}" method="POST" style="padding: 22px;">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Nama Unit *</label>
                <input type="text" name="nama_unit" required placeholder="cth: Fakultas Syariah / Prodi TI"
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kode Unit (Token Nomor Surat) *</label>
                <input type="text" name="kode_unit" required placeholder="cth: Fak.01 / INF-S1 / LPM / Un.30"
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
                <small style="color: #64748b; font-size: 11px;">Akan menggantikan token <code>{kode_unit}</code> pada nomor dinas resmi.</small>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Level Unit *</label>
                    <select name="level" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        <option value="rektorat">Rektorat</option>
                        <option value="fakultas">Fakultas</option>
                        <option value="prodi" selected>Program Studi</option>
                        <option value="lembaga">Lembaga</option>
                        <option value="upt">UPT</option>
                        <option value="pusat">Pusat / Unit</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Status *</label>
                    <select name="status" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        <option value="aktif" selected>Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Unit Induk / Parent (Opsional)</label>
                <select name="id_parent" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Tidak Ada Induk (Independen) --</option>
                    @foreach($units as $pu)
                        <option value="{{ $pu->id_unit }}">{{ $pu->nama_unit }} ({{ $pu->kode_unit }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                <button type="button" onclick="closeCreateUnitModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle"></i> Simpan Unit</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Unit --}}
<div id="modal-edit-unit" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #eff6ff, #f8fafc);">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-pencil-square" style="color: #2563eb;"></i> Edit Unit Kampus
            </div>
            <button type="button" onclick="closeEditUnitModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form id="form-edit-unit" action="" method="POST" style="padding: 22px;">
            @csrf @method('PUT')
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Nama Unit *</label>
                <input type="text" name="nama_unit" id="edit_unit_nama" required
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kode Unit (Token Nomor Surat) *</label>
                <input type="text" name="kode_unit" id="edit_unit_kode" required
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Level Unit *</label>
                    <select name="level" id="edit_unit_level" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        <option value="rektorat">Rektorat</option>
                        <option value="fakultas">Fakultas</option>
                        <option value="prodi">Program Studi</option>
                        <option value="lembaga">Lembaga</option>
                        <option value="upt">UPT</option>
                        <option value="pusat">Pusat / Unit</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Status *</label>
                    <select name="status" id="edit_unit_status" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Unit Induk / Parent</label>
                <select name="id_parent" id="edit_unit_id_parent" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Tidak Ada Induk (Independen) --</option>
                    @foreach($units as $pu)
                        <option value="{{ $pu->id_unit }}">{{ $pu->nama_unit }} ({{ $pu->kode_unit }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                <button type="button" onclick="closeEditUnitModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2-circle"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>

@endif {{-- end unit-organisasi --}}


{{-- ═══════════════════════════════════════════════════════════
     TAB 2 — KOP SURAT
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'kop-surat')
@php
    $kopData = \App\Models\PengaturanSurat::getKop();
@endphp
<div>
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 3px 0;">Pengaturan Kop Surat Resmi</h2>
        <p style="margin: 0; font-size: 12.5px; color: #64748b;">Konfigurasi kop surat standar Kementerian Agama & PTKN untuk seluruh surat keluar dan dokumen dinas ARSITA.</p>
    </div>

    {{-- Info Standar Resmi Kemenag --}}
    <div style="background: linear-gradient(135deg, #eff6ff, #f0fdf4); border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 12.5px; color: #1e3a8a;">
        <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <i class="bi bi-patch-check-fill" style="color: #2563eb; font-size: 15px;"></i>
            Pedoman Resmi Kop Surat Kementerian Agama RI:
        </div>
        <ul style="margin: 0; padding-left: 20px; line-height: 1.6; color: #334155;">
            <li><strong>Baris 1:</strong> Tulisan <em>Kementerian Agama Republik Indonesia</em> (Kapital, Tebal, Arial 14pt).</li>
            <li><strong>Baris 2:</strong> Tulisan <em>Nama PTKN</em> (Kapital, Tebal, Arial 12pt).</li>
            <li><strong>Baris 3 (Dinamis):</strong> Tulisan <em>Nama Fakultas / Program Studi / Unit</em> di bawah nama PTKN (otomatis sesuai unit pembuat surat keluar).</li>
            <li><strong>Baris 4:</strong> Alamat, nomor telepon, dan laman website PTKN (Awal Kapital, Arial 9pt).</li>
        </ul>
    </div>

    {{-- Live Preview Kop --}}
    <div style="background: #fff; border: 2px solid #cbd5e1; border-radius: 12px; padding: 24px 32px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
            <i class="bi bi-eye-fill" style="color: #2563eb;"></i> Live Preview Kop Surat (Tampilan Real di Dokumen & Cetak)
        </div>
        <div id="preview-kop-container" style="background: #ffffff; padding: 10px 0;">
            @include('components.kop_surat', ['kop' => $kopData, 'subUnit' => ($kopData['sub_unit_default'] ?: 'FAKULTAS TARBIYAH DAN KEGURUAN')])
        </div>
        <div style="text-align: center; font-size: 11px; color: #94a3b8; margin-top: 8px; font-style: italic;">
            * Baris fakultas/prodi akan otomatis berganti sesuai unit pengirim pada setiap surat keluar.
        </div>
    </div>

    <form action="{{ route('pengaturan.kop-surat.update') }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Bagian Teks & Identitas --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                <div style="font-size: 14px; font-weight: 700; color: #1e3a8a; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-card-heading"></i> Konten & Identitas Kop Surat
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div style="grid-column: 1 / -1;">
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Baris 1: Tulisan Kementerian *</label>
                        <input type="text" name="kop_kementerian" value="{{ $kopData['nama_kementerian'] ?? 'KEMENTERIAN AGAMA REPUBLIK INDONESIA' }}" required
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="KEMENTERIAN AGAMA REPUBLIK INDONESIA">
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Baris 2: Nama PTKN Lengkap *</label>
                        <input type="text" name="kop_nama_instansi" value="{{ $kopData['nama_instansi'] ?? 'UNIVERSITAS ISLAM NEGERI SIBER SYEKH NURJATI CIREBON' }}" required
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="UNIVERSITAS ISLAM NEGERI SIBER SYEKH NURJATI CIREBON">
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                            Baris 3: Default Sub-Unit / Fakultas / Prodi (Opsional)
                        </label>
                        <input type="text" name="kop_sub_unit" value="{{ $kopData['sub_unit_default'] ?? '' }}"
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="Biarkan kosong agar otomatis mengikuti Unit pengirim pada surat keluar">
                        <small style="color: #64748b; font-size: 11.5px; margin-top: 4px; display: block;">Jika dikosongkan, baris ini otomatis diisi nama unit pembuat surat keluar (misal: FAKULTAS TARBIYAH / PRODI PAI).</small>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Singkatan / Akronim PTKN *</label>
                        <input type="text" name="kop_singkatan" value="{{ $kopData['singkatan'] ?? 'UINSSC' }}" required
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="UINSSC">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Kota *</label>
                        <input type="text" name="kop_kota" value="{{ $kopData['kota'] ?? 'Cirebon' }}" required
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="Cirebon">
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Baris 4: Alamat Lengkap PTKN *</label>
                        <input type="text" name="kop_alamat" value="{{ $kopData['alamat_instansi'] ?? 'Jalan Perjuangan By Pass Sunyaragi' }}" required
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="Jalan Perjuangan By Pass Sunyaragi">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Kode Pos</label>
                        <input type="text" name="kop_kode_pos" value="{{ $kopData['kode_pos'] ?? '45132' }}"
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="45132">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Nomor Telepon</label>
                        <input type="text" name="kop_telepon" value="{{ $kopData['telepon_instansi'] ?? '(0231) 481264' }}"
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="(0231) 481264">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Nama Laman Website</label>
                        <input type="text" name="kop_website" value="{{ $kopData['website_instansi'] ?? 'www.uinssc.ac.id' }}"
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="www.uinssc.ac.id">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Email Resmi</label>
                        <input type="email" name="kop_email" value="{{ $kopData['email_instansi'] ?? 'info@uinssc.ac.id' }}"
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                            placeholder="info@uinssc.ac.id">
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Logo Instansi (PNG/JPG, maks. 2MB)</label>
                        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                            @if(!empty($kopData['logo_instansi']))
                            <div style="width: 60px; height: 60px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ asset('uploads/pengaturan/' . $kopData['logo_instansi']) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                            </div>
                            @endif
                            <input type="file" name="kop_logo" accept=".png,.jpg,.jpeg,.svg"
                                style="flex: 1; padding: 9px 13px; border: 1.5px dashed #cbd5e1; border-radius: 8px; font-size: 13px; cursor: pointer; background: #f8fafc;">
                        </div>
                        <small style="color: #94a3b8; font-size: 11.5px; margin-top: 4px; display: block;">Kosongkan jika tidak ingin mengganti logo.</small>
                    </div>
                </div>
            </div>

            {{-- Bagian Tipografi & Ukuran Huruf Tiap Baris Sesuai Regulasi --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                <div style="font-size: 14px; font-weight: 700; color: #7c3aed; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-type"></i> Tipografi, Ukuran Huruf, & Ketebalan Per Baris
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Jenis Font Kop Surat</label>
                    <select name="kop_font_family" style="width: 100%; max-width: 400px; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        @foreach([
                            'Arial, Helvetica, sans-serif' => 'Arial (Standar Resmi Kemenag)',
                            "'Times New Roman', Times, serif" => 'Times New Roman',
                            'Calibri, Candara, Segoe, sans-serif' => 'Calibri',
                            'Tahoma, Verdana, sans-serif' => 'Tahoma'
                        ] as $fontVal => $fontLabel)
                            <option value="{{ $fontVal }}" {{ ($kopData['font_family'] ?? 'Arial, Helvetica, sans-serif') === $fontVal ? 'selected' : '' }}>
                                {{ $fontLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                    {{-- Setting Baris 1: Kemenag --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                        <div style="font-weight: 700; font-size: 12.5px; color: #0f172a; margin-bottom: 8px;">
                            Baris 1: Kemenag
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="font-size: 11.5px; color: #64748b; display: block; margin-bottom: 4px;">Ukuran Font</label>
                            <input type="text" name="kop_kemenag_font_size" value="{{ $kopData['kemenag_font_size'] ?? '14pt' }}" placeholder="14pt"
                                style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12.5px;">
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <label style="font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="hidden" name="kop_kemenag_bold" value="0">
                                <input type="checkbox" name="kop_kemenag_bold" value="1" {{ !empty($kopData['kemenag_bold']) ? 'checked' : '' }}>
                                <strong>Tebal (Bold)</strong>
                            </label>
                            <input type="color" name="kop_kemenag_color" value="{{ $kopData['kemenag_color'] ?? '#000000' }}" style="height: 30px; width: 40px; border: none; cursor: pointer; padding: 0;">
                        </div>
                    </div>

                    {{-- Setting Baris 2: PTKN --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                        <div style="font-weight: 700; font-size: 12.5px; color: #0f172a; margin-bottom: 8px;">
                            Baris 2: PTKN / Universitas
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="font-size: 11.5px; color: #64748b; display: block; margin-bottom: 4px;">Ukuran Font</label>
                            <input type="text" name="kop_instansi_font_size" value="{{ $kopData['instansi_font_size'] ?? '12pt' }}" placeholder="12pt"
                                style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12.5px;">
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <label style="font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="hidden" name="kop_instansi_bold" value="0">
                                <input type="checkbox" name="kop_instansi_bold" value="1" {{ !empty($kopData['instansi_bold']) ? 'checked' : '' }}>
                                <strong>Tebal (Bold)</strong>
                            </label>
                            <input type="color" name="kop_instansi_color" value="{{ $kopData['instansi_color'] ?? '#000000' }}" style="height: 30px; width: 40px; border: none; cursor: pointer; padding: 0;">
                        </div>
                    </div>

                    {{-- Setting Baris 3: Sub-Unit (Fakultas / Prodi) --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                        <div style="font-weight: 700; font-size: 12.5px; color: #0f172a; margin-bottom: 8px;">
                            Baris 3: Fakultas / Prodi / Unit
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="font-size: 11.5px; color: #64748b; display: block; margin-bottom: 4px;">Ukuran Font</label>
                            <input type="text" name="kop_subunit_font_size" value="{{ $kopData['subunit_font_size'] ?? '11pt' }}" placeholder="11pt"
                                style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12.5px;">
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <label style="font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="hidden" name="kop_subunit_bold" value="0">
                                <input type="checkbox" name="kop_subunit_bold" value="1" {{ !empty($kopData['subunit_bold']) ? 'checked' : '' }}>
                                <strong>Tebal (Bold)</strong>
                            </label>
                            <input type="color" name="kop_subunit_color" value="{{ $kopData['subunit_color'] ?? '#000000' }}" style="height: 30px; width: 40px; border: none; cursor: pointer; padding: 0;">
                        </div>
                    </div>

                    {{-- Setting Baris 4: Alamat & Kontak --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                        <div style="font-weight: 700; font-size: 12.5px; color: #0f172a; margin-bottom: 8px;">
                            Baris 4: Alamat, Telepon & Web
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="font-size: 11.5px; color: #64748b; display: block; margin-bottom: 4px;">Ukuran Font</label>
                            <input type="text" name="kop_kontak_font_size" value="{{ $kopData['kontak_font_size'] ?? '9pt' }}" placeholder="9pt"
                                style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12.5px;">
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <label style="font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="hidden" name="kop_kontak_italic" value="0">
                                <input type="checkbox" name="kop_kontak_italic" value="1" {{ !empty($kopData['kontak_italic']) ? 'checked' : '' }}>
                                <em>Miring (Italic)</em>
                            </label>
                            <input type="color" name="kop_kontak_color" value="{{ $kopData['kontak_color'] ?? '#000000' }}" style="height: 30px; width: 40px; border: none; cursor: pointer; padding: 0;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Garis Pembatas Kop --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                <div style="font-size: 14px; font-weight: 700; color: #059669; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-dash-lg"></i> Garis Pemisah Kop Surat
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Ketebalan Garis</label>
                        <select name="kop_garis_ketebalan" style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                            @foreach(['1px' => '1px (Tipis)', '2px' => '2px (Sedang)', '3px' => '3px (Standar Kemenag)', '4px' => '4px (Tebal)'] as $gKey => $gLabel)
                                <option value="{{ $gKey }}" {{ ($kopData['garis_ketebalan'] ?? '3px') === $gKey ? 'selected' : '' }}>{{ $gLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Model Garis</label>
                        <select name="kop_garis_tipe" style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                            @foreach(['double' => 'Double (Ganda - Standar)', 'solid' => 'Solid (Satu Garis)', 'dashed' => 'Dashed (Putus-putus)'] as $tKey => $tLabel)
                                <option value="{{ $tKey }}" {{ ($kopData['garis_tipe'] ?? 'double') === $tKey ? 'selected' : '' }}>{{ $tLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Warna Garis</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="color" name="kop_garis_warna" value="{{ $kopData['garis_warna'] ?? '#000000' }}" style="height: 38px; width: 60px; border: 1.5px solid #cbd5e1; border-radius: 8px; cursor: pointer; padding: 2px;">
                            <span style="font-size: 12px; color: #64748b;">Standar: Hitam (#000000)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Simpan --}}
            <div style="display: flex; justify-content: flex-end; padding-top: 10px;">
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 30px; font-size: 14px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                    <i class="bi bi-save-fill"></i> Simpan Pengaturan Kop Surat
                </button>
            </div>
        </div>
    </form>
</div>
@endif {{-- end kop-surat --}}


{{-- ═══════════════════════════════════════════════════════════
     TAB 3 — FORMAT SURAT
══════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'format-surat')
<div>
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 3px 0;">Format & Tata Letak Dokumen</h2>
        <p style="margin: 0; font-size: 12.5px; color: #64748b;">Atur ukuran kertas, margin, font, dan spasi default surat dinas</p>
    </div>

    <form action="{{ route('pengaturan.format-surat.update') }}" method="POST">
        @csrf @method('PUT')
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

            {{-- Kartu Kertas & Orientasi --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                <div style="font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #eff6ff; padding-bottom: 10px;">
                    <i class="bi bi-file-earmark"></i> Kertas & Orientasi
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Ukuran Kertas</label>
                    <select name="format_ukuran_kertas" style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        @foreach(['A4' => 'A4 (210 × 297 mm)', 'A5' => 'A5 (148 × 210 mm)', 'F4' => 'F4 / Folio (215 × 330 mm)', 'Letter' => 'Letter (215.9 × 279.4 mm)'] as $val => $label)
                            <option value="{{ $val }}" {{ ($formatSurat['format_ukuran_kertas'] ?? 'A4') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 8px;">Orientasi</label>
                    <div style="display: flex; gap: 12px;">
                        @foreach(['potrait' => ['label' => 'Potrait', 'icon' => 'bi-file-earmark'], 'landscape' => ['label' => 'Landscape', 'icon' => 'bi-file-earmark-text']] as $val => $opt)
                        <label style="flex: 1; border: 2px solid {{ ($formatSurat['format_orientasi'] ?? 'potrait') === $val ? '#2563eb' : '#e2e8f0' }}; border-radius: 10px; padding: 14px; cursor: pointer; text-align: center; transition: all 0.2s; background: {{ ($formatSurat['format_orientasi'] ?? 'potrait') === $val ? '#eff6ff' : '#fff' }};">
                            <input type="radio" name="format_orientasi" value="{{ $val }}" style="display: none;" {{ ($formatSurat['format_orientasi'] ?? 'potrait') === $val ? 'checked' : '' }}
                                onchange="this.closest('form').querySelectorAll('[name=format_orientasi]').forEach(function(r){ r.closest('label').style.borderColor = r.checked ? '#2563eb' : '#e2e8f0'; r.closest('label').style.background = r.checked ? '#eff6ff' : '#fff'; })">
                            <i class="bi {{ $opt['icon'] }}" style="font-size: 24px; color: {{ ($formatSurat['format_orientasi'] ?? 'potrait') === $val ? '#2563eb' : '#94a3b8' }};{{ $val === 'landscape' ? ' transform: rotate(90deg); display: inline-block;' : '' }}"></i>
                            <div style="font-size: 12px; font-weight: 600; color: {{ ($formatSurat['format_orientasi'] ?? 'potrait') === $val ? '#1e40af' : '#64748b' }}; margin-top: 6px;">{{ $opt['label'] }}</div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Kartu Tipografi --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                <div style="font-size: 13px; font-weight: 700; color: #7c3aed; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f5f3ff; padding-bottom: 10px;">
                    <i class="bi bi-type"></i> Tipografi
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Font Dokumen</label>
                    <select name="format_font" style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        @foreach(['Times New Roman', 'Arial', 'Calibri', 'Garamond', 'Book Antiqua'] as $font)
                            <option value="{{ $font }}" {{ ($formatSurat['format_font'] ?? 'Times New Roman') === $font ? 'selected' : '' }}>{{ $font }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Ukuran Font (pt)</label>
                    <select name="format_ukuran_font" style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        @foreach([10, 11, 12, 14] as $size)
                            <option value="{{ $size }}" {{ (int)($formatSurat['format_ukuran_font'] ?? 12) === $size ? 'selected' : '' }}>{{ $size }} pt</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">Spasi Antar Baris</label>
                    <select name="format_spasi" style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        @foreach(['1' => 'Single (1)', '1.15' => '1.15', '1.5' => 'One and a half (1.5)', '2' => 'Double (2)'] as $val => $label)
                            <option value="{{ $val }}" {{ ($formatSurat['format_spasi'] ?? '1.5') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Kartu Margin --}}
            <div style="grid-column: 1 / -1; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                <div style="font-size: 13px; font-weight: 700; color: #059669; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f0fdf4; padding-bottom: 10px;">
                    <i class="bi bi-layout-text-window"></i> Margin Dokumen (cm)
                </div>

                {{-- Diagram Margin --}}
                <div style="display: flex; justify-content: center; margin-bottom: 24px;">
                    <div style="position: relative; width: 240px; height: 300px; border: 2px solid #1e3a5f; border-radius: 4px; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                        {{-- Kertas isi --}}
                        <div style="position: absolute; inset: 30px 25px 25px 35px; border: 1.5px dashed #94a3b8; border-radius: 2px; background: #fff; display: flex; align-items: center; justify-content: center;">
                            <div style="text-align: center; color: #94a3b8; font-size: 10px;">
                                <i class="bi bi-text-paragraph" style="font-size: 20px; display: block; margin-bottom: 4px;"></i>
                                Area Isi Surat
                            </div>
                        </div>
                        {{-- Label margin --}}
                        <div style="position: absolute; top: 6px; left: 50%; transform: translateX(-50%); font-size: 10px; font-weight: 700; color: #1e40af;">
                            Atas: <span id="preview-atas">{{ $formatSurat['format_margin_atas'] ?? '3' }}</span> cm
                        </div>
                        <div style="position: absolute; bottom: 6px; left: 50%; transform: translateX(-50%); font-size: 10px; font-weight: 700; color: #1e40af;">
                            Bawah: <span id="preview-bawah">{{ $formatSurat['format_margin_bawah'] ?? '3' }}</span> cm
                        </div>
                        <div style="position: absolute; left: 4px; top: 50%; transform: translateY(-50%) rotate(-90deg); font-size: 10px; font-weight: 700; color: #1e40af; white-space: nowrap;">
                            Kiri: <span id="preview-kiri">{{ $formatSurat['format_margin_kiri'] ?? '4' }}</span> cm
                        </div>
                        <div style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%) rotate(90deg); font-size: 10px; font-weight: 700; color: #1e40af; white-space: nowrap;">
                            Kanan: <span id="preview-kanan">{{ $formatSurat['format_margin_kanan'] ?? '3' }}</span> cm
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px;">
                    @foreach(['format_margin_atas' => 'Margin Atas', 'format_margin_bawah' => 'Margin Bawah', 'format_margin_kiri' => 'Margin Kiri', 'format_margin_kanan' => 'Margin Kanan'] as $field => $label)
                    @php $suffix = str_replace('format_margin_', '', $field); @endphp
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px;">{{ $label }} (cm)</label>
                        <input type="number" name="{{ $field }}" id="input-{{ $suffix }}"
                            value="{{ $formatSurat[$field] ?? (str_contains($field, 'kiri') ? 4 : 3) }}"
                            min="1" max="10" step="0.5"
                            oninput="document.getElementById('preview-{{ $suffix }}').textContent = this.value"
                            style="width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box; transition: border-color 0.2s; text-align: center; font-weight: 700;"
                            onfocus="this.style.borderColor='#059669'" onblur="this.style.borderColor='#cbd5e1'">
                    </div>
                    @endforeach
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; margin-top: 14px; font-size: 12px; color: #166534;">
                    <i class="bi bi-lightbulb-fill"></i>
                    <strong>Standar Kemendikbud:</strong> Atas 3cm · Bawah 3cm · Kiri 4cm · Kanan 3cm (sesuai Permendikbud No.30/2022)
                </div>
            </div>

            {{-- Tombol Simpan --}}
            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 12px; padding-top: 4px;">
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 28px;">
                    <i class="bi bi-save-fill"></i> Simpan Format Surat
                </button>
            </div>
        </div>
    </form>
</div>
@endif {{-- end format-surat --}}


@push('scripts')
<script>
/* ─── Modal Format Nomor ─── */
function openCreateModal() {
    var m = document.getElementById('modal-create-format');
    if (m) { m.style.pointerEvents = 'all'; m.classList.add('modal-visible'); }
}
function closeCreateModal() {
    var m = document.getElementById('modal-create-format');
    if (m) { m.classList.remove('modal-visible'); m.style.pointerEvents = 'none'; }
}
function openEditModal(btn) {
    try {
        var data = JSON.parse(btn.getAttribute('data-format'));
        var form = document.getElementById('form-edit-format');
        form.action = "{{ url('pengaturan/format-nomor') }}/" + data.id_format;

        document.getElementById('edit_id_unit').value        = data.id_unit        ? String(data.id_unit)        : '';
        document.getElementById('edit_id_jenis_surat').value = data.id_jenis_surat ? String(data.id_jenis_surat) : '';
        document.getElementById('edit_kategori_id').value    = data.kategori_id    ? String(data.kategori_id)    : '';
        document.getElementById('edit_format_nomor').value   = data.format_nomor   || '';
        document.getElementById('edit_contoh').value         = data.contoh         || '';
        document.getElementById('edit_aktif').value          = (data.aktif === true || data.aktif == 1) ? '1' : '0';

        var m = document.getElementById('modal-edit-format');
        if (m) { m.style.pointerEvents = 'all'; m.classList.add('modal-visible'); }
    } catch (e) {
        alert('Gagal membuka form edit: ' + e.message);
        console.error(e);
    }
}
function closeEditModal() {
    var m = document.getElementById('modal-edit-format');
    if (m) { m.classList.remove('modal-visible'); m.style.pointerEvents = 'none'; }
}

// Tutup modal via backdrop
['modal-create-format', 'modal-edit-format', 'modal-create-kategori', 'modal-edit-kategori', 'modal-create-jenis', 'modal-edit-jenis', 'modal-create-unit', 'modal-edit-unit'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) {
        el.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('modal-visible');
                this.style.pointerEvents = 'none';
            }
        });
    }
});

/* ─── Modal Kategori ─── */
function openCreateKategoriModal() {
    var m = document.getElementById('modal-create-kategori');
    if (m) { m.style.pointerEvents = 'all'; m.classList.add('modal-visible'); }
}
function closeCreateKategoriModal() {
    var m = document.getElementById('modal-create-kategori');
    if (m) { m.classList.remove('modal-visible'); m.style.pointerEvents = 'none'; }
}
function openEditKategoriModal(btn) {
    try {
        var data = JSON.parse(btn.getAttribute('data-kategori'));
        var form = document.getElementById('form-edit-kategori');
        form.action = "{{ url('pengaturan/kategori') }}/" + data.id_kategori;
        document.getElementById('edit_kategori_nama').value = data.nama_kategori || '';
        document.getElementById('edit_kategori_kode').value = data.kode_kategori || '';
        document.getElementById('edit_kategori_deskripsi').value = data.deskripsi || '';
        var m = document.getElementById('modal-edit-kategori');
        if (m) { m.style.pointerEvents = 'all'; m.classList.add('modal-visible'); }
    } catch (e) {
        alert('Gagal membuka edit kategori: ' + e.message);
    }
}
function closeEditKategoriModal() {
    var m = document.getElementById('modal-edit-kategori');
    if (m) { m.classList.remove('modal-visible'); m.style.pointerEvents = 'none'; }
}

/* ─── Modal Jenis ─── */
function openCreateJenisModal() {
    var m = document.getElementById('modal-create-jenis');
    if (m) { m.style.pointerEvents = 'all'; m.classList.add('modal-visible'); }
}
function closeCreateJenisModal() {
    var m = document.getElementById('modal-create-jenis');
    if (m) { m.classList.remove('modal-visible'); m.style.pointerEvents = 'none'; }
}
function openEditJenisModal(btn) {
    try {
        var data = JSON.parse(btn.getAttribute('data-jenis'));
        var form = document.getElementById('form-edit-jenis');
        form.action = "{{ url('pengaturan/jenis') }}/" + data.id_jenis_surat;
        document.getElementById('edit_jenis_nama').value = data.nama_jenis || '';
        document.getElementById('edit_jenis_kode').value = data.kode_jenis || '';
        document.getElementById('edit_jenis_kategori_id').value = data.kategori_id ? String(data.kategori_id) : '';
        document.getElementById('edit_jenis_keterangan').value = data.keterangan || '';
        var m = document.getElementById('modal-edit-jenis');
        if (m) { m.style.pointerEvents = 'all'; m.classList.add('modal-visible'); }
    } catch (e) {
        alert('Gagal membuka edit jenis: ' + e.message);
    }
}
function closeEditJenisModal() {
    var m = document.getElementById('modal-edit-jenis');
    if (m) { m.classList.remove('modal-visible'); m.style.pointerEvents = 'none'; }
}

/* ─── Modal Unit ─── */
function openCreateUnitModal() {
    var m = document.getElementById('modal-create-unit');
    if (m) { m.style.pointerEvents = 'all'; m.classList.add('modal-visible'); }
}
function closeCreateUnitModal() {
    var m = document.getElementById('modal-create-unit');
    if (m) { m.classList.remove('modal-visible'); m.style.pointerEvents = 'none'; }
}
function openEditUnitModal(btn) {
    try {
        var data = JSON.parse(btn.getAttribute('data-unit'));
        var form = document.getElementById('form-edit-unit');
        form.action = "{{ url('pengaturan/unit') }}/" + data.id_unit;
        document.getElementById('edit_unit_nama').value = data.nama_unit || '';
        document.getElementById('edit_unit_kode').value = data.kode_unit || '';
        document.getElementById('edit_unit_level').value = data.level || 'prodi';
        document.getElementById('edit_unit_status').value = data.status || 'aktif';
        document.getElementById('edit_unit_id_parent').value = data.id_parent ? String(data.id_parent) : '';
        var m = document.getElementById('modal-edit-unit');
        if (m) { m.style.pointerEvents = 'all'; m.classList.add('modal-visible'); }
    } catch (e) {
        alert('Gagal membuka edit unit: ' + e.message);
    }
}
function closeEditUnitModal() {
    var m = document.getElementById('modal-edit-unit');
    if (m) { m.classList.remove('modal-visible'); m.style.pointerEvents = 'none'; }
}

/* ─── Modal Sifat Surat ─── */
function openCreateSifatModal() {
    var m = document.getElementById('modalCreateSifat');
    if (m) m.style.display = 'flex';
}
function closeCreateSifatModal() {
    var m = document.getElementById('modalCreateSifat');
    if (m) m.style.display = 'none';
}
function openEditSifatModal(data) {
    var m = document.getElementById('modalEditSifat');
    var form = document.getElementById('formEditSifat');
    if (m && form) {
        form.action = "{{ url('pengaturan/sifat') }}/" + data.id_sifat;
        document.getElementById('edit_kode_sifat').value = data.kode_sifat || '';
        document.getElementById('edit_nama_sifat').value = data.nama_sifat || '';
        document.getElementById('edit_warna_tinta').value = data.warna_tinta || 'hitam';
        document.getElementById('edit_keterangan_sifat').value = data.keterangan || '';
        m.style.display = 'flex';
    }
}
function closeEditSifatModal() {
    var m = document.getElementById('modalEditSifat');
    if (m) m.style.display = 'none';
}
</script>
@endpush
@endsection
