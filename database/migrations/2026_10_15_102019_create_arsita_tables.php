<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_role');
            $table->string('nama_role', 50);
            $table->string('deskripsi', 255)->nullable();
        });

        // 2. Units
        Schema::create('units', function (Blueprint $table) {
            $table->id('id_unit');
            $table->string('nama_unit', 100);
            $table->string('kode_unit', 20);
            $table->string('level', 20); // 'rektorat', 'fakultas', 'prodi'
            $table->unsignedBigInteger('id_parent')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->foreign('id_parent')->references('id_unit')->on('units')->nullOnDelete();
        });

        // Update default users table or adjust for arsita
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nama')) {
                $table->string('nama', 100)->nullable();
            }
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->unique();
            }
            if (! Schema::hasColumn('users', 'id_unit')) {
                $table->unsignedBigInteger('id_unit')->nullable();
                $table->foreign('id_unit')->references('id_unit')->on('units')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'id_role')) {
                $table->unsignedBigInteger('id_role')->nullable();
                $table->foreign('id_role')->references('id_role')->on('roles')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('aktif');
            }
        });

        // 3. Kategori Surat
        Schema::create('kategori_surat', function (Blueprint $table) {
            $table->id('id_kategori');
            $table->string('nama_kategori', 100);
            $table->string('deskripsi', 255)->nullable();
        });

        // 4. Jenis Surat
        Schema::create('jenis_surat', function (Blueprint $table) {
            $table->id('id_jenis_surat');
            $table->string('nama_jenis', 100);
            $table->string('kode_jenis', 20)->nullable();
            $table->string('keterangan', 255)->nullable();
        });

        // 5. Format Nomor
        Schema::create('format_nomor', function (Blueprint $table) {
            $table->id('id_format');
            $table->unsignedBigInteger('id_unit');
            $table->unsignedBigInteger('id_jenis_surat');
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->string('format_nomor', 150);
            $table->string('contoh', 150)->nullable();
            $table->integer('nomor_terakhir')->default(0);
            $table->boolean('aktif')->default(true);

            $table->foreign('id_unit')->references('id_unit')->on('units')->cascadeOnDelete();
            $table->foreign('id_jenis_surat')->references('id_jenis_surat')->on('jenis_surat')->cascadeOnDelete();
            $table->foreign('kategori_id')->references('id_kategori')->on('kategori_surat')->nullOnDelete();
        });

        // 6. Surat Masuk
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id('id_surat_masuk');
            $table->string('no_surat_asal', 100)->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->date('tanggal_terima')->nullable();
            $table->string('asal_surat', 150)->nullable();
            $table->string('perihal', 255)->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->unsignedBigInteger('jenis_surat_id')->nullable();
            $table->unsignedBigInteger('id_unit');
            $table->string('file_surat', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('asal_disposisi_id')->nullable();
            $table->string('status_arsip', 30)->default('belum_ditinjau');
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->timestamps();

            $table->foreign('kategori_id')->references('id_kategori')->on('kategori_surat')->nullOnDelete();
            $table->foreign('jenis_surat_id')->references('id_jenis_surat')->on('jenis_surat')->nullOnDelete();
            $table->foreign('id_unit')->references('id_unit')->on('units')->cascadeOnDelete();
            $table->foreign('dicatat_oleh')->references('id')->on('users')->nullOnDelete();
        });

        // 7. Surat Keluar
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id('id_surat_keluar');
            $table->unsignedBigInteger('id_unit');
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->unsignedBigInteger('jenis_surat_id')->nullable();
            $table->string('sifat', 20)->default('biasa');
            $table->string('tujuan', 255)->nullable();
            $table->string('lampiran', 100)->nullable();
            $table->string('perihal', 255)->nullable();
            $table->text('isi_surat')->nullable();
            $table->text('tembusan')->nullable();
            $table->date('tanggal_draft')->nullable();
            $table->string('file_draft', 255)->nullable();
            $table->string('file_final', 255)->nullable();
            $table->string('status_surat', 30)->default('draft');
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();

            $table->foreign('id_unit')->references('id_unit')->on('units')->cascadeOnDelete();
            $table->foreign('kategori_id')->references('id_kategori')->on('kategori_surat')->nullOnDelete();
            $table->foreign('jenis_surat_id')->references('id_jenis_surat')->on('jenis_surat')->nullOnDelete();
            $table->foreign('dibuat_oleh')->references('id')->on('users')->nullOnDelete();
        });

        // 8. ACC Surat Keluar
        Schema::create('acc_surat_keluar', function (Blueprint $table) {
            $table->id('id_acc');
            $table->unsignedBigInteger('id_surat_keluar');
            $table->integer('tahap_ke');
            $table->unsignedBigInteger('disetujui_oleh')->nullable();
            $table->string('status', 20)->default('menunggu');
            $table->text('catatan_penolakan')->nullable();
            $table->timestamp('tanggal')->nullable();

            $table->foreign('id_surat_keluar')->references('id_surat_keluar')->on('surat_keluar')->cascadeOnDelete();
            $table->foreign('disetujui_oleh')->references('id')->on('users')->nullOnDelete();
        });

        // 9. Nomor Surat
        Schema::create('nomor_surat', function (Blueprint $table) {
            $table->id('id_nomor');
            $table->unsignedBigInteger('id_surat_keluar');
            $table->string('nomor_surat', 100);
            $table->date('tanggal_nomor')->nullable();
            $table->integer('tahun')->nullable();
            $table->unsignedBigInteger('id_unit')->nullable();
            $table->unsignedBigInteger('id_jenis_surat')->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->timestamps();

            $table->foreign('id_surat_keluar')->references('id_surat_keluar')->on('surat_keluar')->cascadeOnDelete();
            $table->foreign('id_unit')->references('id_unit')->on('units')->nullOnDelete();
            $table->foreign('id_jenis_surat')->references('id_jenis_surat')->on('jenis_surat')->nullOnDelete();
            $table->foreign('kategori_id')->references('id_kategori')->on('kategori_surat')->nullOnDelete();
        });

        // 10. Disposisi
        Schema::create('disposisi', function (Blueprint $table) {
            $table->id('id_disposisi');
            $table->unsignedBigInteger('id_surat_masuk');
            $table->unsignedBigInteger('dari_user_id')->nullable();
            $table->text('instruksi')->nullable();
            $table->string('sifat', 20)->default('biasa');
            $table->date('tenggat_waktu')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_kirim')->useCurrent();

            $table->foreign('id_surat_masuk')->references('id_surat_masuk')->on('surat_masuk')->cascadeOnDelete();
            $table->foreign('dari_user_id')->references('id')->on('users')->nullOnDelete();
        });

        // 11. Disposisi Tujuan
        Schema::create('disposisi_tujuan', function (Blueprint $table) {
            $table->id('id_disposisi_tujuan');
            $table->unsignedBigInteger('id_disposisi');
            $table->string('tujuan_tipe', 20); // 'individu', 'unit'
            $table->unsignedBigInteger('ke_user_id')->nullable();
            $table->unsignedBigInteger('ke_unit_id')->nullable();
            $table->string('status', 30)->default('belum_diproses');

            $table->foreign('id_disposisi')->references('id_disposisi')->on('disposisi')->cascadeOnDelete();
            $table->foreign('ke_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('ke_unit_id')->references('id_unit')->on('units')->nullOnDelete();
        });

        // 12. Tindak Lanjut
        Schema::create('tindak_lanjut', function (Blueprint $table) {
            $table->id('id_tindak_lanjut');
            $table->unsignedBigInteger('id_disposisi_tujuan');
            $table->text('catatan')->nullable();
            $table->string('lampiran', 255)->nullable();
            $table->timestamp('tanggal_update')->useCurrent();

            $table->foreign('id_disposisi_tujuan')->references('id_disposisi_tujuan')->on('disposisi_tujuan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut');
        Schema::dropIfExists('disposisi_tujuan');
        Schema::dropIfExists('disposisi');
        Schema::dropIfExists('nomor_surat');
        Schema::dropIfExists('acc_surat_keluar');
        Schema::dropIfExists('surat_keluar');
        Schema::dropIfExists('surat_masuk');
        Schema::dropIfExists('format_nomor');
        Schema::dropIfExists('jenis_surat');
        Schema::dropIfExists('kategori_surat');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_unit']);
            $table->dropForeign(['id_role']);
            $table->dropColumn(['username', 'id_unit', 'id_role', 'status']);
        });

        Schema::dropIfExists('units');
        Schema::dropIfExists('roles');
    }
};
