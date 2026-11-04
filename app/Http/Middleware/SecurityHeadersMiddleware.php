<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah Clickjacking (iframe embedding)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Mencegah MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Mencegah kebocoran referer yang sensitif
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Proteksi XSS pada browser lama
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Pembatasan izin fitur browser
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}
