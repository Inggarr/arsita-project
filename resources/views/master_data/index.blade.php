@extends('layouts.app')
@section('title', 'Master Data')

@section('content')
@php
    $user = Auth::user();
    $role = $user->role ? $user->role->nama_role : '';
    $isStaffTu = ($role === 'staff_tu');
    $isAdminRektorat = ($role === 'admin_rektorat');

    $allTabs = [];
    if ($isAdminRektorat) {
        $allTabs['kategori'] = 'Kategori Surat';
        $allTabs['jenis'] = 'Jenis Surat';
        $allTabs['unit'] = 'Unit Organisasi';
    }
    if ($isStaffTu) {
        $allTabs['format'] = 'Penomoran Surat';
    }

    $defaultTab = $isStaffTu ? 'format' : 'kategori';
@endphp

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">
        {{ $isStaffTu ? 'Pengaturan Penomoran Surat' : 'Master Data' }}
    </h1>
    <p style="color: #64748b; font-size: 13px; margin: 0;">
        {{ $isStaffTu ? 'Kelola pola format penomoran surat otomatis per unit dan jenis surat.' : 'Kelola data referensi: kategori surat, jenis surat, dan unit organisasi.' }}
    </p>
</div>

<!-- Tabs -->
@if(count($allTabs) > 1)
<div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px; gap: 0;">
    @foreach($allTabs as $tab => $label)
    <a href="#{{ $tab }}" onclick="showTab('{{ $tab }}', this)" class="tab-link {{ $tab === $defaultTab ? 'tab-active' : '' }}"
       style="padding: 10px 20px; font-size: 13px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $tab === $defaultTab ? '#2563eb' : 'transparent' }}; margin-bottom: -2px; color: {{ $tab === $defaultTab ? '#2563eb' : '#64748b' }}; cursor: pointer;">
        {{ $label }}
    </a>
    @endforeach
</div>
@endif

@if($isAdminRektorat)
<!-- Kategori Surat -->
<div id="tab-kategori" style="{{ $defaultTab === 'kategori' ? 'display: block;' : 'display: none;' }}">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Kategori Surat</h2>
        <button onclick="document.getElementById('form-kategori').style.display = document.getElementById('form-kategori').style.display === 'none' ? 'block' : 'none'" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah</button>
    </div>
    <div id="form-kategori" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 14px;">
        <form action="{{ route('master.kategori.store') }}" method="POST" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            @csrf
            <input type="text" name="nama_kategori" placeholder="Nama kategori baru..." required style="flex: 1; min-width: 200px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
            <input type="text" name="kode_kategori" placeholder="Kode (cth: UMUM)" style="width: 140px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
            <textarea name="deskripsi" placeholder="Deskripsi (opsional)..." rows="1" style="flex: 1; min-width: 160px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; resize: none;"></textarea>
            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
        </form>
    </div>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead><tr style="background: #f8fafc;">
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Kode</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Nama Kategori</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Deskripsi</th>
                <th style="padding: 10px 16px; text-align: center; color: #64748b; border-bottom: 1px solid #e2e8f0; width: 80px;">Aksi</th>
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
        <button onclick="document.getElementById('form-jenis').style.display = document.getElementById('form-jenis').style.display === 'none' ? 'block' : 'none'" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah</button>
    </div>
    <div id="form-jenis" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 14px;">
        <form action="{{ route('master.jenis.store') }}" method="POST" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            @csrf
            <input type="text" name="nama_jenis" placeholder="Nama jenis surat..." required style="flex: 1; min-width: 200px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
            <input type="text" name="kode_jenis" placeholder="Kode (cth: SK)" required style="width: 120px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
            <textarea name="deskripsi" placeholder="Deskripsi..." rows="1" style="flex: 1; min-width: 160px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; resize: none;"></textarea>
            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
        </form>
    </div>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead><tr style="background: #f8fafc;">
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Kode</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Nama Jenis</th>
                <th style="padding: 10px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Deskripsi</th>
                <th style="padding: 10px 16px; text-align: center; color: #64748b; border-bottom: 1px solid #e2e8f0; width: 80px;">Aksi</th>
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
                        @php
                            $badgeStyles = [
                                'rektorat' => 'background: #fef3c7; color: #d97706;',
                                'fakultas' => 'background: #eff6ff; color: #2563eb;',
                                'prodi'    => 'background: #ecfdf5; color: #059669;',
                                'lembaga'  => 'background: #f3e8ff; color: #7e22ce;',
                                'upt'      => 'background: #e0f2fe; color: #0284c7;',
                                'pusat'    => 'background: #fef2f2; color: #dc2626;',
                            ];
                            $style = $badgeStyles[$u->level] ?? 'background: #f1f5f9; color: #475569;';
                        @endphp
                        <span style="{{ $style }} padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 600;">{{ ucfirst($u->level) }}</span>
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
@endif

