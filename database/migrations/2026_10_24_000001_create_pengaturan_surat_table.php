<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_surat', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('umum'); // kop_surat | format_surat
            $table->timestamps();
        });

        // Seed default values
        $defaults = [
            // Kop Surat
            ['key' => 'kop_nama_instansi',   'value' => 'Universitas Islam Negeri Sunan Syekh Nurjati Cirebon', 'group' => 'kop_surat'],
            ['key' => 'kop_singkatan',        'value' => 'UINSSC',                                               'group' => 'kop_surat'],
            ['key' => 'kop_alamat',           'value' => 'Jl. Perjuangan By Pass Sunyaragi',                    'group' => 'kop_surat'],
            ['key' => 'kop_kota',             'value' => 'Cirebon',                                              'group' => 'kop_surat'],
            ['key' => 'kop_kode_pos',         'value' => '45132',                                                'group' => 'kop_surat'],
            ['key' => 'kop_telepon',          'value' => '(0231) 481264',                                        'group' => 'kop_surat'],
            ['key' => 'kop_email',            'value' => 'info@uinssc.ac.id',                                    'group' => 'kop_surat'],
            ['key' => 'kop_website',          'value' => 'https://www.uinssc.ac.id',                             'group' => 'kop_surat'],
            ['key' => 'kop_logo',             'value' => null,                                                   'group' => 'kop_surat'],

            // Format Surat
            ['key' => 'format_ukuran_kertas', 'value' => 'A4',     'group' => 'format_surat'],
            ['key' => 'format_orientasi',     'value' => 'potrait', 'group' => 'format_surat'],
            ['key' => 'format_font',          'value' => 'Times New Roman', 'group' => 'format_surat'],
            ['key' => 'format_ukuran_font',   'value' => '12',      'group' => 'format_surat'],
            ['key' => 'format_margin_atas',   'value' => '3',       'group' => 'format_surat'],
            ['key' => 'format_margin_bawah',  'value' => '3',       'group' => 'format_surat'],
            ['key' => 'format_margin_kiri',   'value' => '4',       'group' => 'format_surat'],
            ['key' => 'format_margin_kanan',  'value' => '3',       'group' => 'format_surat'],
            ['key' => 'format_spasi',         'value' => '1.5',     'group' => 'format_surat'],
        ];

        foreach ($defaults as $row) {
            DB::table('pengaturan_surat')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_surat');
    }
};
