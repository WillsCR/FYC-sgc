<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\CarpetaPermiso;
use Illuminate\Support\Facades\Session;

class PermisoService
{
    /**
     * Verifica si el usuario actual puede realizar una acción.
     *
     * Uso:
     *   PermisoService::can('bloque_sig')          → permiso global en sgc_usuarios
     *   PermisoService::can('carga', 'carpeta', 5) → permiso granular por carpeta
     */
    public static function can(string $accion, string $recurso = 'global', int $recursoId = 0): bool
    {
        $usuarioId = Session::get('usuario_id');

        if (! $usuarioId) {
            return false;
        }

        // Los administradores tienen acceso total
        if (Session::get('es_admin', false)) {
            return true;
        }

        $usuario = Usuario::find($usuarioId);

        if (! $usuario) {
            return false;
        }

        // Permiso global en sgc_usuarios
        if ($recurso === 'global') {
            return $usuario->puedeVer($accion);
        }

        // Permiso granular por carpeta en sgc_carpetas_permisos
        if ($recurso === 'carpeta' && $recursoId > 0) {
            $permiso = CarpetaPermiso::where('id_carpeta', $recursoId)
                ->where('id_usuario', $usuarioId)
                ->first();

            if (! $permiso) {
                return false;
            }

            return (bool) ($permiso->$accion ?? false);
        }

        return false;
    }

    /**
     * Aborta con 403 si el usuario no tiene el permiso.
     * Usar en controllers para proteger acciones.
     */
    public static function require(string $accion, string $recurso = 'global', int $recursoId = 0): void
    {
        if (! self::can($accion, $recurso, $recursoId)) {
            request()->expectsJson()
                ? abort(response()->json(['error' => 'Acceso no autorizado.'], 403))
                : abort(403, 'No tienes permisos para realizar esta acción.');
        }
    }

    /**
     * Devuelve el usuario actual desde sesión + BD
     */
    public static function usuarioActual(): ?Usuario
    {
        $id = Session::get('usuario_id');
        return $id ? Usuario::find($id) : null;
    }
}
