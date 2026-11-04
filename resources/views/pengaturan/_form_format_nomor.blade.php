{{-- Partial: form fields untuk modal Tambah & Edit Format Nomor --}}
{{-- $prefix: 'create' | 'edit'  |  $data: null (create) atau array (edit) --}}

<div class="form-group" style="margin-bottom: 14px;">
    <label for="{{ $prefix }}_id_unit" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Unit Kampus *</label>
    @if(Auth::user()->isAdminRektorat())
        <select name="id_unit" id="{{ $prefix }}_id_unit" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
            <option value="">-- Pilih Unit --</option>
            @foreach($units as $u)
                <option value="{{ $u->id_unit }}">{{ $u->nama_unit }} ({{ $u->kode_unit }})</option>
            @endforeach
        </select>
    @else
        <input type="hidden" name="id_unit" id="{{ $prefix }}_id_unit" value="{{ Auth::user()->id_unit }}">
        <div style="padding: 9px 12px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 700; color: #1e3a8a;">
            {{ Auth::user()->unit?->nama_unit }} ({{ Auth::user()->unit?->kode_unit }})
        </div>
        <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">Format nomor dikunci untuk unit Anda.</small>
    @endif
</div>

<div class="form-group" style="margin-bottom: 14px;">
    <label for="{{ $prefix }}_id_jenis_surat" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Jenis Surat *</label>
    <select name="id_jenis_surat" id="{{ $prefix }}_id_jenis_surat" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
        <option value="">-- Pilih Jenis Surat --</option>
        @foreach($jenisList as $j)
            <option value="{{ $j->id_jenis_surat }}">{{ $j->nama_jenis }} ({{ $j->kode_jenis }})</option>
        @endforeach
    </select>
</div>

<div class="form-group" style="margin-bottom: 14px;">
    <label for="{{ $prefix }}_kategori_id" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kategori (Opsional)</label>
    <select name="kategori_id" id="{{ $prefix }}_kategori_id" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
        <option value="">-- Semua Kategori --</option>
        @foreach($kategoriList as $k)
            <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
        @endforeach
    </select>
</div>

<div class="form-group" style="margin-bottom: 14px;">
    <label for="{{ $prefix }}_format_nomor" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Template Format Nomor *</label>
    <input type="text" name="format_nomor" id="{{ $prefix }}_format_nomor" required
        placeholder="{sifat_kode}-{nomor}/{kode_unit}/{kode_jabatan}/{kode_arsip}/{bulan}/{tahun}"
        @if($prefix === 'create') value="{sifat_kode}-{nomor}/{kode_unit}/{kode_jabatan}/{kode_arsip}/{bulan}/{tahun}" @endif
        style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
    <small style="color: #64748b; font-size: 11.5px; margin-top: 4px; display: block; line-height: 1.4;">
        Token tersedia: <code>{sifat_kode}</code> (B/T/R/SR), <code>{nomor}</code> (0001/001), <code>{kode_unit}</code>, <code>{kode_jabatan}</code> (R/F.I/J.I.16), <code>{kode_arsip}</code> (PP.04/OT.01), <code>{kode_jenis}</code>, <code>{bulan}</code> (10), <code>{bulan_romawi}</code> (X), <code>{tahun}</code> ({{ date('Y') }})
    </small>
</div>

<div class="form-group" style="margin-bottom: 14px;">
    <label for="{{ $prefix }}_contoh" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Contoh Hasil Output</label>
    <input type="text" name="contoh" id="{{ $prefix }}_contoh"
        placeholder="B-0001/Un.30/R.III/PP.04/{{ date('m/Y') }}"
        style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
</div>

@if($prefix === 'edit')
<div class="form-group" style="margin-bottom: 14px;">
    <label for="{{ $prefix }}_aktif" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Status Aktif</label>
    <select name="aktif" id="{{ $prefix }}_aktif" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
        <option value="1">Aktif</option>
        <option value="0">Nonaktif</option>
    </select>
</div>
@endif
