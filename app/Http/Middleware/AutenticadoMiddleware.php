<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AutenticadoMiddleware
{
    /**
     * Protege las rutas privadas.
     * Agrega headers de caché para que el navegador no guarde
     * las páginas protegidas — evita el acceso con el botón "atrás"
     * después de cerrar sesión.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Session::has('usuario_id')) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para continuar.');
        }

        $response = $next($request);

        // ── Caché: evita que el navegador guarde páginas protegidas ──
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma',  'no-cache');
        $response->headers->set('Expires', '0');

        // ── Headers de seguridad HTTP ─────────────────────────────────
        // Evita clickjacking (iframes de otros dominios)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // Evita que el navegador adivine el tipo de contenido
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        // Reduce información enviada en el Referer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Deshabilita funcionalidades del navegador no usadas
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        // Protección básica XSS (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
