<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    protected $table = 'jenis_surat';

    protected $primaryKey = 'id_jenis_surat';

    public $timestamps = false;

    protected $fillable = [
        'nama_jenis',
        'kode_jenis',
        'keterangan',
    ];

    public function suratMasuk()
    {
        return $this->hasMany(SuratMasuk::class, 'jenis_surat_id', 'id_jenis_surat');
    }

    public function suratKeluar()
    {
        return $this->hasMany(SuratKeluar::class, 'jenis_surat_id', 'id_jenis_surat');
    }

    public function formatNomor()
    {
        return $this->hasMany(FormatNomor::class, 'id_jenis_surat', 'id_jenis_surat');
    }
}
