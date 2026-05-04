<?php

namespace App\Services;

use App\Models\Carpeta;
use App\Models\Usuario;
use App\Models\CarpetasPermisos;
use Illuminate\Support\Facades\Session;

class PermisoService
{
    private const ACCIONES_VALIDAS = ['carga', 'descarga', 'crear', 'eliminar', 'editar'];
    private const RECURSOS_VALIDOS = ['global', 'carpeta'];

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
        // Whitelist estricto — rechaza acciones o recursos desconocidos
        if (! in_array($accion,  self::ACCIONES_VALIDAS, true)) return false;
        if (! in_array($recurso, self::RECURSOS_VALIDOS, true)) return false;

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

        // Permiso granular por carpeta (con herencia hacia arriba en el árbol)
        if ($recurso === 'carpeta' && $recursoId > 0) {
            // Admin puede acceder a todas las carpetas
            if ((int) $usuario->id_perfil === 2) return true;

            return self::canEnCarpeta($accion, $recursoId, $usuarioId);
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
     * Busca el permiso de una acción en la carpeta dada o en sus ancestros.
     * Los permisos se heredan hacia abajo: si tienes permiso en el padre,
     * lo tienes en todos sus descendientes.
     */
    private static function canEnCarpeta(string $accion, int $carpetaId, int $usuarioId): bool
    {
        $permiso = CarpetasPermisos::where('id_carpeta', $carpetaId)
            ->where('id_usuario', $usuarioId)
            ->first();

        if ($permiso) {
            return (bool) ($permiso->$accion ?? false);
        }

        // Subir al padre
        $carpeta = Carpeta::find($carpetaId);
        if ($carpeta && (int) $carpeta->id_padre > 0) {
            return self::canEnCarpeta($accion, $carpeta->id_padre, $usuarioId);
        }

        return false;
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
