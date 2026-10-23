@extends('layouts.app')
@section('title', 'Buat Draft Surat Keluar')
@section('content')
<div style="max-width: 960px; margin: 0 auto;">
<div style="margin-bottom: 20px;">
    <a href="{{ route('surat-keluar.index') }}" style="color: #64748b; text-decoration: none; font-size: 13px;">← Kembali ke Surat Keluar</a>
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 6px;">
        <div>
            <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0;">Buat Draft Surat Keluar</h1>
            <p style="font-size: 13px; color: #64748b; margin: 4px 0 0 0;">
                Unit Pembuat: <strong style="color: #1e3a8a;">{{ $user->unit ? $user->unit->nama_unit : 'Rektorat / Universitas' }}</strong> (Otomatis tercatat sesuai akun login Anda)
            </p>
        </div>
        <span style="font-size: 12px; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 9999px; font-weight: 600;">
            <i class="bi bi-person-check-fill"></i> {{ $user->nama ?? $user->name }}
        </span>
    </div>
</div>

<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; margin-bottom: 28px;">
<form action="{{ route('surat-keluar.store') }}" method="POST" enctype="multipart/form-data">
@csrf
@if($errors->any())
<div style="background: #fef2f2; border: 1px solid #fecaca; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #991b1b;">
    <ul style="margin: 0; padding-left: 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- Input id_unit otomatis dari akun user --}}
@if($user->isAdminRektorat())
<div style="margin-bottom: 18px; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Pilih Unit Pembuat (Khusus Hak Admin Rektorat)</label>
    <select name="id_unit" id="form_id_unit" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
        @foreach($unitList as $u)
            <option value="{{ $u->id_unit }}" data-nama="{{ $u->nama_unit }}" data-level="{{ $u->level }}" {{ old('id_unit', $user->id_unit) == $u->id_unit ? 'selected' : '' }}>
                {{ $u->nama_unit }} (Kode: {{ $u->kode_unit }})
            </option>
        @endforeach
    </select>
</div>
@else
<input type="hidden" name="id_unit" id="form_id_unit" value="{{ $user->id_unit ?: 1 }}">
@endif

