<?php

namespace App\Http\Controllers;

use App\Models\AccSuratKeluar;
use App\Models\DisposisiTujuan;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isRektorat = $user->isAdminRektorat() || ($user->unit && $user->unit->level === 'rektorat');
        $role = $user->role ? $user->role->nama_role : '';

        // Statistik
        $queryMasuk = SuratMasuk::query();
        $queryKeluar = SuratKeluar::query();

        if (! $isRektorat && $user->id_unit) {
            $queryMasuk->where('id_unit', $user->id_unit);
            $queryKeluar->where('id_unit', $user->id_unit);
        }

        $totalSuratMasuk = (clone $queryMasuk)->count();
        $suratMasukPerluTinjau = (clone $queryMasuk)->where('status_arsip', 'belum_ditinjau')->count();
        $totalSuratKeluar = (clone $queryKeluar)->count();

        // Disposisi menunggu tindak lanjut
        $disposisiMenunggu = DisposisiTujuan::where(function ($q) use ($user) {
            $q->where('ke_user_id', $user->id_user)
                ->orWhere(function ($sub) use ($user) {
                    $sub->where('tujuan_tipe', 'unit')->where('ke_unit_id', $user->id_unit);
                });
        })->whereIn('status', ['belum_diproses', 'diproses'])->count();

        // Jumlah surat keluar menunggu ACC: untuk pimpinan/admin, hitung ACC yang perlu tindakan mereka
        $suratKeluarMenungguAcc = 0;
        if (in_array($role, ['pimpinan', 'admin_rektorat'])) {
            $accCountQuery = AccSuratKeluar::where('status', 'menunggu');
            if (! $user->isAdminRektorat()) {
                $accCountQuery->where(function ($q) use ($user) {
                    $q->where('disetujui_oleh', $user->id_user)
                        ->orWhere(function ($sub) use ($user) {
                            $sub->whereNull('disetujui_oleh')
                                ->whereHas('suratKeluar', function ($sk) use ($user) {
                                    $sk->where('id_unit', $user->id_unit);
                                });
                        });
                });
            }
            $suratKeluarMenungguAcc = $accCountQuery->count();
        } else {
            $suratKeluarMenungguAcc = (clone $queryKeluar)->where('status_surat', 'menunggu_acc')->count();
        }

        // Daftar ACC yang menunggu untuk pimpinan / admin rektorat
        $pendingAccList = collect();
        if (in_array($role, ['pimpinan', 'admin_rektorat'])) {
            $accQuery = AccSuratKeluar::with(['suratKeluar.unit', 'suratKeluar.jenisSurat', 'suratKeluar.pembuat'])
                ->where('status', 'menunggu');

            if (! $user->isAdminRektorat()) {
                $accQuery->where(function ($q) use ($user) {
                    $q->where('disetujui_oleh', $user->id_user)
                        ->orWhere(function ($sub) use ($user) {
                            $sub->whereNull('disetujui_oleh')
                                ->whereHas('suratKeluar', function ($sk) use ($user) {
                                    $sk->where('id_unit', $user->id_unit);
                                });
                        });
                });
            }

            $pendingAccList = $accQuery->latest('id_acc')->limit(5)->get();
        }

        // Data terbaru
        $recentSuratMasuk = (clone $queryMasuk)->with(['kategori', 'unit'])->latest('id_surat_masuk')->limit(5)->get();
        $recentSuratKeluar = (clone $queryKeluar)->with(['kategori', 'unit', 'nomorSurat'])->latest('id_surat_keluar')->limit(5)->get();

        // Tugas disposisi aktif user
        $activeDisposisi = DisposisiTujuan::with(['disposisi.suratMasuk', 'disposisi.pengirim'])
            ->where(function ($q) use ($user) {
                $q->where('ke_user_id', $user->id_user)
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('tujuan_tipe', 'unit')->where('ke_unit_id', $user->id_unit);
                    });
            })
            ->latest('id_disposisi_tujuan')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'user',
            'totalSuratMasuk',
            'suratMasukPerluTinjau',
            'totalSuratKeluar',
            'suratKeluarMenungguAcc',
            'disposisiMenunggu',
            'recentSuratMasuk',
            'recentSuratKeluar',
            'activeDisposisi',
            'pendingAccList'
        ));
    }
}
