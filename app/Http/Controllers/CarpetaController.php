<?php

namespace App\Http\Controllers;

use App\Helpers\EstiloModulo;
use App\Models\Carpeta;
use App\Models\CarpetasPermisos;
use App\Models\Documento;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CarpetaController extends Controller
{
    public function index()
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $raices = Carpeta::where('id_padre', 0)
            ->orderBy('descripcion')
            ->get()
            ->filter(fn($c) => $esAdmin || $this->tieneAcceso($c->id, $usuario->id));

        $carpetaActual = $raices->first();
        $contenido     = $carpetaActual
            ? $this->contenidoCarpeta($carpetaActual->id)
            : collect();

        $subcarpetas = $carpetaActual
            ? Carpeta::where('id_padre', $carpetaActual->id)->orderBy('descripcion')->get()
                ->filter(fn($c) => $esAdmin || $this->tieneAcceso($c->id, $usuario->id))
            : collect();

        $permisos = $esAdmin
            ? ['carga'=>true,'descarga'=>true,'crear'=>true,'eliminar'=>true,'editar'=>true]
            : ($carpetaActual ? $this->permisosEnCarpeta($carpetaActual->id, $usuario->id) : []);

        $breadcrumb = $carpetaActual ? $this->buildBreadcrumb($carpetaActual) : [];

        return view('carpetas.index', compact(
            'raices', 'carpetaActual', 'contenido',
            'subcarpetas', 'permisos', 'breadcrumb',
            'usuario', 'esAdmin'
        ));
    }

    public function show(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        if (! $esAdmin && ! $this->tieneAcceso($id, $usuario->id)) {
            return redirect()->route('panel')
                ->with('sin_permiso_carpeta', 'No tienes permisos para acceder a ese módulo.');
        }

        $carpetaActual = Carpeta::findOrFail($id);

        $raices = Carpeta::where('id_padre', 0)
            ->orderBy('descripcion')
            ->get()
            ->filter(fn($c) => $esAdmin || $this->tieneAcceso($c->id, $usuario->id));

        $contenido = $this->contenidoCarpeta($id);

        $subcarpetasRaw = Carpeta::where('id_padre', $id)
            ->orderBy('descripcion')
            ->get()
            ->filter(fn($c) => $esAdmin || $this->tieneAcceso($c->id, $usuario->id));

        // Enriquecer subcarpetas con color y emoji cuando estén en un módulo raíz
        $esModuloRaiz = in_array((int) $carpetaActual->id_padre, [0], true);
        $subcarpetas  = $subcarpetasRaw->map(function ($c) use ($esModuloRaiz) {
            $estilo          = EstiloModulo::submodulo($c->descripcion);
            $c->color_estilo = $esModuloRaiz ? $estilo['color'] : null;
            $c->emoji_estilo = $esModuloRaiz ? $estilo['emoji'] : null;
            return $c;
        });

        $permisos = $esAdmin
            ? ['carga'=>true,'descarga'=>true,'crear'=>true,'eliminar'=>true,'editar'=>true]
            : $this->permisosEnCarpeta($id, $usuario->id);

        $breadcrumb = $this->buildBreadcrumb($carpetaActual);

        return view('carpetas.index', compact(
            'raices', 'carpetaActual', 'contenido',
            'subcarpetas', 'permisos', 'breadcrumb',
            'usuario', 'esAdmin'
        ));
    }

    public function store(Request $request, int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        if (! $esAdmin) {
            PermisoService::require('crear', 'carpeta', $id);
        }

        $request->validate([
            'descripcion' => ['required', 'string', 'max:200'],
        ], [
            'descripcion.required' => 'El nombre de la carpeta es obligatorio.',
            'descripcion.max'      => 'El nombre no puede superar 200 caracteres.',
        ]);

        $padre     = Carpeta::findOrFail($id);
        $slug      = \Illuminate\Support\Str::slug($request->input('descripcion'), '_');
        $rutaNueva = ltrim(($padre->ruta ?? '') . '/' . $slug, '/');

        Carpeta::create([
            'descripcion' => $request->input('descripcion'),
            'id_padre'    => $id,
            'nivel'       => ($padre->nivel ?? 0) + 1,
            'ruta'        => $rutaNueva,
            'creada_el'   => now(),
        ]);

        return redirect()
            ->route('carpetas.show', $id)
            ->with('ok', 'Carpeta "' . $request->input('descripcion') . '" creada correctamente.');
    }

    public function destroy(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        if (! $esAdmin) {
            PermisoService::require('eliminar', 'carpeta', $id);
        }

        $carpeta = Carpeta::findOrFail($id);

        // No se pueden eliminar carpetas raíz (módulos del sistema)
        if ((int) $carpeta->id_padre === 0) {
            return response()->json(['error' => 'No se pueden eliminar los módulos raíz del sistema.'], 403);
        }

        // No eliminar si tiene subcarpetas
        $tieneHijos = Carpeta::where('id_padre', $id)->exists();
        if ($tieneHijos) {
            return response()->json(['error' => 'La carpeta tiene subcarpetas. Elimínalas primero.'], 422);
        }

        // No eliminar si tiene documentos
        $tieneDocumentos = DB::table('sgc_carpetas_contenido3')->where('id_carpeta', $id)->exists();
        if ($tieneDocumentos) {
            return response()->json(['error' => 'La carpeta contiene documentos. Elimínalos primero.'], 422);
        }

        $nombre = $carpeta->descripcion;
        $padreId = $carpeta->id_padre;
        $carpeta->delete();

        return response()->json(['ok' => true, 'mensaje' => "Carpeta \"{$nombre}\" eliminada.", 'padre_id' => $padreId]);
    }

    /**
     * Eliminación en cascada de un submódulo (nivel 1).
     * Solo admins. Borra recursivamente todas las subcarpetas,
     * documentos en sgc_carpetas_contenido3 y archivos físicos
     * que no tengan otras referencias.
     */
    public function destroySubmodulo(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar submódulos.'], 403);
        }

        $carpeta = Carpeta::findOrFail($id);

        // Nunca borrar módulos raíz del sistema
        if ((int) $carpeta->id_padre === 0) {
            return response()->json(['error' => 'No se pueden eliminar los módulos raíz del sistema.'], 403);
        }

        $nombre = $carpeta->descripcion;

        try {
            DB::transaction(function () use ($carpeta) {
                $this->eliminarCarpetaEnCascada($carpeta);
            });
        } catch (\Exception $e) {
            \Log::error('CarpetaController::destroySubmodulo — ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['error' => 'No se pudo eliminar el submódulo. Inténtalo nuevamente.'], 500);
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => "Submódulo \"{$nombre}\" eliminado junto con todo su contenido.",
        ]);
    }

    /**
     * Elimina recursivamente una carpeta y todo su contenido (BFS inverso).
     * Orden: hoja → raíz para respetar integridad referencial.
     */
    private function eliminarCarpetaEnCascada(Carpeta $carpeta): void
    {
        // Primero procesar todos los hijos
        foreach (Carpeta::where('id_padre', $carpeta->id)->get() as $hijo) {
            $this->eliminarCarpetaEnCascada($hijo);
        }

        // Eliminar documentos de esta carpeta
        $contenidos = DB::table('sgc_carpetas_contenido3')
            ->where('id_carpeta', $carpeta->id)
            ->get();

        foreach ($contenidos as $contenido) {
            // Eliminar el registro de contenido
            DB::table('sgc_carpetas_contenido3')->where('id', $contenido->id)->delete();

            // Solo borrar el archivo físico si no hay otras referencias al documento
            $otrasRefs = DB::table('sgc_carpetas_contenido3')
                ->where('id_documento', $contenido->id_documento)
                ->exists();

            if (! $otrasRefs) {
                $doc = Documento::find($contenido->id_documento);
                if ($doc) {
                    try {
                        Storage::disk('local')->delete($doc->archivo);
                    } catch (\Exception $e) {
                        \Log::warning('No se pudo borrar archivo físico: ' . $e->getMessage());
                    }
                    $doc->delete();
                }
            }
        }

        // Eliminar permisos asociados a esta carpeta
        CarpetasPermisos::where('id_carpeta', $carpeta->id)->delete();

        // Finalmente eliminar la carpeta
        $carpeta->delete();
    }

    public function hijos(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $hijos = Carpeta::where('id_padre', $id)
            ->orderBy('descripcion')
            ->get()
            ->filter(fn($c) => $esAdmin || $this->tieneAcceso($c->id, $usuario->id))
            ->map(fn($c) => [
                'id'          => $c->id,
                'descripcion' => $c->descripcion,
                'tiene_hijos' => Carpeta::where('id_padre', $c->id)->exists(),
            ])
            ->values();

        return response()->json($hijos);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function contenidoCarpeta(int $carpetaId): \Illuminate\Support\Collection
    {
        return DB::table('sgc_carpetas_contenido3 as cc')
            ->join('sgc_documentos as d', 'd.id', '=', 'cc.id_documento')
            ->where('cc.id_carpeta', $carpetaId)
            ->orderBy('cc.creada_el', 'desc')
            ->select('cc.id', 'cc.descripcion', 'cc.creada_el', 'd.archivo', 'd.nombre_original')
            ->get()
            ->map(fn($row) => (object)[
                'id'        => $row->id,
                'nombre'    => $row->descripcion ?: $row->nombre_original,
                'archivo'   => $row->archivo,
                'creada_el' => $row->creada_el,
                'extension' => strtolower(pathinfo($row->archivo, PATHINFO_EXTENSION)),
                'es_legacy' => false,
            ]);
    }

    /**
     * Verifica si el usuario puede acceder a una carpeta.
     *
     * Dos reglas combinadas:
     *
     * A) Herencia hacia abajo (padre → hijo):
     *    Si tienes permiso en un ancestro directo, lo heredas.
     *    Se implementa en tieneAccesoAncestral() de forma ESTRICTA
     *    (nunca aplica la regla B durante la subida del árbol).
     *
     * B) Visibilidad de módulo raíz (solo para carpetas con id_padre=0):
     *    Si tienes permiso en algún descendiente, puedes ver el módulo
     *    raíz para poder navegar hasta tu submódulo permitido.
     *    Esta regla NO se aplica a carpetas intermedias para evitar que
     *    un permiso en un hermano "contagie" acceso a otros hermanos
     *    que comparten el mismo ancestro.
     */
    private function tieneAcceso(int $carpetaId, int $usuarioId): bool
    {
        // 1. Permiso directo
        if (CarpetasPermisos::where('id_carpeta', $carpetaId)
                ->where('id_usuario', $usuarioId)->exists()) {
            return true;
        }

        $carpeta = Carpeta::find($carpetaId);

        // 2. Si tiene padre → herencia estricta ascendente (sin descendientes)
        if ($carpeta && (int) $carpeta->id_padre > 0) {
            return $this->tieneAccesoAncestral($carpeta->id_padre, $usuarioId);
        }

        // 3. Solo para módulos raíz (id_padre=0): permitir si algún descendiente
        //    tiene permiso, para que el usuario pueda navegar hasta él.
        return $this->tieneDescendienteConAcceso($carpetaId, $usuarioId);
    }

    /**
     * Sube el árbol comprobando solo permisos directos en cada ancestro.
     * NO aplica la regla de descendientes en ningún nivel intermedio.
     * Esto evita que compartir un ancestro común sea suficiente para
     * acceder a carpetas hermanas sin permiso explícito.
     */
    private function tieneAccesoAncestral(int $carpetaId, int $usuarioId): bool
    {
        if (CarpetasPermisos::where('id_carpeta', $carpetaId)
                ->where('id_usuario', $usuarioId)->exists()) {
            return true;
        }

        $carpeta = Carpeta::find($carpetaId);
        if ($carpeta && (int) $carpeta->id_padre > 0) {
            return $this->tieneAccesoAncestral($carpeta->id_padre, $usuarioId);
        }

        return false;
    }

    /**
     * BFS sobre los descendientes de una carpeta.
     * Devuelve true si el usuario tiene permiso en alguno de ellos.
     * Solo se llama para módulos raíz (id_padre=0).
     */
    private function tieneDescendienteConAcceso(int $carpetaId, int $usuarioId): bool
    {
        $cola = Carpeta::where('id_padre', $carpetaId)->pluck('id')->toArray();

        while (! empty($cola)) {
            if (CarpetasPermisos::whereIn('id_carpeta', $cola)
                    ->where('id_usuario', $usuarioId)->exists()) {
                return true;
            }
            $cola = Carpeta::whereIn('id_padre', $cola)->pluck('id')->toArray();
        }

        return false;
    }

    /**
     * Devuelve los permisos efectivos para una carpeta.
     * Si la carpeta no tiene registro propio, busca en el ancestro más cercano.
     */
    private function permisosEnCarpeta(int $carpetaId, int $usuarioId): array
    {
        $p = CarpetasPermisos::where('id_carpeta', $carpetaId)
            ->where('id_usuario', $usuarioId)
            ->first();

        if (! $p) {
            // Heredar del padre
            $carpeta = Carpeta::find($carpetaId);
            if ($carpeta && (int) $carpeta->id_padre > 0) {
                return $this->permisosEnCarpeta($carpeta->id_padre, $usuarioId);
            }
            return ['carga'=>false,'descarga'=>false,'crear'=>false,'eliminar'=>false,'editar'=>false];
        }

        return [
            'carga'    => (bool) $p->carga,
            'descarga' => (bool) $p->descarga,
            'crear'    => (bool) $p->crear,
            'eliminar' => (bool) $p->eliminar,
            'editar'   => (bool) $p->editar,
        ];
    }

    private function buildBreadcrumb(Carpeta $carpeta): array
    {
        $ruta   = [];
        $actual = $carpeta;

        while ($actual) {
            array_unshift($ruta, ['id' => $actual->id, 'descripcion' => $actual->descripcion]);
            $actual = $actual->id_padre > 0 ? Carpeta::find($actual->id_padre) : null;
        }

        return $ruta;
    }
}
