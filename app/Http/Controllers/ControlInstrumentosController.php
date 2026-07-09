<?php

namespace App\Http\Controllers;

use App\Models\ProgramaVerificacion;
use App\Models\ArchivoEquipo;
use App\Models\Area;
use App\Models\Usuario;
use App\Services\ArchivoEquipoService;
use App\Services\PermisoService;
use App\Helpers\ControlInstrumentosHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Str;

class ControlInstrumentosController extends Controller
{
    private const CI_CARPETA_ID = 98;

    public function __construct(private ArchivoEquipoService $archivoService) {}

    /**
     * Permiso único: `carga` sobre carpeta 98 controla TODO el acceso a CI.
     * Admins siempre tienen acceso completo.
     */
    /** Puede ver (solo lectura o acceso completo). */
    private function tieneAccesoCI(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin()
            || PermisoService::can('ver',   'carpeta', self::CI_CARPETA_ID)
            || PermisoService::can('carga', 'carpeta', self::CI_CARPETA_ID));
    }

    /** Puede gestionar (crear, editar, eliminar). */
    private function puedeGestionarCI(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin() || PermisoService::can('carga', 'carpeta', self::CI_CARPETA_ID));
    }

    private function checkAcceso(): void
    {
        if (! $this->tieneAccesoCI()) {
            abort(403, 'No tienes permiso para acceder al módulo de Control de Instrumentos.');
        }
    }

    // ── Listado principal ────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->checkAcceso();
        $usuario = PermisoService::usuarioActual();

        // Filtros
        $search   = $request->input('search', '');
        $proyecto = $request->input('proyecto', '');
        $estado   = $request->input('estado', '');

        $query = ProgramaVerificacion::with(['area', 'archivosCalidad', 'archivosCalibra']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion',    'like', "%{$search}%")
                  ->orWhere('marca',        'like', "%{$search}%")
                  ->orWhere('modelo',       'like', "%{$search}%")
                  ->orWhere('serie',        'like', "%{$search}%")
                  ->orWhere('certificado_calibracion', 'like', "%{$search}%");
            });
        }

        if ($proyecto) {
            $query->where('id_contrato', $proyecto);
        }

        $hoy = Carbon::today();
        if ($estado === 'vigente') {
            $query->where('proxima', '>', $hoy->copy()->addDays(30));
        } elseif ($estado === 'por_vencer') {
            $query->whereBetween('proxima', [$hoy->toDateString(), $hoy->copy()->addDays(30)->toDateString()]);
        } elseif ($estado === 'vencido') {
            $query->where(function ($q) use ($hoy) {
                $q->where('proxima', '<', $hoy->toDateString())->orWhereNull('proxima');
            });
        }

        $programas = $query->orderBy('ultima', 'desc')->orderBy('descripcion')->paginate(25)->withQueryString();

        // KPIs globales (sin filtros)
        $hoy2 = Carbon::today();
        $kpis = [
            'total'      => ProgramaVerificacion::count(),
            'vigentes'   => ProgramaVerificacion::where('proxima', '>', $hoy2->copy()->addDays(30))->count(),
            'por_vencer' => ProgramaVerificacion::whereBetween('proxima', [
                                $hoy2->toDateString(),
                                $hoy2->copy()->addDays(30)->toDateString(),
                            ])->count(),
            'vencidos'   => ProgramaVerificacion::where(function ($q) use ($hoy2) {
                                $q->where('proxima', '<', $hoy2->toDateString())->orWhereNull('proxima');
                            })->count(),
        ];

        $areas          = Area::orderBy('descripcion')->get();
        $usuarios       = Usuario::orderBy('nombre')->get();
        $puedeGestionar = $this->puedeGestionarCI(); // crear / editar / eliminar
        $puedeImportar  = $usuario && $usuario->esAdmin();  // solo admins

        return view('control-instrumentos.index-nuevo', compact(
            'programas', 'areas', 'usuarios', 'usuario', 'kpis',
            'search', 'proyecto', 'estado',
            'puedeGestionar', 'puedeImportar'
        ));
    }

    // ── Crear ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:150',
            'id_contrato' => 'required|integer|exists:sgc_areas,id',
            'calibracion' => 'required|in:1,2,3,4',
        ]);

        if (! $this->puedeGestionarCI()) abort(403);
        $usuario = PermisoService::usuarioActual();

        $programa = ProgramaVerificacion::create([
            'id_contrato'             => $request->id_contrato,
            'descripcion'             => $request->descripcion,
            'marca'                   => $request->marca,
            'modelo'                  => $request->modelo,
            'serie'                   => $request->serie,
            'interno'                 => $request->interno ?: null,
            'cert_calidad'            => $request->has('cert_calidad')     ? 1 : 0,
            'cert_calibracion'        => $request->has('cert_calibracion') ? 1 : 0,
            'calibracion'             => $request->calibracion,
            'verificacion'            => $request->verificacion ?: null,
            'certificado_calibracion' => $request->num_cert_calibracion,
            'ultima'                  => $request->fecha_ultima  ?: null,
            'proxima'                 => $request->fecha_proxima ?: null,
            'observaciones'           => $request->observaciones,
            'responsable'             => $request->responsable ?: null,
            'correo_aviso'            => $request->correo_aviso ?: null,
            'id_usuario'              => $usuario->id,
        ]);

        if ($request->hasFile('archivo_calidad')) {
            $this->archivoService->subirArchivo(
                $request->file('archivo_calidad'), $usuario->id,
                'cert_calidad', null, $programa->id
            );
        }
        if ($request->hasFile('archivo_calibracion')) {
            $this->archivoService->subirArchivo(
                $request->file('archivo_calibracion'), $usuario->id,
                'cert_calibracion', null, $programa->id
            );
        }

        return response()->json(['success' => true, 'message' => 'Programa creado correctamente', 'id' => $programa->id]);
    }

    // ── Editar (carga dinámica) ──────────────────────────────────────────────

    public function edit(ProgramaVerificacion $programaVerificacion)
    {
        $areas   = Area::orderBy('descripcion')->get();
        $programa = $programaVerificacion;

        return view('control-instrumentos.partials.formulario-edicion', compact('programa', 'areas'));
    }

    // ── Actualizar ───────────────────────────────────────────────────────────

    public function update(Request $request, ProgramaVerificacion $programaVerificacion)
    {
        $request->validate([
            'descripcion' => 'required|string|max:150',
            'calibracion' => 'required|in:1,2,3,4',
        ]);

        if (! $this->puedeGestionarCI()) abort(403);

        $programaVerificacion->update([
            'id_contrato'             => $request->id_contrato ?: $programaVerificacion->id_contrato,
            'descripcion'             => $request->descripcion,
            'marca'                   => $request->marca,
            'modelo'                  => $request->modelo,
            'serie'                   => $request->serie,
            'interno'                 => $request->interno ?: null,
            'cert_calidad'            => $request->has('cert_calidad')     ? 1 : 0,
            'cert_calibracion'        => $request->has('cert_calibracion') ? 1 : 0,
            'calibracion'             => $request->calibracion,
            'verificacion'            => $request->verificacion ?: null,
            'certificado_calibracion' => $request->certificado_calibracion,
            'ultima'                  => $request->ultima  ?: null,
            'proxima'                 => $request->proxima ?: null,
            'observaciones'           => $request->observaciones,
            'responsable'             => $request->responsable ?: null,
            'correo_aviso'            => $request->correo_aviso ?: null,
        ]);

        return response()->json(['success' => true, 'message' => 'Programa actualizado correctamente']);
    }

    // ── Archivos (carga dinámica) ────────────────────────────────────────────

    public function archivos(ProgramaVerificacion $programaVerificacion)
    {
        $programa       = $programaVerificacion->load(['archivosCalidad', 'archivosCalibra']);
        $puedeGestionar = $this->puedeGestionarCI();
        return view('control-instrumentos.partials.gestionar-archivos', compact('programa', 'puedeGestionar'));
    }

    // ── Exportar Excel ───────────────────────────────────────────────────────

    public function exportar(Request $request)
    {
        $this->checkAcceso();

        $search   = $request->input('search', '');
        $proyecto = $request->input('proyecto', '');
        $estado   = $request->input('estado', '');

        $query = ProgramaVerificacion::with(['area']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion',            'like', "%{$search}%")
                  ->orWhere('marca',                'like', "%{$search}%")
                  ->orWhere('modelo',               'like', "%{$search}%")
                  ->orWhere('serie',                'like', "%{$search}%")
                  ->orWhere('certificado_calibracion','like', "%{$search}%");
            });
        }
        if ($proyecto) $query->where('id_contrato', $proyecto);

        $hoy = Carbon::today();
        if ($estado === 'vigente') {
            $query->where('proxima', '>', $hoy->copy()->addDays(30));
        } elseif ($estado === 'por_vencer') {
            $query->whereBetween('proxima', [$hoy->toDateString(), $hoy->copy()->addDays(30)->toDateString()]);
        } elseif ($estado === 'vencido') {
            $query->where(function ($q) use ($hoy) {
                $q->where('proxima', '<', $hoy->toDateString())->orWhereNull('proxima');
            });
        }

        $registros = $query->orderBy('ultima', 'desc')->orderBy('descripcion')->get();

        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('Control de Instrumentos');

        // ── Cabecera ──────────────────────────────────────────────────────────
        $headers = ['N°','Proyecto','Tipo de Certificado','Descripción','Marca','Modelo',
                    'N° Serie','N° Interno','Calibración','Verificación',
                    'N° Cert. Calibración','Última','Próxima','Estado','Responsable'];
        foreach ($headers as $col => $h) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $ws->setCellValue($cell, $h);
        }
        $ws->getStyle('A1:O1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D2B5E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['rgb' => 'FFFFFF']]],
        ]);
        $ws->getRowDimension(1)->setRowHeight(20);

        // ── Datos ─────────────────────────────────────────────────────────────
        foreach ($registros as $i => $p) {
            $row = $i + 2;

            $semaforo = 'Sin fecha';
            if ($p->proxima) {
                $dias = Carbon::today()->diffInDays($p->proxima, false);
                $semaforo = $dias < 0 ? 'Vencido' : ($dias <= 30 ? 'Por vencer' : 'Vigente');
            }

            $ws->setCellValue("A{$row}", $i + 1);
            $ws->setCellValue("B{$row}", $p->area?->descripcion ?? '—');
            $ws->setCellValue("C{$row}", $p->tipo_certificado);
            $ws->setCellValue("D{$row}", $p->descripcion);
            $ws->setCellValue("E{$row}", $p->marca);
            $ws->setCellValue("F{$row}", $p->modelo);
            $ws->setCellValue("G{$row}", $p->serie);
            $ws->setCellValue("H{$row}", $p->numero_interno);
            $ws->setCellValue("I{$row}", $p->calibracion);
            $ws->setCellValue("J{$row}", $p->verificacion);
            $ws->setCellValue("K{$row}", $p->certificado_calibracion);
            $ws->setCellValue("L{$row}", $p->ultima?->format('d-m-Y'));
            $ws->setCellValue("M{$row}", $p->proxima?->format('d-m-Y'));
            $ws->setCellValue("N{$row}", $semaforo);
            $ws->setCellValue("O{$row}", $p->responsable);

            $color = match($semaforo) {
                'Vigente'    => 'DCFCE7',
                'Por vencer' => 'FEF3C7',
                default      => 'FEE2E2',
            };
            $ws->getStyle("N{$row}")->getFill()
               ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
        }

        // ── Ancho de columnas ─────────────────────────────────────────────────
        foreach (['A'=>5,'B'=>20,'C'=>28,'D'=>34,'E'=>12,'F'=>12,'G'=>16,'H'=>10,
                  'I'=>14,'J'=>14,'K'=>18,'L'=>13,'M'=>13,'N'=>13,'O'=>20] as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }

        if ($registros->count() > 0) {
            $ws->getStyle('A2:O' . ($registros->count() + 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                               'color'       => ['rgb' => 'D1D5DB']]],
            ]);
        }

        $filename = 'control_instrumentos_' . now()->format('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ── Importar Excel ───────────────────────────────────────────────────────

    public function importar(Request $request)
    {
        $request->validate(['archivo_excel' => 'required|file']);
        $ext = strtolower($request->file('archivo_excel')->getClientOriginalExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'xlsm'])) {
            return response()->json(['error' => 'Formato no válido. Use .xlsx, .xls o .xlsm'], 422);
        }

        try {
            $filePath = $request->file('archivo_excel')->getPathname();
            $readerType = ($ext === 'xlsm') ? 'Xlsx' : IOFactory::identify($filePath);
            $reader = IOFactory::createReader($readerType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            // Buscar la hoja "Seguimiento" (nombre exacto o parcial, sin distinción de mayúsculas)
            $ws = null;
            foreach ($spreadsheet->getSheetNames() as $nombre) {
                if (stripos($nombre, 'seguimiento') !== false) {
                    $ws = $spreadsheet->getSheetByName($nombre);
                    break;
                }
            }
            // Si no existe "Seguimiento", usar la hoja activa
            if (! $ws) {
                $ws = $spreadsheet->getActiveSheet();
            }

            // Detectar fila de inicio: buscar la primera fila (desde 8 hacia abajo)
            // cuya columna E no esté vacía y no sea un encabezado de texto
            $filaInicio = 11; // valor por defecto conocido
            for ($r = 8; $r <= 15; $r++) {
                $val = trim((string) $ws->getCell('E' . $r)->getValue());
                // Saltar celdas vacías o que sean texto de encabezado
                if ($val && ! preg_match('/descripci[oó]n|instrumento|equipo/i', $val)) {
                    $filaInicio = $r;
                    break;
                }
            }

            if ($request->boolean('borrar_existentes')) {
                ProgramaVerificacion::truncate();
            }

            $importados = 0;
            $errores    = 0;

            foreach ($ws->getRowIterator($filaInicio) as $row) {
                $it = $row->getCellIterator('A', 'S');
                $it->setIterateOnlyExistingCells(false);

                $d = [];
                foreach ($it as $col => $cell) {
                    $d[$col] = $cell->getValue();
                }

                // Saltar filas sin descripción (columna E)
                $descripcion = trim((string) ($d['E'] ?? ''));
                if (empty($descripcion)) continue;

                $res = $this->procesarFila($d);
                $res ? $importados++ : $errores++;
            }

            $hojaUsada = $ws->getTitle();
            return response()->json([
                'success'    => true,
                'importados' => $importados,
                'errores'    => $errores,
                'mensaje'    => "Se importaron {$importados} registros desde hoja \"{$hojaUsada}\"" . ($errores ? " ({$errores} con error)" : ''),
            ]);

        } catch (\Throwable $e) {
            \Log::error('ControlInstrumentos::importar — ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }

    private function procesarFila(array $d): ?ProgramaVerificacion
    {
        try {
            // Mapeo exacto de columnas de la hoja "Seguimiento":
            // B=N°  C=PROYECTO  D=TIPO CERT  E=DESCRIPCIÓN  F=MARCA  G=MODELO
            // H=NÚMERO SERIE  I=NÚMERO INTERNO  J=CALIBRACIÓN  K=VERIFICACIÓN
            // L=N° CERT CALIBRACIÓN  M=ÚLTIMA  N=PRÓXIMA  O=FECHA INGRESO
            // P=OBSERVACIÓN  Q=DESCRIPCIÓN(otros certs)  R=REFERENCIAS  S=RESPONSABLE
            $proyecto    = trim((string) ($d['C'] ?? ''));
            $tipoCert    = strtolower(trim((string) ($d['D'] ?? '')));
            $descripcion = trim((string) ($d['E'] ?? ''));
            $marca       = trim((string) ($d['F'] ?? '')) ?: null;
            $modelo      = trim((string) ($d['G'] ?? '')) ?: null;
            $serie       = trim((string) ($d['H'] ?? '')) ?: null;
            $interno     = trim((string) ($d['I'] ?? ''));
            $calibFreq   = trim((string) ($d['J'] ?? ''));
            $verifFreq   = trim((string) ($d['K'] ?? ''));
            $numCert     = trim((string) ($d['L'] ?? '')) ?: null;
            $ultimaRaw   = $d['M'] ?? null;
            $proximaRaw  = $d['N'] ?? null;
            $observ      = trim((string) ($d['P'] ?? '')) ?: null;   // P = OBSERVACIÓN
            $responsable = trim((string) ($d['S'] ?? '')) ?: null;   // S = RESPONSABLE

            if (! $descripcion) return null;

            // Área / proyecto
            $area       = Area::where('descripcion', 'like', "%{$proyecto}%")->first();
            $idContrato = $area?->id ?? Area::first()?->id;

            // Tipos de certificado — acepta variantes de texto del Excel
            $certCalidad = Str::contains($tipoCert, ['calidad']);
            $certCalibra = Str::contains($tipoCert, ['calibración', 'calibracion', 'calibrac']);

            // Frecuencias (calibracion NOT NULL: usar 3=No aplica como fallback)
            $calibracionId  = $this->freqId($calibFreq) ?? 3;
            $verificacionId = $this->freqId($verifFreq, true);

            // Fechas (pueden ser seriales de Excel o strings)
            $ultima  = $this->parseDate($ultimaRaw);
            $proxima = $this->parseDate($proximaRaw);

            // Nº Interno: si tiene texto placeholder, guardar null
            $internoVal = is_numeric($interno) ? (int) $interno : null;

            return ProgramaVerificacion::create([
                'id_contrato'             => $idContrato,
                'descripcion'             => $descripcion,
                'marca'                   => $marca,
                'modelo'                  => $modelo,
                'serie'                   => $serie,
                'interno'                 => $internoVal,
                'cert_calidad'            => $certCalidad ? 1 : 0,
                'cert_calibracion'        => $certCalibra ? 1 : 0,
                'calibracion'             => $calibracionId,
                'verificacion'            => $verificacionId,
                'certificado_calibracion' => $numCert,
                'ultima'                  => $ultima,
                'proxima'                 => $proxima,
                'observaciones'           => $observ,
                'responsable'             => $responsable,
            ]);

        } catch (\Exception $e) {
            \Log::error('CI Import row error: ' . $e->getMessage());
            return null;
        }
    }

    private function freqId(string $val, bool $esVerif = false): ?int
    {
        $v = strtolower($val);
        if ($esVerif) {
            return Str::contains($v, 'mensual') ? 1 : null;
        }
        if (Str::contains($v, 'anual'))    return 1;
        if (Str::contains($v, 'mensual'))  return 2;
        if (Str::contains($v, ['no aplica', 'no_aplica', 'na'])) return 3;
        if (Str::contains($v, 'semestral')) return 4;
        return null;
    }

    private function parseDate($raw): ?string
    {
        if (! $raw) return null;
        try {
            if (is_numeric($raw)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw)
                )->format('Y-m-d');
            }
            foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-m-y', 'd/m/y'] as $fmt) {
                $obj = \DateTime::createFromFormat($fmt, trim((string) $raw));
                if ($obj) return $obj->format('Y-m-d');
            }
        } catch (\Exception $e) { }
        return null;
    }

    // ── Permisos ─────────────────────────────────────────────────────────────

    public function actualizarPermisos(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario || ! $usuario->esAdmin()) abort(403);

        $target = Usuario::findOrFail($request->usuario_id);
        $campo  = match ($request->tipo) {
            'ver'      => 'ver_control_instrumentos',
            'editar'   => 'editar_control_instrumentos',
            'eliminar' => 'eliminar_control_instrumentos',
            default    => null,
        };

        if ($campo) $target->update([$campo => (int) $request->estado]);

        return response()->json(['success' => true]);
    }

    // ── Eliminar ─────────────────────────────────────────────────────────────

    public function destroy(ProgramaVerificacion $programaVerificacion)
    {
        if (! $this->puedeGestionarCI()) abort(403);

        $programaVerificacion->delete();

        return response()->json(['success' => true, 'message' => 'Programa eliminado correctamente']);
    }
}
