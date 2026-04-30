<?php

namespace App\Http\Controllers;

use App\Models\Carpeta;
use App\Models\CarpetasPermisos;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $subcarpetas = Carpeta::where('id_padre', $id)
            ->orderBy('descripcion')
            ->get()
            ->filter(fn($c) => $esAdmin || $this->tieneAcceso($c->id, $usuario->id));

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

    private function tieneAcceso(int $carpetaId, int $usuarioId): bool
    {
        return CarpetasPermisos::where('id_carpeta', $carpetaId)
            ->where('id_usuario', $usuarioId)
            ->exists();
    }

    private function permisosEnCarpeta(int $carpetaId, int $usuarioId): array
    {
        $p = CarpetasPermisos::where('id_carpeta', $carpetaId)
            ->where('id_usuario', $usuarioId)
            ->first();

        if (!$p) {
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
