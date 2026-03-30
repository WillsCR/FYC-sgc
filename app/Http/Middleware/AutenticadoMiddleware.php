<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AutenticadoMiddleware
{
    /**
     * Redirige al login si no hay sesión activa.
     * Se aplica a todas las rutas protegidas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Session::has('usuario_id')) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para continuar.');
        }

        return $next($request);
    }
}
