<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\DisposisiTujuan;
use App\Models\SuratMasuk;
use App\Models\TindakLanjut;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisposisiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Disposisi masuk untuk user atau unit user
        $disposisiMasuk = DisposisiTujuan::with(['disposisi.suratMasuk', 'disposisi.pengirim', 'tindakLanjut'])
            ->where(function ($q) use ($user) {
                $q->where('ke_user_id', $user->id_user)
                    ->orWhere('ke_unit_id', $user->id_unit);
            })
            ->latest('id_disposisi_tujuan')
            ->paginate(10);

        return view('disposisi.index', compact('disposisiMasuk'));
    }

    public function riwayat()
    {
        $user = Auth::user();

        // Riwayat disposisi yang dikirimkan oleh user
        $riwayatKirim = Disposisi::with(['suratMasuk', 'tujuan.penerimaUser', 'tujuan.penerimaUnit'])
            ->where('dari_user_id', $user->id_user)
            ->latest('id_disposisi')
            ->paginate(10);

        return view('disposisi.riwayat', compact('riwayatKirim'));
    }

    public function create($surat_masuk_id)
    {
        $surat = SuratMasuk::findOrFail($surat_masuk_id);
        $unitList = Unit::where('status', 'aktif')->get();
        $userList = User::where('status', 'aktif')->where('id_user', '!=', Auth::user()->id_user)->get();

        return view('disposisi.create', compact('surat', 'unitList', 'userList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_surat_masuk' => 'required|exists:surat_masuk,id_surat_masuk',
            'instruksi' => 'required|string',
            'sifat' => 'required|in:biasa,segera,sangat_segera',
            'tenggat_waktu' => 'nullable|date',
            'catatan' => 'nullable|string',
            'tujuan_tipe' => 'required|in:individu,unit',
            'ke_user_id' => 'nullable|required_if:tujuan_tipe,individu|exists:users,id_user',
            'ke_unit_id' => 'nullable|required_if:tujuan_tipe,unit|exists:units,id_unit',
        ]);

        $disposisi = Disposisi::create([
            'id_surat_masuk' => $request->id_surat_masuk,
            'dari_user_id' => Auth::user()->id_user,
            'instruksi' => $request->instruksi,
            'sifat' => $request->sifat,
            'tenggat_waktu' => $request->tenggat_waktu,
            'catatan' => $request->catatan,
            'tanggal_kirim' => now(),
        ]);

        $disposisiTujuan = DisposisiTujuan::create([
            'id_disposisi' => $disposisi->id_disposisi,
            'tujuan_tipe' => $request->tujuan_tipe,
            'ke_user_id' => $request->tujuan_tipe === 'individu' ? $request->ke_user_id : null,
            'ke_unit_id' => $request->tujuan_tipe === 'unit' ? $request->ke_unit_id : null,
            'status' => 'belum_diproses',
        ]);

        // Update status surat masuk asal menjadi didisposisikan
        $suratAsal = SuratMasuk::with('unit')->findOrFail($request->id_surat_masuk);
        $suratAsal->update([
            'status_arsip' => 'didisposisikan',
        ]);

        // Logika Berjenjang Antarunit (sesuai alur asli ARSITA Readme 8.1):
        // Jika tujuan adalah UNIT, otomatis buat record surat masuk baru di unit bawahan tersebut!
        if ($request->tujuan_tipe === 'unit' && $request->ke_unit_id) {
            $unitAsalNama = $suratAsal->unit ? $suratAsal->unit->nama_unit : 'Unit Atasan';
            SuratMasuk::create([
                'no_surat_asal' => $suratAsal->no_surat_asal,
                'tanggal_surat' => $suratAsal->tanggal_surat,
                'tanggal_terima' => now()->toDateString(),
                'asal_surat' => $suratAsal->asal_surat.' (via '.$unitAsalNama.')',
                'perihal' => $suratAsal->perihal,
                'kategori_id' => $suratAsal->kategori_id,
                'jenis_surat_id' => $suratAsal->jenis_surat_id,
                'id_unit' => $request->ke_unit_id,
                'file_surat' => $suratAsal->file_surat,
                'keterangan' => 'Disposisi dari '.$unitAsalNama.'. Instruksi: '.$request->instruksi,
                'asal_disposisi_id' => $disposisiTujuan->id_disposisi_tujuan,
                'status_arsip' => 'belum_ditinjau',
                'dicatat_oleh' => Auth::user()->id_user,
            ]);
        }

        return redirect()->route('surat-masuk.show', $request->id_surat_masuk)->with('success', 'Disposisi elektronik berhasil dikirim.');
    }

    public function show($id)
    {
        $disposisiTujuan = DisposisiTujuan::with([
            'disposisi.suratMasuk.unit',
            'disposisi.pengirim',
            'tindakLanjut',
            'penerimaUser',
            'penerimaUnit',
        ])->findOrFail($id);

        return view('disposisi.show', compact('disposisiTujuan'));
    }

    public function tindakLanjut(Request $request, $id)
    {
        $disposisiTujuan = DisposisiTujuan::findOrFail($id);

        $request->validate([
            'catatan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'status' => 'required|in:diproses,selesai',
        ]);

        $fileName = null;
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/tindak_lanjut'), $fileName);
        }

        TindakLanjut::create([
            'id_disposisi_tujuan' => $disposisiTujuan->id_disposisi_tujuan,
            'catatan' => $request->catatan,
            'lampiran' => $fileName,
            'tanggal_update' => now(),
        ]);

        $disposisiTujuan->update(['status' => $request->status]);

        return back()->with('success', 'Laporan tindak lanjut disposisi berhasil disimpan.');
    }
}
