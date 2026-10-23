<?php

namespace App\Http\Controllers;

use App\Models\KategoriSurat;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArsipLaporanController extends Controller
{
    public function arsip(Request $request)
    {
        $user = Auth::user();
        $q = trim($request->get('q', ''));
        $tipe = $request->get('tipe', 'semua'); // 'semua', 'masuk', 'keluar'
        $kategori_id = $request->get('kategori', '');
        $tgl_mulai = $request->get('tgl_mulai', '');
        $tgl_selesai = $request->get('tgl_selesai', '');

        $results = [];

        // 1. Cari Surat Masuk
        if ($tipe === 'semua' || $tipe === 'masuk') {
            $querySM = SuratMasuk::with(['kategori', 'unit']);

            if (! $user->isAdminRektorat() && $user->id_unit) {
                $querySM->where('id_unit', $user->id_unit);
            }

            if ($q !== '') {
                $querySM->where(function ($w) use ($q) {
                    $w->where('no_surat_asal', 'like', "%{$q}%")
                        ->orWhere('perihal', 'like', "%{$q}%")
                        ->orWhere('asal_surat', 'like', "%{$q}%");
                });
            }

            if ($kategori_id !== '') {
                $querySM->where('kategori_id', $kategori_id);
            }

            if ($tgl_mulai !== '') {
                $querySM->where('tanggal_terima', '>=', $tgl_mulai);
            }

            if ($tgl_selesai !== '') {
                $querySM->where('tanggal_terima', '<=', $tgl_selesai);
            }

            $listSM = $querySM->get();
            foreach ($listSM as $sm) {
                $results[] = [
                    'tipe_surat' => 'masuk',
                    'id_ref' => $sm->id_surat_masuk,
                    'nomor_tampil' => $sm->no_surat_asal,
                    'perihal' => $sm->perihal,
                    'pihak_terkait' => $sm->asal_surat,
                    'tanggal_dokumen' => $sm->tanggal_terima,
                    'nama_kategori' => $sm->kategori?->nama_kategori ?? 'Umum',
                    'status_tampil' => $sm->status_arsip,
                    'nama_unit' => $sm->unit?->nama_unit ?? '-',
                    'file_surat' => $sm->file_surat,
                ];
            }
        }

        // 2. Cari Surat Keluar
        if ($tipe === 'semua' || $tipe === 'keluar') {
            $querySK = SuratKeluar::with(['kategori', 'unit', 'nomorSurat']);

            if (! $user->isAdminRektorat() && $user->id_unit) {
                $querySK->where('id_unit', $user->id_unit);
            }

            if ($q !== '') {
                $querySK->where(function ($w) use ($q) {
                    $w->where('perihal', 'like', "%{$q}%")
                        ->orWhere('tujuan', 'like', "%{$q}%")
                        ->orWhereHas('nomorSurat', function ($nq) use ($q) {
                            $nq->where('nomor_surat', 'like', "%{$q}%");
                        });
                });
            }

            if ($kategori_id !== '') {
                $querySK->where('kategori_id', $kategori_id);
            }

            if ($tgl_mulai !== '') {
                $querySK->where('tanggal_draft', '>=', $tgl_mulai);
            }

            if ($tgl_selesai !== '') {
                $querySK->where('tanggal_draft', '<=', $tgl_selesai);
            }

            $listSK = $querySK->get();
            foreach ($listSK as $sk) {
                $results[] = [
                    'tipe_surat' => 'keluar',
                    'id_ref' => $sk->id_surat_keluar,
                    'nomor_tampil' => $sk->nomorSurat?->nomor_surat ?? '[Draft / Menunggu ACC]',
                    'perihal' => $sk->perihal,
                    'pihak_terkait' => $sk->tujuan,
                    'tanggal_dokumen' => $sk->tanggal_draft,
                    'nama_kategori' => $sk->kategori?->nama_kategori ?? 'Umum',
                    'status_tampil' => $sk->status_surat,
                    'nama_unit' => $sk->unit?->nama_unit ?? '-',
                    'file_surat' => null,
                ];
            }
        }

        // Urutkan gabungan berdasarkan tanggal dokumen terbaru
        usort($results, function ($a, $b) {
            return strtotime($b['tanggal_dokumen']) - strtotime($a['tanggal_dokumen']);
        });

        $kategoriList = KategoriSurat::orderBy('nama_kategori', 'asc')->get();

        return view('arsip.index', compact('results', 'kategoriList', 'q', 'tipe', 'kategori_id', 'tgl_mulai', 'tgl_selesai'));
    }

    public function laporan(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->get('start_date', date('Y-01-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $queryM = SuratMasuk::with(['unit', 'kategori'])
            ->whereBetween('tanggal_terima', [$startDate, $endDate]);

        $queryK = SuratKeluar::with(['unit', 'kategori', 'nomorSurat'])
            ->whereBetween('tanggal_draft', [$startDate, $endDate]);

        if (! $user->isAdminRektorat() && $user->id_unit) {
            $queryM->where('id_unit', $user->id_unit);
            $queryK->where('id_unit', $user->id_unit);
        }

        $listMasuk = $queryM->get();
        $listKeluar = $queryK->get();

        return view('laporan.index', compact('listMasuk', 'listKeluar', 'startDate', 'endDate'));
    }
}
