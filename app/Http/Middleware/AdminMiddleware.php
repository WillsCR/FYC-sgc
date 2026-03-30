<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Solo permite el acceso a usuarios con perfil Administrador.
     * Retorna 403 JSON si es petición AJAX, redirige si es web.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Session::get('es_admin', false)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Acceso no autorizado.'], 403);
            }
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
