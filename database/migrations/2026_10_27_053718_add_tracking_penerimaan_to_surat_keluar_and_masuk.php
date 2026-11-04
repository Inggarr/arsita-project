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
        Schema::table('surat_keluar', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_keluar', 'status_penerimaan')) {
                $table->string('status_penerimaan', 30)->nullable()->default(null)->after('status_surat');
            }
            if (! Schema::hasColumn('surat_keluar', 'diterima_pada')) {
                $table->timestamp('diterima_pada')->nullable()->after('status_penerimaan');
            }
            if (! Schema::hasColumn('surat_keluar', 'diterima_oleh')) {
                $table->integer('diterima_oleh')->nullable()->after('diterima_pada');
                $table->foreign('diterima_oleh')->references('id_user')->on('users')->nullOnDelete();
            }
        });

        Schema::table('surat_masuk', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_masuk', 'dibaca_pada')) {
                $table->timestamp('dibaca_pada')->nullable()->after('status_arsip');
            }
            if (! Schema::hasColumn('surat_masuk', 'dibaca_oleh')) {
                $table->integer('dibaca_oleh')->nullable()->after('dibaca_pada');
                $table->foreign('dibaca_oleh')->references('id_user')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            if (Schema::hasColumn('surat_masuk', 'dibaca_oleh')) {
                $table->dropForeign(['dibaca_oleh']);
                $table->dropColumn(['dibaca_pada', 'dibaca_oleh']);
            }
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            if (Schema::hasColumn('surat_keluar', 'diterima_oleh')) {
                $table->dropForeign(['diterima_oleh']);
                $table->dropColumn(['status_penerimaan', 'diterima_pada', 'diterima_oleh']);
            }
        });
    }
};
