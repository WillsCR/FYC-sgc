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

        // Estos headers le dicen al navegador que NO guarde
        // esta página en caché — al presionar "atrás" después
        // de logout, el navegador tendrá que pedir la página
        // de nuevo al servidor, que detectará que no hay sesión
        // y redirigirá al login.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
