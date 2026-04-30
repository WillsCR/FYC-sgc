<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\CarpetasPermisos;
use Illuminate\Support\Facades\Session;

class PermisoService
{
    /**
     * Verifica si el usuario actual puede realizar una acción.
     *
     * Perfiles:
     *   id=1 (SuperAdmin) → acceso total a todo
     *   id=2 (Admin)      → acceso a gestión documental y usuarios normales
     *   id=4 (Trabajador) → solo lo que tenga asignado en carpetas/bloques
     */
    public static function can(string $accion, string $recurso = 'global', int $recursoId = 0): bool
    {
        $usuarioId = Session::get('usuario_id');
        if (! $usuarioId) return false;

        // SuperAdmin siempre puede todo
        if ((int) Session::get('id_perfil') === 1) return true;

        $usuario = Usuario::find($usuarioId);
        if (! $usuario) return false;

        // Permiso global en sgc_usuarios
        if ($recurso === 'global') {
            return $usuario->puedeVer($accion);
        }

        // Permiso granular por carpeta
        if ($recurso === 'carpeta' && $recursoId > 0) {
            // Admin también puede acceder a todas las carpetas
            if ((int) $usuario->id_perfil === 2) return true;

            $permiso = CarpetasPermisos::where('id_carpeta', $recursoId)
                ->where('id_usuario', $usuarioId)
                ->first();

            if (! $permiso) return false;
            return (bool) ($permiso->$accion ?? false);
        }

        return false;
    }

    /**
     * Aborta con 403 si no tiene el permiso
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
     * Retorna el usuario actual desde la BD
     */
    public static function usuarioActual(): ?Usuario
    {
        $id = Session::get('usuario_id');
        return $id ? Usuario::find($id) : null;
    }
}
