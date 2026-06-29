<?php

namespace App\Http\Controllers;

use App\Models\FlotaEquipo;
use App\Services\PermisoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FlotaController extends Controller
{
    /**
     * sgc_carpetas3 id para "Control Abastecimiento e Infraestructura".
     * Ajusta este valor si el ID difiere en producción.
     */
    private const FL_CARPETA_ID = 999; // TODO: confirmar en DB

    private function tieneAcceso(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin()
            || PermisoService::can('ver',   'carpeta', self::FL_CARPETA_ID)
            || PermisoService::can('carga', 'carpeta', self::FL_CARPETA_ID));
    }

    private function puedeGestionar(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin() || PermisoService::can('carga', 'carpeta', self::FL_CARPETA_ID));
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        if (! $this->tieneAcceso()) abort(403);

        $usuario        = PermisoService::usuarioActual();
        $puedeGestionar = $this->puedeGestionar();

        $query = FlotaEquipo::query();
        if ($s = $request->input('buscar'))    $query->where(function ($q) use ($s) {
            $q->where('equipo',   'like', "%{$s}%")
              ->orWhere('patente','like', "%{$s}%")
              ->orWhere('marca',  'like', "%{$s}%")
              ->orWhere('modelo', 'like', "%{$s}%")
              ->orWhere('area',   'like', "%{$s}%");
        });
        if ($s = $request->input('area'))      $query->where('area', 'like', "%{$s}%");

        $equipos = $query->orderBy('equipo')->get();

        // Contadores para las tarjetas de resumen
        $totalEquipos      = $equipos->count();
        $certVencidas      = $equipos->filter(fn($e) => $e->semaforo_cert === 'rojo')->count();
        $certPorVencer     = $equipos->filter(fn($e) => $e->semaforo_cert === 'naranja')->count();
        $kmCriticos        = $equipos->filter(fn($e) => $e->semaforo_km   === 'rojo' || $e->semaforo_km === 'naranja')->count();

        return view('flota.index', compact(
            'equipos', 'usuario', 'puedeGestionar',
            'totalEquipos', 'certVencidas', 'certPorVencer', 'kmCriticos'
        ));
    }

    // ── DATOS (JSON para modal editar) ────────────────────────────────────────

    public function datos(FlotaEquipo $flota)
    {
        if (! $this->tieneAcceso()) return response()->json(['error' => 'Sin permiso'], 403);

        return response()->json([
            'id'                            => $flota->id,
            'equipo'                        => $flota->equipo,
            'marca'                         => $flota->marca,
            'modelo'                        => $flota->modelo,
            'patente'                       => $flota->patente,
            'area'                          => $flota->area,
            // Certificaciones
            'fecha_gps'                     => $flota->fecha_gps?->format('Y-m-d'),
            'fecha_skynav'                  => $flota->fecha_skynav?->format('Y-m-d'),
            'fecha_revision_tecnica'        => $flota->fecha_revision_tecnica?->format('Y-m-d'),
            'fecha_permiso_circulacion'     => $flota->fecha_permiso_circulacion?->format('Y-m-d'),
            'fecha_soap'                    => $flota->fecha_soap?->format('Y-m-d'),
            'fecha_cert_mlp'                => $flota->fecha_cert_mlp?->format('Y-m-d'),
            'fecha_extintor'                => $flota->fecha_extintor?->format('Y-m-d'),
            'fecha_prueba_carga'            => $flota->fecha_prueba_carga?->format('Y-m-d'),
            'fecha_insp_camion_pluma'       => $flota->fecha_insp_camion_pluma?->format('Y-m-d'),
            'fecha_insp_gancho'             => $flota->fecha_insp_gancho?->format('Y-m-d'),
            'fecha_insp_perforadora'        => $flota->fecha_insp_perforadora?->format('Y-m-d'),
            'fecha_gancho_perforadora'      => $flota->fecha_gancho_perforadora?->format('Y-m-d'),
            'fecha_cable_acero_perforadora' => $flota->fecha_cable_acero_perforadora?->format('Y-m-d'),
            'fecha_wuinche_perforadora'     => $flota->fecha_wuinche_perforadora?->format('Y-m-d'),
            // Km
            'km_actual'                     => $flota->km_actual,
            'km_proxima_mantencion'         => $flota->km_proxima_mantencion,
            'responsable'                   => $flota->responsable,
            'correo_aviso'                  => $flota->correo_aviso,
            'observaciones'                 => $flota->observaciones,
            // Semáforos calculados
            'semaforo'                      => $flota->semaforo,
            'semaforo_cert'                 => $flota->semaforo_cert,
            'semaforo_km'                   => $flota->semaforo_km,
            'km_restantes'                  => $flota->km_restantes,
            'certs'                         => $flota->certArray(),
        ]);
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        if (! $this->puedeGestionar()) return response()->json(['error' => 'Sin permiso'], 403);

        $request->validate(['equipo' => 'required|string|max:100']);

        $equipo = FlotaEquipo::create($this->datosDesdeRequest($request));

        return response()->json(['success' => true, 'id' => $equipo->id, 'message' => 'Equipo creado']);
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    public function update(Request $request, FlotaEquipo $flota)
    {
        if (! $this->puedeGestionar()) return response()->json(['error' => 'Sin permiso'], 403);

        $request->validate(['equipo' => 'required|string|max:100']);

        $flota->update($this->datosDesdeRequest($request));

        return response()->json(['success' => true, 'message' => 'Equipo actualizado']);
    }

    // ── DESTROY ───────────────────────────────────────────────────────────────

    public function destroy(FlotaEquipo $flota)
    {
        if (! $this->puedeGestionar()) return response()->json(['error' => 'Sin permiso'], 403);

        $flota->delete();

        return response()->json(['success' => true, 'message' => 'Equipo eliminado']);
    }

    // ── EXPORTAR EXCEL ────────────────────────────────────────────────────────

    public function exportar(Request $request)
    {
        if (! $this->tieneAcceso()) abort(403);

        $equipos = FlotaEquipo::orderBy('equipo')->get();

        $spreadsheet = new Spreadsheet();
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];

        // ── Hoja 1: Certificaciones ───────────────────────────────────────────
        $ws1 = $spreadsheet->getActiveSheet();
        $ws1->setTitle('Certificaciones');

        $certLabels = array_values(FlotaEquipo::certFields());
        $headers1 = array_merge(['Equipo', 'Patente', 'Marca', 'Modelo', 'Área'], $certLabels, ['Estado General']);
        foreach ($headers1 as $col => $h) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $ws1->setCellValue($cell, $h);
        }
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers1));
        $ws1->getStyle("A1:{$lastColLetter}1")->applyFromArray($headerStyle);

        $certFields = array_keys(FlotaEquipo::certFields());
        foreach ($equipos as $i => $eq) {
            $row = $i + 2;
            $ws1->setCellValue("A{$row}", $eq->equipo);
            $ws1->setCellValue("B{$row}", $eq->patente);
            $ws1->setCellValue("C{$row}", $eq->marca);
            $ws1->setCellValue("D{$row}", $eq->modelo);
            $ws1->setCellValue("E{$row}", $eq->area);

            foreach ($certFields as $ci => $campo) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 6);
                $fecha = $eq->$campo;
                $ws1->setCellValue("{$colLetter}{$row}", $fecha ? $fecha->format('d/m/Y') : '—');
                $sem = $eq->semaforoCert($campo);
                $color = match($sem) { 'rojo' => 'FEE2E2', 'naranja' => 'FEF3C7', 'verde' => 'DCFCE7', default => 'F3F4F6' };
                $ws1->getStyle("{$colLetter}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
            }

            $estadoCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($certFields) + 6);
            $ws1->setCellValue("{$estadoCol}{$row}", ucfirst($eq->semaforo_cert));
            $color = match($eq->semaforo_cert) { 'rojo' => 'FEE2E2', 'naranja' => 'FEF3C7', 'verde' => 'DCFCE7', default => 'F3F4F6' };
            $ws1->getStyle("{$estadoCol}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
        }
        $ws1->getColumnDimension('A')->setWidth(22);
        $ws1->getColumnDimension('B')->setWidth(12);
        $ws1->getColumnDimension('C')->setWidth(14);
        $ws1->getColumnDimension('D')->setWidth(14);
        $ws1->getColumnDimension('E')->setWidth(18);
        for ($c = 6; $c <= count($certFields) + 6; $c++) {
            $ws1->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c))->setWidth(16);
        }

        // ── Hoja 2: Kilometrajes ──────────────────────────────────────────────
        $ws2 = $spreadsheet->createSheet();
        $ws2->setTitle('Kilometrajes');

        $headers2 = ['Equipo', 'Patente', 'Área', 'Km Actual', 'Próxima Mantención (km)', 'Km Restantes', 'Estado', 'Responsable', 'Observaciones'];
        foreach ($headers2 as $col => $h) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $ws2->setCellValue($cell, $h);
        }
        $ws2->getStyle('A1:I1')->applyFromArray($headerStyle);

        foreach ($equipos as $i => $eq) {
            $row = $i + 2;
            $ws2->setCellValue("A{$row}", $eq->equipo);
            $ws2->setCellValue("B{$row}", $eq->patente);
            $ws2->setCellValue("C{$row}", $eq->area);
            $ws2->setCellValue("D{$row}", $eq->km_actual);
            $ws2->setCellValue("E{$row}", $eq->km_proxima_mantencion);
            $ws2->setCellValue("F{$row}", $eq->km_restantes ?? '—');
            $ws2->setCellValue("G{$row}", ucfirst($eq->semaforo_km));
            $ws2->setCellValue("H{$row}", $eq->responsable);
            $ws2->setCellValue("I{$row}", $eq->observaciones);
            $color = match($eq->semaforo_km) { 'rojo' => 'FEE2E2', 'naranja' => 'FEF3C7', 'verde' => 'DCFCE7', default => 'F3F4F6' };
            $ws2->getStyle("G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
        }
        foreach (['A'=>22,'B'=>12,'C'=>18,'D'=>14,'E'=>22,'F'=>14,'G'=>14,'H'=>20,'I'=>35] as $col => $w) {
            $ws2->getColumnDimension($col)->setWidth($w);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'control_flota_' . now()->format('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ── IMPORTAR EXCEL ────────────────────────────────────────────────────────

    public function importar(Request $request)
    {
        $u = PermisoService::usuarioActual();
        if (! $u || ! $u->esAdmin()) return response()->json(['error' => 'Solo administradores pueden importar'], 403);

        $request->validate(['archivo_excel' => 'required|file']);

        $ext = strtolower($request->file('archivo_excel')->getClientOriginalExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'xlsm'])) {
            return response()->json(['error' => 'Formato no válido. Use .xlsx o .xls'], 422);
        }

        try {
            $filePath    = $request->file('archivo_excel')->getPathname();
            $readerType  = in_array($ext, ['xlsm']) ? 'Xlsx' : IOFactory::identify($filePath);
            $reader      = IOFactory::createReader($readerType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $importados = 0;
            $omitidos   = 0;

            foreach ($spreadsheet->getAllSheets() as $ws) {
                $hoja = strtolower(trim($ws->getTitle()));

                // Hoja de certificaciones
                if (str_contains($hoja, 'certif')) {
                    $filaInicio = $this->detectarFilaInicio($ws, 'B', 2, 15);
                    if (! $filaInicio) continue;

                    foreach ($ws->getRowIterator($filaInicio) as $row) {
                        $ri     = $row->getRowIndex();
                        $equipo = trim((string) $ws->getCell('A' . $ri)->getFormattedValue());
                        if (! $equipo) continue;

                        $datos = [
                            'equipo'  => $equipo,
                            'marca'   => trim((string) $ws->getCell('B' . $ri)->getFormattedValue()) ?: null,
                            'modelo'  => trim((string) $ws->getCell('C' . $ri)->getFormattedValue()) ?: null,
                            'patente' => trim((string) $ws->getCell('D' . $ri)->getFormattedValue()) ?: null,
                            'area'    => trim((string) $ws->getCell('E' . $ri)->getFormattedValue()) ?: null,
                        ];

                        // Columnas F..S = 14 fechas
                        $campos = array_keys(FlotaEquipo::certFields());
                        for ($c = 0; $c < 14; $c++) {
                            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + 6);
                            $datos[$campos[$c]] = $this->parsearFecha($ws->getCell($col . $ri)->getValue());
                        }

                        $patente = $datos['patente'];
                        if ($patente && FlotaEquipo::where('patente', $patente)->exists()) {
                            FlotaEquipo::where('patente', $patente)->update($datos);
                        } else {
                            FlotaEquipo::create($datos);
                            $importados++;
                        }
                    }
                }

                // Hoja de kilometrajes
                if (str_contains($hoja, 'kilom') || str_contains($hoja, 'km')) {
                    $filaInicio = $this->detectarFilaInicio($ws, 'A', 2, 15);
                    if (! $filaInicio) continue;

                    foreach ($ws->getRowIterator($filaInicio) as $row) {
                        $ri      = $row->getRowIndex();
                        $patente = trim((string) $ws->getCell('B' . $ri)->getFormattedValue());
                        if (! $patente) continue;

                        $kmActual = $ws->getCell('E' . $ri)->getValue();
                        $kmProx   = $ws->getCell('F' . $ri)->getValue();

                        FlotaEquipo::where('patente', $patente)->update([
                            'km_actual'             => is_numeric($kmActual) ? (int) $kmActual : null,
                            'km_proxima_mantencion' => is_numeric($kmProx)   ? (int) $kmProx   : null,
                            'responsable'           => trim((string) $ws->getCell('I' . $ri)->getFormattedValue()) ?: null,
                            'observaciones'         => trim((string) $ws->getCell('J' . $ri)->getFormattedValue()) ?: null,
                        ]);
                    }
                }
            }

            return response()->json([
                'success'    => true,
                'importados' => $importados,
                'omitidos'   => $omitidos,
                'mensaje'    => "Importados {$importados} registros" . ($omitidos ? " ({$omitidos} omitidos)" : '') . '.',
            ]);

        } catch (\Throwable $e) {
            \Log::error('FlotaController::importar — ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function datosDesdeRequest(Request $request): array
    {
        return [
            'equipo'                        => $request->equipo,
            'marca'                         => $request->marca                         ?: null,
            'modelo'                        => $request->modelo                        ?: null,
            'patente'                       => $request->patente                       ?: null,
            'area'                          => $request->area                          ?: null,
            'fecha_gps'                     => $request->fecha_gps                     ?: null,
            'fecha_skynav'                  => $request->fecha_skynav                  ?: null,
            'fecha_revision_tecnica'        => $request->fecha_revision_tecnica        ?: null,
            'fecha_permiso_circulacion'     => $request->fecha_permiso_circulacion     ?: null,
            'fecha_soap'                    => $request->fecha_soap                    ?: null,
            'fecha_cert_mlp'                => $request->fecha_cert_mlp                ?: null,
            'fecha_extintor'                => $request->fecha_extintor                ?: null,
            'fecha_prueba_carga'            => $request->fecha_prueba_carga            ?: null,
            'fecha_insp_camion_pluma'       => $request->fecha_insp_camion_pluma       ?: null,
            'fecha_insp_gancho'             => $request->fecha_insp_gancho             ?: null,
            'fecha_insp_perforadora'        => $request->fecha_insp_perforadora        ?: null,
            'fecha_gancho_perforadora'      => $request->fecha_gancho_perforadora      ?: null,
            'fecha_cable_acero_perforadora' => $request->fecha_cable_acero_perforadora ?: null,
            'fecha_wuinche_perforadora'     => $request->fecha_wuinche_perforadora     ?: null,
            'km_actual'                     => $request->km_actual                     ?: null,
            'km_proxima_mantencion'         => $request->km_proxima_mantencion         ?: null,
            'responsable'                   => $request->responsable                   ?: null,
            'correo_aviso'                  => $request->correo_aviso                  ?: null,
            'observaciones'                 => $request->observaciones                  ?: null,
        ];
    }

    private function detectarFilaInicio($ws, string $col, int $desde, int $hasta): ?int
    {
        for ($r = $desde; $r <= $hasta; $r++) {
            $v = trim((string) $ws->getCell($col . $r)->getFormattedValue());
            if ($v && strlen($v) > 1) return $r;
        }
        return null;
    }

    private function parsearFecha($raw): ?string
    {
        if (! $raw || strtolower(trim((string) $raw)) === 'n/a') return null;
        try {
            if (is_numeric($raw)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw)
                )->format('Y-m-d');
            }
            $str = trim((string) $raw);
            foreach (['m/d/Y', 'd/m/Y', 'Y-m-d', 'd-m-Y'] as $fmt) {
                $obj = \DateTime::createFromFormat($fmt, $str);
                if ($obj && $obj->format($fmt) === $str) return $obj->format('Y-m-d');
            }
            $ts = strtotime($str);
            if ($ts) return date('Y-m-d', $ts);
        } catch (\Exception $e) {}
        return null;
    }
}
