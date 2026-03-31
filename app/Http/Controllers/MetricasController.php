<?php

namespace App\Http\Controllers;

use App\Services\PermisoService;
use Illuminate\Support\Facades\DB;

class MetricasController extends Controller
{
    public function index()
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario) {
            return redirect()->route('login');
        }

        $stats          = $this->estadisticasGlobales();
        $graficoPorArea = $this->graficoCumplimientoPorArea();
        $graficoMinutas = $this->graficoMinutasPorMes();

        return view('metricas.index', compact(
            'usuario', 'stats', 'graficoPorArea', 'graficoMinutas'
        ));
    }

    // ─── Estadísticas globales ────────────────────────────────────────────

    private function estadisticasGlobales(): array
    {
        try {
            $totalPlan  = DB::table('sgc_planificaciones')->count();
            $cerradas   = DB::table('sgc_planificaciones')->where('id_estado', 2)->count();
            $pendientes = DB::table('sgc_planificaciones')->where('id_estado', 1)->count();
            $cumplimiento = $totalPlan > 0
                ? round(($cerradas / $totalPlan) * 100, 1) : 0;

            $minutasMes = DB::table('sgc_minutas')
                ->whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->count();

            $minutasAnio = DB::table('sgc_minutas')
                ->whereYear('fecha', now()->year)
                ->count();

            $documentos = DB::table('sgc_carpetas_contenido')->count();

        } catch (\Exception $e) {
            $totalPlan    = 0; $cerradas     = 0;
            $pendientes   = 0; $cumplimiento = 0;
            $minutasMes   = 0; $minutasAnio  = 0;
            $documentos   = 0;
        }

        return compact(
            'totalPlan', 'cerradas', 'pendientes',
            'cumplimiento', 'minutasMes', 'minutasAnio', 'documentos'
        );
    }

    // ─── Cumplimiento por área ────────────────────────────────────────────

    private function graficoCumplimientoPorArea(): array
    {
        $nombresAreas = [
            1 => 'RRHH',              2 => 'Seguridad SST',
            3 => 'Abastecimiento',    4 => 'Contrato Pozos',
            5 => 'Medio Ambiente',    6 => 'Control SGI',
            7 => 'SGI Gestión',       8 => 'Patios e Infra.',
            9 => 'Gerencia Oper.',   10 => 'Gerencia Gral.',
        ];

        try {
            $datos = DB::table('sgc_planificaciones')
                ->select(
                    'area',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN id_estado = 2 THEN 1 ELSE 0 END) as cerradas'),
                    DB::raw('SUM(CASE WHEN id_estado = 1 THEN 1 ELSE 0 END) as pendientes')
                )
                ->whereIn('area', array_keys($nombresAreas))
                ->groupBy('area')
                ->orderBy('area')
                ->get();

            $labels = $cumplimiento = $totales = $pendientesPorArea = $cerradasPorArea = [];

            foreach ($datos as $row) {
                $labels[]            = $nombresAreas[$row->area];
                $pct                 = $row->total > 0 ? round(($row->cerradas / $row->total) * 100, 1) : 0;
                $cumplimiento[]      = $pct;
                $totales[]           = $row->total;
                $cerradasPorArea[]   = (int) $row->cerradas;
                $pendientesPorArea[] = (int) $row->pendientes;
            }

        } catch (\Exception $e) {
            $labels = $cumplimiento = $totales = $cerradasPorArea = $pendientesPorArea = [];
        }

        return compact('labels', 'cumplimiento', 'totales', 'cerradasPorArea', 'pendientesPorArea');
    }

    // ─── Minutas por mes ─────────────────────────────────────────────────

    private function graficoMinutasPorMes(): array
    {
        $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

        try {
            $datos = DB::table('sgc_minutas')
                ->select(DB::raw('MONTH(fecha) as mes'), DB::raw('COUNT(*) as total'))
                ->whereYear('fecha', now()->year)
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();

            $porMes = array_fill(1, 12, 0);
            foreach ($datos as $row) {
                $porMes[(int) $row->mes] = $row->total;
            }

            $valores = array_values($porMes);

        } catch (\Exception $e) {
            $valores = array_fill(0, 12, 0);
        }

        return ['labels' => $meses, 'valores' => $valores];
    }
}
