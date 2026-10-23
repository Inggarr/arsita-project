@extends('layouts.app')
@section('title', 'Edit Data Pengguna')

@section('content')
<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <div class="card-title">Edit Data Pengguna</div>
            <div class="card-subtitle">Perbarui hak akses, unit penempatan, atau reset password</div>
        </div>
        <a href="{{ route('pengguna.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form action="{{ route('pengguna.update', $pengguna->id_user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="nama">Nama Lengkap &amp; Gelar *</label>
            <input type="text" name="nama" id="nama" value="{{ old('nama', $pengguna->nama) }}" required>
        </div>

        <div class="form-group">
            <label for="username">Username Login *</label>
            <input type="text" name="username" id="username" value="{{ old('username', $pengguna->username) }}" required>
        </div>

        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" name="password" id="password" placeholder="Biarkan kosong jika tidak ingin mengubah password">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $pengguna->email) }}">
        </div>

        <div class="form-group">
            <label for="id_unit">Unit Kampus *</label>
            <select name="id_unit" id="id_unit" required>
                @foreach($units as $unit)
                    <option value="{{ $unit->id_unit }}" {{ old('id_unit', $pengguna->id_unit) == $unit->id_unit ? 'selected' : '' }}>
                        {{ $unit->nama_unit }} ({{ $unit->kode_unit }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="id_role">Role Akses *</label>
            <select name="id_role" id="id_role" required>
                @foreach($roles as $r)
                    <option value="{{ $r->id_role }}" {{ old('id_role', $pengguna->id_role) == $r->id_role ? 'selected' : '' }}>
                        {{ $r->nama_role }} ({{ $r->deskripsi ?? '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status Akun</label>
            <select name="status" id="status">
                <option value="aktif" {{ old('status', $pengguna->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ old('status', $pengguna->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div class="form-actions">
            <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
