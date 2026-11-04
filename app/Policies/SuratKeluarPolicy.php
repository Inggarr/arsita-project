<?php

namespace App\Policies;

use App\Models\SuratKeluar;
use App\Models\User;

class SuratKeluarPolicy
{
    /**
     * Admin Rektorat memiliki bypass penuh untuk semua aksi
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdminRektorat()) {
            return true;
        }

        return null;
    }

    /**
     * Boleh melihat naskah surat keluar jika:
     * - Berasal dari unit yang sama dengan pembuat surat
     * - Atau merupakan unit penerima internal surat tersebut
     */
    public function view(User $user, SuratKeluar $suratKeluar): bool
    {
        // Unit pengirim/pembuat surat
        if ($user->id_unit && $user->id_unit === $suratKeluar->id_unit) {
            return true;
        }

        // Unit penerima internal surat
        if ($suratKeluar->tujuan_unit_id && $user->id_unit === $suratKeluar->tujuan_unit_id) {
            return true;
        }

        // Pembuat surat langsung
        if ($suratKeluar->dibuat_oleh && $user->id_user === $suratKeluar->dibuat_oleh) {
            return true;
        }

        return false;
    }

    /**
     * Boleh mengedit draft/revisi surat jika:
     * - Berasal dari unit yang sama
     * - Memiliki peran pembuat (staff_tu / admin_unit)
     * - Status surat masih draft atau revisi
     */
    public function update(User $user, SuratKeluar $suratKeluar): bool
    {
        if (! in_array($suratKeluar->status_surat, ['draft', 'revisi'], true)) {
            return false;
        }

        if ($user->isStaffTu() || $user->isAdminUnit()) {
            return $user->id_unit === $suratKeluar->id_unit;
        }

        return false;
    }

    /**
     * Boleh menghapus surat jika masih draft dan berasal dari unit sendiri
     */
    public function delete(User $user, SuratKeluar $suratKeluar): bool
    {
        if ($suratKeluar->status_surat !== 'draft') {
            return false;
        }

        if ($user->isStaffTu() || $user->isAdminUnit()) {
            return $user->id_unit === $suratKeluar->id_unit;
        }

        return false;
    }

    /**
     * Boleh mengajukan surat keluar ke pimpinan
     */
    public function ajukan(User $user, SuratKeluar $suratKeluar): bool
    {
        if (! in_array($suratKeluar->status_surat, ['draft', 'revisi'], true)) {
            return false;
        }

        return $user->id_unit === $suratKeluar->id_unit;
    }
}
