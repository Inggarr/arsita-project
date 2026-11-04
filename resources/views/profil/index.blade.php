@extends('layouts.app')
@section('title', 'Profil & Tanda Tangan')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0; display: flex; align-items: center; gap: 10px;">
            <span style="width: 36px; height: 36px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="bi bi-person-badge-fill" style="color: #fff; font-size: 18px;"></i>
            </span>
            Profil Akun &amp; Tanda Tangan
        </h1>
        <p style="color: #64748b; font-size: 13px; margin: 0 0 0 46px;">Kelola informasi akun pengguna dan spesimen tanda tangan digital untuk persetujuan naskah dinas.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 24px;">
        
        {{-- Card 1: Informasi Akun --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                <i class="bi bi-person-lines-fill" style="font-size: 18px; color: #2563eb;"></i>
                <h2 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;">Data Pengguna</h2>
            </div>

            <form action="{{ route('profil.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Nama Lengkap &amp; Gelar</label>
                    <input type="text" name="nama" value="{{ old('nama', $user->nama) }}" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Username Login</label>
                    <input type="text" value="{{ $user->username }}" disabled style="width: 100%; padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13.5px; background: #f8fafc; color: #64748b;">
                    <small style="color: #94a3b8; font-size: 11px;">Username login tidak dapat diubah.</small>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 5px;">Unit &amp; Jabatan Struktural</label>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; font-size: 12.5px; color: #334155;">
                        <div><strong>Unit:</strong> {{ $user->unit?->nama_unit ?? 'Rektorat' }} ({{ $user->unit?->kode_unit ?? 'Un.30' }})</div>
                        <div style="margin-top: 4px;"><strong>Kode Jabatan:</strong> <code style="background: #eff6ff; color: #1e40af; padding: 1px 6px; border-radius: 4px;">{{ $user->kode_jabatan ?: '—' }}</code></div>
                    </div>
                </div>

                <div style="border-top: 1px dashed #e2e8f0; margin: 18px 0; padding-top: 14px;">
                    <div style="font-weight: 600; font-size: 13px; color: #0f172a; margin-bottom: 10px;">Ganti Password (Opsional)</div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <input type="password" name="password" placeholder="Password Baru (minimal 6 karakter)" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 14px;">
                        <input type="password" name="password_confirmation" placeholder="Ulangi Password Baru" style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 9px 18px; font-weight: 600;">
                        <i class="bi bi-save"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>

        {{-- Card 2: Tanda Tangan Digital (Khusus Pimpinan / Admin Rektorat) --}}
        @if($user->isPimpinan() || $user->isAdminRektorat())
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-pen-fill" style="font-size: 18px; color: #16a34a;"></i>
                    <h2 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;">Spesimen Tanda Tangan</h2>
                </div>
                @if($user->tanda_tangan)
                    <span style="background: #dcfce7; color: #15803d; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">
                        <i class="bi bi-check-circle-fill"></i> TTD Tersimpan
                    </span>
                @else
                    <span style="background: #fee2e2; color: #dc2626; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">
                        <i class="bi bi-exclamation-circle-fill"></i> Belum Ada TTD
                    </span>
                @endif
            </div>

            <p style="font-size: 12.5px; color: #64748b; margin-top: 0; line-height: 1.5;">
                Upload foto scan atau coretan tanda tangan asli Anda di atas kertas putih.
                Sistem akan <strong>otomatis menghapus background putih</strong> menjadi transparan agar tanda tangan menempel bersih dan rapi di lembar surat keluar resmi saat Anda melakukan <strong>ACC</strong>.
            </p>

            {{-- Area Pratinjau TTD --}}
            <div style="margin: 14px 0 18px; padding: 18px; border: 2px dashed {{ $user->tanda_tangan ? '#86efac' : '#cbd5e1' }}; border-radius: 12px; background: {{ $user->tanda_tangan ? '#f0fdf4' : '#f8fafc' }}; text-align: center; min-height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                @if($user->tanda_tangan)
                    <div style="background: repeating-conic-gradient(#f1f5f9 0% 25%, #fff 0% 50%) 50% / 16px 16px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 24px; display: inline-block;">
                        <img src="{{ asset('uploads/tanda_tangan/' . $user->tanda_tangan) }}?t={{ time() }}" alt="Tanda Tangan {{ $user->nama }}" style="max-height: 100px; max-width: 260px; object-fit: contain; display: block; margin: 0 auto;">
                    </div>
                    <div style="font-size: 11.5px; color: #166534; font-weight: 600; margin-top: 10px;">
                        <i class="bi bi-shield-check"></i> Background kertas telah transparan
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                        Pejabat: <strong>{{ $user->nama }}</strong>
                    </div>
                @else
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                        <i class="bi bi-pen" style="font-size: 24px; color: #dc2626;"></i>
                    </div>
                    <div style="font-size: 13px; font-weight: 600; color: #334155;">Belum ada spesimen tanda tangan</div>
                    <div style="font-size: 11.5px; color: #94a3b8; max-width: 300px; margin-top: 4px;">Silakan upload foto/scan tanda tangan Anda di form bawah</div>
                @endif
            </div>

            {{-- Form Upload TTD --}}
            <form action="{{ route('profil.tanda-tangan.store') }}" method="POST" enctype="multipart/form-data" style="margin-top: auto;">
                @csrf
                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        {{ $user->tanda_tangan ? 'Ganti Spesimen Tanda Tangan' : 'Upload Scan Tanda Tangan' }} (PNG/JPG/WEBP, Maks. 3MB)
                    </label>
                    <input type="file" name="tanda_tangan" accept="image/png,image/jpeg,image/jpg,image/webp" required
                        style="width: 100%; padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 12.5px; background: #fff; cursor: pointer;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 11.5px; color: #0284c7;">
                        <i class="bi bi-magic"></i> <span>Fitur <strong>Auto Remove Background</strong> aktif otomatis saat disimpan.</span>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                    @if($user->tanda_tangan)
                    <button type="button" onclick="if(confirm('Hapus spesimen tanda tangan Anda?')) document.getElementById('form-hapus-ttd').submit();" class="btn btn-secondary btn-sm" style="color: #dc2626; border-color: #fecaca; background: #fff;">
                        <i class="bi bi-trash"></i> Hapus TTD
                    </button>
                    @endif
                    <button type="submit" class="btn btn-success btn-sm" style="padding: 9px 18px; font-weight: 600;">
                        <i class="bi bi-cloud-arrow-up-fill"></i> {{ $user->tanda_tangan ? 'Simpan & Perbarui TTD' : 'Upload & Hapus Background' }}
                    </button>
                </div>
            </form>

            @if($user->tanda_tangan)
            <form id="form-hapus-ttd" action="{{ route('profil.tanda-tangan.destroy') }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>
        @else
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: #64748b;">
            <i class="bi bi-shield-lock" style="font-size: 36px; color: #94a3b8; margin-bottom: 12px;"></i>
            <h3 style="font-size: 15px; font-weight: 700; color: #334155; margin: 0 0 6px;">Spesimen Tanda Tangan Khusus Pimpinan</h3>
            <p style="font-size: 12.5px; margin: 0; max-width: 320px;">Fitur tanda tangan otomatis pada naskah dinas hanya diaktifkan untuk akun Pejabat / Pimpinan penandatangan surat.</p>
        </div>
        @endif

    </div>
</div>
@endsection

