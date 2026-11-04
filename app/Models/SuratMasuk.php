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
        'dibaca_pada',
        'dibaca_oleh',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_terima' => 'date',
        'dibaca_pada' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }

    public function pembaca()
    {
        return $this->belongsTo(User::class, 'dibaca_oleh', 'id_user');
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

    public function isDariUnitLain(): bool
    {
        // 1. Jika berasal dari alur disposisi atasan/unit lain
        if ($this->asal_disposisi_id) {
            return true;
        }

        // 2. Jika berasal dari naskah dinas resmi surat keluar yang disetujui
        if ($this->suratKeluarAsal()->exists()) {
            return true;
        }

        // 3. Fallback: jika pencatatnya bukan dari unit penerima
        if ($this->pencatat && $this->id_unit && $this->pencatat->id_unit && $this->pencatat->id_unit != $this->id_unit) {
            return true;
        }

        return false;
    }

    public function canBeEditedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        // Surat dari unit lain atau hasil disposisi tidak boleh diedit oleh penerima
        if ($this->isDariUnitLain()) {
            return false;
        }

        // Admin rektorat bisa edit jika bukan dari unit lain
        if ($user->isAdminRektorat()) {
            return true;
        }

        // Hanya staf TU atau admin unit yang mencatat suratnya sendiri di unitnya yang berhak edit
        if ($user->isStaffTu() || $user->isAdminUnit()) {
            return $user->id_unit == $this->id_unit && ($user->id_user == $this->dicatat_oleh || ! $this->dicatat_oleh);
        }

        return false;
    }
}
