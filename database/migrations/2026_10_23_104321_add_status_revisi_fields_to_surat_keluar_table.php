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
            $table->boolean('sudah_direvisi')->default(false)->after('status_surat');
            $table->text('catatan_revisi_terakhir')->nullable()->after('sudah_direvisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropColumn(['sudah_direvisi', 'catatan_revisi_terakhir']);
        });
    }
};
