<?php

namespace App\Http\Controllers;

use App\Models\FormatNomor;
use App\Models\JenisSurat;
use App\Models\KategoriSurat;
use App\Models\Unit;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    // 1. Format Nomor
    public function formatNomorIndex()
    {
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
        $request->validate([
            'id_unit' => 'required|exists:units,id_unit',
            'id_jenis_surat' => 'required|exists:jenis_surat,id_jenis_surat',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'format_nomor' => 'required|string',
            'contoh' => 'nullable|string',
        ]);

        FormatNomor::updateOrCreate(
            [
                'id_unit' => $request->id_unit,
                'id_jenis_surat' => $request->id_jenis_surat,
                'kategori_id' => $request->kategori_id ?: null,
            ],
            [
                'format_nomor' => $request->format_nomor,
                'contoh' => $request->contoh,
                'aktif' => 1,
            ]
        );

        return back()->with('success', 'Format penomoran surat berhasil disimpan.');
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
            'level' => 'required|in:rektorat,fakultas,prodi',
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