@if($isStaffTu)
<!-- Format Nomor (CRUD Lengkap Khusus Staff TU) -->
<div id="tab-format" style="{{ $defaultTab === 'format' ? 'display: block;' : 'display: none;' }}">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 2px 0;">Daftar Format Penomoran Otomatis</h2>
            <p style="margin: 0; font-size: 12.5px; color: #64748b;">Konfigurasi template nomor dinas per unit dan per jenis surat</p>
        </div>
        <button type="button" onclick="openCreateModal()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bi bi-plus-circle-fill"></i> Tambah Format Nomor
        </button>
    </div>

    <!-- Info Token Bantuan -->
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 12.5px; color: #166534; display: flex; align-items: flex-start; gap: 10px;">
        <i class="bi bi-info-circle-fill" style="font-size: 16px; margin-top: 1px; color: #15803d;"></i>
        <div>
            <strong>Panduan Token Template:</strong>
            <div style="margin-top: 4px; display: flex; flex-wrap: wrap; gap: 6px;">
                <code style="background: #dcfce7; padding: 2px 6px; border-radius: 4px; color: #14532d; font-weight: 600;">{nomor}</code> (nomor urut otomatis 3 digit, misal 001)
                <code style="background: #dcfce7; padding: 2px 6px; border-radius: 4px; color: #14532d; font-weight: 600;">{kode_jenis}</code> (kode jenis surat, misal SK)
                <code style="background: #dcfce7; padding: 2px 6px; border-radius: 4px; color: #14532d; font-weight: 600;">{kode_unit}</code> (kode unit, misal UN.14)
                <code style="background: #dcfce7; padding: 2px 6px; border-radius: 4px; color: #14532d; font-weight: 600;">{tahun}</code> (tahun, misal {{ date('Y') }})
            </div>
            <div style="margin-top: 6px; color: #15803d;">
                Contoh: <code style="background: #dcfce7; padding: 1px 5px; border-radius: 3px;">{nomor}/{kode_jenis}/{kode_unit}/{tahun}</code> &rarr; <strong>001/SK/UN.14/{{ date('Y') }}</strong>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 16px;">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div class="table-responsive" style="margin: 0;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0; width: 40px;">No</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Unit Kampus</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Jenis Surat</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Kategori</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Template Format Nomor</th>
                        <th style="padding: 12px 16px; text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">Contoh Hasil</th>
                        <th style="padding: 12px 16px; text-align: center; color: #64748b; border-bottom: 1px solid #e2e8f0; width: 70px;">Status</th>
                        <th style="padding: 12px 16px; text-align: center; color: #64748b; border-bottom: 1px solid #e2e8f0; width: 110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($formatList as $index => $f)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                        <td style="padding: 12px 16px; color: #94a3b8;">{{ $index + 1 }}</td>
                        <td style="padding: 12px 16px; color: #0f172a; font-weight: 600;">
                            {{ $f->unit?->nama_unit ?? 'Semua Unit' }}
                            @if($f->unit)
                                <br><small style="color: #64748b; font-weight: 400; font-family: monospace;">({{ $f->unit->kode_unit }})</small>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; color: #1e40af; font-weight: 600;">
                            {{ $f->jenisSurat?->nama_jenis ?? 'Semua Jenis' }}
                            @if($f->jenisSurat)
                                <br><small style="color: #64748b; font-weight: 400; font-family: monospace;">Kode: {{ $f->jenisSurat->kode_jenis }}</small>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; color: #475569;">
                            {{ $f->kategori?->nama_kategori ?? 'Semua Kategori' }}
                        </td>
                        <td style="padding: 12px 16px;">
                            <code style="background: #f1f5f9; padding: 3px 8px; border-radius: 4px; font-family: monospace; font-size: 12px; color: #7c3aed; font-weight: 600; border: 1px solid #e2e8f0;">
                                {{ $f->format_nomor }}
                            </code>
                        </td>
                        <td style="padding: 12px 16px; font-size: 12px; color: #059669; font-weight: 600; font-family: monospace;">
                            {{ $f->contoh ?? '—' }}
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            @if($f->aktif)
                                <span style="background: #ecfdf5; color: #059669; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">Aktif</span>
                            @else
                                <span style="background: #fef2f2; color: #dc2626; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">Nonaktif</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <button type="button"
                                    onclick="openEditModal(this)"
                                    data-format="{{ json_encode($f->only(['id_format','id_unit','id_jenis_surat','kategori_id','format_nomor','contoh','aktif'])) }}"
                                    class="btn btn-secondary btn-sm" style="padding: 5px 9px;" title="Edit Format">
                                    <i class="bi bi-pencil-square" style="color: #2563eb;"></i>
                                </button>
                                <form action="{{ route('master.format-nomor.destroy', $f->id_format) }}" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus format penomoran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 5px 9px;" title="Hapus Format">
                                        <i class="bi bi-trash" style="color: #dc2626;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 36px 20px; text-align: center; color: #94a3b8;">
                            <i class="bi bi-hash" style="font-size: 32px; display: block; margin-bottom: 8px; color: #cbd5e1;"></i>
                            Belum ada format nomor yang dikonfigurasi. Klik tombol <strong>Tambah Format Nomor</strong> di atas untuk membuat format baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah / Buat Format Nomor -->
