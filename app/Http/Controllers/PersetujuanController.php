<?php

namespace App\Http\Controllers;

use App\Models\AccSuratKeluar;
use App\Models\Notifikasi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Services\ArsitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class PersetujuanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->nama_role : '';

        // Antrean surat yang menunggu ACC oleh user ini
        $query = AccSuratKeluar::with(['suratKeluar.unit', 'suratKeluar.jenisSurat', 'suratKeluar.pembuat', 'suratKeluar.nomorSurat'])
            ->where('status', 'menunggu');

        if (! $user->isAdminRektorat()) {
            $query->where(function ($q) use ($user) {
                // Surat yang ditugaskan khusus ke user ini
                $q->where('disetujui_oleh', $user->id_user)
                    // Atau surat yang belum ditugaskan (disetujui_oleh is null) dan berasal dari unit pimpinan ini
                    ->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('disetujui_oleh')
                            ->whereHas('suratKeluar', function ($sk) use ($user) {
                                $sk->where('id_unit', $user->id_unit);
                            });
                    });
            });
        }

        $daftarAcc = $query->orderBy('id_acc', 'asc')->get();

        // Riwayat persetujuan terakhir
        $riwayatQuery = AccSuratKeluar::with(['suratKeluar.nomorSurat', 'suratKeluar.unit'])
            ->where('status', '!=', 'menunggu');

        if (! $user->isAdminRektorat()) {
            $riwayatQuery->where(function ($q) use ($user) {
                $q->where('disetujui_oleh', $user->id_user)
                    ->orWhereHas('suratKeluar', function ($sk) use ($user) {
                        $sk->where('id_unit', $user->id_unit);
                    });
            });
        }

        $riwayatAcc = $riwayatQuery->latest('tanggal')
            ->limit(10)
            ->get();

        return view('persetujuan.index', compact('daftarAcc', 'riwayatAcc'));
    }

    public function show($id)
    {
        $acc = AccSuratKeluar::with(['suratKeluar.unit', 'suratKeluar.jenisSurat', 'suratKeluar.pembuat', 'suratKeluar.nomorSurat', 'suratKeluar.persetujuan'])
            ->findOrFail($id);
        $user = Auth::user();

        return view('persetujuan.show', compact('acc', 'user'));
    }

    public function proses(Request $request, $id)
    {
        $request->validate([
            'aksi' => 'required|in:setuju,tolak',
            'catatan_penolakan' => 'nullable|string',
        ]);

        $surat = SuratKeluar::with(['unit', 'persetujuan'])->findOrFail($id);
        $user = Auth::user();

        // Cari tahap yang saat ini sedang menunggu
        $tahapAktif = $surat->persetujuan()->where('status', 'menunggu')->orderBy('tahap_ke', 'asc')->first();

        if (! $tahapAktif) {
            return back()->with('error', 'Tidak ada tahap persetujuan yang sedang aktif untuk surat ini.');
        }

        if ($request->aksi === 'tolak') {
            $catatan = $request->catatan_penolakan ?: 'Revisi diperlukan dari pimpinan.';
            $tahapAktif->update([
                'status' => 'ditolak',
                'disetujui_oleh' => $user->id_user,
                'catatan_penolakan' => $catatan,
                'tanggal' => now(),
            ]);

            $surat->update(['status_surat' => 'revisi']);

            // Berikan notifikasi langsung ke staf pembuat surat
            if ($surat->dibuat_oleh) {
                $pimpinanNama = $user->nama ?: $user->name ?: 'Pimpinan';
                Notifikasi::create([
                    'user_id' => $surat->dibuat_oleh,
                    'sender_id' => $user->id_user,
                    'judul' => 'Surat Keluar Perlu Revisi',
                    'pesan' => "Draft surat '{$surat->perihal}' ditolak oleh {$pimpinanNama}. Catatan: {$catatan}",
                    'url' => route('surat-keluar.show', $surat->id_surat_keluar),
                    'tipe' => 'revisi',
                    'is_read' => false,
                ]);
            }

            return redirect()->route('persetujuan.index')->with('success', 'Surat ditolak dan notifikasi revisi telah dikirimkan ke pembuat.');
        }

        // Jika disetujui
        $tahapAktif->update([
            'status' => 'disetujui',
            'disetujui_oleh' => $user->id_user,
            'tanggal' => now(),
        ]);

        // Cek apakah masih ada tahap berikutnya
        $sisaTahap = $surat->persetujuan()->where('status', 'menunggu')->count();

        if ($sisaTahap === 0) {
            // Semua tahap ACC selesai! Generate nomor otomatis sekarang
            $nomorSurat = ArsitaService::generateNomorSurat(
                $surat->id_surat_keluar,
                $surat->id_unit,
                $surat->jenis_surat_id,
                (int) date('Y'),
                $surat->kategori_id
            );

            // Berikan notifikasi sukses ke pembuat surat bahwa surat telah disetujui & nomor resmi terbit
            if ($surat->dibuat_oleh) {
                Notifikasi::create([
                    'user_id' => $surat->dibuat_oleh,
                    'sender_id' => $user->id_user,
                    'judul' => 'Surat Keluar Disetujui (ACC)',
                    'pesan' => "Surat '{$surat->perihal}' telah disetujui penuh. Nomor surat resmi: {$nomorSurat}.",
                    'url' => route('surat-keluar.show', $surat->id_surat_keluar),
                    'tipe' => 'success',
                    'is_read' => false,
                ]);
            }

            // Jika ditujukan ke unit internal, teruskan otomatis ke Surat Masuk unit tersebut
            if ($surat->tujuan_unit_id) {
                $fileMasuk = null;
                $sourceFile = $surat->file_final ?: $surat->file_draft;

                if ($sourceFile && File::exists(public_path('uploads/surat_keluar/'.$sourceFile))) {
                    $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
                    $fileMasuk = 'sm_'.time().'_'.uniqid().'.'.$ext;
                    if (! File::isDirectory(public_path('uploads/surat_masuk'))) {
                        File::makeDirectory(public_path('uploads/surat_masuk'), 0755, true);
                    }
                    File::copy(public_path('uploads/surat_keluar/'.$sourceFile), public_path('uploads/surat_masuk/'.$fileMasuk));
                }

                $asalUnitNama = $surat->unit ? $surat->unit->nama_unit : 'Internal UINSSC';

                SuratMasuk::create([
                    'no_surat_asal' => $nomorSurat,
                    'tanggal_surat' => $surat->tanggal_draft ?: now()->toDateString(),
                    'tanggal_terima' => now()->toDateString(),
                    'asal_surat' => $asalUnitNama,
                    'perihal' => $surat->perihal,
                    'kategori_id' => $surat->kategori_id,
                    'jenis_surat_id' => $surat->jenis_surat_id,
                    'id_unit' => $surat->tujuan_unit_id,
                    'file_surat' => $fileMasuk,
                    'keterangan' => 'Surat masuk otomatis dari Surat Keluar: '.$nomorSurat."\nIsi: ".$surat->isi_surat,
                    'status_arsip' => 'belum_ditinjau',
                    'dicatat_oleh' => $user->id_user,
                ]);

                $surat->update([
                    'status_surat' => 'terkirim',
                ]);

                return redirect()->route('persetujuan.index')->with('success', "Surat telah disetujui penuh! Nomor surat diterbitkan: {$nomorSurat}, dan surat telah otomatis masuk ke Surat Masuk unit tujuan.");
            }

            $surat->update([
                'status_surat' => 'disetujui',
            ]);

            return redirect()->route('persetujuan.index')->with('success', "Surat telah disetujui penuh pada tahap final! Nomor surat otomatis diterbitkan: {$nomorSurat}");
        }

        return redirect()->route('persetujuan.index')->with('success', "Tahap ke-{$tahapAktif->tahap_ke} berhasil disetujui. Melanjutkan ke tahap berikutnya.");
    }
}
