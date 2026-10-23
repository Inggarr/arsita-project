<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisposisiTujuan extends Model
{
    protected $table = 'disposisi_tujuan';

    protected $primaryKey = 'id_disposisi_tujuan';

    public $timestamps = false;

    protected $fillable = [
        'id_disposisi',
        'tujuan_tipe',
        'ke_user_id',
        'ke_unit_id',
        'status',
    ];

    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class, 'id_disposisi', 'id_disposisi');
    }

    public function penerimaUser()
    {
        return $this->belongsTo(User::class, 'ke_user_id', 'id_user');
    }

    public function penerimaUnit()
    {
        return $this->belongsTo(Unit::class, 'ke_unit_id', 'id_unit');
    }

    public function tindakLanjut()
    {
        return $this->hasMany(TindakLanjut::class, 'id_disposisi_tujuan', 'id_disposisi_tujuan')->orderBy('tanggal_update', 'desc');
    }
}
