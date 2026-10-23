@extends('layouts.app')
@section('title', 'Buat Disposisi')
@section('content')
<div style="max-width: 700px;">
<div style="margin-bottom: 20px;">
    <a href="{{ route('surat-masuk.show', $surat->id_surat_masuk) }}" style="color: #64748b; text-decoration: none; font-size: 13px;">← Kembali ke Detail Surat</a>
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 8px 0 0 0;">Buat Disposisi Elektronik</h1>
</div>

<div style="padding: 14px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
    <strong>Surat yang didisposisikan:</strong> {{ $surat->perihal }} ({{ $surat->no_surat_asal }}) — dari {{ $surat->asal_surat }}
</div>

@if(!empty($catatanAlur))
<div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-left: 4px solid #7c3aed; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;">
    <div style="display: flex; align-items: flex-start; gap: 10px;">
        <i class="bi bi-diagram-3-fill" style="font-size: 18px; color: #7c3aed; margin-top: 1px;"></i>
        <div>
            <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Prinsip Hierarki Disposisi:</div>
            <div style="font-size: 12.5px; color: #475569; margin-top: 3px; line-height: 1.5;">
                {{ $catatanAlur }}
            </div>
        </div>
    </div>
</div>
@endif

<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px;">
<form action="{{ route('disposisi.store') }}" method="POST">
@csrf
<input type="hidden" name="id_surat_masuk" value="{{ $surat->id_surat_masuk }}">
@if($errors->any())
<div style="background: #fef2f2; border: 1px solid #fecaca; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; color: #991b1b;">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

<div style="margin-bottom: 18px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Instruksi Disposisi *</label>
    <textarea name="instruksi" rows="4" required placeholder="Mohon ditindaklanjuti sesuai prosedur atau dikoordinasikan..." style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; resize: vertical;">{{ old('instruksi') }}</textarea>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Sifat Disposisi *</label>
        <select name="sifat" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
            <option value="biasa" {{ old('sifat') === 'biasa' ? 'selected' : '' }}>Biasa</option>
            <option value="segera" {{ old('sifat') === 'segera' ? 'selected' : '' }}>Segera</option>
            <option value="sangat_segera" {{ old('sifat') === 'sangat_segera' ? 'selected' : '' }}>Sangat Segera</option>
        </select>
    </div>
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tenggat Waktu</label>
        <input type="date" name="tenggat_waktu" value="{{ old('tenggat_waktu') }}" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
    </div>
</div>

