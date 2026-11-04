<?php

namespace App\Http\Controllers;

use App\Models\FormatNomor;
use App\Models\JenisSurat;
use App\Models\KategoriSurat;
use App\Models\Unit;
use App\Services\ArsitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterDataController extends Controller
{
    private function checkStaffTu(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isStaffTu()) {
            abort(403, 'Akses ditolak. Fitur penomoran surat hanya dapat diakses oleh Staff TU.');
        }
    }

    // 1. Format Nomor
    public function formatNomorIndex()
    {
        $this->checkStaffTu();

        $formatList = FormatNomor::with(['unit', 'jenisSurat', 'kategori'])->get();
        $units = Unit::all();
        $unitList = $units;
        $jenisList = JenisSurat::all();
        $kategoriList = KategoriSurat::all();
        $activeTab = 'format';

        return view('master_data.index', compact('formatList', 'units', 'unitList', 'jenisList', 'kategoriList', 'activeTab'));
    }

    public function formatNomorStore(Request $request)
    {
        $this->checkStaffTu();

        $request->validate([
            'id_unit' => 'required|exists:units,id_unit',
            'id_jenis_surat' => 'required|exists:jenis_surat,id_jenis_surat',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'format_nomor' => 'required|string|max:100',
            'contoh' => 'nullable|string|max:100',
        ]);

        FormatNomor::create([
            'id_unit' => $request->id_unit,
            'id_jenis_surat' => $request->id_jenis_surat,
            'kategori_id' => $request->kategori_id ?: null,
            'format_nomor' => $request->format_nomor,
            'contoh' => $request->contoh,
            'nomor_terakhir' => 0,
            'aktif' => 1,
        ]);

        return back()->with('success', 'Format penomoran surat berhasil ditambahkan.');
    }

    public function formatNomorUpdate(Request $request, $id)
    {
        $this->checkStaffTu();

        $format = FormatNomor::findOrFail($id);

        $request->validate([
            'id_unit' => 'required|exists:units,id_unit',
            'id_jenis_surat' => 'required|exists:jenis_surat,id_jenis_surat',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'format_nomor' => 'required|string|max:100',
            'contoh' => 'nullable|string|max:100',
            'aktif' => 'nullable|in:0,1',
        ]);

        $format->update([
            'id_unit' => $request->id_unit,
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

        return back()->with('success', $pesan);
    }

    // 2. Unit
    public function unitIndex()
    {
        $formatList = FormatNomor::with(['unit', 'jenisSurat', 'kategori'])->get();
        $units = Unit::with('parent')->get();
        $unitList = $units;
        $jenisList = JenisSurat::all();
        $kategoriList = KategoriSurat::all();
        $activeTab = 'unit';

        return view('master_data.index', compact('formatList', 'units', 'unitList', 'jenisList', 'kategoriList', 'activeTab'));
    }

    public function unitStore(Request $request)
    {
        $request->validate([
            'nama_unit' => 'required|string|max:100',
            'kode_unit' => 'required|string|max:20',
            'level' => 'required|in:rektorat,fakultas,prodi,lembaga,upt,pusat',
            'id_parent' => 'nullable|exists:units,id_unit',
        ]);

        Unit::create($request->all());

        return back()->with('success', 'Unit baru berhasil ditambahkan.');
    }

    // 3. Kategori Surat
    public function kategoriIndex()
    {
        $formatList = FormatNomor::with(['unit', 'jenisSurat', 'kategori'])->get();
        $units = Unit::all();
        $unitList = $units;
        $jenisList = JenisSurat::all();
        $kategoriList = KategoriSurat::all();
        $activeTab = 'kategori';

        return view('master_data.index', compact('formatList', 'units', 'unitList', 'jenisList', 'kategoriList', 'activeTab'));
    }

    public function kategoriStore(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        KategoriSurat::create($request->all());

        return back()->with('success', 'Kategori surat berhasil ditambahkan.');
    }

    // 4. Jenis Surat
    public function jenisIndex()
    {
        $formatList = FormatNomor::with(['unit', 'jenisSurat', 'kategori'])->get();
        $units = Unit::all();
        $unitList = $units;
        $jenisList = JenisSurat::all();
        $kategoriList = KategoriSurat::all();
        $activeTab = 'jenis';

        return view('master_data.index', compact('formatList', 'units', 'unitList', 'jenisList', 'kategoriList', 'activeTab'));
    }

    public function jenisStore(Request $request)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:100',
            'kode_jenis' => 'required|string|max:20',
            'keterangan' => 'nullable|string|max:255',
        ]);

        JenisSurat::create($request->all());

        return back()->with('success', 'Jenis surat berhasil ditambahkan.');
    }

    public function formatNomorDestroy($id)
    {
        $this->checkStaffTu();

        FormatNomor::findOrFail($id)->delete();

        return back()->with('success', 'Format nomor berhasil dihapus.');
    }

    public function unitDestroy($id)
    {
        Unit::findOrFail($id)->delete();

        return back()->with('success', 'Unit berhasil dihapus.');
    }

    public function kategoriDestroy($id)
    {
        KategoriSurat::findOrFail($id)->delete();

        return back()->with('success', 'Kategori surat berhasil dihapus.');
    }

    public function jenisDestroy($id)
    {
        JenisSurat::findOrFail($id)->delete();

        return back()->with('success', 'Jenis surat berhasil dihapus.');
    }
}
