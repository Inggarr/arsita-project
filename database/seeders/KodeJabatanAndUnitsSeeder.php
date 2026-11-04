<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use App\Models\KategoriSurat;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KodeJabatanAndUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        // 1. Update kode_kategori (Kode Klasifikasi Arsip) default
        $kategoriCodes = [
            'Akademik' => 'PP.04',
            'Kemahasiswaan' => 'KM.01',
            'Kepegawaian' => 'KP.02',
            'Keuangan' => 'KU.03',
            'Kerjasama' => 'KS.05',
            'Umum' => 'OT.01',
        ];

        foreach ($kategoriCodes as $name => $code) {
            KategoriSurat::where('nama_kategori', 'like', "%{$name}%")
                ->update(['kode_kategori' => $code]);
        }

        // Hubungkan default jenis_surat dengan kategori_id
        $akademik = KategoriSurat::where('nama_kategori', 'like', '%Akademik%')->first();
        $umum = KategoriSurat::where('nama_kategori', 'like', '%Umum%')->first();
        $kepegawaian = KategoriSurat::where('nama_kategori', 'like', '%Kepegawaian%')->first();

        if ($kepegawaian) {
            JenisSurat::whereIn('kode_jenis', ['SK', 'ST'])->update(['kategori_id' => $kepegawaian->id_kategori]);
        }
        if ($umum) {
            JenisSurat::whereIn('kode_jenis', ['SE', 'SU', 'SPB'])->update(['kategori_id' => $umum->id_kategori]);
        }
        if ($akademik) {
            JenisSurat::whereIn('kode_jenis', ['SP'])->update(['kategori_id' => $akademik->id_kategori]);
        }

        // 2. Unit Rektorat & Pejabat Rektor / Wakil Rektor
        $rektorat = Unit::firstOrCreate(
            ['kode_unit' => 'UN.14'],
            ['nama_unit' => 'Rektorat', 'level' => 'rektorat', 'status' => 'aktif']
        );

        User::where('username', 'rektor')->update(['kode_jabatan' => 'R']);

        $warekList = [
            ['nama' => 'Prof. Dr. Wakil Rektor Bidang Akademik', 'username' => 'warek1', 'kode' => 'R.I'],
            ['nama' => 'Dr. Wakil Rektor Bidang Perencanaan, Keuangan, dan Umum', 'username' => 'warek2', 'kode' => 'R.II'],
            ['nama' => 'Dr. Wakil Rektor Bidang Kemahasiswaan, Alumni, dan Kerja Sama', 'username' => 'warek3', 'kode' => 'R.III'],
        ];

        foreach ($warekList as $w) {
            User::updateOrCreate(
                ['username' => $w['username']],
                [
                    'nama' => $w['nama'].' (Pimpinan)',
                    'email' => $w['username'].'@uinssc.ac.id',
                    'password' => $defaultPassword,
                    'id_unit' => $rektorat->id_unit,
                    'id_role' => 4, // pimpinan
                    'kode_jabatan' => $w['kode'],
                    'status' => 'aktif',
                ]
            );
        }

        // 3. Unit Biro & Pimpinan Biro
        $unitBiro = Unit::updateOrCreate(
            ['kode_unit' => 'B'],
            [
                'nama_unit' => 'Biro Akademik, Keuangan, dan Umum',
                'level' => 'lembaga',
                'id_parent' => $rektorat->id_unit,
                'status' => 'aktif',
            ]
        );

        User::updateOrCreate(
            ['username' => 'kepala_biro'],
            [
                'nama' => 'Drs. Kepala Biro AUAK (Pimpinan)',
                'email' => 'biro@uinssc.ac.id',
                'password' => $defaultPassword,
                'id_unit' => $unitBiro->id_unit,
                'id_role' => 4,
                'kode_jabatan' => 'B',
                'status' => 'aktif',
            ]
        );
        User::updateOrCreate(
            ['username' => 'kabag_umum_akademik'],
            [
                'nama' => 'Kepala Bagian Umum dan Akademik',
                'email' => 'kabag_akademik@uinssc.ac.id',
                'password' => $defaultPassword,
                'id_unit' => $unitBiro->id_unit,
                'id_role' => 4,
                'kode_jabatan' => 'B.I',
                'status' => 'aktif',
            ]
        );
        User::updateOrCreate(
            ['username' => 'tu_biro'],
            [
                'nama' => 'Staff TU Biro AUAK',
                'email' => 'tu_biro@uinssc.ac.id',
                'password' => $defaultPassword,
                'id_unit' => $unitBiro->id_unit,
                'id_role' => 3,
                'kode_jabatan' => 'JF.B',
                'status' => 'aktif',
            ]
        );

        // 4. Senat, Dewan Penyantun, SPI
        $unitSenat = Unit::updateOrCreate(
            ['kode_unit' => 'SENAT'],
            ['nama_unit' => 'Senat Universitas', 'level' => 'lembaga', 'id_parent' => $rektorat->id_unit, 'status' => 'aktif']
        );
        User::updateOrCreate(
            ['username' => 'ketua_senat'],
            [
                'nama' => 'Prof. Dr. Ketua Senat (Pimpinan)',
                'email' => 'senat@uinssc.ac.id',
                'password' => $defaultPassword,
                'id_unit' => $unitSenat->id_unit,
                'id_role' => 4,
                'kode_jabatan' => 'S',
                'status' => 'aktif',
            ]
        );
        User::updateOrCreate(
            ['username' => 'staff_senat'],
            [
                'nama' => 'Staff Sekretariat Senat',
                'email' => 'tu_senat@uinssc.ac.id',
                'password' => $defaultPassword,
                'id_unit' => $unitSenat->id_unit,
                'id_role' => 3,
                'status' => 'aktif',
            ]
        );

        $unitDP = Unit::updateOrCreate(
            ['kode_unit' => 'DP'],
            ['nama_unit' => 'Dewan Penyantun', 'level' => 'lembaga', 'id_parent' => $rektorat->id_unit, 'status' => 'aktif']
        );
        User::updateOrCreate(
            ['username' => 'ketua_dewan_penyantun'],
            [
                'nama' => 'Ketua Dewan Penyantun (Pimpinan)',
                'email' => 'dp@uinssc.ac.id',
                'password' => $defaultPassword,
                'id_unit' => $unitDP->id_unit,
                'id_role' => 4,
                'kode_jabatan' => 'DP',
                'status' => 'aktif',
            ]
        );
        User::updateOrCreate(
            ['username' => 'staff_dp'],
            [
                'nama' => 'Staff Sekretariat Dewan Penyantun',
                'email' => 'tu_dp@uinssc.ac.id',
                'password' => $defaultPassword,
                'id_unit' => $unitDP->id_unit,
                'id_role' => 3,
                'status' => 'aktif',
            ]
        );

        // Update SPI
        User::where('username', 'kepala_spi')->update(['kode_jabatan' => 'SP']);

        // 5. Update Pejabat Fakultas & Buat Wakil Dekan
        $fakultasMapping = [
            'fitk' => [
                'nama_like' => 'Fakultas Ilmu Tarbiyah%',
                'dekan_username' => 'dekan',
                'kode_dekan' => 'F.I',
                'wadek' => [
                    ['nama' => 'Wakil Dekan Bidang Akademik FITK', 'user' => 'wadek1_fitk', 'kode' => 'F.I.1'],
                    ['nama' => 'Wakil Dekan Bidang Perencanaan, Keuangan, dan Umum FITK', 'user' => 'wadek2_fitk', 'kode' => 'F.I.2'],
                    ['nama' => 'Wakil Dekan Bidang Kemahasiswaan FITK', 'user' => 'wadek3_fitk', 'kode' => 'F.I.3'],
                ],
                'laboratorium' => [
                    ['nama' => 'Kepala Laboratorium MIPA FITK', 'user' => 'kalab_mipa', 'kode' => 'LB.I.1'],
                    ['nama' => 'Kepala Laboratorium Praktek Ibadah FITK', 'user' => 'kalab_ibadah', 'kode' => 'LB.I.2'],
                ],
                'kabag_kode' => 'B.F.I',
                'prodi_map' => [
                    'Pendidikan Agama Islam' => 'J.I.1',
                    'Pendidikan Bahasa Arab' => 'J.I.2',
                    'Tadris Bahasa Inggris' => 'J.I.3',
                    'Tadris Ilmu Pengetahuan Sosial' => 'J.I.4',
                    'Tadris Matematika' => 'J.I.5',
                    'Tadris Biologi' => 'J.I.6',
                    'Pendidikan Guru Madrasah Ibtidaiyah' => 'J.I.7',
                    'Pendidikan Islam Anak Usia Dini' => 'J.I.8',
                    'Manajemen Pendidikan Islam' => 'J.I.9',
                    'Tadris Bahasa Indonesia' => 'J.I.10',
                    'Tadris Kimia' => 'J.I.11',
                    'Pendidikan Profesi Guru' => 'J.I.12',
                    'PJJ Pendidikan Agama Islam' => 'J.I.13',
                    'PJJ Pendidikan Guru Madrasah Ibtidaiyah' => 'J.I.14',
                    'PJJ Pendidikan Bahasa Arab' => 'J.I.15',
                    'Informatika' => 'J.I.16',
                    'Matematika' => 'J.I.17',
                ],
            ],
            'febi' => [
                'nama_like' => 'Fakultas Ekonomi dan Bisnis Islam%',
                'dekan_username' => 'dekan_febi',
                'kode_dekan' => 'F.II',
                'wadek' => [
                    ['nama' => 'Wakil Dekan Bidang Akademik dan Kemahasiswaan FEBI', 'user' => 'wadek1_febi', 'kode' => 'F.II.1'],
                    ['nama' => 'Wakil Dekan Bidang Perencanaan, Keuangan, dan Umum FEBI', 'user' => 'wadek2_febi', 'kode' => 'F.II.2'],
                ],
                'laboratorium' => [
                    ['nama' => 'Kepala Laboratorium Perbankan Syariah FEBI', 'user' => 'kalab_febi', 'kode' => 'LB.II.1'],
                ],
                'kabag_kode' => 'B.F.II',
                'prodi_map' => [
                    'Perbankan Syariah' => 'J.II.1',
                    'Ekonomi Syariah' => 'J.II.2',
                    'Akuntansi Syariah' => 'J.II.3',
                    'Pariwisata Syariah' => 'J.II.4',
                ],
            ],
            'fdki' => [
                'nama_like' => 'Fakultas Dakwah dan Komunikasi Islam%',
                'dekan_username' => 'dekan_fdki',
                'kode_dekan' => 'F.III',
                'wadek' => [
                    ['nama' => 'Wakil Dekan Bidang Akademik dan Kemahasiswaan FDKI', 'user' => 'wadek1_fdki', 'kode' => 'F.III.1'],
                    ['nama' => 'Wakil Dekan Bidang Perencanaan, Keuangan, dan Umum FDKI', 'user' => 'wadek2_fdki', 'kode' => 'F.III.2'],
                ],
                'laboratorium' => [
                    ['nama' => 'Kepala Laboratorium Komunikasi dan Penyiaran Islam FDKI', 'user' => 'kalab_kpi', 'kode' => 'LB.III.1'],
                ],
                'kabag_kode' => 'B.F.III',
                'prodi_map' => [
                    'Komunikasi dan Penyiaran Islam' => 'J.III.1',
                    'Pengembangan Masyarakat Islam' => 'J.III.2',
                    'Bimbingan dan Konseling Islam' => 'J.III.3',
                    'Sosiologi Agama' => 'J.III.4',
                ],
            ],
            'syariah' => [
                'nama_like' => 'Fakultas Syariah%',
                'dekan_username' => 'dekan_syariah',
                'kode_dekan' => 'F.IV',
                'wadek' => [
                    ['nama' => 'Wakil Dekan Bidang Akademik dan Kemahasiswaan FASYA', 'user' => 'wadek1_fasya', 'kode' => 'F.IV.1'],
                    ['nama' => 'Wakil Dekan Bidang Perencanaan, Keuangan, dan Umum FASYA', 'user' => 'wadek2_fasya', 'kode' => 'F.IV.2'],
                ],
                'laboratorium' => [
                    ['nama' => 'Kepala Laboratorium Peradilan Agama FASYA', 'user' => 'kalab_fasya', 'kode' => 'LB.IV.1'],
                ],
                'kabag_kode' => 'B.F.IV',
                'prodi_map' => [
                    'Hukum Keluarga' => 'J.IV.1',
                    'Hukum Ekonomi Syariah' => 'J.IV.2',
                    'Hukum Tatanegara Islam' => 'J.IV.3',
                    'Ilmu Falak' => 'J.IV.4',
                    'PJJ Hukum Keluarga' => 'J.IV.5',
                ],
            ],
            'fua' => [
                'nama_like' => 'Fakultas Ushuluddin dan Adab%',
                'dekan_username' => 'dekan_fua',
                'kode_dekan' => 'F.V',
                'wadek' => [
                    ['nama' => 'Wakil Dekan Bidang Akademik dan Kemahasiswaan FUA', 'user' => 'wadek1_fua', 'kode' => 'F.V.1'],
                    ['nama' => 'Wakil Dekan Bidang Perencanaan, Keuangan, dan Umum FUA', 'user' => 'wadek2_fua', 'kode' => 'F.V.2'],
                ],
                'laboratorium' => [
                    ['nama' => 'Kepala Laboratorium Tafsir Hadits FUA', 'user' => 'kalab_fua', 'kode' => 'LB.V.1'],
                ],
                'kabag_kode' => 'B.F.V',
                'prodi_map' => [
                    'Sejarah Peradaban Islam' => 'J.V.1',
                    'Aqidah dan Filsafat Islam' => 'J.V.2',
                    'Ilmu Al-Qur' => 'J.V.3',
                    'Ilmu Hadis' => 'J.V.4',
                    'Bahasa dan Sastra Arab' => 'J.V.5',
                    'Tasawuf dan Psikoterapi' => 'J.V.6',
                    'PJJ Sejarah Peradaban' => 'J.V.7',
                ],
            ],
        ];

        foreach ($fakultasMapping as $fakKey => $fData) {
            $unitFak = Unit::where('nama_unit', 'like', $fData['nama_like'])->first();
            if (! $unitFak) {
                continue;
            }

            // Update Dekan
            User::where('username', $fData['dekan_username'])->update(['kode_jabatan' => $fData['kode_dekan']]);

            // Wakil Dekan
            foreach ($fData['wadek'] as $wadek) {
                User::updateOrCreate(
                    ['username' => $wadek['user']],
                    [
                        'nama' => $wadek['nama'].' (Pimpinan)',
                        'email' => $wadek['user'].'@uinssc.ac.id',
                        'password' => $defaultPassword,
                        'id_unit' => $unitFak->id_unit,
                        'id_role' => 4,
                        'kode_jabatan' => $wadek['kode'],
                        'status' => 'aktif',
                    ]
                );
            }

            // Laboratorium
            foreach ($fData['laboratorium'] as $lab) {
                User::updateOrCreate(
                    ['username' => $lab['user']],
                    [
                        'nama' => $lab['nama'].' (Pimpinan)',
                        'email' => $lab['user'].'@uinssc.ac.id',
                        'password' => $defaultPassword,
                        'id_unit' => $unitFak->id_unit,
                        'id_role' => 4,
                        'kode_jabatan' => $lab['kode'],
                        'status' => 'aktif',
                    ]
                );
            }

            // Kaprodi di bawah fakultas
            foreach ($fData['prodi_map'] as $prodiNameMatch => $kodeProdi) {
                $unitProdi = Unit::where('id_parent', $unitFak->id_unit)
                    ->where('nama_unit', 'like', "%{$prodiNameMatch}%")
                    ->first();

                if ($unitProdi) {
                    User::where('id_unit', $unitProdi->id_unit)
                        ->where('id_role', 4)
                        ->update(['kode_jabatan' => $kodeProdi]);
                }
            }
        }

        // 6. Pejabat LPPM & LPM
        User::where('username', 'ketua_lp2m')->update(['kode_jabatan' => 'L.I']);
        User::where('username', 'kepala_sdgs')->update(['kode_jabatan' => 'L.I.1']);
        User::where('username', 'kepala_industri')->update(['kode_jabatan' => 'L.I.2']);
        User::where('username', 'kepala_psga')->update(['kode_jabatan' => 'L.I.3']);
        User::where('username', 'kepala_iop')->update(['kode_jabatan' => 'L.I.4']);
        User::where('username', 'ketua_lph')->update(['kode_jabatan' => 'L.I.5']);
        User::where('username', 'kepala_pkbh')->update(['kode_jabatan' => 'L.I.6']);

        User::where('username', 'ketua_lpm')->update(['kode_jabatan' => 'L.II']);
        User::where('username', 'kepala_mutu_digital')->update(['kode_jabatan' => 'L.II.1']);
        User::where('username', 'kepala_audit_mutu')->update(['kode_jabatan' => 'L.II.2']);
        User::where('username', 'kepala_inovasi_digital')->update(['kode_jabatan' => 'L.II.3']);
        User::where('username', 'kepala_pusat_bisnis')->update(['kode_jabatan' => 'L.II.4']);
        User::where('username', 'ketua_lsp')->update(['kode_jabatan' => 'L.II.5']);
        User::where('username', 'kepala_admisi')->update(['kode_jabatan' => 'L.II.6']);

        // 7. Pejabat UPT
        User::where('username', 'kepala_upt_perpus')->update(['kode_jabatan' => 'UP.I']);
        User::where('username', 'kepala_upt_tik')->update(['kode_jabatan' => 'UP.II']);
        User::where('username', 'kepala_upt_bahasa')->update(['kode_jabatan' => 'UP.III']);
        User::where('username', 'kepala_upt_karir')->update(['kode_jabatan' => 'UP.IV']);
        User::where('username', 'kepala_upt_mahad')->update(['kode_jabatan' => 'UP.V']);
        User::where('username', 'kepala_upt_pjj')->update(['kode_jabatan' => 'UP.VI']);
    }
}
