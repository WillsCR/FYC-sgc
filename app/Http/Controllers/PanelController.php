<?php

namespace App\Http\Controllers;

use App\Helpers\EstiloModulo;
use App\Models\Carpeta;
use App\Models\CarpetasPermisos;
use App\Models\Video;
use App\Services\PermisoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function index()
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario) {
            return redirect()->route('login');
        }

        $bloques         = $this->bloquesVisibles($usuario);
        $stats           = $this->estadisticasResumen();
        $videoBienvenida = Video::where('es_bienvenida', true)->first();

        return view('panel.index', compact('usuario', 'bloques', 'stats', 'videoBienvenida'));
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

    /**
     * Eliminar un módulo raíz. Solo SuperAdmin.
     * Restricciones: sin submódulos y sin contenido documental en ninguna carpeta hija.
     */
    public function destroyModulo(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esSuperAdmin()) {
            return response()->json(['error' => 'Solo el Super Administrador puede eliminar módulos.'], 403);
        }

        $modulo = Carpeta::findOrFail($id);

        if ((int) $modulo->id_padre !== 0) {
            return response()->json(['error' => 'Este elemento no es un módulo raíz.'], 422);
        }

        // Verificar que no tenga submódulos
        if (Carpeta::where('id_padre', $id)->exists()) {
            return response()->json([
                'error' => 'No se puede eliminar: el módulo tiene submódulos. Elimínalos primero.'
            ], 422);
        }

        // Verificar que no tenga documentos (en ninguna carpeta descendiente)
        $tieneContenido = DB::table('sgc_carpetas_contenido3')->where('id_carpeta', $id)->exists();
        if ($tieneContenido) {
            return response()->json([
                'error' => 'No se puede eliminar: el módulo contiene documentos. Elimínalos primero.'
            ], 422);
        }

        $nombre = $modulo->descripcion;

        // Eliminar permisos de carpeta asociados
        CarpetasPermisos::where('id_carpeta', $id)->delete();

        $modulo->delete();

        return response()->json([
            'ok'     => true,
            'mensaje'=> "Módulo \"{$nombre}\" eliminado correctamente.",
        ]);
    }

    // ─── Bloques ─────────────────────────────────────────────────────────────

    private function bloquesVisibles($usuario): array
    {
        $todos   = $this->definicionBloques();
        $esAdmin = $usuario->esAdmin();

        return array_filter($todos, function ($bloque) use ($usuario, $esAdmin) {
            if ($esAdmin) return true;

            // Módulos del sistema: columna bloque_xxx en sgc_usuarios
            if ($bloque['permiso']) {
                return (bool) ($usuario->{$bloque['permiso']} ?? false);
            }

            // Módulos dinámicos: verificar permisos en sgc_carpetas_permisos
            // (permiso directo en el módulo o en cualquier descendiente)
            return $this->usuarioTieneAccesoModulo($bloque['carpeta_id'], $usuario->id);
        });
    }

    /**
     * Verifica si el usuario tiene algún permiso en el módulo dinámico
     * o en cualquiera de sus submódulos/carpetas descendientes.
     */
    private function usuarioTieneAccesoModulo(int $carpetaId, int $usuarioId): bool
    {
        // Permiso directo en el módulo raíz
        if (CarpetasPermisos::where('id_carpeta', $carpetaId)
                ->where('id_usuario', $usuarioId)->exists()) {
            return true;
        }

        // BFS sobre descendientes
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
     * Define bloques cargando TODOS los módulos raíz de la BD.
     * Los módulos con clave conocida heredan su metadata hardcodeada
     * (título, badge, permiso, color, emoji) pero se sobreescribe
     * con lo que esté guardado en color/icono de la carpeta.
     * Los módulos creados dinámicamente usan sus propios datos de BD.
     */
    private function definicionBloques(): array
    {
        // Metadata de los módulos originales del sistema (clave por ID de carpeta)
        $meta_conocida = [
            1 => ['clave' => 'sig',            'titulo' => 'Control Sistema Integrado de Gestión',      'badge' => 'SIG',  'permiso' => 'bloque_sig',            'color' => '#1B4F72', 'emoji' => 'fa-clipboard-check'],
            2 => ['clave' => 'ambiente',        'titulo' => 'Control Medio Ambiente',                    'badge' => 'MA',   'permiso' => 'bloque_ambiente',        'color' => '#2E6E8E', 'emoji' => 'fa-seedling'],
            3 => ['clave' => 'seguridad',       'titulo' => 'Control Seguridad y Salud en el Trabajo',  'badge' => 'SST',  'permiso' => 'bloque_seguridad',       'color' => '#3D86A4', 'emoji' => 'fa-shield-halved'],
            4 => ['clave' => 'abastecimiento',  'titulo' => 'Control Abastecimiento e Infraestructura', 'badge' => 'ABI',  'permiso' => 'bloque_abastecimiento',  'color' => '#5499B4', 'emoji' => 'fa-truck'],
            5 => ['clave' => 'rrhh',            'titulo' => 'Control Recursos Humanos',                  'badge' => 'RRHH', 'permiso' => 'bloque_rrhh',            'color' => '#1B4F72', 'emoji' => 'fa-user-tie'],
            6 => ['clave' => 'gerencia',        'titulo' => 'Control Gerencia',                          'badge' => 'GER',  'permiso' => 'bloque_gerencia',        'color' => '#2E6E8E', 'emoji' => 'fa-building'],
            7 => ['clave' => 'proyectos',       'titulo' => 'Control Proyectos',                         'badge' => 'PRY',  'permiso' => 'bloque_proyectos',       'color' => '#3D86A4', 'emoji' => 'fa-chart-line'],
            8 => ['clave' => 'finanzas',        'titulo' => 'Control Finanzas',                          'badge' => 'FIN',  'permiso' => 'bloque_finanzas',        'color' => '#5499B4', 'emoji' => 'fa-coins'],
        ];

        // Cargar TODOS los módulos raíz de la BD
        $carpetasRaiz = Carpeta::where('id_padre', 0)->orderBy('id')->get();

        $bloques = [];

        foreach ($carpetasRaiz as $carpeta) {
            if (isset($meta_conocida[$carpeta->id])) {
                // Módulo del sistema: metadata hardcodeada, sobreescribir color/icono con BD si existen
                $meta  = $meta_conocida[$carpeta->id];
                $clave = $meta['clave'];
                if ($carpeta->color) $meta['color'] = $carpeta->color;
                if ($carpeta->icono) $meta['emoji'] = $carpeta->icono;
            } else {
                // Módulo creado dinámicamente: construir metadata desde BD
                $clave = 'modulo_' . $carpeta->id;
                $meta  = [
                    'clave'   => $clave,
                    'titulo'  => $carpeta->descripcion,
                    'badge'   => strtoupper(mb_substr($carpeta->descripcion, 0, 3)),
                    'permiso' => null, // Solo visible para admins
                    'color'   => $carpeta->color ?? '#374151',
                    'emoji'   => $carpeta->icono ?? 'fa-folder-open',
                ];
            }

            $bloques[$clave] = array_merge($meta, [
                'id'         => $clave,
                'carpeta_id' => $carpeta->id,
            ]);
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