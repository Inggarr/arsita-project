<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SifatSurat extends Model
{
    use HasFactory;

    protected $table = 'sifat_surat';

    protected $primaryKey = 'id_sifat';

    protected $fillable = [
        'kode_sifat',
        'nama_sifat',
        'warna_tinta',
        'badge_bg',
        'badge_text',
        'keterangan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];
}
