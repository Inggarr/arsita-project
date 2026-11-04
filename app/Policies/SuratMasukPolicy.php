<?php

namespace App\Policies;

use App\Models\SuratMasuk;
use App\Models\User;

class SuratMasukPolicy
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
     * Boleh melihat surat masuk jika ditujukan untuk unit user tersebut
     */
    public function view(User $user, SuratMasuk $suratMasuk): bool
    {
        return $user->id_unit && $user->id_unit === $suratMasuk->id_unit;
    }

    /**
     * Boleh mengedit surat masuk sesuai logika canBeEditedBy
     */
    public function update(User $user, SuratMasuk $suratMasuk): bool
    {
        return $suratMasuk->canBeEditedBy($user);
    }

    /**
     * Boleh menghapus surat masuk jika dicatat sendiri dan bukan dari unit lain
     */
    public function delete(User $user, SuratMasuk $suratMasuk): bool
    {
        return $suratMasuk->canBeEditedBy($user);
    }

    /**
     * Boleh mendisposisikan surat masuk
     */
    public function disposisi(User $user, SuratMasuk $suratMasuk): bool
    {
        // Hanya pimpinan, admin_unit, atau staff_tu di unit yang menerima surat
        if ($user->id_unit !== $suratMasuk->id_unit) {
            return false;
        }

        return in_array($user->role?->nama_role, ['pimpinan', 'admin_unit', 'staff_tu'], true);
    }
}
