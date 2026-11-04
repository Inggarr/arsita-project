<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sifat_surat', function (Blueprint $table) {
            $table->increments('id_sifat');
            $table->string('kode_sifat', 10)->unique(); // Misal: B, T, R, SR
            $table->string('nama_sifat', 50);          // Misal: Biasa, Terbatas, Rahasia, Sangat Rahasia
            $table->string('warna_tinta', 30)->default('hitam'); // hitam / merah
            $table->string('badge_bg', 20)->default('#f1f5f9');
            $table->string('badge_text', 20)->default('#475569');
            $table->string('keterangan', 255)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Insert default data sesuai kode.md
        DB::table('sifat_surat')->insert([
            [
                'kode_sifat' => 'B',
                'nama_sifat' => 'Biasa',
                'warna_tinta' => 'hitam',
                'badge_bg' => '#f1f5f9',
                'badge_text' => '#475569',
                'keterangan' => 'Surat bersifat biasa, tinta hitam',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_sifat' => 'T',
                'nama_sifat' => 'Terbatas',
                'warna_tinta' => 'hitam',
                'badge_bg' => '#fef3c7',
                'badge_text' => '#92400e',
                'keterangan' => 'Surat terbatas untuk kalangan tertentu, tinta hitam',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_sifat' => 'R',
                'nama_sifat' => 'Rahasia',
                'warna_tinta' => 'merah',
                'badge_bg' => '#fee2e2',
                'badge_text' => '#991b1b',
                'keterangan' => 'Surat rahasia, kode ditulis tinta merah',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_sifat' => 'SR',
                'nama_sifat' => 'Sangat Rahasia',
                'warna_tinta' => 'merah',
                'badge_bg' => '#fecdd3',
                'badge_text' => '#881337',
                'keterangan' => 'Surat sangat rahasia, kode ditulis tinta merah',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Ubah sifat di surat_keluar menjadi string agar dinamis mengambil kode/nama sifat
        try {
            DB::statement("ALTER TABLE `surat_keluar` MODIFY COLUMN `sifat` VARCHAR(50) DEFAULT 'biasa'");
            // Bersihkan data lama jika ada yang berstatus penting -> ubah jadi biasa
            DB::statement("UPDATE `surat_keluar` SET `sifat` = 'biasa' WHERE `sifat` = 'penting'");
        } catch (Throwable $e) {
            // fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sifat_surat');
    }
};
