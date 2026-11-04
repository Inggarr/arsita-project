<?php

namespace App\Http\Controllers;

use App\Mail\SuratMasukBaruMail;
use App\Models\JenisSurat;
use App\Models\KategoriSurat;
use App\Models\Notifikasi;
use App\Models\PengaturanSurat;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $user = Auth::user();
        $kategoriList = KategoriSurat::all();
        $jenisList = JenisSurat::all();
        // Hanya admin rektorat yang perlu melihat daftar semua unit
        $unitList = $user->isAdminRektorat() ? Unit::orderBy('nama_unit')->get() : collect();
        $isAdminRektorat = $user->isAdminRektorat();

        return view('surat_masuk.create', compact('kategoriList', 'jenisList', 'unitList', 'isAdminRektorat'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Tentukan id_unit: admin rektorat pilih manual, user lain pakai unit sendiri
        $idUnit = $user->isAdminRektorat()
            ? $request->id_unit
            : $user->id_unit;

        $request->validate([
            'no_surat_asal' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'required|date',
            'asal_surat' => 'required|string|max:150',
            'perihal' => 'required|string|max:255',
            'kategori_id' => 'nullable|exists:kategori_surat,id_kategori',
            'jenis_surat_id' => 'nullable|exists:jenis_surat,id_jenis_surat',
            'id_unit' => $user->isAdminRektorat() ? 'required|exists:units,id_unit' : 'nullable',
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'keterangan' => 'nullable|string',
        ]);

        // Pastikan user non-admin punya unit
        if (! $user->isAdminRektorat() && ! $idUnit) {
            return back()->withErrors(['id_unit' => 'Akun Anda belum terhubung ke unit manapun. Hubungi Admin.'])->withInput();
        }

        $fileName = null;
        if ($request->hasFile('file_surat')) {
            $file = $request->file('file_surat');
            $extension = $file->extension() ?: 'pdf';
            $fileName = time().'_'.uniqid().'.'.$extension;
            $file->move(public_path('uploads/surat_masuk'), $fileName);
        }

        $suratMasuk = SuratMasuk::create([
            'no_surat_asal' => $request->no_surat_asal,
            'tanggal_surat' => $request->tanggal_surat,
            'tanggal_terima' => $request->tanggal_terima,
            'asal_surat' => $request->asal_surat,
            'perihal' => $request->perihal,
            'kategori_id' => $request->kategori_id,
            'jenis_surat_id' => $request->jenis_surat_id,
            'id_unit' => $idUnit,
            'file_surat' => $fileName,
            'keterangan' => $request->keterangan,
            'status_arsip' => 'belum_ditinjau',
            'dicatat_oleh' => $user->id_user,
        ]);

        // Kirim notifikasi email ke pimpinan unit terkait
        $this->kirimEmailSuratMasukBaru($suratMasuk->load('unit'));

        return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil dicatat ke sistem.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $surat = SuratMasuk::with(['unit', 'kategori', 'jenisSurat', 'pencatat', 'pembaca', 'disposisi.tujuan.penerimaUser', 'disposisi.tujuan.penerimaUnit', 'disposisi.pengirim'])->findOrFail($id);
        $this->authorize('view', $surat);

        // Jika user adalah anggota unit penerima dan surat belum ditandai dibaca
        if ($user && ($user->isAdminRektorat() || ($user->id_unit && $user->id_unit == $surat->id_unit))) {
            if (! $surat->dibaca_pada) {
                $surat->update([
                    'dibaca_pada' => now(),
                    'dibaca_oleh' => $user->id_user,
                ]);

                // Update juga status pada surat keluar asal jika ada
                $skAsal = $surat->suratKeluarAsal;
                if (! $skAsal && $surat->no_surat_asal) {
                    $skAsal = SuratKeluar::whereHas('nomorSurat', function ($q) use ($surat) {
                        $q->where('nomor_surat', $surat->no_surat_asal);
                    })->first();
                }

                if ($skAsal && in_array($skAsal->status_penerimaan, ['terkirim', null])) {
                    $skAsal->update([
                        'status_penerimaan' => 'sudah_dibaca',
                        'diterima_pada' => now(),
                        'diterima_oleh' => $user->id_user,
                    ]);
                }
            }
        }

        $usersUnit = User::where('status', 'aktif')->get();
        $unitList = Unit::where('status', 'aktif')->get();
        $kop = PengaturanSurat::getKop();
        $format = PengaturanSurat::getFormat();

        return view('surat_masuk.show', compact('surat', 'usersUnit', 'unitList', 'kop', 'format'));
    }

    public function edit($id)
    {
        $user = Auth::user();

        if (! $user->isAdminRektorat() && ! $user->isStaffTu() && ! $user->isAdminUnit()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengedit surat masuk.');
        }

        $surat = SuratMasuk::findOrFail($id);
        $this->authorize('update', $surat);

        if (! $surat->canBeEditedBy($user)) {
            return back()->with('error', 'Surat masuk ini berasal dari unit lain/disposisi resmi sehingga pihak penerima tidak dapat mengubah data surat.');
        }

        $kategoriList = KategoriSurat::all();
        $jenisList = JenisSurat::all();
        $unitList = Unit::all();

        return view('surat_masuk.edit', compact('surat', 'kategoriList', 'jenisList', 'unitList'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (! $user->isAdminRektorat() && ! $user->isStaffTu() && ! $user->isAdminUnit()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengedit surat masuk.');
        }

        $surat = SuratMasuk::findOrFail($id);
        $this->authorize('update', $surat);

        if (! $surat->canBeEditedBy($user)) {
            return back()->with('error', 'Surat masuk ini berasal dari unit lain/disposisi resmi sehingga pihak penerima tidak dapat mengubah data surat.');
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
            $extension = $file->extension() ?: 'pdf';
            $fileName = time().'_'.uniqid().'.'.$extension;
            $file->move(public_path('uploads/surat_masuk'), $fileName);
            $data['file_surat'] = $fileName;
        }

        $surat->update($data);

        return redirect()->route('surat-masuk.show', $surat->id_surat_masuk)->with('success', 'Data surat masuk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if (! $user->isAdminRektorat() && ! $user->isStaffTu() && ! $user->isAdminUnit()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus surat masuk.');
        }

        $surat = SuratMasuk::findOrFail($id);
        $this->authorize('delete', $surat);

        // Surat hasil disposisi atau surat dari unit lain tidak boleh dihapus penerima
        if (! $surat->canBeEditedBy($user)) {
            return back()->with('error', 'Surat masuk ini berasal dari unit lain/disposisi resmi sehingga pihak penerima tidak dapat menghapusnya.');
        }

        $surat->delete();

        return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil dihapus.');
    }

    public function arsipkan($id)
    {
        $surat = SuratMasuk::findOrFail($id);
        $surat->update(['status_arsip' => 'diarsipkan']);

        // Jika surat diarsipkan, berarti urusan telah selesai/ditindaklanjuti.
        // Hapus status belum dibaca pada semua notifikasi yang mengarah ke surat masuk ini.
        Notifikasi::where('url', 'like', '%surat-masuk/'.$surat->id_surat_masuk.'%')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Surat berhasil diarsipkan.');
    }

    /**
     * Kirim notifikasi email ke pimpinan unit saat ada surat masuk baru.
     */
    private function kirimEmailSuratMasukBaru(SuratMasuk $suratMasuk): void
    {
        try {
            if (! $suratMasuk->id_unit) {
                return;
            }

            // Cari pimpinan unit (role id 4 = pimpinan)
            $pimpinan = User::where('id_unit', $suratMasuk->id_unit)
                ->where('id_role', 4)
                ->first();

            if (! $pimpinan || ! $pimpinan->email) {
                return;
            }

            Mail::to($pimpinan->email)->send(new SuratMasukBaruMail($suratMasuk, $pimpinan));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email surat masuk baru: '.$e->getMessage());
        }
    }
}
