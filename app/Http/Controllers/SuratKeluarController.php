<?php

namespace App\Http\Controllers;

use App\Models\AccSuratKeluar;
use App\Models\FormatNomor;
use App\Models\JenisSurat;
use App\Models\KategoriSurat;
use App\Models\SuratKeluar;
use App\Models\Unit;
use App\Services\ArsitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = SuratKeluar::with(['unit', 'kategori', 'jenisSurat', 'pembuat', 'nomorSurat', 'persetujuan']);

        if (! $user->isAdminRektorat() && $user->id_unit) {
            $query->where('id_unit', $user->id_unit);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('perihal', 'like', "%{$q}%")
                    ->orWhere('tujuan', 'like', "%{$q}%")
                    ->orWhereHas('nomorSurat', function ($nq) use ($q) {
                        $nq->where('nomor_surat', 'like', "%{$q}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        } elseif ($request->filled('status_surat')) {
            $query->where('status_surat', $request->status_surat);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_surat_id', $request->jenis);
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $suratKeluar = $query->latest('id_surat_keluar')->paginate(10)->withQueryString();
        $kategoriList = KategoriSurat::all();
        $jenisList = JenisSurat::orderBy('nama_jenis', 'asc')->get();

        return view('surat_keluar.index', compact('suratKeluar', 'kategoriList', 'jenisList'));
    }

    public function create()
    {
        $user = Auth::user();
        $kategoriList = KategoriSurat::all();
        $jenisList = JenisSurat::all();
        $unitList = Unit::all();

        return view('surat_keluar.create', compact('kategoriList', 'jenisList', 'unitList', 'user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $defaultUnitId = $user->id_unit ?: 1;

        $request->validate([
            'id_unit' => 'nullable|exists:units,id_unit',
            'tujuan_unit_id' => 'nullable|exists:units,id_unit',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'jenis_surat_id' => 'required|exists:jenis_surat,id_jenis_surat',
            'sifat' => 'required|in:biasa,penting,rahasia',
            'tujuan' => 'required|string|max:255',
            'lampiran' => 'nullable|string|max:100',
            'perihal' => 'required|string|max:255',
            'isi_surat' => 'required|string',
            'tembusan' => 'nullable|string',
            'tanggal_draft' => 'required|date',
            'file_draft' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $fileName = null;
        if ($request->hasFile('file_draft')) {
            $file = $request->file('file_draft');
            $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/surat_keluar'), $fileName);
        }

        $statusAwal = $request->submit_action === 'ajukan' ? 'menunggu_acc' : 'draft';
        $unitId = $user->isAdminRektorat() && $request->filled('id_unit') ? $request->id_unit : $defaultUnitId;

        $surat = SuratKeluar::create([
            'id_unit' => $unitId,
            'tujuan_unit_id' => $request->tujuan_unit_id,
            'kategori_id' => $request->kategori_id,
            'jenis_surat_id' => $request->jenis_surat_id,
            'sifat' => $request->sifat,
            'tujuan' => $request->tujuan,
            'lampiran' => $request->lampiran ?: '-',
            'perihal' => $request->perihal,
            'isi_surat' => $request->isi_surat,
            'tembusan' => $request->tembusan,
            'tanggal_draft' => $request->tanggal_draft,
            'file_draft' => $fileName,
            'status_surat' => $statusAwal,
            'dibuat_oleh' => $user->id_user,
        ]);

        if ($request->submit_action === 'ajukan') {
            $totalTahap = ArsitaService::hitungTahapAcc($surat->unit);
            for ($i = 1; $i <= $totalTahap; $i++) {
                AccSuratKeluar::create([
                    'id_surat_keluar' => $surat->id_surat_keluar,
                    'tahap_ke' => $i,
                    'status' => 'menunggu',
                ]);
            }

            return redirect()->route('surat-keluar.show', $surat->id_surat_keluar)->with('success', "Draft surat keluar berhasil dibuat dan langsung diajukan ke pimpinan untuk ACC ({$totalTahap} tahap ACC).");
        }

        return redirect()->route('surat-keluar.show', $surat->id_surat_keluar)->with('success', 'Draft surat keluar berhasil dibuat.');
    }

    public function show($id)
    {
        $surat = SuratKeluar::with(['unit', 'tujuanUnit', 'kategori', 'jenisSurat', 'pembuat', 'nomorSurat', 'persetujuan.pemeriksa'])->findOrFail($id);
        $user = Auth::user();

        // Cek format nomor preview
        $formatNomor = FormatNomor::where('id_unit', $surat->id_unit)
            ->where('id_jenis_surat', $surat->jenis_surat_id)
            ->first();

        return view('surat_keluar.show', compact('surat', 'user', 'formatNomor'));
    }

    public function edit($id)
    {
        $surat = SuratKeluar::findOrFail($id);
        if (! in_array($surat->status_surat, ['draft', 'revisi'])) {
            return back()->with('error', 'Surat yang sudah diajukan atau disetujui tidak dapat diedit langsung.');
        }

        $kategoriList = KategoriSurat::all();
        $jenisList = JenisSurat::all();
        $unitList = Unit::all();

        return view('surat_keluar.edit', compact('surat', 'kategoriList', 'jenisList', 'unitList'));
    }

    public function update(Request $request, $id)
    {
        $surat = SuratKeluar::findOrFail($id);

        $request->validate([
            'id_unit' => 'nullable|exists:units,id_unit',
            'tujuan_unit_id' => 'nullable|exists:units,id_unit',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'jenis_surat_id' => 'required|exists:jenis_surat,id_jenis_surat',
            'sifat' => 'required|in:biasa,penting,rahasia',
            'tujuan' => 'required|string|max:255',
            'lampiran' => 'nullable|string|max:100',
            'perihal' => 'required|string|max:255',
            'isi_surat' => 'required|string',
            'tembusan' => 'nullable|string',
            'tanggal_draft' => 'required|date',
            'file_draft' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $data = $request->only([
            'tujuan_unit_id', 'kategori_id', 'jenis_surat_id', 'sifat', 'tujuan',
            'lampiran', 'perihal', 'isi_surat', 'tembusan', 'tanggal_draft',
        ]);

        if (Auth::user()->isAdminRektorat() && $request->filled('id_unit')) {
            $data['id_unit'] = $request->id_unit;
        }

        if ($request->hasFile('file_draft')) {
            $file = $request->file('file_draft');
            $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/surat_keluar'), $fileName);
            $data['file_draft'] = $fileName;
        }

        $surat->update($data);

        return redirect()->route('surat-keluar.show', $surat->id_surat_keluar)->with('success', 'Draft surat berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $surat = SuratKeluar::findOrFail($id);
        $surat->delete();

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil dihapus.');
    }

    public function ajukan($id)
    {
        $surat = SuratKeluar::with('unit')->findOrFail($id);
        $totalTahap = ArsitaService::hitungTahapAcc($surat->unit);

        // Bersihkan data acc lama jika sebelumnya ada revisi
        $surat->persetujuan()->delete();

        // Buat record ACC untuk tiap tahap
        for ($i = 1; $i <= $totalTahap; $i++) {
            AccSuratKeluar::create([
                'id_surat_keluar' => $surat->id_surat_keluar,
                'tahap_ke' => $i,
                'status' => 'menunggu',
            ]);
        }

        $surat->update(['status_surat' => 'menunggu_acc']);

        return back()->with('success', "Surat keluar berhasil diajukan untuk proses persetujuan ({$totalTahap} tahap ACC).");
    }

    public function cetak($id)
    {
        $surat = SuratKeluar::with(['unit', 'kategori', 'jenisSurat', 'nomorSurat', 'pembuat'])->findOrFail($id);

        return view('surat_keluar.cetak', compact('surat'));
    }
}
