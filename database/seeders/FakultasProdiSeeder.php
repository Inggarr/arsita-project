<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FakultasProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');
        $allJenisSurat = JenisSurat::all();

        // Struktur data Fakultas dan Prodi sesuai permintaan user
        $fakultasData = [
            [
                'nama' => 'Fakultas Ilmu Tarbiyah dan Keguruan',
                'singkatan' => 'FITK',
                'kode' => 'Fak.01',
                'prefix_user' => 'fitk',
                'prodi' => [
                    ['nama' => 'Informatika (S1)', 'kode' => 'INF-S1', 'slug' => 'ti'],
                    ['nama' => 'Manajemen Pendidikan Islam (S1)', 'kode' => 'MPI-S1', 'slug' => 'mpi'],
                    ['nama' => 'Matematika (S1)', 'kode' => 'MAT-S1', 'slug' => 'mat'],
                    ['nama' => 'Pendidikan Agama Islam (S1)', 'kode' => 'PAI-S1', 'slug' => 'pai'],
                    ['nama' => 'Pendidikan Bahasa Arab (S1)', 'kode' => 'PBA-S1', 'slug' => 'pba'],
                    ['nama' => 'Pendidikan Guru Madrasah Ibtidaiyah (S1)', 'kode' => 'PGMI-S1', 'slug' => 'pgmi'],
                    ['nama' => 'Pendidikan Islam Anak Usia Dini (S1)', 'kode' => 'PIAUD-S1', 'slug' => 'piaud'],
                    ['nama' => 'PJJ Pendidikan Agama Islam (S1)', 'kode' => 'PJJ-PAI', 'slug' => 'pjj_pai'],
                    ['nama' => 'PJJ Pendidikan Bahasa Arab (S1)', 'kode' => 'PJJ-PBA', 'slug' => 'pjj_pba'],
                    ['nama' => 'PJJ Pendidikan Guru Madrasah Ibtidaiyah (S1)', 'kode' => 'PJJ-PGMI', 'slug' => 'pjj_pgmi'],
                    ['nama' => 'Tadris Bahasa Indonesia (S1)', 'kode' => 'TBIN-S1', 'slug' => 'tbin'],
                    ['nama' => 'Tadris Bahasa Inggris (S1)', 'kode' => 'TBING-S1', 'slug' => 'tbing'],
                    ['nama' => 'Tadris Biologi (S1)', 'kode' => 'TBIO-S1', 'slug' => 'tbio'],
                    ['nama' => 'Tadris Ilmu Pengetahuan Sosial (IPS) (S1)', 'kode' => 'TIPS-S1', 'slug' => 'tips'],
                    ['nama' => 'Tadris Kimia (S1)', 'kode' => 'TKIM-S1', 'slug' => 'tkim'],
                    ['nama' => 'Tadris Matematika (S1)', 'kode' => 'TMAT-S1', 'slug' => 'tmat'],
                ],
            ],
            [
                'nama' => 'Fakultas Ekonomi dan Bisnis Islam',
                'singkatan' => 'FEBI',
                'kode' => 'Fak.02',
                'prefix_user' => 'febi',
                'prodi' => [
                    ['nama' => 'Akuntansi Syariah (S1)', 'kode' => 'AKS-S1', 'slug' => 'aks'],
                    ['nama' => 'Bioteknologi (S1)', 'kode' => 'BIO-S1', 'slug' => 'bio'],
                    ['nama' => 'Ekonomi Syariah (S1)', 'kode' => 'EKS-S1', 'slug' => 'eks'],
                    ['nama' => 'Pariwisata Syariah (S1)', 'kode' => 'PAR-S1', 'slug' => 'par'],
                    ['nama' => 'Perbankan Syariah (S1)', 'kode' => 'PBS-S1', 'slug' => 'pbs'],
                ],
            ],
            [
                'nama' => 'Fakultas Syariah',
                'singkatan' => 'FS',
                'kode' => 'Fak.03',
                'prefix_user' => 'syariah',
                'prodi' => [
                    ['nama' => 'Hukum Ekonomi Syariah (Muamalah) (S1)', 'kode' => 'HES-S1', 'slug' => 'hes'],
                    ['nama' => 'Hukum Keluarga (Akhwal Syakhsiyah) (S1)', 'kode' => 'HK-S1', 'slug' => 'hk'],
                    ['nama' => 'Hukum Tatanegara Islam (S1)', 'kode' => 'HTN-S1', 'slug' => 'htn'],
                    ['nama' => 'Ilmu Falak (S1)', 'kode' => 'IF-S1', 'slug' => 'falak'],
                    ['nama' => 'PJJ Hukum Keluarga Islam (Akhwal Syakhshiyyah) (S1)', 'kode' => 'PJJ-HK', 'slug' => 'pjj_hk'],
                ],
            ],
            [
                'nama' => 'Fakultas Dakwah dan Komunikasi Islam',
                'singkatan' => 'FDKI',
                'kode' => 'Fak.04',
                'prefix_user' => 'fdki',
                'prodi' => [
                    ['nama' => 'Bimbingan dan Konseling Islam (S1)', 'kode' => 'BKI-S1', 'slug' => 'bki'],
                    ['nama' => 'Komunikasi dan Penyiaran Islam (S1)', 'kode' => 'KPI-S1', 'slug' => 'kpi'],
                    ['nama' => 'Pengembangan Masyarakat Islam (S1)', 'kode' => 'PMI-S1', 'slug' => 'pmi'],
                    ['nama' => 'Sosiologi Agama (S1)', 'kode' => 'SA-S1', 'slug' => 'sa'],
                ],
            ],
            [
                'nama' => 'Fakultas Ushuluddin dan Adab',
                'singkatan' => 'FUA',
                'kode' => 'Fak.05',
                'prefix_user' => 'fua',
                'prodi' => [
                    ['nama' => 'Aqidah dan Filsafat Islam (S1)', 'kode' => 'AFI-S1', 'slug' => 'afi'],
                    ['nama' => 'Bahasa dan Sastra Arab (S1)', 'kode' => 'BSA-S1', 'slug' => 'bsa'],
                    ['nama' => 'Ilmu Al-Qur\'an dan Tafsir (S1)', 'kode' => 'IAT-S1', 'slug' => 'iat'],
                    ['nama' => 'Ilmu Hadis (S1)', 'kode' => 'ILHA-S1', 'slug' => 'ilha'],
                    ['nama' => 'PJJ Sejarah Peradaban Islam (S1)', 'kode' => 'PJJ-SPI', 'slug' => 'pjj_spi'],
                    ['nama' => 'Sejarah Peradaban Islam (S1)', 'kode' => 'SPI-S1', 'slug' => 'spi'],
                    ['nama' => 'Tasawuf dan Psikoterapi (S1)', 'kode' => 'TP-S1', 'slug' => 'tp'],
                ],
            ],
        ];

        foreach ($fakultasData as $fak) {
            // 1. Buat atau update Unit Fakultas
            $unitFak = Unit::updateOrCreate(
                ['kode_unit' => $fak['kode']],
                [
                    'nama_unit' => $fak['nama'],
                    'level' => 'fakultas',
                    'id_parent' => 1, // Parent: Rektorat
                    'status' => 'aktif',
                ]
            );

            // 2. Akun Dekan & Staff TU Fakultas
            // Dekan
            $usernameDekan = 'dekan_'.$fak['prefix_user'];
            if ($fak['prefix_user'] === 'fitk') {
                $usernameDekan = 'dekan'; // pertahankan akun dekan eksisting
            }
            User::updateOrCreate(
                ['username' => $usernameDekan],
                [
                    'nama' => 'Dekan '.$fak['singkatan'].' (Pimpinan)',
                    'email' => 'dekan_'.$fak['prefix_user'].'@uinssc.ac.id',
                    'password' => $defaultPassword,
                    'id_unit' => $unitFak->id_unit,
                    'id_role' => 4, // pimpinan
                    'status' => 'aktif',
                ]
            );

            // Staff TU Fakultas
            $usernameStaffFak = 'staff_'.$fak['prefix_user'];
            if ($fak['prefix_user'] === 'fitk') {
                $usernameStaffFak = 'staff_fakultas'; // pertahankan akun staff fakultas eksisting
            }
            User::updateOrCreate(
                ['username' => $usernameStaffFak],
                [
                    'nama' => 'Staff TU '.$fak['singkatan'],
                    'email' => 'tu_'.$fak['prefix_user'].'@uinssc.ac.id',
                    'password' => $defaultPassword,
                    'id_unit' => $unitFak->id_unit,
                    'id_role' => 3, // staff_tu
                    'status' => 'aktif',
                ]
            );

            // 3. Format nomor default untuk Fakultas
            foreach ($allJenisSurat as $j) {
                DB::table('format_nomor')->updateOrInsert(
                    [
                        'id_unit' => $unitFak->id_unit,
                        'id_jenis_surat' => $j->id_jenis_surat,
                        'kategori_id' => null,
                    ],
                    [
                        'format_nomor' => '{nomor}/'.$j->kode_jenis.'/'.$unitFak->kode_unit.'/{tahun}',
                        'contoh' => '001/'.$j->kode_jenis.'/'.$unitFak->kode_unit.'/'.date('Y'),
                        'nomor_terakhir' => 0,
                        'aktif' => 1,
                    ]
                );
            }

            // 4. Proses Program Studi
            foreach ($fak['prodi'] as $p) {
                // Untuk Prodi Informatika (S1), pertahankan id_unit 3 jika sudah ada
                $unitProdiQuery = Unit::where('level', 'prodi')
                    ->where(function ($w) use ($p, $unitFak) {
                        $w->where('kode_unit', $p['kode'])
                            ->orWhere(function ($sub) use ($p, $unitFak) {
                                $sub->where('nama_unit', 'like', '%'.explode(' (', $p['nama'])[0].'%')
                                    ->where('id_parent', $unitFak->id_unit);
                            });
                    });

                $unitProdi = $unitProdiQuery->first();

                if (! $unitProdi) {
                    $unitProdi = Unit::create([
                        'nama_unit' => 'Prodi '.$p['nama'],
                        'kode_unit' => $p['kode'],
                        'level' => 'prodi',
                        'id_parent' => $unitFak->id_unit,
                        'status' => 'aktif',
                    ]);
                } else {
                    $unitProdi->update([
                        'nama_unit' => 'Prodi '.$p['nama'],
                        'kode_unit' => $p['kode'],
                        'id_parent' => $unitFak->id_unit,
                        'status' => 'aktif',
                    ]);
                }

                // Buat Akun Kaprodi (Pimpinan) & Staff TU Prodi
                $usernameKaprodi = 'kaprodi_'.$p['slug'];
                $usernameStaffProdi = 'tu_'.$p['slug'];

                if ($p['slug'] === 'ti') {
                    $usernameKaprodi = 'kajur'; // pertahankan eksisting
                    $usernameStaffProdi = 'staff_prodi'; // pertahankan eksisting
                }

                User::updateOrCreate(
                    ['username' => $usernameKaprodi],
                    [
                        'nama' => 'Kaprodi '.$p['nama'].' (Pimpinan)',
                        'email' => 'kaprodi_'.$p['slug'].'@uinssc.ac.id',
                        'password' => $defaultPassword,
                        'id_unit' => $unitProdi->id_unit,
                        'id_role' => 4, // pimpinan
                        'status' => 'aktif',
                    ]
                );

                User::updateOrCreate(
                    ['username' => $usernameStaffProdi],
                    [
                        'nama' => 'Staff TU '.$p['nama'],
                        'email' => 'tu_'.$p['slug'].'@uinssc.ac.id',
                        'password' => $defaultPassword,
                        'id_unit' => $unitProdi->id_unit,
                        'id_role' => 3, // staff_tu
                        'status' => 'aktif',
                    ]
                );

                // Format nomor default untuk Prodi
                foreach ($allJenisSurat as $j) {
                    DB::table('format_nomor')->updateOrInsert(
                        [
                            'id_unit' => $unitProdi->id_unit,
                            'id_jenis_surat' => $j->id_jenis_surat,
                            'kategori_id' => null,
                        ],
                        [
                            'format_nomor' => '{nomor}/'.$j->kode_jenis.'/'.$unitProdi->kode_unit.'/{tahun}',
                            'contoh' => '001/'.$j->kode_jenis.'/'.$unitProdi->kode_unit.'/'.date('Y'),
                            'nomor_terakhir' => 0,
                            'aktif' => 1,
                        ]
                    );
                }
            }
        }
    }
}
