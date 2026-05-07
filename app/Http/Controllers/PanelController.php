<?php

namespace App\Http\Controllers;

use App\Helpers\EstiloModulo;
use App\Models\Carpeta;
use App\Services\PermisoService;
use Illuminate\Support\Facades\DB;

class PanelController extends Controller
{
    public function index()
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario) {
            return redirect()->route('login');
        }

        $bloques = $this->bloquesVisibles($usuario);
        $stats   = $this->estadisticasResumen();

        return view('panel.index', compact('usuario', 'bloques', 'stats'));
    }

    /**
     * Crear nuevo módulo (nivel 0)
     */
    public function crearModulo(\Illuminate\Http\Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        if (!$usuario->esAdmin()) {
            return response()->json(['error' => 'No tienes permisos para crear módulos.'], 403);
        }

        $request->validate([
            'descripcion' => ['required', 'string', 'max:200'],
            'color'       => ['nullable', 'string', 'max:50'],
            'icono'       => ['nullable', 'string', 'max:100'],
        ]);

        $modulo = Carpeta::create([
            'descripcion' => $request->input('descripcion'),
            'id_padre'    => 0,
            'nivel'       => 0,
            'color'       => $request->input('color'),
            'icono'       => $request->input('icono'),
            'creada_el'   => now(),
        ]);

        return response()->json([
            'ok'     => true,
            'mensaje'=> 'Módulo creado correctamente.',
            'modulo' => [
                'id'          => $modulo->id,
                'descripcion' => $modulo->descripcion,
                'color'       => $modulo->color,
                'icono'       => $modulo->icono,
            ]
        ]);
    }

    /**
     * Crear nuevo submódulo (nivel 1)
     */
    public function crearSubmódulo(\Illuminate\Http\Request $request, int $moduloId)
    {
        $usuario = PermisoService::usuarioActual();
        if (!$usuario->esAdmin()) {
            return response()->json(['error' => 'No tienes permisos para crear submódulos.'], 403);
        }

        $modulo = Carpeta::where('id', $moduloId)->where('nivel', 0)->first();
        if (!$modulo) {
            return response()->json(['error' => 'El módulo no existe.'], 404);
        }

        $request->validate([
            'descripcion' => ['required', 'string', 'max:200'],
            'color'       => ['nullable', 'string', 'max:50'],
            'icono'       => ['nullable', 'string', 'max:100'],
        ]);

        $submódulo = Carpeta::create([
            'descripcion' => $request->input('descripcion'),
            'id_padre'    => $moduloId,
            'nivel'       => 1,
            'color'       => $request->input('color'),
            'icono'       => $request->input('icono'),
            'creada_el'   => now(),
        ]);

        return response()->json([
            'ok'     => true,
            'mensaje'=> 'Submódulo creado correctamente.',
            'submódulo' => [
                'id'          => $submódulo->id,
                'descripcion' => $submódulo->descripcion,
                'color'       => $submódulo->color,
                'icono'       => $submódulo->icono,
            ]
        ]);
    }

    /**
     * Actualizar módulo o submódulo
     */
    public function actualizarModulo(\Illuminate\Http\Request $request, int $id)
    {
        $usuario = PermisoService::usuarioActual();
        if (!$usuario->esAdmin()) {
            return response()->json(['error' => 'No tienes permisos para actualizar módulos.'], 403);
        }

        $carpeta = Carpeta::findOrFail($id);

        // Solo se pueden editar módulos y submódulos
        if ((int) $carpeta->nivel > 1) {
            return response()->json(['error' => 'Solo se pueden editar módulos y submódulos.'], 422);
        }

        $request->validate([
            'descripcion' => ['nullable', 'string', 'max:200'],
            'color'       => ['nullable', 'string', 'max:50'],
            'icono'       => ['nullable', 'string', 'max:100'],
        ]);

        $dataToUpdate = [];

        if ($request->filled('descripcion')) {
            $dataToUpdate['descripcion'] = $request->input('descripcion');
        }

        if ($request->filled('color')) {
            $dataToUpdate['color'] = $request->input('color');
        }

        if ($request->filled('icono')) {
            $dataToUpdate['icono'] = $request->input('icono');
        }

        if (empty($dataToUpdate)) {
            return response()->json(['error' => 'No hay cambios para guardar.'], 422);
        }

        $carpeta->update($dataToUpdate);

        return response()->json([
            'ok'     => true,
            'mensaje'=> 'Módulo actualizado correctamente.',
            'carpeta'=> [
                'id'          => $carpeta->id,
                'descripcion' => $carpeta->descripcion,
                'color'       => $carpeta->color,
                'icono'       => $carpeta->icono,
            ]
        ]);
    }

    // ─── Bloques ─────────────────────────────────────────────────────────────

    private function bloquesVisibles($usuario): array
    {
        $todos   = $this->definicionBloques();
        $esAdmin = $usuario->esAdmin();

        return array_filter($todos, function ($bloque) use ($usuario, $esAdmin) {
            if ($esAdmin) return true;
            return (bool) ($usuario->{$bloque['permiso']} ?? false);
        });
    }

    /**
     * Define bloques con metadatos + carga submódulos de la BD
     */
    private function definicionBloques(): array
    {
        // Mapeo: clave => ID del módulo en sgc_carpetas3
        $modulos_map = [
            'sig'             => 1,  // Sistema Integrado Gestión
            'ambiente'        => 2,  // Control Medio Ambiente
            'seguridad'       => 3,  // Control SST
            'abastecimiento'  => 4,  // Control Abastecimiento
            'rrhh'            => 5,  // Control RRHH
            'gerencia'        => 6,  // Control Gerencia
            'proyectos'       => 7,  // Control Proyectos
            'finanzas'        => 8,  // Control Finanzas
        ];

        // Metadatos de bloques (colores, emojis, permisos)
        $bloques_meta = [
            'sig' => [
                'titulo' => 'Control Sistema Integrado de Gestión',
                'badge' => 'SIG',
                'permiso' => 'bloque_sig',
                'color' => '#0D2B5E',
                'emoji' => '📋',
            ],
            'ambiente' => [
                'titulo' => 'Control Medio Ambiente',
                'badge' => 'MA',
                'permiso' => 'bloque_ambiente',
                'color' => '#15803D',
                'emoji' => '🌿',
            ],
            'seguridad' => [
                'titulo' => 'Control Seguridad y Salud en el Trabajo',
                'badge' => 'SST',
                'permiso' => 'bloque_seguridad',
                'color' => '#991B1B',
                'emoji' => '🛡️',
            ],
            'abastecimiento' => [
                'titulo' => 'Control Abastecimiento e Infraestructura',
                'badge' => 'ABI',
                'permiso' => 'bloque_abastecimiento',
                'color' => '#B45309',
                'emoji' => '🏗️',
            ],
            'rrhh' => [
                'titulo' => 'Control Recursos Humanos',
                'badge' => 'RRHH',
                'permiso' => 'bloque_rrhh',
                'color' => '#7C3AED',
                'emoji' => '👨‍💼',
            ],
            'gerencia' => [
                'titulo' => 'Control Gerencia',
                'badge' => 'GER',
                'permiso' => 'bloque_gerencia',
                'color' => '#0C4A6E',
                'emoji' => '🏢',
            ],
            'proyectos' => [
                'titulo' => 'Control Proyectos',
                'badge' => 'PRY',
                'permiso' => 'bloque_proyectos',
                'color' => '#1D4ED8',
                'emoji' => '📈',
            ],
            'finanzas' => [
                'titulo' => 'Control Finanzas',
                'badge' => 'FIN',
                'permiso' => 'bloque_finanzas',
                'color' => '#065F46',
                'emoji' => '💰',
            ],
        ];

        $bloques = [];

        foreach ($bloques_meta as $clave => $meta) {
            $modulo_id = $modulos_map[$clave] ?? null;
            if (! $modulo_id) continue;

            // Sobrescribir color e icono si existen en la BD
            if ($modulo && $modulo->color) {
                $meta['color'] = $modulo->color;
            }
            if ($modulo && $modulo->icono) {
                $meta['emoji'] = $modulo->icono;
            }

            $bloques[$clave] = array_merge([
                'id'         => $clave,
                'carpeta_id' => $modulo_id,
            ], $meta);
        }

        return $bloques;
    }

    // ─── Estadísticas ────────────────────────────────────────────────────────

    private function estadisticasResumen(): array
    {
        return [
            'cumplimiento'   => 75,
            'pendientes'     => 3,
            'cerradas'       => 12,
            'minutas_mes'    => 2,
        ];
    }
}