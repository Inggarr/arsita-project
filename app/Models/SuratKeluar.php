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
        'status_penerimaan',
        'diterima_pada',
        'diterima_oleh',
        'sudah_direvisi',
        'catatan_revisi_terakhir',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_draft' => 'date',
        'created_at' => 'datetime',
        'diterima_pada' => 'datetime',
        'sudah_direvisi' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }

    public function tujuanUnit()
    {
        return $this->belongsTo(Unit::class, 'tujuan_unit_id', 'id_unit');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'diterima_oleh', 'id_user');
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

    public function suratMasukTujuan()
    {
        return $this->hasOneThrough(
            SuratMasuk::class,
            NomorSurat::class,
            'id_surat_keluar',   // Foreign key on nomor_surat table
            'no_surat_asal',     // Foreign key on surat_masuk table
            'id_surat_keluar',   // Local key on surat_keluar table
            'nomor_surat'        // Local key on nomor_surat table
        );
    }

    /**
     * Dapatkan pejabat pimpinan penandatangan surat:
     * 1. Dari pejabat yang menyetujui tahap akhir (ACC).
     * 2. Atau pimpinan utama unit asal surat.
     */
    public function getPejabatPenandatanganAttribute(): ?User
    {
        $lastAcc = $this->persetujuan()
            ->where('status', 'disetujui')
            ->whereNotNull('disetujui_oleh')
            ->latest('id_acc')
            ->first();

        if ($lastAcc && $lastAcc->disetujui_oleh) {
            $user = User::find($lastAcc->disetujui_oleh);
            if ($user) {
                return $user;
            }
        }

        // Fallback pimpinan unit
        return User::where('id_unit', $this->id_unit)
            ->where('id_role', 4)
            ->first();
    }
}