<div style="margin-bottom: 18px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px;">Pilih Jenis Penerima Disposisi *</label>
    <div style="display: flex; gap: 16px; margin-bottom: 12px;">
        <label style="display: flex; align-items: center; gap: 6px; font-size: 14px; cursor: pointer;">
            <input type="radio" name="tujuan_tipe" value="individu" {{ old('tujuan_tipe', 'individu') === 'individu' ? 'checked' : '' }} onchange="toggleTujuan(this.value)"> Individu / Pegawai
        </label>
        <label style="display: flex; align-items: center; gap: 6px; font-size: 14px; cursor: pointer;">
            <input type="radio" name="tujuan_tipe" value="unit" {{ old('tujuan_tipe') === 'unit' ? 'checked' : '' }} onchange="toggleTujuan(this.value)"> Unit / Lembaga
        </label>
    </div>

    <div id="field-individu">
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Pilih Pengguna / Pegawai *</label>
        <select name="ke_user_id" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
            <option value="">— Pilih Pengguna —</option>
            @foreach($userList as $u)
                <option value="{{ $u->id_user }}" {{ old('ke_user_id') == $u->id_user ? 'selected' : '' }}>
                    {{ $u->nama ?: $u->name }} — {{ ucwords(str_replace('_', ' ', $u->role->nama_role ?? 'Pegawai')) }} ({{ $u->unit?->nama_unit ?? '-' }})
                </option>
            @endforeach
        </select>
        <small style="color: #64748b; font-size: 11.5px; margin-top: 4px; display: block;">
            Daftar pengguna disesuaikan dengan lingkup unit kerja dan hierarki atasan Anda.
        </small>
    </div>

    <div id="field-unit" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <label style="font-size: 13px; font-weight: 600; color: #334155; margin: 0;">
                Pilih Unit Tujuan Disposisi * <span style="font-size: 11.5px; font-weight: 400; color: #64748b;">(Bisa pilih lebih dari satu unit)</span>
            </label>
            @if($unitList->count() > 1)
                <button type="button" id="btn-toggle-all-units" onclick="toggleAllUnits()" style="background: none; border: none; color: #7c3aed; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">
                    Pilih Semua Unit
                </button>
            @endif
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; max-height: 240px; overflow-y: auto; padding: 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #f8fafc;">
            @php
                $oldUnits = old('ke_unit_ids', old('ke_unit_id') ? [old('ke_unit_id')] : []);
            @endphp
            @forelse($unitList as $u)
                <label style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; transition: all 0.15s ease;" onmouseover="this.style.borderColor='#a78bfa'; this.style.background='#faf5ff';" onmouseout="if(!this.querySelector('input').checked){ this.style.borderColor='#e2e8f0'; this.style.background='#ffffff'; }">
                    <input type="checkbox" name="ke_unit_ids[]" value="{{ $u->id_unit }}" class="unit-checkbox" {{ in_array($u->id_unit, (array)$oldUnits) ? 'checked' : '' }} style="width: 16px; height: 16px; cursor: pointer; accent-color: #7c3aed;">
                    <div style="flex: 1;">
                        <span style="font-size: 13.5px; font-weight: 600; color: #0f172a;">{{ $u->nama_unit }}</span>
                        <span style="font-size: 11px; color: #64748b; margin-left: 6px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ ucfirst($u->level) }}</span>
                    </div>
                </label>
            @empty
                <div style="text-align: center; color: #94a3b8; font-size: 12.5px; padding: 16px 0;">
                    Tidak ada unit bawahan yang dapat dipilih.
                </div>
            @endforelse
        </div>

        @if($levelPengirim === 'prodi')
            <div style="margin-top: 8px; padding: 8px 12px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 6px; font-size: 12px; color: #92400e;">
                <i class="bi bi-info-circle-fill"></i> Sesuai alur struktural, Program Studi menyalurkan disposisi ke <strong>Fakultas</strong> terlebih dahulu sebelum diteruskan ke prodi lain.
            </div>
        @else
            <small style="color: #64748b; font-size: 11.5px; margin-top: 6px; display: block;">
                <i class="bi bi-info-circle"></i> Setiap unit yang dicentang akan otomatis menerima salinan surat masuk baru dan notifikasi untuk ditindaklanjuti.
            </small>
        @endif
    </div>
</div>

<div style="margin-bottom: 24px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Catatan Tambahan</label>
    <textarea name="catatan" rows="2" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; resize: vertical;">{{ old('catatan') }}</textarea>
</div>

<div style="display: flex; gap: 12px;">
    <button type="submit" style="padding: 11px 24px; background: #7c3aed; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;"><i class="bi bi-arrow-left-right"></i> Kirim Disposisi</button>
    <a href="{{ route('surat-masuk.show', $surat->id_surat_masuk) }}" style="padding: 11px 20px; background: #f1f5f9; color: #475569; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none;">Batal</a>
</div>
</form>
</div>
</div>

@push('scripts')
<script>
function toggleTujuan(tipe) {
    document.getElementById('field-individu').style.display = tipe === 'individu' ? 'block' : 'none';
    document.getElementById('field-unit').style.display = tipe === 'unit' ? 'block' : 'none';
}
toggleTujuan('{{ old('tujuan_tipe', 'individu') }}');

function toggleAllUnits() {
    var cbs = document.querySelectorAll('.unit-checkbox');
    var btn = document.getElementById('btn-toggle-all-units');
    if (!cbs.length) return;

    var allChecked = Array.from(cbs).every(function(cb) { return cb.checked; });
    var newState = !allChecked;

    cbs.forEach(function(cb) {
        cb.checked = newState;
        var parentLabel = cb.closest('label');
        if (parentLabel) {
            parentLabel.style.borderColor = newState ? '#7c3aed' : '#e2e8f0';
            parentLabel.style.background = newState ? '#f5f3ff' : '#ffffff';
        }
    });

    if (btn) {
        btn.textContent = newState ? 'Hapus Semua Pilihan' : 'Pilih Semua Unit';
    }
}
</script>
@endpush
@endsection
