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
        // 1. Tambah kode_kategori (kode arsip) ke kategori_surat
        Schema::table('kategori_surat', function (Blueprint $table) {
            if (! Schema::hasColumn('kategori_surat', 'kode_kategori')) {
                $table->string('kode_kategori', 30)->nullable()->after('nama_kategori');
            }
        });

        // 2. Tambah kategori_id ke jenis_surat untuk pengelompokan jenis berdasarkan kategori
        Schema::table('jenis_surat', function (Blueprint $table) {
            if (! Schema::hasColumn('jenis_surat', 'kategori_id')) {
                $table->integer('kategori_id')->nullable()->after('id_jenis_surat');
            }
        });

        // 3. Tambah kode_jabatan ke users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'kode_jabatan')) {
                $table->string('kode_jabatan', 50)->nullable()->after('nama');
            }
        });

        // 4. Update enum sifat pada surat_keluar agar mendukung sangat_rahasia & terbatas
        // Menggunakan DB raw statement untuk alter enum jika mysql
        try {
            DB::statement("ALTER TABLE `surat_keluar` MODIFY COLUMN `sifat` ENUM('biasa', 'terbatas', 'penting', 'rahasia', 'sangat_rahasia') DEFAULT 'biasa'");
        } catch (Throwable $e) {
            // fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_surat', function (Blueprint $table) {
            if (Schema::hasColumn('kategori_surat', 'kode_kategori')) {
                $table->dropColumn('kode_kategori');
            }
        });

        Schema::table('jenis_surat', function (Blueprint $table) {
            if (Schema::hasColumn('jenis_surat', 'kategori_id')) {
                $table->dropColumn('kategori_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'kode_jabatan')) {
                $table->dropColumn('kode_jabatan');
            }
        });
    }
};
