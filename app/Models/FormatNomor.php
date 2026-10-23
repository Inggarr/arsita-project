<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormatNomor extends Model
{
    protected $table = 'format_nomor';

    protected $primaryKey = 'id_format';

    public $timestamps = false;

    protected $fillable = [
        'id_unit',
        'id_jenis_surat',
        'kategori_id',
        'format_nomor',
        'contoh',
        'nomor_terakhir',
        'aktif',
    ];

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