<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px;">
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Jenis Surat *</label>
        <select name="jenis_surat_id" id="form_jenis_surat" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
            <option value="">— Pilih Jenis —</option>
            @foreach($jenisList as $j)
                <option value="{{ $j->id_jenis_surat }}" data-nama="{{ $j->nama_jenis }}" {{ old('jenis_surat_id') == $j->id_jenis_surat ? 'selected' : '' }}>{{ $j->nama_jenis }} ({{ $j->kode_jenis }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Kategori</label>
        <select name="kategori_id" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
            <option value="">— Pilih Kategori —</option>
            @foreach($kategoriList as $k)
                <option value="{{ $k->id_kategori }}" {{ old('kategori_id') == $k->id_kategori ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Sifat Surat *</label>
        <select name="sifat" id="form_sifat" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
            <option value="biasa" {{ old('sifat') === 'biasa' ? 'selected' : '' }}>Biasa</option>
            <option value="penting" {{ old('sifat') === 'penting' ? 'selected' : '' }}>Penting</option>
            <option value="rahasia" {{ old('sifat') === 'rahasia' ? 'selected' : '' }}>Rahasia</option>
        </select>
    </div>
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tanggal Surat *</label>
        <input type="date" name="tanggal_draft" id="form_tanggal_draft" value="{{ old('tanggal_draft', date('Y-m-d')) }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
    </div>
    <div style="grid-column: span 2;">
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Lampiran</label>
        <input type="text" name="lampiran" id="form_lampiran" value="{{ old('lampiran', '-') }}" placeholder="cth: 1 berkas / -" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
    </div>
</div>

<!-- Kotak Tujuan Surat dengan Fitur Rekomendasi Unit Pencarian ala Google -->
<div style="margin-top: 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; position: relative;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <label style="font-size: 13px; font-weight: 700; color: #1e293b;">
            <i class="bi bi-geo-alt-fill" style="color: #2563eb;"></i> Kepada / Tujuan Surat *
        </label>
        <span style="font-size: 11px; background: #dbeafe; color: #1d4ed8; padding: 2px 8px; border-radius: 9999px; font-weight: 600;">
            <i class="bi bi-search"></i> Rekomendasi Unit Cerdas
        </span>
    </div>
    <p style="font-size: 12px; color: #64748b; margin: 0 0 10px 0;">
        Ketik nama instansi atau tujuan surat secara bebas. Jika Anda mengetik nama unit internal kampus, sistem otomatis menampilkan rekomendasi unit agar surat <strong>langsung terkirim otomatis</strong> saat disetujui.
    </p>

    <!-- Hidden input ID unit tujuan jika cocok/dipilih -->
    <input type="hidden" name="tujuan_unit_id" id="tujuan_unit_id" value="{{ old('tujuan_unit_id') }}">

    <!-- Input text utama dengan autocomplete dropdown -->
    <div style="position: relative;">
        <input type="text" id="input_tujuan" name="tujuan" value="{{ old('tujuan') }}" 
               placeholder="Ketik tujuan surat... (cth: Yth. Dekan Fakultas Tarbiyah / Kepala Dinas...)" 
               autocomplete="off" required 
               style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
        
        <!-- Dropdown Rekomendasi Unit (Muncul saat mengetik) -->
        <div id="unit_suggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-top: none; border-radius: 0 0 8px 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); max-height: 220px; overflow-y: auto; z-index: 50;">
        </div>
    </div>

    <!-- Status badge info unit internal terpilih -->
    <div id="selected_unit_badge" style="display: none; margin-top: 10px; font-size: 12px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 6px 12px; border-radius: 6px; align-items: center; justify-content: space-between;">
        <span><i class="bi bi-check-circle-fill"></i> Terhubung ke Unit Internal: <strong id="selected_unit_name"></strong> (Surat akan terkirim otomatis ke Surat Masuk unit ini)</span>
        <button type="button" id="btn_clear_unit" style="background: none; border: none; color: #047857; cursor: pointer; font-size: 11px; text-decoration: underline;">Lepas Relasi</button>
    </div>
</div>

<div style="margin-top: 18px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Perihal *</label>
    <input type="text" name="perihal" id="form_perihal" value="{{ old('perihal') }}" placeholder="Perihal surat dinas..." required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
</div>

<div style="margin-top: 18px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Isi Surat *</label>
    <textarea name="isi_surat" id="form_isi_surat" rows="7" required placeholder="Dengan hormat,&#10;&#10;Sehubungan dengan...&#10;&#10;Demikian surat ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih." style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; resize: vertical; font-family: inherit;">{{ old('isi_surat') }}</textarea>
</div>

<div style="margin-top: 18px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tembusan (opsional)</label>
    <textarea name="tembusan" id="form_tembusan" rows="2" placeholder="1. Rektor UINSSC&#10;2. Arsip" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; resize: vertical; font-family: inherit;">{{ old('tembusan') }}</textarea>
</div>

<div style="margin-top: 18px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Upload Lampiran / Berkas Pendukung (opsional)</label>
    <input type="file" name="file_draft" accept=".pdf,.doc,.docx" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
</div>

<!-- Live Preview Lembar Fisik Kertas A4 Word Real-time -->
<div style="margin-top: 30px; border-top: 2px dashed #cbd5e1; padding-top: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
        <div style="font-size: 15px; font-weight: 700; color: #0f172a;">
            <i class="bi bi-file-earmark-text-fill" style="color: #2563eb;"></i> Pratinjau Kertas A4 Standar Word (Live Preview)
        </div>
        <span style="font-size: 12px; color: #64748b;">
            <i class="bi bi-eye-fill"></i> Teks menyesuaikan kertas fisik A4 (210 x 297 mm, Margin 2.54 cm)
        </span>
    </div>

    <div style="background: #e2e8f0; border-radius: 12px; padding: 24px 16px; display: flex; justify-content: center; overflow-x: auto;">
        <!-- Kertas Fisik A4 Word -->
        <div style="width: 210mm; min-height: 297mm; padding: 25.4mm; background: #ffffff; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1); border-radius: 2px; font-family: 'Times New Roman', Times, serif; color: #000000; font-size: 12pt; line-height: 1.5; box-sizing: border-box; flex-shrink: 0;">
            <!-- Kop Surat Resmi -->
            <div style="text-align: center; border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 20px;">
                <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                    KEMENTERIAN AGAMA REPUBLIK INDONESIA
                </div>
                <div style="font-size: 15pt; font-weight: bold; text-transform: uppercase; color: #1e3a8a; margin: 2px 0;">
                    UIN SUNAN SUDJATI SERANG CILEGON
                </div>
                <div id="pv_unit" style="font-size: 12pt; font-weight: bold; text-transform: uppercase; margin-top: 2px;">
                    {{ $user->unit ? $user->unit->nama_unit : 'UNIVERSITAS' }}
                </div>
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
                        <span style="color: #dc2626; font-style: italic;">[Draft - Nomor resmi otomatis terbit setelah ACC]</span>
                    </td>
                    <td id="pv_tanggal" style="text-align: right; width: 40%; padding: 2px 0; vertical-align: top;">
                        Serang, {{ date('d/m/Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; vertical-align: top;">Sifat</td>
                    <td style="padding: 2px 0; vertical-align: top;">:</td>
                    <td id="pv_sifat" style="padding: 2px 0; vertical-align: top;">Biasa</td>
                    <td></td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; vertical-align: top;">Lampiran</td>
                    <td style="padding: 2px 0; vertical-align: top;">:</td>
                    <td id="pv_lampiran" style="padding: 2px 0; vertical-align: top;">-</td>
                    <td></td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; vertical-align: top;">Hal</td>
                    <td style="padding: 2px 0; vertical-align: top;">:</td>
                    <td style="padding: 2px 0; vertical-align: top;"><strong id="pv_perihal">Perihal Surat</strong></td>
                    <td></td>
                </tr>
            </table>

            <!-- Penerima Tujuan -->
            <div style="margin-bottom: 24px; font-size: 12pt;">
                <div>Yth.</div>
                <div id="pv_tujuan" style="font-weight: bold;">[Tujuan Surat]</div>
                <div>di</div>
                <div style="padding-left: 20px;">Tempat</div>
            </div>

            <!-- Isi Naskah Surat Sesuai Margin A4 Word -->
            <div id="pv_isi" style="font-size: 12pt; line-height: 1.6; text-align: justify; margin-bottom: 30px; white-space: pre-wrap;">[Tuliskan isi surat pada form di atas, teks akan langsung tampil di lembar kertas A4 ini secara otomatis...]</div>

            <!-- Tanda Tangan Resmi Pejabat -->
            <div style="display: flex; justify-content: flex-end; margin-top: 40px;">
                <div style="text-align: center; min-width: 240px; font-size: 12pt;">
                    <div id="pv_ttd_unit">{{ $user->unit ? $user->unit->nama_unit : 'UINSSC' }},</div>
                    <div id="pv_ttd_jabatan" style="font-weight: bold; margin-bottom: 65px;">
                        @if($user->unit && $user->unit->level === 'rektorat')
                            Rektor
                        @elseif($user->unit && $user->unit->level === 'fakultas')
                            Dekan
                        @else
                            Ketua Program Studi
                        @endif
                    </div>
                    <div style="font-weight: bold; text-decoration: underline;">
                        {{ $user->nama ?? $user->name }}
                    </div>
                </div>
            </div>

            <!-- Tembusan -->
            <div id="pv_tembusan_wrap" style="display: none; margin-top: 30px; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-size: 10pt; color: #334155;">
                <div style="font-weight: bold; margin-bottom: 4px;">Tembusan:</div>
                <div id="pv_tembusan" style="white-space: pre-wrap;"></div>
            </div>
        </div>
    </div>
</div>

<div style="margin-top: 28px; display: flex; gap: 12px; flex-wrap: wrap;">
    <button type="submit" name="submit_action" value="ajukan" style="padding: 12px 26px; background: #d97706; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
        <i class="bi bi-send-fill"></i> Simpan &amp; Langsung Ajukan ke Pimpinan (ACC)
    </button>
    <button type="submit" name="submit_action" value="draft" style="padding: 12px 22px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
        <i class="bi bi-save-fill"></i> Simpan sebagai Draft
    </button>
    <a href="{{ route('surat-keluar.index') }}" style="padding: 12px 22px; background: #f1f5f9; color: #475569; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none;">Batal</a>
</div>
</form>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Data Unit untuk Rekomendasi Cerdas ala Google
    const unitDatabase = [
        @foreach($unitList as $u)
        {
            id: {{ $u->id_unit }},
            nama: @json($u->nama_unit),
            kode: @json($u->kode_unit),
            level: @json(ucfirst($u->level))
        },
        @endforeach
    ];

    const inputTujuan = document.getElementById('input_tujuan');
    const tujuanUnitId = document.getElementById('tujuan_unit_id');
    const suggestionsBox = document.getElementById('unit_suggestions');
    const selectedBadge = document.getElementById('selected_unit_badge');
    const selectedNameSpan = document.getElementById('selected_unit_name');
    const btnClearUnit = document.getElementById('btn_clear_unit');

    // Input elements for live preview
    const formPerihal = document.getElementById('form_perihal');
    const formIsi = document.getElementById('form_isi_surat');
    const formSifat = document.getElementById('form_sifat');
    const formLampiran = document.getElementById('form_lampiran');
    const formTembusan = document.getElementById('form_tembusan');
    const formTanggal = document.getElementById('form_tanggal_draft');
    const formIdUnit = document.getElementById('form_id_unit');

    // Live preview elements
    const pvPerihal = document.getElementById('pv_perihal');
    const pvIsi = document.getElementById('pv_isi');
    const pvTujuan = document.getElementById('pv_tujuan');
    const pvSifat = document.getElementById('pv_sifat');
    const pvLampiran = document.getElementById('pv_lampiran');
    const pvTembusan = document.getElementById('pv_tembusan');
    const pvTembusanWrap = document.getElementById('pv_tembusan_wrap');
    const pvTanggal = document.getElementById('pv_tanggal');
    const pvUnit = document.getElementById('pv_unit');
    const pvTtdUnit = document.getElementById('pv_ttd_unit');
    const pvTtdJabatan = document.getElementById('pv_ttd_jabatan');

    // Live Update Function
    function updateLivePreview() {
        if (pvPerihal) pvPerihal.textContent = formPerihal.value.trim() || 'Perihal Surat';
        if (pvIsi) pvIsi.textContent = formIsi.value.trim() || '[Tuliskan isi surat pada form di atas, teks akan langsung tampil di lembar kertas A4 ini...]';
        if (pvTujuan) pvTujuan.textContent = inputTujuan.value.trim() || '[Tujuan Surat]';
        if (pvSifat) pvSifat.textContent = formSifat.value.charAt(0).toUpperCase() + formSifat.value.slice(1);
        if (pvLampiran) pvLampiran.textContent = formLampiran.value.trim() || '-';

        if (formTembusan && formTembusan.value.trim()) {
            pvTembusanWrap.style.display = 'block';
            pvTembusan.textContent = formTembusan.value.trim();
        } else {
            pvTembusanWrap.style.display = 'none';
        }

        if (formTanggal && formTanggal.value) {
            const parts = formTanggal.value.split('-');
            if (parts.length === 3) {
                const bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                pvTanggal.textContent = 'Serang, ' + parseInt(parts[2]) + ' ' + (bulan[parseInt(parts[1])] || '') + ' ' + parts[0];
            }
        }

        if (formIdUnit && formIdUnit.tagName === 'SELECT') {
            const opt = formIdUnit.options[formIdUnit.selectedIndex];
            if (opt) {
                const uName = opt.getAttribute('data-nama');
                const uLevel = opt.getAttribute('data-level');
                pvUnit.textContent = uName.toUpperCase();
                pvTtdUnit.textContent = uName + ',';
                pvTtdJabatan.textContent = uLevel === 'rektorat' ? 'Rektor' : (uLevel === 'fakultas' ? 'Dekan' : 'Ketua Program Studi');
            }
        }
    }

    [formPerihal, formIsi, formSifat, formLampiran, formTembusan, formTanggal, inputTujuan].forEach(el => {
        if (el) {
            el.addEventListener('input', updateLivePreview);
            el.addEventListener('change', updateLivePreview);
        }
    });
    if (formIdUnit) {
        formIdUnit.addEventListener('change', updateLivePreview);
    }

    // Google-like Autocomplete Rekomendasi Unit
    inputTujuan.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        updateLivePreview();

        if (query.length < 2) {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
            return;
        }

        // Cari kecocokan di database unit
        const matches = unitDatabase.filter(u => 
            u.nama.toLowerCase().includes(query) || 
            u.kode.toLowerCase().includes(query) ||
            ('yth pimpinan ' + u.nama.toLowerCase()).includes(query)
        );

        if (matches.length === 0) {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
            return;
        }

        let html = '';
        matches.forEach(u => {
            html += `
                <div class="unit-suggestion-item" data-id="${u.id}" data-nama="${u.nama}" style="padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; transition: background 0.15s ease;">
                    <div>
                        <div style="font-weight: 600; color: #0f172a; font-size: 13.5px;">Yth. Pimpinan ${u.nama}</div>
                        <div style="font-size: 11px; color: #64748b;">Kode Unit: ${u.kode} · Level: ${u.level}</div>
                    </div>
                    <span style="font-size: 10px; background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Internal</span>
                </div>
            `;
        });

        suggestionsBox.innerHTML = html;
        suggestionsBox.style.display = 'block';

        // Event listener saat item rekomendasi diklik
        suggestionsBox.querySelectorAll('.unit-suggestion-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.background = '#f8fafc';
            });
            item.addEventListener('mouseleave', function() {
                this.style.background = '#ffffff';
            });
            item.addEventListener('click', function() {
                const uId = this.getAttribute('data-id');
                const uNama = this.getAttribute('data-nama');

                inputTujuan.value = 'Yth. Pimpinan ' + uNama;
                tujuanUnitId.value = uId;

                selectedNameSpan.textContent = uNama;
                selectedBadge.style.display = 'flex';
                suggestionsBox.style.display = 'none';
                suggestionsBox.innerHTML = '';

                updateLivePreview();
            });
        });
    });

    // Lepas relasi internal jika ingin ketik tujuan manual bebas murni eksternal
    btnClearUnit.addEventListener('click', function() {
        tujuanUnitId.value = '';
        selectedBadge.style.display = 'none';
    });

    // Sembunyikan dropdown jika klik di luar
    document.addEventListener('click', function(e) {
        if (!inputTujuan.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });

    // Initial run
    updateLivePreview();
});
</script>
@endsection
