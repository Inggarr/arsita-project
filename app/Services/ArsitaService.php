<?php

namespace App\Services;

use App\Models\AccSuratKeluar;
use App\Models\FormatNomor;
use App\Models\JenisSurat;
use App\Models\KategoriSurat;
use App\Models\NomorSurat;
use App\Models\SifatSurat;
use App\Models\SuratKeluar;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
            'sudah_dibaca' => ['bg' => '#ecfdf5', 'text' => '#047857', 'label' => 'Sudah Dibaca Penerima'],
            'diarsipkan' => ['bg' => '#e2e8f0', 'text' => '#334155', 'label' => 'Diarsipkan'],

            // Status surat masuk
            'belum_ditinjau' => ['bg' => '#fef3c7', 'text' => '#d97706', 'label' => 'Belum Ditinjau'],
            'ditinjau' => ['bg' => '#e0f2fe', 'text' => '#0284c7', 'label' => 'Ditinjau Pimpinan'],
            'didisposisikan' => ['bg' => '#f3e8ff', 'text' => '#7e22ce', 'label' => 'Didisposisikan'],

            // Status disposisi
            'belum_diproses' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'label' => 'Belum Diproses'],
            'diproses' => ['bg' => '#fef3c7', 'text' => '#b45309', 'label' => 'Sedang Diproses'],
            'selesai' => ['bg' => '#dcfce7', 'text' => '#15803d', 'label' => 'Selesai'],

            // Sifat surat / Klasifikasi Keamanan
            'biasa' => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => 'Biasa (B)'],
            'terbatas' => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'label' => 'Terbatas (T)'],
            'segera' => ['bg' => '#fef3c7', 'text' => '#b45309', 'label' => 'Segera'],
            'sangat_segera' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'label' => 'Sangat Segera'],
            'rahasia' => ['bg' => '#fce7f3', 'text' => '#be185d', 'label' => 'Rahasia (R)'],
            'sangat_rahasia' => ['bg' => '#ffe4e6', 'text' => '#e11d48', 'label' => 'Sangat Rahasia (SR)'],
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

            $template = $formatRow ? $formatRow->format_nomor : '{sifat_kode}-{nomor}/{kode_unit}/{kode_jabatan}/{kode_arsip}/{bulan}/{tahun}';
            $nomorTerakhirAwal = $formatRow ? (int) $formatRow->nomor_terakhir : 0;

            // Info kode unit & jenis surat
            $unit = Unit::find($idUnit);
            $kodeUnit = $unit ? $unit->kode_unit : 'UN.14';

            $jenis = JenisSurat::find($idJenisSurat);
            $kodeJenis = $jenis ? $jenis->kode_jenis : 'SRT';

            // Ambil data surat keluar untuk sifat & penandatangan
            $surat = SuratKeluar::find($idSuratKeluar);

            // Kode Klasifikasi Keamanan Surat (Biasa, Terbatas, Rahasia, Sangat Rahasia, atau custom dari SifatSurat)
            $sifat = $surat ? trim((string) $surat->sifat) : 'biasa';
            $sifatRow = SifatSurat::where('nama_sifat', $sifat)
                ->orWhere('kode_sifat', strtoupper($sifat))
                ->first();

            if ($sifatRow) {
                $sifatKode = strtoupper($sifatRow->kode_sifat);
            } else {
                $sifatLower = strtolower($sifat);
                $sifatKode = match ($sifatLower) {
                    'sangat_rahasia', 'sr' => 'SR',
                    'rahasia', 'r' => 'R',
                    'terbatas', 't' => 'T',
                    default => 'B',
                };
            }

            // Kode Arsip Surat
            $kategori = $kategoriId ? KategoriSurat::find($kategoriId) : ($surat?->kategori_id ? KategoriSurat::find($surat->kategori_id) : null);
            $kodeArsip = $kategori?->kode_kategori ?: ($kategori?->nama_kategori ? strtoupper(substr($kategori->nama_kategori, 0, 4)) : 'PP.04');

            // Kode Jabatan Penandatangan (dari pejabat yang meng-ACC surat keluar tahap terakhir / pimpinan unit)
            $kodeJabatan = 'R';
            $lastAcc = AccSuratKeluar::where('id_surat_keluar', $idSuratKeluar)
                ->where('status', 'disetujui')
                ->latest('id_acc')
                ->first();

            if ($lastAcc && $lastAcc->disetujui_oleh) {
                $pejabat = User::find($lastAcc->disetujui_oleh);
                if ($pejabat && $pejabat->kode_jabatan) {
                    $kodeJabatan = $pejabat->kode_jabatan;
                }
            } else {
                // Fallback cari pimpinan di unit pembuat surat
                $pimpinan = User::where('id_unit', $idUnit)->where('id_role', 4)->first();
                if ($pimpinan && $pimpinan->kode_jabatan) {
                    $kodeJabatan = $pimpinan->kode_jabatan;
                }
            }

            // Bulan & Tanggal
            $tglSurat = $surat?->tanggal_draft ? Carbon::parse($surat->tanggal_draft) : now();
            $bulan = str_pad((string) $tglSurat->month, 2, '0', STR_PAD_LEFT);
            $bulanRomawiList = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $bulanRomawi = $bulanRomawiList[(int) $tglSurat->month] ?? 'I';

            // 2. Hitung jumlah nomor surat terdaftar tahun ini
            $countQuery = NomorSurat::where('id_unit', $idUnit)
                ->where('id_jenis_surat', $idJenisSurat)
                ->where('tahun', $tahun);

            if ($kategoriId && $formatRow && $formatRow->kategori_id) {
                $countQuery->where('kategori_id', $kategoriId);
            }

            $jumlahSurat = $countQuery->lockForUpdate()->count();
            $urutan = $nomorTerakhirAwal + $jumlahSurat + 1;
            // Dukung format 4 digit (0001) seperti contoh kode.md jika menggunakan {nomor4} atau default 4 digit bila pola B-0001
            $nomorEmpatDigit = str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
            $nomorTigaDigit = str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);

            // Ganti token di template
            $nomorFinal = str_replace(
                [
                    '{sifat_kode}',
                    '{nomor4}',
                    '{nomor}',
                    '{kode_jenis}',
                    '{kode_unit}',
                    '{kode_jabatan}',
                    '{kode_arsip}',
                    '{bulan}',
                    '{bulan_romawi}',
                    '{tahun}',
                ],
                [
                    $sifatKode,
                    $nomorEmpatDigit,
                    str_contains($template, '{sifat_kode}') ? $nomorEmpatDigit : $nomorTigaDigit,
                    $kodeJenis,
                    $kodeUnit,
                    $kodeJabatan,
                    $kodeArsip,
                    $bulan,
                    $bulanRomawi,
                    (string) $tahun,
                ],
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

    /**
     * Mengambil daftar unit dan individu yang sah sebagai tujuan disposisi.
     *
     * Aturan:
     * - Disposisi individu: hanya individu dalam unit sendiri atau unit yang berada di bawah naungannya.
     * - Disposisi unit: hanya unit kerja yang berada dalam cakupan kewenangannya (unit bawahan langsung).
     *
     * @param  User  $user  Pengirim disposisi
     * @return array{units: Collection, users: Collection, catatan_alur: string, level_pengirim: string}
     */
    public static function getTujuanDisposisiValid(User $user): array
    {
        $unit = $user->unit;
        $level = $unit?->level ?? 'rektorat';

        $validUnits = collect();
        $catatanAlur = '';

        if ($user->isAdminRektorat() || $level === 'rektorat') {
            // Rektorat: bisa disposisi unit ke semua unit bawahan langsung (level fakultas, lembaga, upt, pusat)
            $validUnits = Unit::where('status', 'aktif')
                ->where('id_unit', '!=', $user->id_unit)
                ->whereIn('level', ['fakultas', 'lembaga', 'upt', 'pusat'])
                ->orderBy('nama_unit', 'asc')
                ->get();
            $catatanAlur = 'Tingkat Rektorat: Disposisi unit ditujukan ke Fakultas, Lembaga, UPT, atau Pusat. Disposisi individu ditujukan ke seluruh sivitas dalam universitas.';
        } elseif ($level === 'fakultas') {
            // Fakultas: bisa disposisi unit ke prodi-prodi langsung di bawahnya
            $validUnits = Unit::where('status', 'aktif')
                ->where('id_parent', $unit->id_unit)
                ->orderBy('nama_unit', 'asc')
                ->get();
            $catatanAlur = 'Tingkat Fakultas: Disposisi unit ditujukan ke Program Studi / Jurusan di bawah naungan fakultas ini. Disposisi individu ditujukan ke staf/pimpinan internal fakultas atau prodi bawahannya.';
        } elseif ($level === 'prodi') {
            // Prodi: tidak memiliki unit bawahan — hanya bisa disposisi ke individu internal prodi
            $validUnits = collect();
            $catatanAlur = 'Tingkat Program Studi: Disposisi hanya dapat ditujukan ke individu (staf/dosen) dalam Program Studi ini.';
        } elseif (in_array($level, ['lembaga', 'upt', 'pusat'])) {
            // Lembaga/UPT/Pusat: bisa disposisi ke unit di bawahnya jika ada (child)
            $validUnits = Unit::where('status', 'aktif')
                ->where('id_parent', $unit->id_unit)
                ->orderBy('nama_unit', 'asc')
                ->get();
            $catatanAlur = 'Tingkat '.ucfirst($level).': Disposisi unit ditujukan ke unit di bawah naungan langsung. Disposisi individu ditujukan ke staf/anggota internal unit.';
        }

        // ---- Daftar individu yang valid sebagai tujuan disposisi ----
        // Prinsip: hanya unit sendiri + unit di bawah naungannya (childUnitIds)
        $childUnitIds = $validUnits->pluck('id_unit')->toArray();

        $validUsersQuery = User::where('status', 'aktif')
            ->where('id_user', '!=', $user->id_user)
            ->where(function ($q) use ($user, $childUnitIds) {
                // Individu dari unit sendiri
                $q->where('id_unit', $user->id_unit);
                // Individu dari unit bawahan (jika ada)
                if (! empty($childUnitIds)) {
                    $q->orWhereIn('id_unit', $childUnitIds);
                }
            });

        $validUsers = $validUsersQuery
            ->with(['unit', 'role'])
            ->orderBy('id_unit', 'asc')
            ->orderBy('nama', 'asc')
            ->get();

        return [
            'units' => $validUnits,
            'users' => $validUsers,
            'catatan_alur' => $catatanAlur,
            'level_pengirim' => $level,
        ];
    }

    /**
     * Perbarui semua nomor_surat yang cocok dengan FormatNomor yang diedit/diubah
     */
    public static function updateNomorSuratByFormat(FormatNomor $format): int
    {
        $query = NomorSurat::with(['unit', 'jenisSurat'])
            ->where('id_unit', $format->id_unit)
            ->where('id_jenis_surat', $format->id_jenis_surat);

        if ($format->kategori_id) {
            $query->where('kategori_id', $format->kategori_id);
        }

        $records = $query->orderBy('id_nomor', 'asc')->get();
        $updatedCount = 0;

        foreach ($records as $index => $item) {
            // Urutan nomor
            $urutan = (int) $format->nomor_terakhir + ($index + 1);

            // Jika nomor lama berawalan angka digit, kita bisa prioritaskan nomor urutan itu
            if (preg_match('/^(\d+)/', (string) $item->nomor_surat, $matches)) {
                $nomorUrutDigit = $matches[1];
            } else {
                $nomorUrutDigit = str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
            }

            $kodeUnit = $item->unit?->kode_unit ?? 'Un.30';
            $kodeJenis = $item->jenisSurat?->kode_jenis ?? 'SRT';
            $tahun = (string) ($item->tahun ?: date('Y'));

            // Ambil data surat keluar terkait untuk sifat, kategori, pejabat
            $surat = SuratKeluar::find($item->id_surat_keluar);
            $sifat = $surat ? trim((string) $surat->sifat) : 'biasa';
            $sifatRow = SifatSurat::where('nama_sifat', $sifat)->orWhere('kode_sifat', strtoupper($sifat))->first();
            $sifatKode = $sifatRow ? strtoupper($sifatRow->kode_sifat) : (match (strtolower($sifat)) {
                'sangat_rahasia', 'sr' => 'SR',
                'rahasia', 'r' => 'R',
                'terbatas', 't' => 'T',
                default => 'B',
            });

            $kategori = $item->kategori_id ? KategoriSurat::find($item->kategori_id) : ($surat?->kategori_id ? KategoriSurat::find($surat->kategori_id) : null);
            $kodeArsip = $kategori?->kode_kategori ?: ($kategori?->nama_kategori ? strtoupper(substr($kategori->nama_kategori, 0, 4)) : 'PP.04');

            // Kode Jabatan Pimpinan Unit
            $kodeJabatan = 'R';
            $lastAcc = AccSuratKeluar::where('id_surat_keluar', $item->id_surat_keluar)->where('status', 'disetujui')->latest('id_acc')->first();
            if ($lastAcc && $lastAcc->disetujui_oleh) {
                $pejabat = User::find($lastAcc->disetujui_oleh);
                if ($pejabat && $pejabat->kode_jabatan) {
                    $kodeJabatan = $pejabat->kode_jabatan;
                }
            } else {
                $pimpinan = User::where('id_unit', $item->id_unit)->where('id_role', 4)->first();
                if ($pimpinan && $pimpinan->kode_jabatan) {
                    $kodeJabatan = $pimpinan->kode_jabatan;
                }
            }

            $tglSurat = $surat?->tanggal_draft ? Carbon::parse($surat->tanggal_draft) : ($item->tanggal_nomor ? Carbon::parse($item->tanggal_nomor) : now());
            $bulan = str_pad((string) $tglSurat->month, 2, '0', STR_PAD_LEFT);
            $bulanRomawiList = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $bulanRomawi = $bulanRomawiList[(int) $tglSurat->month] ?? 'I';

            $nomorEmpatDigit = str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);

            $nomorBaru = str_replace(
                [
                    '{sifat_kode}',
                    '{nomor4}',
                    '{nomor}',
                    '{kode_jenis}',
                    '{kode_unit}',
                    '{kode_jabatan}',
                    '{kode_arsip}',
                    '{bulan}',
                    '{bulan_romawi}',
                    '{tahun}',
                ],
                [
                    $sifatKode,
                    $nomorEmpatDigit,
                    str_contains($format->format_nomor, '{sifat_kode}') ? $nomorEmpatDigit : $nomorUrutDigit,
                    $kodeJenis,
                    $kodeUnit,
                    $kodeJabatan,
                    $kodeArsip,
                    $bulan,
                    $bulanRomawi,
                    $tahun,
                ],
                $format->format_nomor
            );

            if ($item->nomor_surat !== $nomorBaru) {
                $item->nomor_surat = $nomorBaru;
                $item->save();
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
