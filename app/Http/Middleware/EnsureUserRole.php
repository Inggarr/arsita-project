<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles  Daftar role yang diizinkan (misal: 'admin_rektorat', 'pimpinan')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $userRole = $user->role ? $user->role->nama_role : '';

        // Jika user memiliki salah satu dari role yang diizinkan
        if (in_array($userRole, $roles, true)) {
            return $next($request);
        }

        // Jika tidak memiliki akses
        abort(403, 'Akses ditolak. Anda tidak memiliki kewenangan (role) untuk membuka halaman ini.');
    }
}
