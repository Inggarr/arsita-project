<?php

namespace App\Http\Controllers;

use App\Mail\DisposisiMasukMail;
use App\Models\Disposisi;
use App\Models\DisposisiTujuan;
use App\Models\Notifikasi;
use App\Models\SuratMasuk;
use App\Models\TindakLanjut;
use App\Models\Unit;
use App\Models\User;
use App\Services\ArsitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $user = Auth::user();

        // Validasi: Surat masuk harus dibaca terlebih dahulu sebelum didisposisikan
        if (! $surat->dibaca_pada) {
            return redirect()->route('surat-masuk.show', $surat->id_surat_masuk)
                ->with('warning', 'Harap buka dan baca naskah surat masuk terlebih dahulu sebelum membuat lembar disposisi.');
        }

        // Ambil tujuan valid sesuai prinsip hierarki jabatan & unit
        $tujuanData = ArsitaService::getTujuanDisposisiValid($user);
        $unitList = $tujuanData['units'];
        $userList = $tujuanData['users'];
        $catatanAlur = $tujuanData['catatan_alur'];
        $levelPengirim = $tujuanData['level_pengirim'];

        return view('disposisi.create', compact('surat', 'unitList', 'userList', 'catatanAlur', 'levelPengirim'));
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
            'ke_user_ids' => 'nullable|required_if:tujuan_tipe,individu|array|min:1',
            'ke_user_ids.*' => 'exists:users,id_user',
            'ke_user_id' => 'nullable|exists:users,id_user',
            'ke_unit_ids' => 'nullable|required_if:tujuan_tipe,unit|array|min:1',
            'ke_unit_ids.*' => 'exists:units,id_unit',
            'ke_unit_id' => 'nullable|exists:units,id_unit',
        ]);

        $suratAsal = SuratMasuk::findOrFail($request->id_surat_masuk);
        if (! $suratAsal->dibaca_pada) {
            return redirect()->route('surat-masuk.show', $suratAsal->id_surat_masuk)
                ->with('error', 'Surat masuk belum dibaca. Silakan telaah isi surat terlebih dahulu sebelum mendisposisikan.');
        }

        $user = Auth::user();
        $tujuanData = ArsitaService::getTujuanDisposisiValid($user);

        // Kumpulkan unit target (bisa array ke_unit_ids atau fallback ke_unit_id)
        $targetUnitIds = [];
        if ($request->tujuan_tipe === 'unit') {
            if ($request->has('ke_unit_ids') && is_array($request->ke_unit_ids)) {
                $targetUnitIds = array_map('intval', $request->ke_unit_ids);
            } elseif ($request->filled('ke_unit_id')) {
                $targetUnitIds = [(int) $request->ke_unit_id];
            }

            if (empty($targetUnitIds)) {
                return back()->withInput()->with('error', 'Pilih minimal satu unit tujuan disposisi.');
            }

            // Validasi Hierarki Struktural untuk tiap unit tujuan
            $allowedUnitIds = $tujuanData['units']->pluck('id_unit')->toArray();

            // Prodi tidak memiliki unit bawahan — blokir disposisi unit dari level prodi
            if ($tujuanData['level_pengirim'] === 'prodi') {
                return back()->withInput()->with('error', 'Program Studi tidak dapat melakukan disposisi ke unit lain. Disposisi hanya dapat ditujukan ke individu internal Program Studi.');
            }

            foreach ($targetUnitIds as $targetUnitId) {
                if (! in_array($targetUnitId, $allowedUnitIds, true)) {
                    $targetUnit = Unit::find($targetUnitId);
                    $targetName = $targetUnit ? $targetUnit->nama_unit : 'Unit yang dipilih';

                    return back()->withInput()->with('error', "Unit {$targetName} tidak berada dalam cakupan kewenangan disposisi Anda.");
                }
            }
        }

        // 2. Kumpulkan & validasi user target jika tujuan INDIVIDU
        $targetUserIds = [];
        if ($request->tujuan_tipe === 'individu') {
            if ($request->has('ke_user_ids') && is_array($request->ke_user_ids)) {
                $targetUserIds = array_map('intval', $request->ke_user_ids);
            } elseif ($request->filled('ke_user_id')) {
                $targetUserIds = [(int) $request->ke_user_id];
            }

            if (empty($targetUserIds)) {
                return back()->withInput()->with('error', 'Pilih minimal satu pegawai tujuan disposisi.');
            }

            $allowedUserIds = $tujuanData['users']->pluck('id_user')->toArray();
            foreach ($targetUserIds as $targetUserId) {
                if (! in_array($targetUserId, $allowedUserIds, true)) {
                    return back()->withInput()->with('error', 'Satu atau lebih pegawai yang dipilih berada di luar hierarki kewenangan disposisi unit Anda.');
                }
            }
        }

        $disposisi = Disposisi::create([
            'id_surat_masuk' => $request->id_surat_masuk,
            'dari_user_id' => $user->id_user,
            'instruksi' => $request->instruksi,
            'sifat' => $request->sifat,
            'tenggat_waktu' => $request->tenggat_waktu,
            'catatan' => $request->catatan,
            'tanggal_kirim' => now(),
        ]);

        // Update status surat masuk asal menjadi didisposisikan
        $suratAsal = SuratMasuk::with('unit')->findOrFail($request->id_surat_masuk);
        $suratAsal->update([
            'status_arsip' => 'didisposisikan',
        ]);

        // Jika tujuan INDIVIDU (bisa satu atau lebih pegawai)
        if ($request->tujuan_tipe === 'individu' && ! empty($targetUserIds)) {
            foreach ($targetUserIds as $targetUserId) {
                $disposisiTujuan = DisposisiTujuan::create([
                    'id_disposisi' => $disposisi->id_disposisi,
                    'tujuan_tipe' => 'individu',
                    'ke_user_id' => $targetUserId,
                    'ke_unit_id' => null,
                    'status' => 'belum_diproses',
                ]);

                Notifikasi::create([
                    'user_id' => $targetUserId,
                    'sender_id' => $user->id_user,
                    'judul' => 'Disposisi Surat Masuk Baru',
                    'pesan' => "Anda menerima disposisi baru untuk surat '{$suratAsal->perihal}'. Instruksi: {$request->instruksi}",
                    'url' => route('disposisi.show', $disposisiTujuan->id_disposisi_tujuan),
                    'tipe' => 'info',
                    'is_read' => false,
                ]);

                // Kirim email ke penerima individu
                $penerimaUser = User::find($targetUserId);
                if ($penerimaUser && $penerimaUser->email) {
                    try {
                        Mail::to($penerimaUser->email)->send(new DisposisiMasukMail($suratAsal, $disposisi, $penerimaUser, $disposisiTujuan));
                    } catch (\Throwable $e) {
                        Log::error('Gagal mengirim email disposisi individu: '.$e->getMessage());
                    }
                }
            }
        }

        // Jika tujuan UNIT (bisa satu atau lebih dari satu unit)
        if ($request->tujuan_tipe === 'unit' && ! empty($targetUnitIds)) {
            $unitAsalNama = $suratAsal->unit ? $suratAsal->unit->nama_unit : 'Unit Pengirim';

            foreach ($targetUnitIds as $targetUnitId) {
                $disposisiTujuan = DisposisiTujuan::create([
                    'id_disposisi' => $disposisi->id_disposisi,
                    'tujuan_tipe' => 'unit',
                    'ke_user_id' => null,
                    'ke_unit_id' => $targetUnitId,
                    'status' => 'belum_diproses',
                ]);

                // Buat record surat masuk otomatis di setiap unit tujuan
                $suratMasukUnit = SuratMasuk::create([
                    'no_surat_asal' => $suratAsal->no_surat_asal,
                    'tanggal_surat' => $suratAsal->tanggal_surat,
                    'tanggal_terima' => now()->toDateString(),
                    'asal_surat' => $suratAsal->asal_surat.' (via '.$unitAsalNama.')',
                    'perihal' => $suratAsal->perihal,
                    'kategori_id' => $suratAsal->kategori_id,
                    'jenis_surat_id' => $suratAsal->jenis_surat_id,
                    'id_unit' => $targetUnitId,
                    'file_surat' => $suratAsal->file_surat,
                    'keterangan' => 'Disposisi dari '.$unitAsalNama.'. Instruksi: '.$request->instruksi,
                    'asal_disposisi_id' => $disposisiTujuan->id_disposisi_tujuan,
                    'status_arsip' => 'belum_ditinjau',
                    'dicatat_oleh' => $user->id_user,
                ]);

                // Kirim notifikasi real-time ke staf & pimpinan di unit tujuan tersebut
                $staffUnit = User::where('id_unit', $targetUnitId)
                    ->where('status', 'aktif')
                    ->whereHas('role', function ($r) {
                        $r->whereIn('nama_role', ['pimpinan', 'staff_tu', 'admin_unit']);
                    })->get();

                foreach ($staffUnit as $pegawai) {
                    Notifikasi::create([
                        'user_id' => $pegawai->id_user,
                        'sender_id' => $user->id_user,
                        'judul' => 'Surat Masuk via Disposisi '.$unitAsalNama,
                        'pesan' => "Unit Anda menerima lembar disposisi baru untuk perihal: '{$suratAsal->perihal}'",
                        'url' => route('surat-masuk.show', $suratMasukUnit->id_surat_masuk),
                        'tipe' => 'info',
                        'is_read' => false,
                    ]);
                }

                // Kirim email hanya ke pimpinan unit tujuan (role 4)
                $pimpinanUnit = User::where('id_unit', $targetUnitId)
                    ->where('id_role', 4)
                    ->first();

                if ($pimpinanUnit && $pimpinanUnit->email) {
                    try {
                        Mail::to($pimpinanUnit->email)->send(new DisposisiMasukMail($suratAsal, $disposisi, $pimpinanUnit, $disposisiTujuan));
                    } catch (\Throwable $e) {
                        Log::error('Gagal mengirim email disposisi unit: '.$e->getMessage());
                    }
                }
            }
        }

        $jumlahUnit = count($targetUnitIds);
        $jumlahUser = count($targetUserIds);
        $pesanSukses = $request->tujuan_tipe === 'unit'
            ? "Disposisi elektronik berhasil dikirim ke {$jumlahUnit} unit tujuan."
            : "Disposisi elektronik berhasil dikirim ke {$jumlahUser} pegawai tujuan.";

        return redirect()->route('surat-masuk.show', $request->id_surat_masuk)->with('success', $pesanSukses);
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

        $user = Auth::user();

        // Otorisasi: hanya penerima (user/unit), pengirim disposisi, atau admin rektorat yang boleh melihat
        $isPenerimaIndividu = $disposisiTujuan->ke_user_id === $user->id_user;
        $isPenerimaUnit = $disposisiTujuan->ke_unit_id && $disposisiTujuan->ke_unit_id === $user->id_unit;
        $isPengirim = $disposisiTujuan->disposisi && $disposisiTujuan->disposisi->dari_user_id === $user->id_user;

        if (! $user->isAdminRektorat() && ! $isPenerimaIndividu && ! $isPenerimaUnit && ! $isPengirim) {
            abort(403, 'Akses ditolak. Lembar disposisi ini bukan ditujukan untuk akun atau unit kerja Anda.');
        }

        return view('disposisi.show', compact('disposisiTujuan'));
    }

    public function tindakLanjut(Request $request, $id)
    {
        $disposisiTujuan = DisposisiTujuan::findOrFail($id);
        $user = Auth::user();

        // Otorisasi: hanya penerima (user/unit) atau admin rektorat yang berhak mengisi tindak lanjut
        $isPenerimaIndividu = $disposisiTujuan->ke_user_id === $user->id_user;
        $isPenerimaUnit = $disposisiTujuan->ke_unit_id && $disposisiTujuan->ke_unit_id === $user->id_unit;

        if (! $user->isAdminRektorat() && ! $isPenerimaIndividu && ! $isPenerimaUnit) {
            abort(403, 'Akses ditolak. Anda tidak memiliki kewenangan untuk menindaklanjuti lembar disposisi ini.');
        }

        $request->validate([
            'catatan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'status' => 'required|in:diproses,selesai',
        ]);

        $fileName = null;
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $extension = $file->extension() ?: 'pdf';
            $fileName = time().'_'.uniqid().'.'.$extension;
            $file->move(public_path('uploads/tindak_lanjut'), $fileName);
        }

        TindakLanjut::create([
            'id_disposisi_tujuan' => $disposisiTujuan->id_disposisi_tujuan,
            'catatan' => $request->catatan,
            'lampiran' => $fileName,
            'tanggal_update' => now(),
        ]);

        $disposisiTujuan->update(['status' => $request->status]);

        // Tandai notifikasi terkait disposisi ini sebagai sudah dibaca (karena sudah ditindaklanjuti)
        Notifikasi::where('url', 'like', '%disposisi/'.$disposisiTujuan->id_disposisi_tujuan.'%')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Laporan tindak lanjut disposisi berhasil disimpan.');
    }
}
