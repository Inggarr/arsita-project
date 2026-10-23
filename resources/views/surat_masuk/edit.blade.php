@extends('layouts.app')
@section('title', 'Edit Surat Masuk')
@section('content')
<div style="max-width: 800px;">
<div style="margin-bottom: 20px;">
    <a href="{{ route('surat-masuk.show', $surat->id_surat_masuk) }}" style="color: #64748b; text-decoration: none; font-size: 13px;">← Kembali ke Detail Surat</a>
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 8px 0 0 0;">Edit Surat Masuk</h1>
</div>
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px;">
<form action="{{ route('surat-masuk.update', $surat->id_surat_masuk) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
@if($errors->any())
<div style="background: #fef2f2; border: 1px solid #fecaca; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #991b1b;">
    <ul style="margin: 0; padding-left: 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">No. Surat Asal *</label>
        <input type="text" name="no_surat_asal" value="{{ old('no_surat_asal', $surat->no_surat_asal) }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
    </div>
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Pengirim / Asal Instansi *</label>
        <input type="text" name="asal_surat" value="{{ old('asal_surat', $surat->asal_surat) }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
    </div>
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tanggal Surat *</label>
        <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d')) }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
    </div>
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tanggal Diterima *</label>
        <input type="date" name="tanggal_terima" value="{{ old('tanggal_terima', $surat->tanggal_terima?->format('Y-m-d')) }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
    </div>
</div>
<div style="margin-top: 18px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Perihal *</label>
    <input type="text" name="perihal" value="{{ old('perihal', $surat->perihal) }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
</div>
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; margin-top: 18px;">
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Kategori</label>
        <select name="kategori_id" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
            <option value="">— Pilih —</option>
            @foreach($kategoriList as $k)
                <option value="{{ $k->id_kategori }}" {{ old('kategori_id', $surat->kategori_id) == $k->id_kategori ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Jenis Surat</label>
        <select name="jenis_surat_id" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
            <option value="">— Pilih —</option>
            @foreach($jenisList as $j)
                <option value="{{ $j->id_jenis_surat }}" {{ old('jenis_surat_id', $surat->jenis_surat_id) == $j->id_jenis_surat ? 'selected' : '' }}>{{ $j->nama_jenis }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Unit Tujuan *</label>
        <select name="id_unit" required style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
            @foreach($unitList as $u)
                <option value="{{ $u->id_unit }}" {{ old('id_unit', $surat->id_unit) == $u->id_unit ? 'selected' : '' }}>{{ $u->nama_unit }}</option>
            @endforeach
        </select>
    </div>
</div>
@if($surat->file_surat)
<div style="margin-top: 16px; padding: 12px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0; font-size: 13px;">
    File saat ini: <a href="{{ asset('uploads/surat_masuk/' . $surat->file_surat) }}" target="_blank" style="color: #059669; font-weight: 600;">{{ $surat->file_surat }}</a>
</div>
@endif
<div style="margin-top: 18px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Ganti File Surat (opsional)</label>
    <input type="file" name="file_surat" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff;">
</div>
<div style="margin-top: 18px;">
    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Keterangan</label>
    <textarea name="keterangan" rows="3" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; resize: vertical;">{{ old('keterangan', $surat->keterangan) }}</textarea>
</div>
<div style="margin-top: 24px; display: flex; gap: 12px;">
    <button type="submit" style="padding: 11px 24px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;"><i class="bi bi-save-fill"></i> Simpan Perubahan</button>
    <a href="{{ route('surat-masuk.show', $surat->id_surat_masuk) }}" style="padding: 11px 20px; background: #f1f5f9; color: #475569; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none;">Batal</a>
</div>
</form>
</div>
</div>
@endsection
