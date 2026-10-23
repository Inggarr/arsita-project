@extends('layouts.app')
@section('title', 'Daftar Surat Masuk')

@section('content')
@php
    $user = Auth::user();
    $role = $user->role ? $user->role->nama_role : '';
    $unit_nama = $user->unit ? $user->unit->nama_unit : '';
@endphp

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Data Surat Masuk {{ $unit_nama }}</div>
            <div class="card-subtitle">Kelola dan catat seluruh surat dinas yang diterima unit</div>
        </div>
        @if(in_array($role, ['admin_rektorat', 'staff_tu']))
            <a href="{{ route('surat-masuk.create') }}" class="btn btn-primary">
                <i class="bi bi-file-earmark-plus"></i> Catat Surat Masuk
            </a>
        @endif
    </div>

    <!-- Form Filter Identik Arsita Asli -->
    <form method="GET" action="{{ route('surat-masuk.index') }}" class="d-flex gap-3 flex-wrap mb-4">
        <input type="text" name="q" placeholder="Cari nomor, pengirim, atau perihal..." value="{{ request('q') }}" class="flex-1" style="min-width:200px">
        
        <select name="kategori" style="flex:1;min-width:150px">
            <option value="">Semua Kategori</option>
            @foreach($kategoriList as $k)
                <option value="{{ $k->id_kategori }}" {{ (request('kategori') == $k->id_kategori || request('kategori_id') == $k->id_kategori) ? 'selected' : '' }}>
                    {{ $k->nama_kategori }}
                </option>
            @endforeach
        </select>

        <select name="status" style="flex:1;min-width:150px">
            <option value="">Semua Status</option>
            <option value="belum_ditinjau" {{ (request('status') == 'belum_ditinjau' || request('status_arsip') == 'belum_ditinjau') ? 'selected' : '' }}>Belum Ditinjau</option>
            <option value="ditinjau" {{ (request('status') == 'ditinjau' || request('status_arsip') == 'ditinjau') ? 'selected' : '' }}>Ditinjau</option>
            <option value="didisposisikan" {{ (request('status') == 'didisposisikan' || request('status_arsip') == 'didisposisikan') ? 'selected' : '' }}>Didisposisikan</option>
            <option value="diarsipkan" {{ (request('status') == 'diarsipkan' || request('status_arsip') == 'diarsipkan') ? 'selected' : '' }}>Diarsipkan</option>
        </select>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request('q') || request('kategori') || request('kategori_id') || request('status') || request('status_arsip'))
            <a href="{{ route('surat-masuk.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>No. Surat Asal</th>
                    <th>Tgl Terima</th>
                    <th>Pengirim / Asal</th>
                    <th>Perihal</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suratMasuk as $sm)
                    <tr>
                        <td>{{ $loop->iteration + ($suratMasuk->currentPage() - 1) * $suratMasuk->perPage() }}</td>
                        <td>
                            <b>{{ $sm->no_surat_asal ?: '-' }}</b>
                            @if($sm->asal_disposisi_id)
                                <br><span class="badge badge-purple">Disposisi Atasan</span>
                            @endif
                        </td>
                        <td>{{ \App\Services\ArsitaService::tglIndo($sm->tanggal_terima) }}</td>
                        <td>{{ $sm->asal_surat ?: '-' }}</td>
                        <td>
                            <b>{{ $sm->perihal }}</b>
                        </td>
                        <td>{{ $sm->kategori->nama_kategori ?? 'Umum' }}</td>
                        <td>{!! \App\Services\ArsitaService::badgeStatus($sm->status_arsip) !!}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('surat-masuk.show', $sm->id_surat_masuk) }}" class="btn btn-secondary btn-sm" title="Lihat Detail">
                                    Detail
                                </a>
                                @if(in_array($role, ['admin_rektorat', 'staff_tu']) && $sm->status_arsip !== 'diarsipkan')
                                    <a href="{{ route('surat-masuk.edit', $sm->id_surat_masuk) }}" class="btn btn-secondary btn-sm" title="Edit Surat">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('surat-masuk.destroy', $sm->id_surat_masuk) }}" method="POST" style="display:inline;" onsubmit="return confirm('Surat masuk ini akan dihapus secara permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);" title="Hapus Surat">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($role === 'pimpinan' && $sm->status_arsip !== 'diarsipkan')
                                    <a href="{{ route('disposisi.create', $sm->id_surat_masuk) }}" class="btn btn-primary btn-sm" title="Buat Disposisi">
                                        Disposisi
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:32px">
                            Tidak ada surat masuk yang cocok dengan kriteria pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suratMasuk->hasPages())
        <div style="margin-top: 20px; display: flex; justify-content: center;">
            {{ $suratMasuk->links() }}
        </div>
    @endif
</div>
@endsection
