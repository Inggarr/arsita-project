<?php

use App\Http\Controllers\ArsipLaporanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PersetujuanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratMasukController;
use Illuminate\Support\Facades\Route;

// Guest Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Application
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Surat Masuk
    Route::prefix('surat-masuk')->name('surat-masuk.')->group(function () {
        Route::get('/', [SuratMasukController::class, 'index'])->name('index');
        Route::get('/create', [SuratMasukController::class, 'create'])->name('create');
        Route::post('/', [SuratMasukController::class, 'store'])->name('store');
        Route::get('/{id}', [SuratMasukController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [SuratMasukController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SuratMasukController::class, 'update'])->name('update');
        Route::delete('/{id}', [SuratMasukController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/arsipkan', [SuratMasukController::class, 'arsipkan'])->name('arsipkan');
    });

    // Surat Keluar
    Route::prefix('surat-keluar')->name('surat-keluar.')->group(function () {
        Route::get('/', [SuratKeluarController::class, 'index'])->name('index');
        Route::get('/create', [SuratKeluarController::class, 'create'])->name('create');
        Route::post('/', [SuratKeluarController::class, 'store'])->name('store');
        Route::get('/{id}', [SuratKeluarController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [SuratKeluarController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SuratKeluarController::class, 'update'])->name('update');
        Route::delete('/{id}', [SuratKeluarController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/ajukan', [SuratKeluarController::class, 'ajukan'])->name('ajukan');
        Route::get('/{id}/cetak', [SuratKeluarController::class, 'cetak'])->name('cetak');
    });

    // Persetujuan / ACC Surat Keluar (Hanya Pimpinan & Admin Rektorat)
    Route::prefix('persetujuan')->name('persetujuan.')->middleware('role:pimpinan,admin_rektorat')->group(function () {
        Route::get('/', [PersetujuanController::class, 'index'])->name('index');
        Route::get('/{id}', [PersetujuanController::class, 'show'])->name('show');
        Route::post('/{id}/proses', [PersetujuanController::class, 'proses'])->name('proses');
    });

    // Notifikasi Sistem & Realtime
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/list', [NotifikasiController::class, 'getNotifications'])->name('list');
        Route::get('/{id}/baca', [NotifikasiController::class, 'readAndRedirect'])->name('read');
        Route::post('/tandai-semua-dibaca', [NotifikasiController::class, 'markAllAsRead'])->name('mark-all');
    });

    // Disposisi
    Route::prefix('disposisi')->name('disposisi.')->group(function () {
        Route::get('/', [DisposisiController::class, 'index'])->name('index');
        Route::get('/riwayat', [DisposisiController::class, 'riwayat'])->name('riwayat');
        Route::get('/buat/{surat_masuk_id}', [DisposisiController::class, 'create'])->name('create');
        Route::post('/', [DisposisiController::class, 'store'])->name('store');
        Route::get('/{id}', [DisposisiController::class, 'show'])->name('show');
        Route::post('/{id}/tindak-lanjut', [DisposisiController::class, 'tindakLanjut'])->name('tindak-lanjut');
    });

    // Master Data (Hanya Admin Rektorat)
    Route::prefix('master')->name('master.')->middleware('role:admin_rektorat')->group(function () {
        Route::get('/format-nomor', [MasterDataController::class, 'formatNomorIndex'])->name('format-nomor');
        Route::post('/format-nomor', [MasterDataController::class, 'formatNomorStore'])->name('format-nomor.store');
        Route::put('/format-nomor/{id}', [MasterDataController::class, 'formatNomorUpdate'])->name('format-nomor.update');
        Route::delete('/format-nomor/{id}', [MasterDataController::class, 'formatNomorDestroy'])->name('format-nomor.destroy');
        Route::get('/unit', [MasterDataController::class, 'unitIndex'])->name('unit');
        Route::post('/unit', [MasterDataController::class, 'unitStore'])->name('unit.store');
        Route::delete('/unit/{id}', [MasterDataController::class, 'unitDestroy'])->name('unit.destroy');
        Route::get('/kategori', [MasterDataController::class, 'kategoriIndex'])->name('kategori');
        Route::post('/kategori', [MasterDataController::class, 'kategoriStore'])->name('kategori.store');
        Route::delete('/kategori/{id}', [MasterDataController::class, 'kategoriDestroy'])->name('kategori.destroy');
        Route::get('/jenis', [MasterDataController::class, 'jenisIndex'])->name('jenis');
        Route::post('/jenis', [MasterDataController::class, 'jenisStore'])->name('jenis.store');
        Route::delete('/jenis/{id}', [MasterDataController::class, 'jenisDestroy'])->name('jenis.destroy');
    });

    // Manajemen Pengguna (Hanya Admin Rektorat)
    Route::prefix('pengguna')->name('pengguna.')->middleware('role:admin_rektorat')->group(function () {
        Route::get('/', [PenggunaController::class, 'index'])->name('index');
        Route::post('/', [PenggunaController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PenggunaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PenggunaController::class, 'update'])->name('update');
        Route::delete('/{id}', [PenggunaController::class, 'destroy'])->name('destroy');
    });

    // Arsip (Bisa dibuka oleh seluruh staf yang terautentikasi sesuai unitnya)
    Route::get('/arsip', [ArsipLaporanController::class, 'arsip'])->name('arsip.index');

    // Laporan & Rekap Ekspor (Khusus Pimpinan & Administrator)
    Route::middleware('role:pimpinan,admin_rektorat,admin_unit')->group(function () {
        Route::get('/laporan', [ArsipLaporanController::class, 'laporan'])->name('laporan.index');
        Route::get('/laporan/pdf', [ArsipLaporanController::class, 'laporanPdf'])->name('laporan.pdf');
        Route::get('/laporan/excel', [ArsipLaporanController::class, 'laporanExcel'])->name('laporan.excel');
    });

    // Pengaturan Sistem (Admin Rektorat, Admin Unit & Staff TU / Sekretaris)
    Route::prefix('pengaturan')->name('pengaturan.')->middleware('role:admin_rektorat,admin_unit,staff_tu')->group(function () {
        Route::get('/', [PengaturanController::class, 'index'])->name('index');
        Route::post('/format-nomor', [PengaturanController::class, 'formatNomorStore'])->name('format-nomor.store');
        Route::put('/format-nomor/{id}', [PengaturanController::class, 'formatNomorUpdate'])->name('format-nomor.update');
        Route::delete('/format-nomor/{id}', [PengaturanController::class, 'formatNomorDestroy'])->name('format-nomor.destroy');
        Route::put('/kop-surat', [PengaturanController::class, 'updateKopSurat'])->name('kop-surat.update');
        Route::put('/format-surat', [PengaturanController::class, 'updateFormatSurat'])->name('format-surat.update');

        // Kategori & Jenis Surat CRUD
        Route::post('/kategori', [PengaturanController::class, 'kategoriStore'])->name('kategori.store');
        Route::put('/kategori/{id}', [PengaturanController::class, 'kategoriUpdate'])->name('kategori.update');
        Route::delete('/kategori/{id}', [PengaturanController::class, 'kategoriDestroy'])->name('kategori.destroy');

        Route::post('/jenis', [PengaturanController::class, 'jenisStore'])->name('jenis.store');
        Route::put('/jenis/{id}', [PengaturanController::class, 'jenisUpdate'])->name('jenis.update');
        Route::delete('/jenis/{id}', [PengaturanController::class, 'jenisDestroy'])->name('jenis.destroy');

        // Unit Organisasi CRUD
        Route::post('/unit', [PengaturanController::class, 'unitStore'])->name('unit.store');
        Route::put('/unit/{id}', [PengaturanController::class, 'unitUpdate'])->name('unit.update');
        Route::delete('/unit/{id}', [PengaturanController::class, 'unitDestroy'])->name('unit.destroy');

        // Klasifikasi / Sifat Surat CRUD
        Route::post('/sifat', [PengaturanController::class, 'sifatStore'])->name('sifat.store');
        Route::put('/sifat/{id}', [PengaturanController::class, 'sifatUpdate'])->name('sifat.update');
        Route::delete('/sifat/{id}', [PengaturanController::class, 'sifatDestroy'])->name('sifat.destroy');
    });

    // Profil & Tanda Tangan Digital Pimpinan
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::put('/profil', [ProfilController::class, 'updateProfil'])->name('profil.update');
    Route::post('/profil/tanda-tangan', [ProfilController::class, 'updateTandaTangan'])->name('profil.tanda-tangan.store');
    Route::delete('/profil/tanda-tangan', [ProfilController::class, 'destroyTandaTangan'])->name('profil.tanda-tangan.destroy');
});
