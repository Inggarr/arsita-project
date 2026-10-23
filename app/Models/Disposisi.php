<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    protected $table = 'disposisi';

    protected $primaryKey = 'id_disposisi';

    public $timestamps = false;

    protected $fillable = [
        'id_surat_masuk',
        'dari_user_id',
        'instruksi',
        'sifat',
        'tenggat_waktu',
        'catatan',
        'tanggal_kirim',
    ];

    protected $casts = [
        'tenggat_waktu' => 'date',
        'tanggal_kirim' => 'datetime',
    ];

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'id_surat_masuk', 'id_surat_masuk');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'dari_user_id', 'id_user');
    }

    public function tujuan()
    {
        return $this->hasMany(DisposisiTujuan::class, 'id_disposisi', 'id_disposisi');
    }
}
