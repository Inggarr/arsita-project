<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TindakLanjut extends Model
{
    protected $table = 'tindak_lanjut';

    protected $primaryKey = 'id_tindak_lanjut';

    public $timestamps = false;

    protected $fillable = [
        'id_disposisi_tujuan',
        'catatan',
        'lampiran',
        'tanggal_update',
    ];

    protected $casts = [
        'tanggal_update' => 'datetime',
    ];

    public function disposisiTujuan()
    {
        return $this->belongsTo(DisposisiTujuan::class, 'id_disposisi_tujuan', 'id_disposisi_tujuan');
    }
}
