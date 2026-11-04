<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LembagaUptPusatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');
        $allJenisSurat = JenisSurat::all();

        // Daftar unit baru: Lembaga, UPT, dan Pusat/Unit
        $unitsData = [
            // 1. Pimpinan Lembaga
            [
                'nama' => 'Lembaga Penjaminan Mutu',
                'kode' => 'LPM',
                'level' => 'lembaga',
                'pimpinan' => [
                    'nama' => 'Prof. Dr. Hj. Ria Yulia Gloria, M.Pd.',
                    'username' => 'ketua_lpm',
                    'email' => 'ketua_lpm@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'H. Toheri, S.Si., M.Pd.',
                    'username' => 'sekretaris_lpm',
                    'email' => 'sekretaris_lpm@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'Lembaga Penelitian dan Pengabdian kepada Masyarakat',
                'kode' => 'LP2M',
                'level' => 'lembaga',
                'pimpinan' => [
                    'nama' => 'Dr. H. Faqiuddin Abdul Kodir, MA',
                    'username' => 'ketua_lp2m',
                    'email' => 'ketua_lp2m@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Wakhit Hasim, M.Hum.',
                    'username' => 'sekretaris_lp2m',
                    'email' => 'sekretaris_lp2m@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'Satuan Pengawas Internal',
                'kode' => 'SPI',
                'level' => 'lembaga',
                'pimpinan' => [
                    'nama' => 'Budi Affandi, S.Ag., M.Pd.I.',
                    'username' => 'kepala_spi',
                    'email' => 'kepala_spi@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Abdul Wakhid, S.Pd.I.',
                    'username' => 'sekretaris_spi',
                    'email' => 'sekretaris_spi@uinssc.ac.id',
                ],
            ],

            // 2. Pimpinan Unit Pelaksana Teknis (UPT)
            [
                'nama' => 'UPT Pusat Bahasa',
                'kode' => 'UPT-BAHASA',
                'level' => 'upt',
                'pimpinan' => [
                    'nama' => 'Dra. Hj. Darrotul Jannah, M.Ag',
                    'username' => 'kepala_upt_bahasa',
                    'email' => 'kepala_bahasa@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Zaenal Mutaqin, M.I.Kom.',
                    'username' => 'sekretaris_upt_bahasa',
                    'email' => 'sekretaris_bahasa@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'UPT Teknologi Informasi dan Komunikasi',
                'kode' => 'UPT-TIK',
                'level' => 'upt',
                'pimpinan' => [
                    'nama' => 'Riyanto, S.T., M.Kom.',
                    'username' => 'kepala_upt_tik',
                    'email' => 'kepala_tik@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Abdurrozaq Nasrudin, S.Kom., M.M.',
                    'username' => 'sekretaris_upt_tik',
                    'email' => 'sekretaris_tik@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'UPT Pengembangan Karir',
                'kode' => 'UPT-KARIR',
                'level' => 'upt',
                'pimpinan' => [
                    'nama' => 'Wahyono, S.Pd.I., M.Pd.I',
                    'username' => 'kepala_upt_karir',
                    'email' => 'kepala_karir@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Nelly Husni Laely, M.Pd.',
                    'username' => 'sekretaris_upt_karir',
                    'email' => 'sekretaris_karir@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'UPT Perpustakaan',
                'kode' => 'UPT-PERPUS',
                'level' => 'upt',
                'pimpinan' => [
                    'nama' => 'Syibli Maufur, M.Pd.',
                    'username' => 'kepala_upt_perpus',
                    'email' => 'kepala_perpus@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Tohirin, S.Ag., S.I.P.I.',
                    'username' => 'sekretaris_upt_perpus',
                    'email' => 'sekretaris_perpus@uinssc.ac.id',
                ],
            ],
            [
                'nama' => "UPT Ma'had Al-Jami'ah",
                'kode' => 'UPT-MAHAD',
                'level' => 'upt',
                'pimpinan' => [
                    'nama' => 'Dr. Muhsin Riyadi, MA',
                    'username' => 'kepala_upt_mahad',
                    'email' => 'kepala_mahad@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'A. Syukron Syahroni, S.Sos.I.',
                    'username' => 'sekretaris_upt_mahad',
                    'email' => 'sekretaris_mahad@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'UPT Pendidikan Jarak Jauh (PJJ)',
                'kode' => 'UPT-PJJ',
                'level' => 'upt',
                'pimpinan' => [
                    'nama' => 'Dr. Muslihudin, M.Ag.',
                    'username' => 'kepala_upt_pjj',
                    'email' => 'kepala_pjj@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Listiana Ikawati, M.Hum.',
                    'username' => 'sekretaris_upt_pjj',
                    'email' => 'sekretaris_pjj@uinssc.ac.id',
                ],
            ],

            // 3. Pimpinan Pusat dan Unit
            [
                'nama' => 'Pusat Pengembangan Standar Mutu Digital',
                'kode' => 'PUSAT-MUTU-DIGITAL',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Hj. Ery Khaeriyah, S.Ag., MA',
                    'username' => 'kepala_mutu_digital',
                    'email' => 'mutu_digital@uinssc.ac.id',
                ],
                'sekretaris' => null,
            ],
            [
                'nama' => 'Pusat Audit dan Pengendalian Mutu',
                'kode' => 'PUSAT-AUDIT-MUTU',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'H. Ilham Bustomi, M.Ag.',
                    'username' => 'kepala_audit_mutu',
                    'email' => 'audit_mutu@uinssc.ac.id',
                ],
                'sekretaris' => null,
            ],
            [
                'nama' => 'Pusat Inovasi Pembelajaran Digital',
                'kode' => 'PUSAT-INOVASI-DIGITAL',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Heru Mudiyanto, M.Pd',
                    'username' => 'kepala_inovasi_digital',
                    'email' => 'inovasi_digital@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Yunita Dwi Jayanti, M.Pd.',
                    'username' => 'sekretaris_inovasi_digital',
                    'email' => 'sekretaris_inovasi@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'Pusat Pengembangan Bisnis',
                'kode' => 'PUSAT-BISNIS',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Dr. Abdul Nasir, M.E.Sy.',
                    'username' => 'kepala_pusat_bisnis',
                    'email' => 'pusat_bisnis@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Muhamad Mujahidin, S.E.I., M.E.',
                    'username' => 'sekretaris_pusat_bisnis',
                    'email' => 'sekretaris_bisnis@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'Lembaga Sertifikasi Profesi',
                'kode' => 'LSP',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Indra Gunawan, M.Pd.',
                    'username' => 'ketua_lsp',
                    'email' => 'lsp@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Suci Ramdaeni, M.Pd.',
                    'username' => 'sekretaris_lsp',
                    'email' => 'sekretaris_lsp@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'Pusat Admisi dan Promosi',
                'kode' => 'PUSAT-ADMISI',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Basiran, MA',
                    'username' => 'kepala_admisi',
                    'email' => 'admisi@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Oktavianus Bere, S.Sos',
                    'username' => 'sekretaris_admisi',
                    'email' => 'sekretaris_admisi@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'Pusat Layanan Terpadu',
                'kode' => 'PLT',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Hartawan, S.I.P.',
                    'username' => 'kepala_plt',
                    'email' => 'plt@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Mukti Ali Fauzi, S.Pd.I.',
                    'username' => 'sekretaris_plt',
                    'email' => 'sekretaris_plt@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'Pusat Pengabdian Kepada Masyarakat dan SDG’s',
                'kode' => 'PUSAT-SDGS',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Dr. Budi Manfaat, M.Si.',
                    'username' => 'kepala_sdgs',
                    'email' => 'sdgs@uinssc.ac.id',
                ],
                'sekretaris' => null,
            ],
            [
                'nama' => 'Pusat Penelitian Pengembangan Industri',
                'kode' => 'PUSAT-INDUSTRI',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Mujib Ubaidillah, M.Pd.',
                    'username' => 'kepala_industri',
                    'email' => 'industri@uinssc.ac.id',
                ],
                'sekretaris' => null,
            ],
            [
                'nama' => 'Pusat Studi Gender, Anak, dan Disabilitas',
                'kode' => 'PSGA',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => "Masri'ah, M.Ag.",
                    'username' => 'kepala_psga',
                    'email' => 'psga@uinssc.ac.id',
                ],
                'sekretaris' => null,
            ],
            [
                'nama' => 'Internasional Office and Partnership',
                'kode' => 'IOP',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Lala Bumela, M.Pd., Ph.D',
                    'username' => 'kepala_iop',
                    'email' => 'iop@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Ivo Dinasta Yanuar, S.S., M.Appling.',
                    'username' => 'sekretaris_iop',
                    'email' => 'sekretaris_iop@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'Lembaga Pemeriksa Halal',
                'kode' => 'LPH',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Mariyah Ulfah, M.E.Sy',
                    'username' => 'ketua_lph',
                    'email' => 'lph@uinssc.ac.id',
                ],
                'sekretaris' => [
                    'nama' => 'Mahrun Nisa Ali, M.A.',
                    'username' => 'sekretaris_lph',
                    'email' => 'sekretaris_lph@uinssc.ac.id',
                ],
            ],
            [
                'nama' => 'Pusat Konsultasi dan Bantuan Hukum',
                'kode' => 'PKBH',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Ahmad Khoirudin, M.H.',
                    'username' => 'kepala_pkbh',
                    'email' => 'pkbh@uinssc.ac.id',
                ],
                'sekretaris' => null,
            ],
            [
                'nama' => 'Pusat Publikasi dan Pemeringkatan',
                'kode' => 'PUSAT-PUBLIKASI',
                'level' => 'pusat',
                'pimpinan' => [
                    'nama' => 'Maulidya Ulfah, S.Pd.I., M.Pd.I.',
                    'username' => 'kepala_publikasi',
                    'email' => 'publikasi@uinssc.ac.id',
                ],
                'sekretaris' => null,
            ],
        ];

        foreach ($unitsData as $item) {
            // 1. Buat atau update Unit
            $unit = Unit::updateOrCreate(
                ['kode_unit' => $item['kode']],
                [
                    'nama_unit' => $item['nama'],
                    'level' => $item['level'],
                    'id_parent' => 1, // Di bawah koordinasi Rektorat
                    'status' => 'aktif',
                ]
            );

            // 2. Akun Pimpinan (Role: pimpinan / id_role: 4)
            if ($item['pimpinan']) {
                User::updateOrCreate(
                    ['username' => $item['pimpinan']['username']],
                    [
                        'nama' => $item['pimpinan']['nama'].' (Pimpinan)',
                        'email' => $item['pimpinan']['email'],
                        'password' => $defaultPassword,
                        'id_unit' => $unit->id_unit,
                        'id_role' => 4, // pimpinan
                        'status' => 'aktif',
                    ]
                );
            }

            // 3. Akun Sekretaris / Staff TU (Role: staff_tu / id_role: 3)
            // Jika ada nama sekretaris, buat dengan nama tsb, jika tidak ada ('-'), buat akun staff TU unit default
            if ($item['sekretaris']) {
                User::updateOrCreate(
                    ['username' => $item['sekretaris']['username']],
                    [
                        'nama' => $item['sekretaris']['nama'].' (Sekretaris)',
                        'email' => $item['sekretaris']['email'],
                        'password' => $defaultPassword,
                        'id_unit' => $unit->id_unit,
                        'id_role' => 3, // staff_tu / sekretaris
                        'status' => 'aktif',
                    ]
                );
            } else {
                // Buat staf TU unit untuk mengelola administrasi surat masuk / draf surat keluar unit
                $slug = strtolower(str_replace(['-', ' '], '_', $item['kode']));
                User::updateOrCreate(
                    ['username' => 'staff_'.$slug],
                    [
                        'nama' => 'Staff TU '.$item['nama'],
                        'email' => 'staff_'.$slug.'@uinssc.ac.id',
                        'password' => $defaultPassword,
                        'id_unit' => $unit->id_unit,
                        'id_role' => 3, // staff_tu
                        'status' => 'aktif',
                    ]
                );
            }

            // 4. Format nomor default untuk unit
            foreach ($allJenisSurat as $j) {
                DB::table('format_nomor')->updateOrInsert(
                    [
                        'id_unit' => $unit->id_unit,
                        'id_jenis_surat' => $j->id_jenis_surat,
                        'kategori_id' => null,
                    ],
                    [
                        'format_nomor' => '{nomor}/'.$j->kode_jenis.'/'.$unit->kode_unit.'/{tahun}',
                        'contoh' => '001/'.$j->kode_jenis.'/'.$unit->kode_unit.'/'.date('Y'),
                        'nomor_terakhir' => 0,
                        'aktif' => 1,
                    ]
                );
            }
        }
    }
}
