<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Key unik berdasarkan username/email + IP address
        $throttleKey = Str::lower($credentials['username']).'|'.$request->ip();

        // Cek apakah sudah melebihi batas percobaan (maksimal 5x dalam 60 detik)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login yang gagal. Silakan tunggu {$seconds} detik lagi sebelum mencoba kembali.",
            ])->onlyInput('username');
        }

        // Coba login dengan username
        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'status' => 'aktif'])) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, '.Auth::user()->nama);
        }

        // Coba login dengan email jika user mengetikkan email
        if (Auth::attempt(['email' => $credentials['username'], 'password' => $credentials['password'], 'status' => 'aktif'])) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, '.Auth::user()->nama);
        }

        // Catat percobaan gagal (dihitung selama 60 detik)
        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'username' => 'Username/password salah atau akun dinonaktifkan.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
