<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    protected $table = 'surat_keluar';

    protected $primaryKey = 'id_surat_keluar';

    public $timestamps = false;

    protected $fillable = [
        'id_unit',
        'kategori_id',
        'jenis_surat_id',
        'sifat',
        'tujuan_unit_id',
        'tujuan',
        'lampiran',
        'perihal',
        'isi_surat',
        'tembusan',
        'tanggal_draft',
        'file_draft',
        'file_final',
        'status_surat',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_draft' => 'date',
        'created_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }

    public function tujuanUnit()
    {
        return $this->belongsTo(Unit::class, 'tujuan_unit_id', 'id_unit');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSurat::class, 'kategori_id', 'id_kategori');
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id', 'id_jenis_surat');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }

    public function nomorSurat()
    {
        return $this->hasOne(NomorSurat::class, 'id_surat_keluar', 'id_surat_keluar');
    }

    public function persetujuan()
    {
        return $this->hasMany(AccSuratKeluar::class, 'id_surat_keluar', 'id_surat_keluar')->orderBy('tahap_ke', 'asc');
    }
}
