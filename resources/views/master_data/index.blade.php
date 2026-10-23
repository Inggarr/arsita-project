@extends('layouts.app')
@section('title', 'Master Data')
@section('content')
<div style="margin-bottom: 24px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Master Data</h1>
    <p style="color: #64748b; font-size: 13px; margin: 0;">Kelola data referensi: kategori surat, jenis surat, unit organisasi, dan format penomoran.</p>
</div>

<!-- Tabs -->
<div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px; gap: 0;">
    @foreach(['kategori'=>'Kategori Surat','jenis'=>'Jenis Surat','unit'=>'Unit Organisasi','format'=>'Format Nomor'] as $tab => $label)
    <a href="#{{ $tab }}" onclick="showTab('{{ $tab }}', this)" class="tab-link {{ $tab === 'kategori' ? 'tab-active' : '' }}"
       style="padding: 10px 20px; font-size: 13px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $tab === 'kategori' ? '#2563eb' : 'transparent' }}; margin-bottom: -2px; color: {{ $tab === 'kategori' ? '#2563eb' : '#64748b' }}; cursor: pointer;">
        {{ $label }}
    </a>
    @endforeach
</div>

<!-- Kategori Surat -->
<div id="tab-kategori">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Kategori Surat</h2>
    <button onclick="document.getElementById('form-kategori').style.display = document.getElementById('form-kategori').style.display === 'none' ? 'block' : 'none'" style="padding: 8px 14px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600;"><i class="bi bi-plus-lg"></i> Tambah</button>
</div>
<div id="form-kategori" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 14px;">
    <form action="{{ route('master.kategori.store') }}" method="POST" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        @csrf
        <input type="text" name="nama_kategori" placeholder="Nama kategori baru..." required style="flex: 1; min-width: 200px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
        <input type="text" name="kode_kategori" placeholder="Kode (cth: UMUM)" style="width: 140px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
        <textarea name="deskripsi" placeholder="Deskripsi (opsional)..." rows="1" style="flex: 1; min-width: 160px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; resize: none;"></textarea>
        <button type="submit" style="padding: 9px 18px; background: #059669; color: #fff; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600;">Simpan</button>
    </form>
</div>
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead><tr style="background: #f8fafc;">
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Kode</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Nama Kategori</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Deskripsi</th>
            <th style="padding: 10px 16px; text-align: center; color: #64748b; border-bottom: 1px solid #e2e8f0;">Aksi</th>
        </tr></thead>
        <tbody>
            @forelse($kategoriList as $k)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 16px; font-family: monospace; color: #475569;">{{ $k->kode_kategori ?? '—' }}</td>
                <td style="padding: 10px 16px; font-weight: 600; color: #0f172a;">{{ $k->nama_kategori }}</td>
                <td style="padding: 10px 16px; color: #64748b;">{{ $k->deskripsi ?? '—' }}</td>
                <td style="padding: 10px 16px; text-align: center;">
                    <form action="{{ route('master.kategori.destroy', $k->id_kategori) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #dc2626; font-size: 15px; cursor: pointer;"><i class="bi bi-trash-fill"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="padding: 24px; text-align: center; color: #94a3b8;">Belum ada data kategori.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>

<!-- Jenis Surat -->
<div id="tab-jenis" style="display: none;">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Jenis Surat</h2>
    <button onclick="document.getElementById('form-jenis').style.display = document.getElementById('form-jenis').style.display === 'none' ? 'block' : 'none'" style="padding: 8px 14px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600;"><i class="bi bi-plus-lg"></i> Tambah</button>
</div>
<div id="form-jenis" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 14px;">
    <form action="{{ route('master.jenis.store') }}" method="POST" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        @csrf
        <input type="text" name="nama_jenis" placeholder="Nama jenis surat..." required style="flex: 1; min-width: 200px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
        <input type="text" name="kode_jenis" placeholder="Kode (cth: SK)" required style="width: 120px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
        <textarea name="deskripsi" placeholder="Deskripsi..." rows="1" style="flex: 1; min-width: 160px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; resize: none;"></textarea>
        <button type="submit" style="padding: 9px 18px; background: #059669; color: #fff; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600;">Simpan</button>
    </form>
