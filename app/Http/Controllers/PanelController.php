<?php

namespace App\Http\Controllers;

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
        $stats   = $this->estadisticasGlobales();

        return view('panel.index', compact('usuario', 'bloques', 'stats'));
    }

    // ─── Bloques ─────────────────────────────────────────────────────────────

    private function bloquesVisibles($usuario): array
    {
        $todos   = $this->definicionBloques();
        $esAdmin = $usuario->esAdmin();

        return array_filter($todos, function ($bloque) use ($usuario, $esAdmin) {
            if ($esAdmin) return true;
            // Leer la columna de permiso dinámicamente desde sgc_usuarios
            return (bool) ($usuario->{$bloque['permiso']} ?? false);
        });
    }

    private function definicionBloques(): array
    {
        return [

            // ── Bloques originales ─────────────────────────────────────────

            'sig' => [
                'id'      => 'sig',
                'titulo'  => 'Control Sistema Integrado de Gestión',
                'badge'   => 'SIG',
                'permiso' => 'bloque_sig',
                'color'   => '#0D2B5E',
                'emoji'   => '📋',
                'sub'     => [
                    ['titulo' => 'Documentación del SIG',         'color' => '#DC2626', 'emoji' => '📁', 'ruta' => '#'],
                    ['titulo' => 'Control No Conformidades',      'color' => '#D97706', 'emoji' => '⚠️', 'ruta' => '#'],
                    ['titulo' => 'Control Instrumentos Medición', 'color' => '#0F6E56', 'emoji' => '📏', 'ruta' => '#'],
                    ['titulo' => 'Certificados de Calidad',       'color' => '#B45309', 'emoji' => '🏅', 'ruta' => '#'],
                    ['titulo' => 'Certificados EPP',              'color' => '#C05621', 'emoji' => '🦺', 'ruta' => '#'],
                ],
            ],

            'seguridad' => [
                'id'      => 'seguridad',
                'titulo'  => 'Control Seguridad y Salud en el Trabajo',
                'badge'   => 'SST',
                'permiso' => 'bloque_seguridad',
                'color'   => '#991B1B',
                'emoji'   => '🛡️',
                'sub'     => [
                    ['titulo' => 'Protocolos MINSAL',    'color' => '#DC2626', 'emoji' => '🏥', 'ruta' => '#'],
                    ['titulo' => 'DS44',                 'color' => '#D97706', 'emoji' => '⚖️', 'ruta' => '#'],
                    ['titulo' => 'Sustancias y Residuos','color' => '#0F6E56', 'emoji' => '♻️', 'ruta' => '#'],
                    ['titulo' => 'Comité Paritario',     'color' => '#7C3AED', 'emoji' => '👥', 'ruta' => '#'],
                ],
            ],

            'ambiente' => [
                'id'      => 'ambiente',
                'titulo'  => 'Control Medio Ambiente',
                'badge'   => 'MA',
                'permiso' => 'bloque_ambiente',
                'color'   => '#15803D',
                'emoji'   => '🌿',
                'sub'     => [
                    ['titulo' => 'Estadísticas MA',      'color' => '#15803D', 'emoji' => '📊', 'ruta' => '#'],
                    ['titulo' => 'Recursos y Residuos',  'color' => '#0369A1', 'emoji' => '♻️', 'ruta' => '#'],
                    ['titulo' => 'Informes Ambientales', 'color' => '#D97706', 'emoji' => '📄', 'ruta' => '#'],
                ],
            ],

            'rrhh' => [
                'id'      => 'rrhh',
                'titulo'  => 'Control Recursos Humanos',
                'badge'   => 'RRHH',
                'permiso' => 'bloque_rrhh',
                'color'   => '#7C3AED',
                'emoji'   => '👨‍💼',
                'sub'     => [
                    ['titulo' => 'Matriz de Cursos',     'color' => '#7C3AED', 'emoji' => '📚', 'ruta' => '#'],
                    ['titulo' => 'Control Trabajadores', 'color' => '#1D4ED8', 'emoji' => '👤', 'ruta' => '#'],
                    ['titulo' => 'Capacitaciones',       'color' => '#0369A1', 'emoji' => '🎓', 'ruta' => '#'],
                ],
            ],

            'abastecimiento' => [
                'id'      => 'abastecimiento',
                'titulo'  => 'Control Abastecimiento e Infraestructura',
                'badge'   => 'ABI',
                'permiso' => 'bloque_abastecimiento',
                'color'   => '#B45309',
                'emoji'   => '🏗️',
                'sub'     => [
                    ['titulo' => 'Mantención Infraestructura', 'color' => '#B45309', 'emoji' => '🔧', 'ruta' => '#'],
                    ['titulo' => 'Control Pozos',              'color' => '#0369A1', 'emoji' => '💧', 'ruta' => '#'],
                    ['titulo' => 'Abastecimiento General',     'color' => '#374151', 'emoji' => '📦', 'ruta' => '#'],
                ],
            ],

            'proyectos' => [
                'id'      => 'proyectos',
                'titulo'  => 'Control Proyectos',
                'badge'   => 'PRY',
                'permiso' => 'bloque_proyectos',
                'color'   => '#1D4ED8',
                'emoji'   => '📈',
                'sub'     => [
                    ['titulo' => 'Documentos Proyecto', 'color' => '#1D4ED8', 'emoji' => '📁', 'ruta' => '#'],
                    ['titulo' => 'Seguimiento',         'color' => '#0F6E56', 'emoji' => '📊', 'ruta' => '#'],
                    ['titulo' => 'Equipo',              'color' => '#7C3AED', 'emoji' => '👥', 'ruta' => '#'],
                ],
            ],

            // ── Bloques nuevos (requieren ALTER TABLE) ─────────────────────

            'gerencia' => [
                'id'      => 'gerencia',
                'titulo'  => 'Gerencia',
                'badge'   => 'GER',
                'permiso' => 'bloque_gerencia',
                'color'   => '#0C4A6E',
                'emoji'   => '🏢',
                'sub'     => [
                    ['titulo' => 'Documentos de Gerencia', 'color' => '#0C4A6E', 'emoji' => '📄', 'ruta' => '#'],
                    ['titulo' => 'Actas y Resoluciones',   'color' => '#075985', 'emoji' => '📝', 'ruta' => '#'],
                    ['titulo' => 'Indicadores',            'color' => '#0369A1', 'emoji' => '📊', 'ruta' => '#'],
                ],
            ],

            'patio' => [
                'id'      => 'patio',
                'titulo'  => 'Patio e Infraestructura',
                'badge'   => 'PAT',
                'permiso' => 'bloque_patio',
                'color'   => '#78350F',
                'emoji'   => '🏭',
                'sub'     => [
                    ['titulo' => 'Control de Equipos',     'color' => '#78350F', 'emoji' => '⚙️',  'ruta' => '#'],
                    ['titulo' => 'Mantención Vehículos',   'color' => '#92400E', 'emoji' => '🚗', 'ruta' => '#'],
                    ['titulo' => 'Infraestructura Física', 'color' => '#B45309', 'emoji' => '🏗️', 'ruta' => '#'],
                ],
            ],

            'calidad' => [
                'id'      => 'calidad',
                'titulo'  => 'Calidad',
                'badge'   => 'CAL',
                'permiso' => 'bloque_calidad',
                'color'   => '#065F46',
                'emoji'   => '✅',
                'sub'     => [
                    ['titulo' => 'Control de Calidad',     'color' => '#065F46', 'emoji' => '🔍', 'ruta' => '#'],
                    ['titulo' => 'Auditorías',             'color' => '#047857', 'emoji' => '📋', 'ruta' => '#'],
                    ['titulo' => 'Registros de Calidad',   'color' => '#0F6E56', 'emoji' => '📁', 'ruta' => '#'],
                ],
            ],

            'docs_legales' => [
                'id'      => 'docs_legales',
                'titulo'  => 'Documentos Legales',
                'badge'   => 'LEG',
                'permiso' => 'bloque_docs_legales',
                'color'   => '#4C1D95',
                'emoji'   => '⚖️',
                'sub'     => [
                    ['titulo' => 'Contratos',              'color' => '#4C1D95', 'emoji' => '📜', 'ruta' => '#'],
                    ['titulo' => 'Permisos y Licencias',   'color' => '#5B21B6', 'emoji' => '🪪', 'ruta' => '#'],
                    ['titulo' => 'Normativa Legal',        'color' => '#6D28D9', 'emoji' => '🏛️', 'ruta' => '#'],
                ],
            ],

            'formatos' => [
                'id'      => 'formatos',
                'titulo'  => 'Formatos',
                'badge'   => 'FMT',
                'permiso' => 'bloque_formatos',
                'color'   => '#1E3A5F',
                'emoji'   => '📝',
                'sub'     => [
                    ['titulo' => 'Formatos SIG',           'color' => '#1E3A5F', 'emoji' => '📋', 'ruta' => '#'],
                    ['titulo' => 'Formatos RRHH',          'color' => '#1D4ED8', 'emoji' => '👤', 'ruta' => '#'],
                    ['titulo' => 'Formatos Operacionales', 'color' => '#0369A1', 'emoji' => '⚙️',  'ruta' => '#'],
                ],
            ],

            'listado_interes' => [
                'id'      => 'listado_interes',
                'titulo'  => 'Listado de Interés',
                'badge'   => 'LDI',
                'permiso' => 'bloque_listado_interes',
                'color'   => '#134E4A',
                'emoji'   => '📌',
                'sub'     => [
                    ['titulo' => 'Partes Interesadas',     'color' => '#134E4A', 'emoji' => '🤝', 'ruta' => '#'],
                    ['titulo' => 'Proveedores Clave',      'color' => '#115E59', 'emoji' => '🏪', 'ruta' => '#'],
                    ['titulo' => 'Clientes',               'color' => '#0F766E', 'emoji' => '👔', 'ruta' => '#'],
                ],
            ],

        ];
    }

    // ─── Estadísticas ────────────────────────────────────────────────────────

    private function estadisticasGlobales(): array
    {
        try {
            $totalPlan = DB::table('sgc_planificaciones')->count();

            $pendientesCriticos = DB::table('sgc_planificaciones')
                ->where('estado', 'Pendiente')
                ->where('fecha_termino', '<', now()->toDateString())
                ->count();

            $cerradas = DB::table('sgc_planificaciones')
                ->where('estado', 'Cerrado')
                ->count();

            $cumplimiento = $totalPlan > 0
                ? round(($cerradas / $totalPlan) * 100, 1)
                : 0;

            $minutasMes = DB::table('sgc_minutas')
                ->whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->count();

            $documentos = DB::table('sgc_carpetas_contenido')->count();

        } catch (\Exception $e) {
            $cumplimiento       = 0;
            $pendientesCriticos = 0;
            $minutasMes         = 0;
            $documentos         = 0;
        }

        return [
            'cumplimiento'       => $cumplimiento,
            'pendientes'         => $pendientesCriticos,
            'minutas_mes'        => $minutasMes,
            'documentos_activos' => $documentos,
        ];
    }
}
