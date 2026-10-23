<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'unit'])->orderBy('id_unit')->orderBy('id_role')->get();
        $roles = Role::orderBy('id_role', 'asc')->get();
        $units = Unit::where('status', 'aktif')->orderBy('level', 'asc')->orderBy('nama_unit', 'asc')->get();

        return view('pengguna.index', compact('users', 'roles', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'id_role' => 'required|exists:roles,id_role',
            'id_unit' => 'required|exists:units,id_unit',
        ]);

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_role' => $request->id_role,
            'id_unit' => $request->id_unit,
            'status' => 'aktif',
        ]);

        return back()->with('success', "Akun pengguna <b>{$request->username}</b> berhasil didaftarkan!");
    }

    public function edit($id)
    {
        $pengguna = User::with(['role', 'unit'])->findOrFail($id);
        $roles = Role::orderBy('id_role', 'asc')->get();
        $units = Unit::where('status', 'aktif')->orderBy('level', 'asc')->orderBy('nama_unit', 'asc')->get();

        return view('pengguna.edit', compact('pengguna', 'roles', 'units'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,'.$user->id_user.',id_user',
            'email' => 'required|email|unique:users,email,'.$user->id_user.',id_user',
            'password' => 'nullable|string|min:6',
            'id_role' => 'required|exists:roles,id_role',
            'id_unit' => 'required|exists:units,id_unit',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $data = $request->only(['nama', 'username', 'email', 'id_role', 'id_unit', 'status']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