<div id="modal-create-format" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 580px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-plus-circle" style="color: #2563eb;"></i> Tambah Format Penomoran
            </div>
            <button type="button" onclick="closeCreateModal()" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer; padding: 0;">&times;</button>
        </div>
        <form action="{{ route('master.format-nomor.store') }}" method="POST" style="padding: 22px;">
            @csrf
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="create_id_unit" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Unit Kampus *</label>
                <select name="id_unit" id="create_id_unit" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Pilih Unit --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id_unit }}">{{ $u->nama_unit }} ({{ $u->kode_unit }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="create_id_jenis_surat" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Jenis Surat *</label>
                <select name="id_jenis_surat" id="create_id_jenis_surat" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Pilih Jenis Surat --</option>
                    @foreach($jenisList as $j)
                        <option value="{{ $j->id_jenis_surat }}">{{ $j->nama_jenis }} (Kode: {{ $j->kode_jenis }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="create_kategori_id" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kategori Surat (Opsional)</label>
                <select name="kategori_id" id="create_kategori_id" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Semua Kategori / Berlaku Umum --</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="create_format_nomor" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Template Format Nomor *</label>
                <input type="text" name="format_nomor" id="create_format_nomor" required placeholder="Contoh: {nomor}/{kode_jenis}/{kode_unit}/{tahun}" value="{nomor}/{kode_jenis}/{kode_unit}/{tahun}" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace;">
                <small style="color: #64748b; font-size: 11.5px; margin-top: 4px; display: block;">Token: {nomor}, {kode_jenis}, {kode_unit}, {tahun}</small>
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
                <label for="create_contoh" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Contoh Hasil Output</label>
                <input type="text" name="contoh" id="create_contoh" placeholder="Contoh: 001/SK/UN.14/{{ date('Y') }}" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                <button type="button" onclick="closeCreateModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle"></i> Simpan Format</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Format Nomor -->
<div id="modal-edit-format" class="arsita-modal-overlay" style="pointer-events: none;">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 580px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); overflow: hidden; margin: 20px;">
        <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-pencil-square" style="color: #2563eb;"></i> Edit Format Penomoran
            </div>
            <button type="button" onclick="closeEditModal()" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer; padding: 0;">&times;</button>
        </div>
        <form id="form-edit-format" action="" method="POST" style="padding: 22px;">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="edit_id_unit" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Unit Kampus *</label>
                <select name="id_unit" id="edit_id_unit" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Pilih Unit --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id_unit }}">{{ $u->nama_unit }} ({{ $u->kode_unit }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="edit_id_jenis_surat" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Jenis Surat *</label>
                <select name="id_jenis_surat" id="edit_id_jenis_surat" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Pilih Jenis Surat --</option>
                    @foreach($jenisList as $j)
                        <option value="{{ $j->id_jenis_surat }}">{{ $j->nama_jenis }} (Kode: {{ $j->kode_jenis }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="edit_kategori_id" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Kategori Surat (Opsional)</label>
                <select name="kategori_id" id="edit_kategori_id" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="">-- Semua Kategori / Berlaku Umum --</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="edit_format_nomor" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Template Format Nomor *</label>
                <input type="text" name="format_nomor" id="edit_format_nomor" required placeholder="Contoh: {nomor}/{kode_jenis}/{kode_unit}/{tahun}" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace;">
                <small style="color: #64748b; font-size: 11.5px; margin-top: 4px; display: block;">Token: {nomor}, {kode_jenis}, {kode_unit}, {tahun}</small>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label for="edit_contoh" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Contoh Hasil Output</label>
                <input type="text" name="contoh" id="edit_contoh" placeholder="Contoh: 001/SK/UN.14/{{ date('Y') }}" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace;">
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
                <label for="edit_aktif" style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Status Aktif</label>
                <select name="aktif" id="edit_aktif" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2-circle"></i> Perbarui Format</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
function showTab(tab, el) {
    var tabs = ['kategori','jenis','unit','format'];
    tabs.forEach(function(t) {
        var elTab = document.getElementById('tab-' + t);
        if (elTab) elTab.style.display = 'none';
    });
    var activeEl = document.getElementById('tab-' + tab);
    if (activeEl) activeEl.style.display = 'block';

    document.querySelectorAll('.tab-link').forEach(function(a) {
        a.style.borderBottomColor = 'transparent';
        a.style.color = '#64748b';
    });
    if (el) {
        el.style.borderBottomColor = '#2563eb';
        el.style.color = '#2563eb';
    }
}

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
        form.action = "{{ url('master/format-nomor') }}/" + data.id_format;

        // Pastikan value select cocok (string comparison)
        document.getElementById('edit_id_unit').value        = data.id_unit        ? String(data.id_unit)        : '';
        document.getElementById('edit_id_jenis_surat').value = data.id_jenis_surat ? String(data.id_jenis_surat) : '';
        document.getElementById('edit_kategori_id').value    = data.kategori_id    ? String(data.kategori_id)    : '';
        document.getElementById('edit_format_nomor').value   = data.format_nomor   || '';
        document.getElementById('edit_contoh').value         = data.contoh         || '';

        // aktif bisa berupa boolean true/false (dari Laravel JSON) atau integer 1/0
        var aktifVal = (data.aktif === true || data.aktif == 1) ? '1' : '0';
        document.getElementById('edit_aktif').value = aktifVal;

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

// Tutup modal bila klik backdrop
document.getElementById('modal-create-format').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});
document.getElementById('modal-edit-format').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
@endpush
@endsection

