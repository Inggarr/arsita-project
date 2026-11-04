<?php

namespace App\Http\Controllers;

use App\Models\FormatNomor;
use App\Models\JenisSurat;
use App\Models\KategoriSurat;
use App\Models\PengaturanSurat;
use App\Models\SifatSurat;
use App\Models\Unit;
use App\Services\ArsitaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    private function checkAccess(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isAdminRektorat() && ! $user->isStaffTu() && ! $user->isAdminUnit())) {
            abort(403, 'Akses ditolak. Halaman pengaturan hanya dapat diakses oleh Admin atau Staff TU/Sekretaris.');
        }
    }

    public function index(Request $request): View
    {
        $this->checkAccess();

        $user = Auth::user();
        $activeTab = $request->query('tab', 'format-nomor');

        // Jika Staff TU / Sekretaris atau Admin Unit, hanya tampilkan format nomor & data unitnya sendiri
        $formatQuery = FormatNomor::with(['unit', 'jenisSurat', 'kategori']);
        if (! $user->isAdminRektorat()) {
            $formatQuery->where('id_unit', $user->id_unit);
            $units = Unit::with('parent')->where('id_unit', $user->id_unit)->get();
        } else {
            $units = Unit::with('parent')->orderBy('nama_unit')->get();
        }

        $formatList = $formatQuery->get();
        $jenisList = JenisSurat::with('kategori')->orderBy('nama_jenis')->get();
        $kategoriList = KategoriSurat::withCount('jenisSurat')->orderBy('nama_kategori')->get();
        $sifatList = SifatSurat::orderBy('id_sifat')->get();

        $kopSurat = PengaturanSurat::group('kop_surat');
        $formatSurat = PengaturanSurat::group('format_surat');

        return view('pengaturan.index', compact(
            'activeTab', 'formatList', 'units', 'jenisList', 'kategoriList', 'sifatList',
            'kopSurat', 'formatSurat', 'user'
        ));
    }

    /* ─────────────────────── Format Nomor ─────────────────────── */

    public function formatNomorStore(Request $request): RedirectResponse
    {
        $this->checkAccess();
        $user = Auth::user();

        // Validasi pembatasan unit untuk Staff TU / Sekretaris / Admin Unit
        $idUnit = (int) $request->id_unit;
        if (! $user->isAdminRektorat() && (int) $user->id_unit !== $idUnit) {
            abort(403, 'Anda hanya dapat mengatur format penomoran untuk unit Anda sendiri.');
        }

        $request->validate([
            'id_unit' => 'required|exists:units,id_unit',
            'id_jenis_surat' => 'required|exists:jenis_surat,id_jenis_surat',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'format_nomor' => 'required|string|max:100',
            'contoh' => 'nullable|string|max:100',
        ]);

        FormatNomor::create([
            'id_unit' => $idUnit,
            'id_jenis_surat' => $request->id_jenis_surat,
            'kategori_id' => $request->kategori_id ?: null,
            'format_nomor' => $request->format_nomor,
            'contoh' => $request->contoh,
            'nomor_terakhir' => 0,
            'aktif' => 1,
        ]);

        return redirect()->route('pengaturan.index', ['tab' => 'format-nomor'])
            ->with('success', 'Format penomoran surat berhasil ditambahkan.');
    }

    public function formatNomorUpdate(Request $request, int $id): RedirectResponse
    {
        $this->checkAccess();
        $user = Auth::user();

        $format = FormatNomor::findOrFail($id);

        // Validasi hak akses unit
        if (! $user->isAdminRektorat() && (int) $user->id_unit !== (int) $format->id_unit) {
            abort(403, 'Akses ditolak. Anda tidak berhak mengubah format penomoran unit lain.');
        }

        $idUnit = $user->isAdminRektorat() ? (int) $request->id_unit : (int) $user->id_unit;

        $request->validate([
            'id_unit' => 'required|exists:units,id_unit',
            'id_jenis_surat' => 'required|exists:jenis_surat,id_jenis_surat',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'format_nomor' => 'required|string|max:100',
            'contoh' => 'nullable|string|max:100',
            'aktif' => 'nullable|in:0,1',
        ]);

        $format->update([
            'id_unit' => $idUnit,
            'id_jenis_surat' => $request->id_jenis_surat,
            'kategori_id' => $request->kategori_id ?: null,
            'format_nomor' => $request->format_nomor,
            'contoh' => $request->contoh,
            'aktif' => $request->has('aktif') ? (int) $request->aktif : 1,
        ]);

        $updatedCount = ArsitaService::updateNomorSuratByFormat($format);
        $pesan = $updatedCount > 0
            ? "Format penomoran berhasil diperbarui dan {$updatedCount} nomor surat keluar terkait telah disinkronkan."
            : 'Nomor surat berhasil diperbarui.';

        return redirect()->route('pengaturan.index', ['tab' => 'format-nomor'])
            ->with('success', $pesan);
    }

    public function formatNomorDestroy(int $id): RedirectResponse
    {
        $this->checkAccess();
        $user = Auth::user();

        $format = FormatNomor::findOrFail($id);

        if (! $user->isAdminRektorat() && (int) $user->id_unit !== (int) $format->id_unit) {
            abort(403, 'Akses ditolak. Anda tidak berhak menghapus format penomoran unit lain.');
        }

        $format->delete();

        return redirect()->route('pengaturan.index', ['tab' => 'format-nomor'])
            ->with('success', 'Format penomoran berhasil dihapus.');
    }

    /* ─────────────────────── Kop Surat ─────────────────────── */

    public function updateKopSurat(Request $request): RedirectResponse
    {
        $this->checkAccess();

        $request->validate([
            'kop_kementerian' => 'nullable|string|max:200',
            'kop_nama_instansi' => 'required|string|max:200',
            'kop_sub_unit' => 'nullable|string|max:200',
            'kop_singkatan' => 'required|string|max:30',
            'kop_alamat' => 'required|string|max:255',
            'kop_kota' => 'required|string|max:100',
            'kop_kode_pos' => 'nullable|string|max:10',
            'kop_telepon' => 'nullable|string|max:50',
            'kop_email' => 'nullable|email|max:100',
            'kop_website' => 'nullable|string|max:150',
            'kop_logo' => 'nullable|file|mimes:png,jpg,jpeg,svg|max:2048',

            // Styling & Font
            'kop_font_family' => 'nullable|string|max:100',
            'kop_kemenag_font_size' => 'nullable|string|max:20',
            'kop_kemenag_bold' => 'nullable|in:0,1',
            'kop_kemenag_color' => 'nullable|string|max:20',

            'kop_instansi_font_size' => 'nullable|string|max:20',
            'kop_instansi_bold' => 'nullable|in:0,1',
            'kop_instansi_color' => 'nullable|string|max:20',

            'kop_subunit_font_size' => 'nullable|string|max:20',
            'kop_subunit_bold' => 'nullable|in:0,1',
            'kop_subunit_color' => 'nullable|string|max:20',

            'kop_kontak_font_size' => 'nullable|string|max:20',
            'kop_kontak_italic' => 'nullable|in:0,1',
            'kop_kontak_color' => 'nullable|string|max:20',

            'kop_garis_ketebalan' => 'nullable|string|max:20',
            'kop_garis_tipe' => 'nullable|string|max:20',
            'kop_garis_warna' => 'nullable|string|max:20',
        ]);

        $fields = [
            'kop_kementerian', 'kop_nama_instansi', 'kop_sub_unit', 'kop_singkatan', 'kop_alamat',
            'kop_kota', 'kop_kode_pos', 'kop_telepon', 'kop_email', 'kop_website',
            'kop_font_family',
            'kop_kemenag_font_size', 'kop_kemenag_bold', 'kop_kemenag_color',
            'kop_instansi_font_size', 'kop_instansi_bold', 'kop_instansi_color',
            'kop_subunit_font_size', 'kop_subunit_bold', 'kop_subunit_color',
            'kop_kontak_font_size', 'kop_kontak_italic', 'kop_kontak_color',
            'kop_garis_ketebalan', 'kop_garis_tipe', 'kop_garis_warna',
        ];

        foreach ($fields as $field) {
            $val = $request->input($field);
            // Handle checkboxes jika unchecked (bisa dikirim default 0 atau dihandle)
            PengaturanSurat::set($field, $val, 'kop_surat');
        }

        if ($request->hasFile('kop_logo')) {
            $file = $request->file('kop_logo');
            $fileName = 'kop_logo_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/pengaturan'), $fileName);
            PengaturanSurat::set('kop_logo', $fileName, 'kop_surat');
        }

        return redirect()->route('pengaturan.index', ['tab' => 'kop-surat'])
            ->with('success', 'Pengaturan kop surat berhasil disimpan.');
    }

    /* ─────────────────────── Format Surat ─────────────────────── */

    public function updateFormatSurat(Request $request): RedirectResponse
    {
        $this->checkAccess();

        $request->validate([
            'format_ukuran_kertas' => 'required|in:A4,A5,F4,Letter',
            'format_orientasi' => 'required|in:potrait,landscape',
            'format_font' => 'required|string|max:50',
            'format_ukuran_font' => 'required|integer|min:8|max:24',
            'format_margin_atas' => 'required|numeric|min:1|max:10',
            'format_margin_bawah' => 'required|numeric|min:1|max:10',
            'format_margin_kiri' => 'required|numeric|min:1|max:10',
            'format_margin_kanan' => 'required|numeric|min:1|max:10',
            'format_spasi' => 'required|in:1,1.15,1.5,2',
        ]);

        $fields = [
            'format_ukuran_kertas', 'format_orientasi', 'format_font',
            'format_ukuran_font', 'format_margin_atas', 'format_margin_bawah',
            'format_margin_kiri', 'format_margin_kanan', 'format_spasi',
        ];

        foreach ($fields as $field) {
            PengaturanSurat::set($field, $request->input($field), 'format_surat');
        }

        return redirect()->route('pengaturan.index', ['tab' => 'format-surat'])
            ->with('success', 'Pengaturan format surat berhasil disimpan.');
    }

    /* ─────────────────────── Kategori Surat CRUD ─────────────────────── */

    public function kategoriStore(Request $request): RedirectResponse
    {
        $this->checkAccess();

        $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'kode_kategori' => 'nullable|string|max:30',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        KategoriSurat::create([
            'nama_kategori' => $request->nama_kategori,
            'kode_kategori' => $request->kode_kategori ? strtoupper(trim($request->kode_kategori)) : null,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('pengaturan.index', ['tab' => 'kategori-jenis'])
            ->with('success', 'Kategori surat baru berhasil ditambahkan.');
    }

    public function kategoriUpdate(Request $request, int $id): RedirectResponse
    {
        $this->checkAccess();

        $kategori = KategoriSurat::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'kode_kategori' => 'nullable|string|max:30',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
            'kode_kategori' => $request->kode_kategori ? strtoupper(trim($request->kode_kategori)) : null,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('pengaturan.index', ['tab' => 'kategori-jenis'])
            ->with('success', 'Kategori surat berhasil diperbarui.');
    }

    public function kategoriDestroy(int $id): RedirectResponse
    {
        $this->checkAccess();

        $kategori = KategoriSurat::findOrFail($id);
        $kategori->delete();

        return redirect()->route('pengaturan.index', ['tab' => 'kategori-jenis'])
            ->with('success', 'Kategori surat berhasil dihapus.');
    }

    /* ─────────────────────── Jenis Surat CRUD ─────────────────────── */

    public function jenisStore(Request $request): RedirectResponse
    {
        $this->checkAccess();

        $request->validate([
            'nama_jenis' => 'required|string|max:100',
            'kode_jenis' => 'required|string|max:20',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'keterangan' => 'nullable|string|max:255',
        ]);

        JenisSurat::create([
            'nama_jenis' => $request->nama_jenis,
            'kode_jenis' => strtoupper(trim($request->kode_jenis)),
            'kategori_id' => $request->kategori_id ?: null,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('pengaturan.index', ['tab' => 'kategori-jenis'])
            ->with('success', 'Jenis surat baru berhasil ditambahkan.');
    }

    public function jenisUpdate(Request $request, int $id): RedirectResponse
    {
        $this->checkAccess();

        $jenis = JenisSurat::findOrFail($id);

        $request->validate([
            'nama_jenis' => 'required|string|max:100',
            'kode_jenis' => 'required|string|max:20',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $jenis->update([
            'nama_jenis' => $request->nama_jenis,
            'kode_jenis' => strtoupper(trim($request->kode_jenis)),
            'kategori_id' => $request->kategori_id ?: null,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('pengaturan.index', ['tab' => 'kategori-jenis'])
            ->with('success', 'Jenis surat berhasil diperbarui.');
    }

    public function jenisDestroy(int $id): RedirectResponse
    {
        $this->checkAccess();

        $jenis = JenisSurat::findOrFail($id);
        $jenis->delete();

        return redirect()->route('pengaturan.index', ['tab' => 'kategori-jenis'])
            ->with('success', 'Jenis surat berhasil dihapus.');
    }

    /* ─────────────────────── Unit Organisasi CRUD ─────────────────────── */

    public function unitStore(Request $request): RedirectResponse
    {
        $this->checkAccess();

        $request->validate([
            'nama_unit' => 'required|string|max:100',
            'kode_unit' => 'required|string|max:50',
            'level' => 'required|in:rektorat,fakultas,prodi,lembaga,upt,pusat',
            'id_parent' => 'nullable|exists:units,id_unit',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        Unit::create([
            'nama_unit' => $request->nama_unit,
            'kode_unit' => trim($request->kode_unit),
            'level' => $request->level,
            'id_parent' => $request->id_parent ?: null,
            'status' => $request->status,
        ]);

        return redirect()->route('pengaturan.index', ['tab' => 'unit-organisasi'])
            ->with('success', 'Unit organisasi baru berhasil ditambahkan.');
    }

    public function unitUpdate(Request $request, int $id): RedirectResponse
    {
        $this->checkAccess();

        $unit = Unit::findOrFail($id);

        $request->validate([
            'nama_unit' => 'required|string|max:100',
            'kode_unit' => 'required|string|max:50',
            'level' => 'required|in:rektorat,fakultas,prodi,lembaga,upt,pusat',
            'id_parent' => 'nullable|exists:units,id_unit',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $unit->update([
            'nama_unit' => $request->nama_unit,
            'kode_unit' => trim($request->kode_unit),
            'level' => $request->level,
            'id_parent' => $request->id_parent ?: null,
            'status' => $request->status,
        ]);

        return redirect()->route('pengaturan.index', ['tab' => 'unit-organisasi'])
            ->with('success', 'Data unit organisasi berhasil diperbarui.');
    }

    public function unitDestroy(int $id): RedirectResponse
    {
        $this->checkAccess();

        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('pengaturan.index', ['tab' => 'unit-organisasi'])
            ->with('success', 'Unit organisasi berhasil dihapus.');
    }

    /* ──────────────── Klasifikasi / Sifat Surat ──────────────── */

    public function sifatStore(Request $request): RedirectResponse
    {
        $this->checkAccess();

        $request->validate([
            'kode_sifat' => 'required|string|max:10|unique:sifat_surat,kode_sifat',
            'nama_sifat' => 'required|string|max:50',
            'warna_tinta' => 'required|in:hitam,merah',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $warna = $request->warna_tinta;
        $badgeBg = $warna === 'merah' ? '#fee2e2' : '#f1f5f9';
        $badgeText = $warna === 'merah' ? '#991b1b' : '#475569';

        SifatSurat::create([
            'kode_sifat' => strtoupper(trim($request->kode_sifat)),
            'nama_sifat' => trim($request->nama_sifat),
            'warna_tinta' => $warna,
            'badge_bg' => $badgeBg,
            'badge_text' => $badgeText,
            'keterangan' => $request->keterangan,
            'aktif' => true,
        ]);

        return redirect()->route('pengaturan.index', ['tab' => 'klasifikasi-sifat'])
            ->with('success', 'Klasifikasi keamanan / sifat surat berhasil ditambahkan.');
    }

    public function sifatUpdate(Request $request, int $id): RedirectResponse
    {
        $this->checkAccess();

        $sifat = SifatSurat::findOrFail($id);

        $request->validate([
            'kode_sifat' => 'required|string|max:10|unique:sifat_surat,kode_sifat,'.$id.',id_sifat',
            'nama_sifat' => 'required|string|max:50',
            'warna_tinta' => 'required|in:hitam,merah',
            'keterangan' => 'nullable|string|max:255',
            'aktif' => 'nullable|boolean',
        ]);

        $warna = $request->warna_tinta;
        $badgeBg = $warna === 'merah' ? '#fee2e2' : '#f1f5f9';
        $badgeText = $warna === 'merah' ? '#991b1b' : '#475569';

        $sifat->update([
            'kode_sifat' => strtoupper(trim($request->kode_sifat)),
            'nama_sifat' => trim($request->nama_sifat),
            'warna_tinta' => $warna,
            'badge_bg' => $badgeBg,
            'badge_text' => $badgeText,
            'keterangan' => $request->keterangan,
            'aktif' => $request->has('aktif') ? (bool) $request->aktif : $sifat->aktif,
        ]);

        return redirect()->route('pengaturan.index', ['tab' => 'klasifikasi-sifat'])
            ->with('success', 'Data klasifikasi / sifat surat berhasil diperbarui.');
    }

    public function sifatDestroy(int $id): RedirectResponse
    {
        $this->checkAccess();

        $sifat = SifatSurat::findOrFail($id);
        $sifat->delete();

        return redirect()->route('pengaturan.index', ['tab' => 'klasifikasi-sifat'])
            ->with('success', 'Klasifikasi / sifat surat berhasil dihapus.');
    }
}
