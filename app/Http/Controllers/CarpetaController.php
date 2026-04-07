<?php

namespace App\Http\Controllers;

use App\Models\Carpeta;
use App\Models\CarpetasPermisos;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarpetaController extends Controller
{
    /**
     * Vista principal — árbol de carpetas con la primera raíz seleccionada
     */
    public function index()
    {
        $usuario  = PermisoService::usuarioActual();
        $esAdmin  = $usuario->esAdmin();

        // Carpetas raíz (id_padre = 0)
        $raices = Carpeta::where('id_padre', 0)
            ->orderBy('descripcion')
            ->get()
            ->filter(fn($c) => $esAdmin || $this->tieneAcceso($c->id, $usuario->id));

        // Seleccionar la primera raíz por defecto
        $carpetaActual = $raices->first();
        $contenido     = $carpetaActual
            ? $this->contenidoCarpeta($carpetaActual->id, $usuario->id, $esAdmin)
            : collect();

        return view('carpetas.index', compact(
            'raices', 'carpetaActual', 'contenido', 'usuario', 'esAdmin'
        ));
    }

    /**
     * Muestra el contenido de una carpeta específica
     */
    public function show(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        // Verificar permiso de descarga (mínimo para ver)
        if (! $esAdmin) {
            PermisoService::require('descarga', 'carpeta', $id);
        }

        $carpetaActual = Carpeta::findOrFail($id);
        $raices = Carpeta::where('id_padre', 0)
            ->orderBy('descripcion')
            ->get()
            ->filter(fn($c) => $esAdmin || $this->tieneAcceso($c->id, $usuario->id));

        $contenido = $this->contenidoCarpeta($id, $usuario->id, $esAdmin);

        // Subcarpetas de la carpeta actual
        $subcarpetas = Carpeta::where('id_padre', $id)
            ->orderBy('descripcion')
            ->get()
            ->filter(fn($c) => $esAdmin || $this->tieneAcceso($c->id, $usuario->id));

        // Permisos del usuario en esta carpeta (para mostrar/ocultar botones)
        $permisos = $esAdmin
            ? ['carga'=>true,'descarga'=>true,'crear'=>true,'eliminar'=>true,'editar'=>true]
            : $this->permisosEnCarpeta($id, $usuario->id);

        // Ruta de migas de pan (breadcrumb)
        $breadcrumb = $this->buildBreadcrumb($carpetaActual);

        return view('carpetas.index', compact(
            'raices', 'carpetaActual', 'contenido',
            'subcarpetas', 'permisos', 'breadcrumb',
            'usuario', 'esAdmin'
        ));
    }

    /**
     * Crear nueva subcarpeta dentro de una carpeta padre
     */
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

        $padre = Carpeta::findOrFail($id);

        // Construir la ruta: hereda la ruta del padre más el slug del nombre
        $slug       = \Illuminate\Support\Str::slug($request->input('descripcion'), '_');
        $rutaNueva  = ltrim(($padre->ruta ?? '') . '/' . $slug, '/');
        $nivelNuevo = ($padre->nivel ?? 0) + 1;

        Carpeta::create([
            'descripcion' => $request->input('descripcion'),
            'id_padre'    => $id,
            'nivel'       => $nivelNuevo,
            'ruta'        => $rutaNueva,
            'creada_el'   => now(),
        ]);

        return redirect()
            ->route('carpetas.show', $id)
            ->with('ok', 'Carpeta "' . $request->input('descripcion') . '" creada correctamente.');
    }

    /**
     * Retorna subcarpetas en JSON — para expansión dinámica del árbol
     */
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
                'nivel'       => $c->nivel,
                'tiene_hijos' => Carpeta::where('id_padre', $c->id)->exists(),
            ])
            ->values();

        return response()->json($hijos);
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * Archivos de una carpeta con info formateada
     */
    private function contenidoCarpeta(int $carpetaId, int $usuarioId, bool $esAdmin): \Illuminate\Support\Collection
    {
        return DB::table('sgc_carpetas_contenido')
            ->where('id_carpeta', $carpetaId)
            ->orderBy('creada_el', 'desc')
            ->get()
            ->map(function ($row) {
                return (object) [
                    'id'          => $row->id,
                    'nombre'      => $row->descripcion,
                    'archivo'     => $row->archivo,
                    'creada_el'   => $row->creada_el,
                    'extension'   => strtolower(pathinfo($row->archivo, PATHINFO_EXTENSION)),
                    'es_legacy'   => ! str_contains($row->archivo, '-') || strlen($row->archivo) < 36,
                ];
            });
    }

    /**
     * Verifica si el usuario tiene al menos un permiso en la carpeta
     * (o en alguna subcarpeta)
     */
    private function tieneAcceso(int $carpetaId, int $usuarioId): bool
    {
        return CarpetasPermisos::where('id_carpeta', $carpetaId)
            ->where('id_usuario', $usuarioId)
            ->exists();
    }

    /**
     * Devuelve los permisos del usuario en una carpeta específica
     */
    private function permisosEnCarpeta(int $carpetaId, int $usuarioId): array
    {
        $p = CarpetasPermisos::where('id_carpeta', $carpetaId)
            ->where('id_usuario', $usuarioId)
            ->first();

        if (! $p) {
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

    /**
     * Construye el breadcrumb navegando hacia arriba por id_padre
     */
    private function buildBreadcrumb(Carpeta $carpeta): array
    {
        $ruta  = [];
        $actual = $carpeta;

        while ($actual) {
            array_unshift($ruta, ['id' => $actual->id, 'descripcion' => $actual->descripcion]);
            $actual = $actual->id_padre > 0 ? Carpeta::find($actual->id_padre) : null;
        }

        return $ruta;
    }
}
