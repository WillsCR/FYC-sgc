<?php

namespace App\Http\Controllers;

use App\Services\PermisoService;
use App\Models\Planificacion;
use App\Models\Minuta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PanelController extends Controller
{
    /**
     * Panel principal — carga usuario, bloques visibles y estadísticas globales
     */
    public function index()
    {
        $usuario = PermisoService::usuarioActual();

        if (! $usuario) {
            return redirect()->route('login');
        }

        // Bloques principales visibles según perfil
        $bloques = $this->bloquesVisibles($usuario);

        // Estadísticas globales para el panel
        $stats = $this->estadisticasGlobales($usuario);

        return view('panel.index', compact('usuario', 'bloques', 'stats'));
    }

    // ─── Bloques de módulos ──────────────────────────────────────────────────

    /**
     * Devuelve los bloques que el usuario puede ver,
     * con sus sub-bloques y configuración visual
     */
    private function bloquesVisibles($usuario): array
    {
        $todos = $this->definicionBloques();
        $esAdmin = $usuario->esAdmin();

        return array_filter($todos, function ($bloque) use ($usuario, $esAdmin) {
            if ($esAdmin) return true;
            return (bool) ($usuario->{$bloque['permiso']} ?? false);
        });
    }

    /**
     * Definición completa de bloques y sub-bloques
     * Cada bloque tiene: id, titulo, permiso, color, icono SVG y sub-bloques
     */
    private function definicionBloques(): array
    {
        return [
            'sig' => [
                'id'      => 'sig',
                'titulo'  => 'Control Sistema Integrado de Gestión',
                'badge'   => 'SIG',
                'permiso' => 'bloque_sig',
                'color'   => '#0D2B5E',
                'icono'   => 'check-circle',
                'sub'     => [
                    ['titulo' => 'Documentación del SIG',        'color' => '#DC2626', 'icono' => 'folder',    'ruta' => '#'],
                    ['titulo' => 'Control No Conformidades',     'color' => '#D97706', 'icono' => 'alert',     'ruta' => '#'],
                    ['titulo' => 'Control Instrumentos Medición','color' => '#0F6E56', 'icono' => 'chart-bar', 'ruta' => '#'],
                    ['titulo' => 'Certificados de Calidad',      'color' => '#B45309', 'icono' => 'badge',     'ruta' => '#'],
                    ['titulo' => 'Certificados EPP',             'color' => '#C05621', 'icono' => 'user',      'ruta' => '#'],
                ],
            ],
            'seguridad' => [
                'id'      => 'seguridad',
                'titulo'  => 'Control Seguridad y Salud en el Trabajo',
                'badge'   => 'SST',
                'permiso' => 'bloque_seguridad',
                'color'   => '#991B1B',
                'icono'   => 'shield',
                'sub'     => [
                    ['titulo' => 'Protocolos MINSAL',   'color' => '#DC2626', 'icono' => 'document', 'ruta' => '#'],
                    ['titulo' => 'DS44',                'color' => '#D97706', 'icono' => 'scale',    'ruta' => '#'],
                    ['titulo' => 'Sustancias y Residuos','color' => '#0F6E56','icono' => 'recycle',  'ruta' => '#'],
                    ['titulo' => 'Comité Paritario',    'color' => '#7C3AED', 'icono' => 'users',    'ruta' => '#'],
                ],
            ],
            'ambiente' => [
                'id'      => 'ambiente',
                'titulo'  => 'Control Medio Ambiente',
                'badge'   => 'MA',
                'permiso' => 'bloque_ambiente',
                'color'   => '#15803D',
                'icono'   => 'leaf',
                'sub'     => [
                    ['titulo' => 'Estadísticas MA',      'color' => '#15803D', 'icono' => 'chart-bar', 'ruta' => '#'],
                    ['titulo' => 'Recursos y Residuos',  'color' => '#0369A1', 'icono' => 'recycle',   'ruta' => '#'],
                    ['titulo' => 'Informes Ambientales', 'color' => '#D97706', 'icono' => 'document',  'ruta' => '#'],
                ],
            ],
            'rrhh' => [
                'id'      => 'rrhh',
                'titulo'  => 'Control Recursos Humanos',
                'badge'   => 'RRHH',
                'permiso' => 'bloque_rrhh',
                'color'   => '#7C3AED',
                'icono'   => 'users',
                'sub'     => [
                    ['titulo' => 'Matriz de Cursos',    'color' => '#7C3AED', 'icono' => 'book',     'ruta' => '#'],
                    ['titulo' => 'Control Trabajadores','color' => '#1D4ED8', 'icono' => 'user',     'ruta' => '#'],
                    ['titulo' => 'Capacitaciones',      'color' => '#0369A1', 'icono' => 'calendar', 'ruta' => '#'],
                ],
            ],
            'abastecimiento' => [
                'id'      => 'abastecimiento',
                'titulo'  => 'Control Abastecimiento e Infraestructura',
                'badge'   => 'ABI',
                'permiso' => 'bloque_abastecimiento',
                'color'   => '#B45309',
                'icono'   => 'building',
                'sub'     => [
                    ['titulo' => 'Mantención Infraestructura','color' => '#B45309', 'icono' => 'wrench',   'ruta' => '#'],
                    ['titulo' => 'Control Pozos',             'color' => '#0369A1', 'icono' => 'droplet',  'ruta' => '#'],
                    ['titulo' => 'Abastecimiento General',    'color' => '#374151', 'icono' => 'package',  'ruta' => '#'],
                ],
            ],
            'proyectos' => [
                'id'      => 'proyectos',
                'titulo'  => 'Control Proyectos',
                'badge'   => 'PRY',
                'permiso' => 'bloque_proyectos',
                'color'   => '#1D4ED8',
                'icono'   => 'chart-bar',
                'sub'     => [
                    ['titulo' => 'Documentos Proyecto', 'color' => '#1D4ED8', 'icono' => 'folder',    'ruta' => '#'],
                    ['titulo' => 'Seguimiento',         'color' => '#0F6E56', 'icono' => 'chart-bar', 'ruta' => '#'],
                    ['titulo' => 'Equipo',              'color' => '#7C3AED', 'icono' => 'users',     'ruta' => '#'],
                ],
            ],
        ];
    }

    // ─── Estadísticas globales ───────────────────────────────────────────────

    /**
     * Calcula las 4 métricas del panel principal desde la BD real
     */
    private function estadisticasGlobales($usuario): array
    {
        try {
            // Total planificaciones
            $totalPlan = DB::table('sgc_planificaciones')->count();

            // Pendientes críticos (estado = 'Pendiente' con fecha vencida)
            $pendientesCriticos = DB::table('sgc_planificaciones')
                ->where('estado', 'Pendiente')
                ->where('fecha_termino', '<', now()->toDateString())
                ->count();

            // Cumplimiento global
            $cerradas = DB::table('sgc_planificaciones')
                ->where('estado', 'Cerrado')
                ->count();
            $cumplimiento = $totalPlan > 0
                ? round(($cerradas / $totalPlan) * 100, 1)
                : 0;

            // Minutas del mes actual
            $minutasMes = DB::table('sgc_minutas')
                ->whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->count();

            // Documentos activos (carpetas con contenido)
            $documentos = DB::table('sgc_carpetas_contenido')->count();

        } catch (\Exception $e) {
            // Si alguna tabla no existe aún, devolver ceros
            $cumplimiento      = 0;
            $pendientesCriticos = 0;
            $minutasMes        = 0;
            $documentos        = 0;
        }

        return [
            'cumplimiento'       => $cumplimiento,
            'pendientes'         => $pendientesCriticos,
            'minutas_mes'        => $minutasMes,
            'documentos_activos' => $documentos,
        ];
    }
}
