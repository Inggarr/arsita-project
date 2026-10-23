<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    protected $table = 'surat_masuk';

    protected $primaryKey = 'id_surat_masuk';

    public $timestamps = false;

    protected $fillable = [
        'no_surat_asal',
        'tanggal_surat',
        'tanggal_terima',
        'asal_surat',
        'perihal',
        'kategori_id',
        'jenis_surat_id',
        'id_unit',
        'file_surat',
        'keterangan',
        'asal_disposisi_id',
        'status_arsip',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_terima' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSurat::class, 'kategori_id', 'id_kategori');
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id', 'id_jenis_surat');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh', 'id_user');
    }

    public function disposisi()
    {
        return $this->hasMany(Disposisi::class, 'id_surat_masuk', 'id_surat_masuk');
    }

    public function suratKeluarAsal()
    {
        return $this->hasOneThrough(
            SuratKeluar::class,
            NomorSurat::class,
            'nomor_surat',       // Foreign key on nomor_surat table...
            'id_surat_keluar',   // Foreign key on surat_keluar table...
            'no_surat_asal',     // Local key on surat_masuk table...
            'id_surat_keluar'    // Local key on nomor_surat table...
        );
    }
}
