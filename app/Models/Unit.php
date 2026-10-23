<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'units';

    protected $primaryKey = 'id_unit';

    public $timestamps = false;

    protected $fillable = [
        'nama_unit',
        'kode_unit',
        'level',
        'id_parent',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(Unit::class, 'id_parent', 'id_unit');
    }

    public function children()
    {
        return $this->hasMany(Unit::class, 'id_parent', 'id_unit');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_unit', 'id_unit');
    }

    public function suratMasuk()
    {
        return $this->hasMany(SuratMasuk::class, 'id_unit', 'id_unit');
    }

    public function suratKeluar()
    {
        return $this->hasMany(SuratKeluar::class, 'id_unit', 'id_unit');
    }

    public function formatNomor()
    {
        return $this->hasMany(FormatNomor::class, 'id_unit', 'id_unit');
    }
}
