<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['id_role' => 1, 'nama_role' => 'admin_rektorat', 'deskripsi' => 'Kelola data master global, oversight semua unit'],
            ['id_role' => 2, 'nama_role' => 'admin_unit', 'deskripsi' => 'Kelola akun dan master data unit sendiri'],
            ['id_role' => 3, 'nama_role' => 'staff_tu', 'deskripsi' => 'Input surat masuk, buat draft surat keluar'],
            ['id_role' => 4, 'nama_role' => 'pimpinan', 'deskripsi' => 'ACC surat keluar, buat disposisi, tinjau surat masuk'],
        ];
        DB::table('roles')->insertOrIgnore($roles);

        // 2. Units
        $units = [
            ['id_unit' => 1, 'nama_unit' => 'Rektorat', 'kode_unit' => 'UN.14', 'level' => 'rektorat', 'id_parent' => null, 'status' => 'aktif'],
            ['id_unit' => 2, 'nama_unit' => 'Fakultas Ilmu Tarbiyah dan Keguruan', 'kode_unit' => 'Fak.01', 'level' => 'fakultas', 'id_parent' => 1, 'status' => 'aktif'],
            ['id_unit' => 3, 'nama_unit' => 'Prodi Informatika', 'kode_unit' => 'Prodi.03', 'level' => 'prodi', 'id_parent' => 2, 'status' => 'aktif'],
        ];
        DB::table('units')->insertOrIgnore($units);

        // 3. Kategori Surat
        $kategori = [
            ['id_kategori' => 1, 'nama_kategori' => 'Akademik', 'deskripsi' => 'Perkuliahan, penelitian, kurikulum'],
            ['id_kategori' => 2, 'nama_kategori' => 'Kemahasiswaan', 'deskripsi' => 'Organisasi mahasiswa, beasiswa, kedisiplinan'],
            ['id_kategori' => 3, 'nama_kategori' => 'Kepegawaian', 'deskripsi' => 'Dosen dan staff (SK, cuti, tugas dinas)'],
            ['id_kategori' => 4, 'nama_kategori' => 'Keuangan', 'deskripsi' => 'Anggaran, pembayaran, tagihan'],
            ['id_kategori' => 5, 'nama_kategori' => 'Kerjasama', 'deskripsi' => 'MoU, hubungan dengan pihak luar'],
            ['id_kategori' => 6, 'nama_kategori' => 'Umum', 'deskripsi' => 'Administrasi harian, edaran, rapat internal'],
        ];
        DB::table('kategori_surat')->insertOrIgnore($kategori);

        // 4. Jenis Surat
        $jenis = [
            ['id_jenis_surat' => 1, 'nama_jenis' => 'Surat Keputusan', 'kode_jenis' => 'SK', 'keterangan' => 'Surat Keputusan Resmi'],
            ['id_jenis_surat' => 2, 'nama_jenis' => 'Surat Tugas', 'kode_jenis' => 'ST', 'keterangan' => 'Surat Penugasan'],
            ['id_jenis_surat' => 3, 'nama_jenis' => 'Surat Edaran', 'kode_jenis' => 'SE', 'keterangan' => 'Surat Edaran Bersama'],
            ['id_jenis_surat' => 4, 'nama_jenis' => 'Surat Undangan', 'kode_jenis' => 'SU', 'keterangan' => 'Undangan Kegiatan'],
            ['id_jenis_surat' => 5, 'nama_jenis' => 'Surat Permohonan', 'kode_jenis' => 'SP', 'keterangan' => 'Permohonan / Pengajuan'],
            ['id_jenis_surat' => 6, 'nama_jenis' => 'Surat Pemberitahuan', 'kode_jenis' => 'SPB', 'keterangan' => 'Pemberitahuan Umum'],
        ];
        DB::table('jenis_surat')->insertOrIgnore($jenis);

        // 5. Format Nomor Default
        $formatNomor = [];
        foreach ($units as $u) {
            foreach ($jenis as $j) {
                $formatNomor[] = [
                    'id_unit' => $u['id_unit'],
                    'id_jenis_surat' => $j['id_jenis_surat'],
                    'kategori_id' => null,
                    'format_nomor' => '{nomor}/'.$j['kode_jenis'].'/'.$u['kode_unit'].'/{tahun}',
                    'contoh' => '001/'.$j['kode_jenis'].'/'.$u['kode_unit'].'/'.date('Y'),
                    'nomor_terakhir' => 0,
                    'aktif' => 1,
                ];
            }
        }
        DB::table('format_nomor')->insertOrIgnore($formatNomor);

        // 6. Users
        $defaultPassword = Hash::make('password123');
        $sampleUsers = [
            ['nama' => 'Admin Rektorat', 'username' => 'admin_rektorat', 'password' => $defaultPassword, 'email' => 'admin@uinssc.ac.id', 'id_unit' => 1, 'id_role' => 1, 'status' => 'aktif'],
            ['nama' => 'Staff TU Rektorat', 'username' => 'staff_rektorat', 'password' => $defaultPassword, 'email' => 'tu_rektorat@uinssc.ac.id', 'id_unit' => 1, 'id_role' => 3, 'status' => 'aktif'],
            ['nama' => 'Prof. Dr. Rektor (Pimpinan)', 'username' => 'rektor', 'password' => $defaultPassword, 'email' => 'rektor@uinssc.ac.id', 'id_unit' => 1, 'id_role' => 4, 'status' => 'aktif'],
            ['nama' => 'Kabag Rektorat (Pimpinan)', 'username' => 'kabag', 'password' => $defaultPassword, 'email' => 'kabag@uinssc.ac.id', 'id_unit' => 1, 'id_role' => 4, 'status' => 'aktif'],
            ['nama' => 'Kasubbag Rektorat (Pimpinan)', 'username' => 'kasubbag', 'password' => $defaultPassword, 'email' => 'kasubbag@uinssc.ac.id', 'id_unit' => 1, 'id_role' => 4, 'status' => 'aktif'],
            ['nama' => 'Staff TU Fakultas', 'username' => 'staff_fakultas', 'password' => $defaultPassword, 'email' => 'tu_fitk@uinssc.ac.id', 'id_unit' => 2, 'id_role' => 3, 'status' => 'aktif'],
            ['nama' => 'Dekan FITK (Pimpinan)', 'username' => 'dekan', 'password' => $defaultPassword, 'email' => 'dekan_fitk@uinssc.ac.id', 'id_unit' => 2, 'id_role' => 4, 'status' => 'aktif'],
            ['nama' => 'Staff TU Prodi', 'username' => 'staff_prodi', 'password' => $defaultPassword, 'email' => 'tu_ti@uinssc.ac.id', 'id_unit' => 3, 'id_role' => 3, 'status' => 'aktif'],
            ['nama' => 'Kajur Informatika (Pimpinan)', 'username' => 'kajur', 'password' => $defaultPassword, 'email' => 'kajur_ti@uinssc.ac.id', 'id_unit' => 3, 'id_role' => 4, 'status' => 'aktif'],
        ];

        foreach ($sampleUsers as $u) {
            DB::table('users')->updateOrInsert(
                ['username' => $u['username']],
                $u
            );
        }
    }
}
