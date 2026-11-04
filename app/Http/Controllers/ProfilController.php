<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        return view('profil.index', compact('user'));
    }

    public function updateProfil(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:users,email,'.$user->id_user.',id_user',
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Data profil Anda berhasil diperbarui.');
    }

    public function updateTandaTangan(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isPimpinan() && ! $user->isAdminRektorat()) {
            abort(403, 'Fitur tanda tangan digital hanya dapat diakses oleh Pimpinan.');
        }

        $request->validate([
            'tanda_tangan' => 'required|file|mimes:png,jpg,jpeg,webp|max:3072',
        ]);

        $file = $request->file('tanda_tangan');
        $fileName = 'ttd_'.$user->id_user.'_'.time().'.png';
        $destDir = public_path('uploads/tanda_tangan');

        if (! File::isDirectory($destDir)) {
            File::makeDirectory($destDir, 0755, true, true);
        }

        $destinationPath = $destDir.'/'.$fileName;

        // Proses otomatis hapus background menjadi transparan murni
        SignatureService::processTransparentSignature($file->getRealPath(), $destinationPath);

        // Hapus file lama jika ada
        if ($user->tanda_tangan && File::exists($destDir.'/'.$user->tanda_tangan)) {
            @unlink($destDir.'/'.$user->tanda_tangan);
        }

        $user->update([
            'tanda_tangan' => $fileName,
        ]);

        return back()->with('success', 'Scan tanda tangan berhasil disimpan! Background putih kertas telah otomatis dihapus menjadi transparan.');
    }

    public function destroyTandaTangan(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->tanda_tangan) {
            $destDir = public_path('uploads/tanda_tangan');
            if (File::exists($destDir.'/'.$user->tanda_tangan)) {
                @unlink($destDir.'/'.$user->tanda_tangan);
            }

            $user->update([
                'tanda_tangan' => null,
            ]);
        }

        return back()->with('success', 'Tanda tangan Anda berhasil dihapus.');
    }
}
