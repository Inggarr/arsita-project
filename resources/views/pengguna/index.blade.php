@extends('layouts.app')
@section('title', 'Kelola Pengguna Sistem')

@section('content')
<div class="detail-grid">
    <!-- Daftar Pengguna -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Daftar Akun Pengguna ARSITA</div>
                <div class="card-subtitle">
                    Hak akses role dan penempatan unit kerja pegawai
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama &amp; Username</th>
                        <th>Role Akses</th>
                        <th>Unit Kampus</th>
                        <th>Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>
                                <b>{{ $u->nama }}</b><br>
                                <small class="text-muted">@{{ $u->username }}</small>
                            </td>
                            <td>
                                <span style="font-size: 11.5px; font-weight: 600; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">
                                    {{ $u->role?->nama_role ?? '—' }}
                                </span>
                            </td>
                            <td>{{ ($u->unit?->nama_unit ?? '-') . ' (' . ($u->unit?->kode_unit ?? '-') . ')' }}</td>
                            <td>
                                @if($u->status === 'aktif')
                                    <span style="color: var(--success); font-weight: 600; font-size: 12px;">Aktif</span>
                                @else
                                    <span style="color: var(--danger); font-weight: 600; font-size: 12px;">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('pengguna.edit', $u->id_user) }}" class="btn btn-secondary btn-sm" title="Edit Pengguna">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($u->id_user !== Auth::user()->id_user)
                                        <form action="{{ route('pengguna.destroy', $u->id_user) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);" title="Hapus Pengguna">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted" style="padding:32px">Belum ada pengguna terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tambah Pengguna Baru -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Tambah Pengguna Baru</div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <form action="{{ route('pengguna.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nama">Nama Lengkap &amp; Gelar *</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" placeholder="Contoh: Dr. H. Ahmad, M.Ag." required>
            </div>

            <div class="form-group">
                <label for="username">Username Login *</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="Contoh: ahmad_tu" required>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" name="password" id="password" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Contoh: ahmad@uinssc.ac.id">
            </div>

            <div class="form-group">
                <label for="id_unit">Unit Kampus *</label>
                <select name="id_unit" id="id_unit" required>
                    <option value="">-- Pilih Unit --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id_unit }}" {{ old('id_unit') == $unit->id_unit ? 'selected' : '' }}>
                            {{ $unit->nama_unit }} ({{ $unit->kode_unit }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="id_role">Role Pengguna *</label>
                <select name="id_role" id="id_role" required>
                    <option value="">-- Pilih Role --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id_role }}" {{ old('id_role') == $r->id_role ? 'selected' : '' }}>
                            {{ $r->nama_role }} ({{ $r->deskripsi ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                Daftarkan Pengguna
            </button>
        </form>
    </div>
</div>
@endsection
