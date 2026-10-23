<?php

namespace App\Services;

use App\Models\FormatNomor;
use App\Models\JenisSurat;
use App\Models\NomorSurat;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ArsitaService
{
    /**
     * Format tanggal Indonesia
     */
    public static function tglIndo(mixed $tanggal): string
    {
        if (! $tanggal) {
            return '-';
        }

        // Handle Carbon / DateTimeInterface objects
        if ($tanggal instanceof \DateTimeInterface) {
            $tanggalStr = $tanggal->format('Y-m-d');
        } else {
            $tanggalStr = (string) $tanggal;
        }

        if ($tanggalStr === '0000-00-00') {
            return '-';
        }

        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $time = strtotime($tanggalStr);
        if (! $time) {
            return $tanggalStr;
        }

        $pecahkan = explode('-', date('Y-m-d', $time));

        return (int) $pecahkan[2].' '.($bulan[(int) $pecahkan[1]] ?? '').' '.$pecahkan[0];
    }

    /**
     * Badge status badge warna
     */
    public static function badgeStatus(string $status): string
    {
        $map = [
            // Status surat keluar
            'draft' => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => 'Draft'],
            'menunggu_acc' => ['bg' => '#fef3c7', 'text' => '#d97706', 'label' => 'Menunggu ACC'],
            'revisi' => ['bg' => '#fee2e2', 'text' => '#dc2626', 'label' => 'Revisi Diperlukan'],
            'disetujui' => ['bg' => '#dcfce7', 'text' => '#16a34a', 'label' => 'Disetujui'],
            'terkirim' => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'label' => 'Terkirim'],
            'diarsipkan' => ['bg' => '#e2e8f0', 'text' => '#334155', 'label' => 'Diarsipkan'],

            // Status surat masuk
            'belum_ditinjau' => ['bg' => '#fef3c7', 'text' => '#d97706', 'label' => 'Belum Ditinjau'],
            'ditinjau' => ['bg' => '#e0f2fe', 'text' => '#0284c7', 'label' => 'Ditinjau Pimpinan'],
            'didisposisikan' => ['bg' => '#f3e8ff', 'text' => '#7e22ce', 'label' => 'Didisposisikan'],

            // Status disposisi
            'belum_diproses' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'label' => 'Belum Diproses'],
            'diproses' => ['bg' => '#fef3c7', 'text' => '#b45309', 'label' => 'Sedang Diproses'],
            'selesai' => ['bg' => '#dcfce7', 'text' => '#15803d', 'label' => 'Selesai'],

            // Sifat surat
            'biasa' => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => 'Biasa'],
            'segera' => ['bg' => '#fef3c7', 'text' => '#b45309', 'label' => 'Segera'],
            'sangat_segera' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'label' => 'Sangat Segera'],
            'penting' => ['bg' => '#ffedd5', 'text' => '#c2410c', 'label' => 'Penting'],
            'rahasia' => ['bg' => '#fce7f3', 'text' => '#be185d', 'label' => 'Rahasia'],
        ];

        $item = $map[$status] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst(str_replace('_', ' ', $status))];

        return "<span style=\"display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 9999px; background-color: {$item['bg']}; color: {$item['text']};\">{$item['label']}</span>";
    }

    /**
     * Logika Inti Penomoran Otomatis ARSITA
     * Menjamin nomor terbit berurutan, tidak dobel/bentrok per unit, jenis surat, dan tahun
     */
    public static function generateNomorSurat(int $idSuratKeluar, int $idUnit, int $idJenisSurat, ?int $tahun = null, ?int $kategoriId = null): string
    {
        if (! $tahun) {
            $tahun = (int) date('Y');
        }

        return DB::transaction(function () use ($idSuratKeluar, $idUnit, $idJenisSurat, $tahun, $kategoriId) {
            // Cek apakah surat keluar ini sudah pernah dibuatkan nomor
            $existing = NomorSurat::where('id_surat_keluar', $idSuratKeluar)->first();
            if ($existing) {
                return $existing->nomor_surat;
            }

            // 1. Ambil format nomor untuk kombinasi ini
            $formatRow = null;
            if ($kategoriId) {
                $formatRow = FormatNomor::where('id_unit', $idUnit)
                    ->where('id_jenis_surat', $idJenisSurat)
                    ->where('kategori_id', $kategoriId)
                    ->where('aktif', 1)
                    ->first();
            }

            if (! $formatRow) {
                $formatRow = FormatNomor::where('id_unit', $idUnit)
                    ->where('id_jenis_surat', $idJenisSurat)
                    ->whereNull('kategori_id')
                    ->where('aktif', 1)
                    ->first();
            }

            $template = $formatRow ? $formatRow->format_nomor : '{nomor}/{kode_jenis}/{kode_unit}/{tahun}';
            $nomorTerakhirAwal = $formatRow ? (int) $formatRow->nomor_terakhir : 0;

            // Info kode unit & jenis surat
            $unit = Unit::find($idUnit);
            $kodeUnit = $unit ? $unit->kode_unit : 'UN.14';

            $jenis = JenisSurat::find($idJenisSurat);
            $kodeJenis = $jenis ? $jenis->kode_jenis : 'SRT';

            // 2. Hitung jumlah nomor surat terdaftar tahun ini
            $countQuery = NomorSurat::where('id_unit', $idUnit)
                ->where('id_jenis_surat', $idJenisSurat)
                ->where('tahun', $tahun);

            if ($kategoriId && $formatRow && $formatRow->kategori_id) {
                $countQuery->where('kategori_id', $kategoriId);
            }

            $jumlahSurat = $countQuery->lockForUpdate()->count();
            $urutan = $nomorTerakhirAwal + $jumlahSurat + 1;
            $nomorTigaDigit = str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);

            $nomorFinal = str_replace(
                ['{nomor}', '{kode_jenis}', '{kode_unit}', '{tahun}'],
                [$nomorTigaDigit, $kodeJenis, $kodeUnit, (string) $tahun],
                $template
            );

            // 3. Simpan ke nomor_surat
            NomorSurat::create([
                'id_surat_keluar' => $idSuratKeluar,
                'nomor_surat' => $nomorFinal,
                'tanggal_nomor' => date('Y-m-d'),
                'tahun' => $tahun,
                'id_unit' => $idUnit,
                'id_jenis_surat' => $idJenisSurat,
                'kategori_id' => $kategoriId,
            ]);

            return $nomorFinal;
        });
    }

    /**
     * Hitung total tahap ACC berdasarkan level unit
     * Prodi: 1 tahap (Kajur)
     * Fakultas: 1 tahap (Dekan/Wadek)
     * Rektorat: 3 tahap (Kasubbag -> Kabag -> Rektor)
     */
    public static function hitungTahapAcc(Unit $unit): int
    {
        return match ($unit->level) {
            'rektorat' => 3,
            default => 1,
        };
    }
}