</div>
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead><tr style="background: #f8fafc;">
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Kode</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Nama Jenis</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Deskripsi</th>
            <th style="padding: 10px 16px; text-align: center; color: #64748b; border-bottom: 1px solid #e2e8f0;">Aksi</th>
        </tr></thead>
        <tbody>
            @forelse($jenisList as $j)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 16px; font-family: monospace; font-weight: 700; color: #2563eb;">{{ $j->kode_jenis }}</td>
                <td style="padding: 10px 16px; font-weight: 600; color: #0f172a;">{{ $j->nama_jenis }}</td>
                <td style="padding: 10px 16px; color: #64748b;">{{ $j->deskripsi ?? '—' }}</td>
                <td style="padding: 10px 16px; text-align: center;">
                    <form action="{{ route('master.jenis.destroy', $j->id_jenis_surat) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #dc2626; font-size: 15px; cursor: pointer;"><i class="bi bi-trash-fill"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="padding: 24px; text-align: center; color: #94a3b8;">Belum ada data jenis surat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>

<!-- Unit -->
<div id="tab-unit" style="display: none;">
<h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 14px 0;">Struktur Unit Organisasi</h2>
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead><tr style="background: #f8fafc;">
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Kode</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Nama Unit</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Level</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Induk</th>
        </tr></thead>
        <tbody>
            @forelse($unitList as $u)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 16px; font-family: monospace; color: #475569;">{{ $u->kode_unit }}</td>
                <td style="padding: 10px 16px; font-weight: 600; color: #0f172a;">{{ $u->nama_unit }}</td>
                <td style="padding: 10px 16px;">
                    <span style="background: {{ $u->level === 'rektorat' ? '#fef3c7' : ($u->level === 'fakultas' ? '#eff6ff' : '#ecfdf5') }}; color: {{ $u->level === 'rektorat' ? '#d97706' : ($u->level === 'fakultas' ? '#2563eb' : '#059669') }}; padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 600;">{{ ucfirst($u->level) }}</span>
                </td>
                <td style="padding: 10px 16px; color: #64748b; font-size: 12px;">{{ $u->parent?->nama_unit ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="padding: 24px; text-align: center; color: #94a3b8;">Belum ada data unit.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>

<!-- Format Nomor -->
<div id="tab-format" style="display: none;">
<h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 14px 0;">Format Penomoran Otomatis</h2>
<div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 16px; margin-bottom: 14px; font-size: 13px; color: #92400e;">
    <i class="bi bi-info-circle-fill"></i>
    <strong>Token yang tersedia:</strong> <code>{nomor}</code> (urutan), <code>{kode_jenis}</code> (cth: SK), <code>{kode_unit}</code> (cth: UN.14), <code>{bulan_romawi}</code> (cth: X), <code>{tahun}</code> (cth: 2025)
    <br>Contoh: <code>{nomor}/{kode_jenis}/{kode_unit}/{bulan_romawi}/{tahun}</code> → <strong>001/SK/UN.14/X/2025</strong>
</div>
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead><tr style="background: #f8fafc;">
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Jenis Surat</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Unit</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Format Nomor</th>
            <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Contoh Output</th>
        </tr></thead>
        <tbody>
            @forelse($formatList as $f)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px 16px; color: #0f172a; font-weight: 600;">{{ $f->jenisSurat?->nama_jenis ?? 'Semua Jenis' }}</td>
                <td style="padding: 10px 16px; color: #475569;">{{ $f->unit?->nama_unit ?? 'Semua Unit' }}</td>
                <td style="padding: 10px 16px; font-family: monospace; font-size: 12px; color: #7c3aed;">{{ $f->format_template }}</td>
                <td style="padding: 10px 16px; font-size: 12px; color: #059669;">{{ $f->contoh ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="padding: 24px; text-align: center; color: #94a3b8;">Belum ada format nomor yang dikonfigurasi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>

@push('scripts')
<script>
function showTab(tab, el) {
    ['kategori','jenis','unit','format'].forEach(t => {
        document.getElementById('tab-' + t).style.display = 'none';
    });
    document.getElementById('tab-' + tab).style.display = 'block';
    document.querySelectorAll('.tab-link').forEach(a => {
        a.style.borderBottomColor = 'transparent';
        a.style.color = '#64748b';
    });
    el.style.borderBottomColor = '#2563eb';
    el.style.color = '#2563eb';
}
</script>
@endpush
@endsection
