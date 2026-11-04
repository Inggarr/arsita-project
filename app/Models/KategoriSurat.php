<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriSurat extends Model
{
    protected $table = 'kategori_surat';

    protected $primaryKey = 'id_kategori';

    public $timestamps = false;

    protected $fillable = [
        'nama_kategori',
        'kode_kategori',
        'deskripsi',
    ];

    public function jenisSurat()
    {
        return $this->hasMany(JenisSurat::class, 'kategori_id', 'id_kategori');
    }

    public function suratMasuk()
    {
        return $this->hasMany(SuratMasuk::class, 'kategori_id', 'id_kategori');
    }

    public function suratKeluar()
    {
        return $this->hasMany(SuratKeluar::class, 'kategori_id', 'id_kategori');
    }
}
