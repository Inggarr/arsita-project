<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorSurat extends Model
{
    protected $table = 'nomor_surat';

    protected $primaryKey = 'id_nomor';

    public $timestamps = false;

    protected $fillable = [
        'id_surat_keluar',
        'nomor_surat',
        'tanggal_nomor',
        'tahun',
        'id_unit',
        'id_jenis_surat',
        'kategori_id',
    ];

    protected $casts = [
        'tanggal_nomor' => 'date',
        'tahun' => 'integer',
    ];

    public function suratKeluar()
    {
        return $this->belongsTo(SuratKeluar::class, 'id_surat_keluar', 'id_surat_keluar');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class, 'id_jenis_surat', 'id_jenis_surat');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSurat::class, 'kategori_id', 'id_kategori');
    }
}
