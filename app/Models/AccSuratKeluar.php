<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccSuratKeluar extends Model
{
    protected $table = 'acc_surat_keluar';

    protected $primaryKey = 'id_acc';

    public $timestamps = false;

    protected $fillable = [
        'id_surat_keluar',
        'tahap_ke',
        'disetujui_oleh',
        'status',
        'catatan_penolakan',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function suratKeluar()
    {
        return $this->belongsTo(SuratKeluar::class, 'id_surat_keluar', 'id_surat_keluar');
    }

    public function pemeriksa()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh', 'id_user');
    }
}
