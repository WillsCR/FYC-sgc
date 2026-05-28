<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\UsuarioArea;
use App\Models\UsuarioPermisoArea;
use App\Services\PermisoService;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    private function verificarAcceso(): void
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->verificarAcceso();

        $usuario = PermisoService::usuarioActual();
        $areas   = Area::orderBy('id')->get()->map(function ($area) {
            $area->total_usuarios = UsuarioArea::where('id_area', $area->id)->count();
            return $area;
        });

        return view('areas.index', compact('areas', 'usuario'));
    }

    public function store(Request $request)
    {
        $this->verificarAcceso();

        $request->validate([
            'descripcion' => ['required', 'string', 'max:100', 'unique:sgc_areas,descripcion'],
        ], [
            'descripcion.required' => 'El nombre del área es obligatorio.',
            'descripcion.max'      => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.unique'   => 'Ya existe un área con ese nombre.',
        ]);

        $area = Area::create([
            'descripcion' => trim($request->input('descripcion')),
        ]);

        return response()->json([
            'ok'   => true,
            'area' => [
                'id'              => $area->id,
                'descripcion'     => $area->descripcion,
                'total_usuarios'  => 0,
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->verificarAcceso();

        $area = Area::findOrFail($id);

        $request->validate([
            'descripcion' => ['required', 'string', 'max:100', "unique:sgc_areas,descripcion,{$id}"],
        ], [
            'descripcion.required' => 'El nombre del área es obligatorio.',
            'descripcion.max'      => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.unique'   => 'Ya existe otra área con ese nombre.',
        ]);

        $area->descripcion = trim($request->input('descripcion'));
        $area->save();

        return response()->json([
            'ok'   => true,
            'area' => [
                'id'          => $area->id,
                'descripcion' => $area->descripcion,
            ],
        ]);
    }

    public function destroy(int $id)
    {
        $this->verificarAcceso();

        $area = Area::findOrFail($id);

        $totalUsuarios = UsuarioArea::where('id_area', $id)->count();
        if ($totalUsuarios > 0) {
            return response()->json([
                'error' => "No se puede eliminar: el área tiene {$totalUsuarios} usuario(s) asignado(s). Reasígnalos primero.",
            ], 422);
        }

        // Eliminar también permisos de área huérfanos
        UsuarioPermisoArea::where('id_area', $id)->delete();

        $nombre = $area->descripcion;
        $area->delete();

        return response()->json([
            'ok'     => true,
            'mensaje'=> "Área \"{$nombre}\" eliminada correctamente.",
        ]);
    }
}
