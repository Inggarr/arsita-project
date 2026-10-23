<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\KategoriSurat;
use App\Models\SuratMasuk;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = SuratMasuk::with(['unit', 'kategori', 'jenisSurat', 'pencatat', 'disposisi']);

        // Scope filter unit jika bukan admin_rektorat
        if (! $user->isAdminRektorat() && $user->id_unit) {
            $query->where('id_unit', $user->id_unit);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('no_surat_asal', 'like', "%{$q}%")
                    ->orWhere('perihal', 'like', "%{$q}%")
                    ->orWhere('asal_surat', 'like', "%{$q}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        } elseif ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('status')) {
            $query->where('status_arsip', $request->status);
        } elseif ($request->filled('status_arsip')) {
            $query->where('status_arsip', $request->status_arsip);
        }

        $suratMasuk = $query->latest('id_surat_masuk')->paginate(10)->withQueryString();
        $kategoriList = KategoriSurat::orderBy('nama_kategori', 'asc')->get();

        return view('surat_masuk.index', compact('suratMasuk', 'kategoriList'));
    }

    public function create()
    {
        $kategoriList = KategoriSurat::all();
        $jenisList = JenisSurat::all();
        $unitList = Unit::all();

        return view('surat_masuk.create', compact('kategoriList', 'jenisList', 'unitList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_surat_asal' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'required|date',
            'asal_surat' => 'required|string|max:150',
            'perihal' => 'required|string|max:255',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'jenis_surat_id' => 'nullable|exists:jenis_surat,id_jenis_surat',
            'id_unit' => 'required|exists:units,id_unit',
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'keterangan' => 'nullable|string',
        ]);

        $fileName = null;
        if ($request->hasFile('file_surat')) {
            $file = $request->file('file_surat');
            $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/surat_masuk'), $fileName);
        }

        SuratMasuk::create([
            'no_surat_asal' => $request->no_surat_asal,
            'tanggal_surat' => $request->tanggal_surat,
            'tanggal_terima' => $request->tanggal_terima,
            'asal_surat' => $request->asal_surat,
            'perihal' => $request->perihal,
            'kategori_id' => $request->kategori_id,
            'jenis_surat_id' => $request->jenis_surat_id,
            'id_unit' => $request->id_unit,
            'file_surat' => $fileName,
            'keterangan' => $request->keterangan,
            'status_arsip' => 'belum_ditinjau',
            'dicatat_oleh' => Auth::user()->id_user,
        ]);

        return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil dicatat ke sistem.');
    }

    public function show($id)
    {
        $surat = SuratMasuk::with(['unit', 'kategori', 'jenisSurat', 'pencatat', 'disposisi.tujuan.penerimaUser', 'disposisi.tujuan.penerimaUnit', 'disposisi.pengirim'])->findOrFail($id);
        $usersUnit = User::where('status', 'aktif')->get();
        $unitList = Unit::where('status', 'aktif')->get();

        return view('surat_masuk.show', compact('surat', 'usersUnit', 'unitList'));
    }

    public function edit($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        // Surat hasil disposisi tidak boleh diedit oleh unit penerima
        if ($surat->asal_disposisi_id) {
            return back()->with('error', 'Surat masuk ini merupakan naskah dinas hasil disposisi atasan dan bersifat resmi sehingga tidak dapat diedit.');
        }

        $kategoriList = KategoriSurat::all();
        $jenisList = JenisSurat::all();
        $unitList = Unit::all();

        return view('surat_masuk.edit', compact('surat', 'kategoriList', 'jenisList', 'unitList'));
    }

    public function update(Request $request, $id)
    {
        $surat = SuratMasuk::findOrFail($id);

        // Surat hasil disposisi tidak boleh diedit oleh unit penerima
        if ($surat->asal_disposisi_id) {
            return back()->with('error', 'Surat masuk ini merupakan naskah dinas hasil disposisi atasan dan tidak dapat diedit.');
        }

        $request->validate([
            'no_surat_asal' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'required|date',
            'asal_surat' => 'required|string|max:150',
            'perihal' => 'required|string|max:255',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'jenis_surat_id' => 'nullable|exists:jenis_surat,id_jenis_surat',
            'id_unit' => 'required|exists:units,id_unit',
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'keterangan' => 'nullable|string',
        ]);

        $data = $request->only([
            'no_surat_asal', 'tanggal_surat', 'tanggal_terima', 'asal_surat',
            'perihal', 'kategori_id', 'jenis_surat_id', 'id_unit', 'keterangan',
        ]);

        if ($request->hasFile('file_surat')) {
            $file = $request->file('file_surat');
            $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/surat_masuk'), $fileName);
            $data['file_surat'] = $fileName;
        }

        $surat->update($data);

        return redirect()->route('surat-masuk.show', $surat->id_surat_masuk)->with('success', 'Data surat masuk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        // Surat hasil disposisi tidak boleh dihapus oleh unit penerima
        if ($surat->asal_disposisi_id) {
            return back()->with('error', 'Surat masuk ini merupakan dokumen disposisi resmi dari atasan dan tidak dapat dihapus.');
        }

        $surat->delete();

        return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil dihapus.');
    }

    public function arsipkan($id)
    {
        $surat = SuratMasuk::findOrFail($id);
        $surat->update(['status_arsip' => 'diarsipkan']);

        return back()->with('success', 'Surat berhasil diarsipkan.');
    }
}
