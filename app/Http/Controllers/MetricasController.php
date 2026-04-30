<?php

namespace App\Http\Controllers;

use App\Services\PermisoService;
use Illuminate\Support\Facades\DB;

class MetricasController extends Controller
{
    public function index()
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario) return redirect()->route('login');

        $stats          = $this->estadisticasGlobales();
        $graficoPorArea = $this->graficoCumplimientoPorArea();
        $graficoMinutas = $this->graficoMinutasPorMes();

        return view('metricas.index', compact(
            'usuario', 'stats', 'graficoPorArea', 'graficoMinutas'
        ));
    }

    /**
     * Descarga el Excel con todas las métricas
     */
    public function exportarExcel()
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario) return redirect()->route('login');

        $stats          = $this->estadisticasGlobales();
        $graficoPorArea = $this->graficoCumplimientoPorArea();
        $graficoMinutas = $this->graficoMinutasPorMes();

        // Nombre del archivo con fecha
        $nombreArchivo = 'Metricas_SGC_' . now()->format('d-m-Y') . '.xlsx';

        // Generar Excel con PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Métricas SGC — F&C Chile SPA')
            ->setCreator('Sistema SGC')
            ->setDescription('Métricas de cumplimiento exportadas el ' . now()->format('d/m/Y'));

        // ── Hoja 1: Resumen KPIs ─────────────────────────────────────────
        $hoja1 = $spreadsheet->getActiveSheet();
        $hoja1->setTitle('Resumen KPIs');

        // Encabezado
        $hoja1->setCellValue('A1', 'F&C Chile SPA — Métricas del Sistema SGC');
        $hoja1->setCellValue('A2', 'Generado el ' . now()->format('d/m/Y H:i'));
        $hoja1->mergeCells('A1:D1');
        $hoja1->mergeCells('A2:D2');

        // Estilo encabezado
        $hoja1->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D2B5E']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $hoja1->getStyle('A2')->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => '64748B']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EFF6FF']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // KPIs
        $hoja1->setCellValue('A4', 'Indicador');
        $hoja1->setCellValue('B4', 'Valor');
        $hoja1->setCellValue('C4', 'Estado');

        $hoja1->getStyle('A4:C4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D4ED8']],
        ]);

        $kpis = [
            ['Cumplimiento Global',        $stats['cumplimiento'] . '%',
             $stats['cumplimiento'] >= 80 ? 'Óptimo' : ($stats['cumplimiento'] >= 60 ? 'Regular' : 'Crítico')],
            ['Actividades Cerradas',       $stats['cerradas'],       'Completadas'],
            ['Actividades Pendientes',     $stats['pendientes'],     $stats['pendientes'] > 5 ? 'Crítico' : 'Normal'],
            ['Total Planificaciones',      $stats['totalPlan'],      '—'],
            ['Minutas este mes',           $stats['minutasMes'],     '—'],
            ['Minutas año ' . now()->year, $stats['minutasAnio'],    '—'],
            ['Documentos activos',         $stats['documentos'],     '—'],
        ];

        foreach ($kpis as $i => $kpi) {
            $fila = $i + 5;
            $hoja1->setCellValue('A' . $fila, $kpi[0]);
            $hoja1->setCellValue('B' . $fila, $kpi[1]);
            $hoja1->setCellValue('C' . $fila, $kpi[2]);

            $bg = $i % 2 === 0 ? 'F8FAFC' : 'FFFFFF';
            $hoja1->getStyle("A{$fila}:C{$fila}")->applyFromArray([
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $bg]],
            ]);
        }

        $hoja1->getColumnDimension('A')->setWidth(32);
        $hoja1->getColumnDimension('B')->setWidth(18);
        $hoja1->getColumnDimension('C')->setWidth(18);
        $hoja1->getRowDimension(1)->setRowHeight(28);

        // ── Hoja 2: Cumplimiento por Área ────────────────────────────────
        $hoja2 = $spreadsheet->createSheet();
        $hoja2->setTitle('Cumplimiento por Área');

        $hoja2->setCellValue('A1', 'Cumplimiento por Área — ' . now()->format('d/m/Y'));
        $hoja2->mergeCells('A1:F1');
        $hoja2->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F6E56']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Cabeceras
        $cabeceras = ['Área', 'Total', 'Cerradas', 'Pendientes', 'Cumplimiento %', 'Estado'];
        foreach ($cabeceras as $col => $cab) {
            $letra = chr(65 + $col);
            $hoja2->setCellValue($letra . '3', $cab);
        }
        $hoja2->getStyle('A3:F3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D2B5E']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Datos
        foreach ($graficoPorArea['labels'] as $i => $area) {
            $fila  = $i + 4;
            $pct   = $graficoPorArea['cumplimiento'][$i];
            $cerradas  = $graficoPorArea['cerradasPorArea'][$i];
            $pendientes = $graficoPorArea['pendientesPorArea'][$i];
            $estado = $pct >= 80 ? 'Óptimo' : ($pct >= 60 ? 'Regular' : 'Crítico');
            $colorEstado = $pct >= 80 ? '16A34A' : ($pct >= 60 ? 'D97706' : 'DC2626');

            $hoja2->setCellValue('A' . $fila, $area);
            $hoja2->setCellValue('B' . $fila, $graficoPorArea['totales'][$i]);
            $hoja2->setCellValue('C' . $fila, $cerradas);
            $hoja2->setCellValue('D' . $fila, $pendientes);
            $hoja2->setCellValue('E' . $fila, $pct . '%');
            $hoja2->setCellValue('F' . $fila, $estado);

            $bg = $i % 2 === 0 ? 'F8FAFC' : 'FFFFFF';
            $hoja2->getStyle("A{$fila}:F{$fila}")->applyFromArray([
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $bg]],
            ]);
            $hoja2->getStyle("F{$fila}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $colorEstado]],
            ]);
            $hoja2->getStyle("E{$fila}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $colorEstado]],
            ]);
        }

        // Fila de totales
        $filaTotal = count($graficoPorArea['labels']) + 4;
        $totalCerradas  = array_sum($graficoPorArea['cerradasPorArea']);
        $totalPend      = array_sum($graficoPorArea['pendientesPorArea']);
        $totalActividades = array_sum($graficoPorArea['totales']);
        $pctTotal = $totalActividades > 0 ? round($totalCerradas / $totalActividades * 100, 1) : 0;

        $hoja2->setCellValue('A' . $filaTotal, 'TOTAL GLOBAL');
        $hoja2->setCellValue('B' . $filaTotal, $totalActividades);
        $hoja2->setCellValue('C' . $filaTotal, $totalCerradas);
        $hoja2->setCellValue('D' . $filaTotal, $totalPend);
        $hoja2->setCellValue('E' . $filaTotal, $pctTotal . '%');
        $hoja2->setCellValue('F' . $filaTotal, $pctTotal >= 80 ? 'Óptimo' : 'Regular');
        $hoja2->getStyle("A{$filaTotal}:F{$filaTotal}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D2B5E']],
        ]);

        foreach (['A','B','C','D','E','F'] as $col) {
            $hoja2->getColumnDimension($col)->setAutoSize(true);
        }
        $hoja2->getStyle('B3:F' . $filaTotal)->getAlignment()->setHorizontal('center');

        // ── Hoja 3: Minutas por Mes ──────────────────────────────────────
        $hoja3 = $spreadsheet->createSheet();
        $hoja3->setTitle('Minutas ' . now()->year);

        $hoja3->setCellValue('A1', 'Minutas por Mes — ' . now()->year);
        $hoja3->mergeCells('A1:C1');
        $hoja3->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D2B5E']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $hoja3->setCellValue('A3', 'Mes');
        $hoja3->setCellValue('B3', 'Cantidad de Minutas');
        $hoja3->setCellValue('C3', 'Acumulado');
        $hoja3->getStyle('A3:C3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D2B5E']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $acumulado = 0;
        foreach ($graficoMinutas['labels'] as $i => $mes) {
            $fila = $i + 4;
            $cantidad = $graficoMinutas['valores'][$i];
            $acumulado += $cantidad;
            $bg = $i % 2 === 0 ? 'F8FAFC' : 'FFFFFF';

            $hoja3->setCellValue('A' . $fila, $mes);
            $hoja3->setCellValue('B' . $fila, $cantidad);
            $hoja3->setCellValue('C' . $fila, $acumulado);

            $hoja3->getStyle("A{$fila}:C{$fila}")->applyFromArray([
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $bg]],
                'alignment' => ['horizontal' => 'center'],
            ]);
            if ($cantidad > 0) {
                $hoja3->getStyle("B{$fila}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0D2B5E']],
                ]);
            }
        }

        $hoja3->getColumnDimension('A')->setWidth(14);
        $hoja3->getColumnDimension('B')->setWidth(24);
        $hoja3->getColumnDimension('C')->setWidth(16);

        // Activar hoja 1
        $spreadsheet->setActiveSheetIndex(0);

        // Generar respuesta de descarga
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ─── Métodos de datos (mismos que el MetricasController actual) ──────────

    private function estadisticasGlobales(): array
    {
        try {
            $totalPlan  = DB::table('sgc_planificaciones')->count();
            $cerradas   = DB::table('sgc_planificaciones')->where('id_estado', 2)->count();
            $pendientes = DB::table('sgc_planificaciones')->where('id_estado', 1)->count();
            $cumplimiento = $totalPlan > 0 ? round(($cerradas / $totalPlan) * 100, 1) : 0;
            $minutasMes  = DB::table('sgc_minutas')->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->count();
            $minutasAnio = DB::table('sgc_minutas')->whereYear('fecha', now()->year)->count();
            $documentos  = DB::table('sgc_carpetas_contenido')->count();
        } catch (\Exception $e) {
            $totalPlan = $cerradas = $pendientes = $cumplimiento = 0;
            $minutasMes = $minutasAnio = $documentos = 0;
        }
        return compact('totalPlan','cerradas','pendientes','cumplimiento','minutasMes','minutasAnio','documentos');
    }

    private function graficoCumplimientoPorArea(): array
    {
        $nombresAreas = [
            1=>'RRHH', 2=>'Seguridad SST', 3=>'Abastecimiento',
            4=>'Contrato Pozos', 5=>'Medio Ambiente', 6=>'Control SGI',
            7=>'SGI Gestión', 8=>'Patios e Infra.', 9=>'Gerencia Oper.', 10=>'Gerencia Gral.',
        ];
        try {
            $datos = DB::table('sgc_planificaciones')
                ->select('area', DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN id_estado = 2 THEN 1 ELSE 0 END) as cerradas'),
                    DB::raw('SUM(CASE WHEN id_estado = 1 THEN 1 ELSE 0 END) as pendientes'))
                ->whereIn('area', array_keys($nombresAreas))
                ->groupBy('area')->orderBy('area')->get();

            $labels = $cumplimiento = $totales = $cerradasPorArea = $pendientesPorArea = [];
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
        return compact('labels','cumplimiento','totales','cerradasPorArea','pendientesPorArea');
    }

    private function graficoMinutasPorMes(): array
    {
        $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        try {
            $datos = DB::table('sgc_minutas')
                ->select(DB::raw('MONTH(fecha) as mes'), DB::raw('COUNT(*) as total'))
                ->whereYear('fecha', now()->year)
                ->groupBy('mes')->orderBy('mes')->get();
            $porMes = array_fill(1, 12, 0);
            foreach ($datos as $row) $porMes[(int)$row->mes] = $row->total;
            $valores = array_values($porMes);
        } catch (\Exception $e) {
            $valores = array_fill(0, 12, 0);
        }
        return ['labels' => $meses, 'valores' => $valores];
    }
}
