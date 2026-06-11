<?php

namespace App\Http\Controllers;

use App\Models\AccionCorrectiva;
use App\Models\AccionInmediata;
use App\Models\Area;
use App\Models\NoConformidad;
use App\Models\NoConformidadDoc;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class NoConformidadController extends Controller
{
    private const TIPOS_ACCION  = [1 => 'Correctiva', 2 => 'Oportunidad de Mejora'];
    private const STATUS        = [0 => 'Pendiente',   1 => 'Resuelta'];
    private const EFICAZ        = [0 => '-',           1 => 'Sí', 2 => 'No'];
    private const NC_CARPETA_ID = 9;

    /**
     * Permiso único: `carga` sobre carpeta 9 es el único flag que controla
     * TODO el acceso a No Conformidades (lectura, escritura, exportar, importar).
     * Admins (perfil 1 y 2) siempre tienen acceso completo.
     */
    /** Puede ver (solo lectura o acceso completo). */
    private function tieneAccesoNC(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin()
            || PermisoService::can('ver',   'carpeta', self::NC_CARPETA_ID)
            || PermisoService::can('carga', 'carpeta', self::NC_CARPETA_ID));
    }

    /** Puede gestionar (crear, editar, eliminar, importar). */
    private function puedeGestionar(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin() || PermisoService::can('carga', 'carpeta', self::NC_CARPETA_ID));
    }

    /** Aborta 403 HTML si no puede ver. */
    private function checkVer(): void
    {
        if (! $this->tieneAccesoNC()) {
            abort(403, 'No tienes permiso para acceder al módulo de No Conformidades.');
        }
    }

    /** Aborta 403 JSON si no puede gestionar. */
    private function checkGestionar(): void
    {
        if (! $this->puedeGestionar()) {
            abort(response()->json(['error' => 'Sin permiso para gestionar No Conformidades.'], 403));
        }
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $this->checkVer();
        $usuario = PermisoService::usuarioActual();
        $esAdmin       = $this->puedeGestionar(); // puede crear/editar/eliminar/exportar
        $puedeImportar = $usuario->esAdmin();   // solo admins reales pueden importar

        $areasMap = Area::orderBy('id')->pluck('descripcion', 'id')->toArray();

        $ncs = NoConformidad::with(['accionesInmediatas', 'accionesCorrectivas', 'documentos'])
            ->orderBy('num_nc')
            ->get()
            ->map(function ($nc) use ($areasMap) {
                $nc->area_nombre         = $areasMap[$nc->id_area] ?? '—';
                $nc->tipo_nombre         = self::TIPOS_ACCION[$nc->tipo_accion] ?? '—';
                $nc->status_corr_txt     = self::STATUS[$nc->status_correccion]     ?? '—';
                $nc->status_acc_txt      = self::STATUS[$nc->status_acc_corr]       ?? '—';
                $nc->status_seguim_txt   = self::STATUS[$nc->status_seguim_acc_corr] ?? '—';
                $nc->eficaz_txt          = self::EFICAZ[$nc->accion_eficaz]         ?? '—';
                return $nc;
            });

        $areas = $areasMap;

        return view('no_conformidades.index', compact('ncs', 'areas', 'usuario', 'esAdmin', 'puedeImportar'));
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->checkGestionar();
        $usuario = PermisoService::usuarioActual();

        $request->validate([
            'fecha_deteccion' => ['required', 'date'],
            'origen'          => ['required', 'string', 'max:100'],
            'tipo_accion'     => ['required', 'integer', 'in:1,2'],
            'detectada_por'   => ['required', 'string', 'max:150'],
            'cargo'           => ['required', 'string', 'max:100'],
            'descripcion'     => ['required', 'string'],
        ], [
            'fecha_deteccion.required' => 'La fecha de detección es obligatoria.',
            'origen.required'          => 'El origen es obligatorio.',
            'detectada_por.required'   => 'El nombre de quien detectó es obligatorio.',
            'cargo.required'           => 'El cargo es obligatorio.',
            'descripcion.required'     => 'La descripción del hallazgo es obligatoria.',
        ]);

        $numNc = (NoConformidad::max('num_nc') ?? 0) + 1;

        $nc = NoConformidad::create([
            'num_nc'                  => $numNc,
            'fecha_deteccion'         => $request->fecha_deteccion,
            'origen'                  => $request->origen,
            'id_area'                 => $request->filled('id_area') ? (int) $request->id_area : null,
            'tipo_accion'             => (int) $request->tipo_accion,
            'detectada_por'           => $request->detectada_por,
            'cargo'                   => $request->cargo,
            'descripcion'             => $request->descripcion,
            'resp_inmediata_nombre'   => $request->input('resp_inmediata_nombre', ''),
            'resp_inmediata_cargo'    => $request->input('resp_inmediata_cargo', ''),
            'status_correccion'       => (int) $request->input('status_correccion', 0),
            'resp_correctiva_nombre'  => $request->input('resp_correctiva_nombre', ''),
            'resp_correctiva_cargo'   => $request->input('resp_correctiva_cargo', ''),
            'fecha_implem_acc_corr'   => $request->filled('fecha_implem_acc_corr') ? $request->fecha_implem_acc_corr : null,
            'fecha_seguim_acc_corr'   => $request->filled('fecha_seguim_acc_corr') ? $request->fecha_seguim_acc_corr : null,
            'status_acc_corr'         => (int) $request->input('status_acc_corr', 0),
            'status_seguim_acc_corr'  => (int) $request->input('status_seguim_acc_corr', 0),
            'accion_eficaz'           => (int) $request->input('accion_eficaz', 0),
            'fecha_cierre'            => $request->filled('fecha_cierre') ? $request->fecha_cierre : null,
            'registros'               => $request->input('registros') ?: null,
            'observaciones'           => $request->input('observaciones') ?: null,
            'id_usuario'              => $usuario->id,
        ]);

        $this->guardarAcciones($nc->id, $request, 1, $usuario->id);
        $this->guardarAcciones($nc->id, $request, 2, $usuario->id);

        return response()->json([
            'ok'     => true,
            'num_nc' => $nc->num_nc,
            'mensaje'=> "No Conformidad N° {$nc->num_nc} creada correctamente.",
        ]);
    }

    // ── DATOS (JSON para el modal de edición) ─────────────────────────────────

    public function datos(int $id)
    {
        $this->checkVer();
        $nc = NoConformidad::with(['accionesInmediatas', 'accionesCorrectivas', 'documentos'])->findOrFail($id);

        return response()->json([
            'nc' => [
                'id'                     => $nc->id,
                'num_nc'                 => $nc->num_nc,
                'fecha_deteccion'        => $nc->fecha_deteccion?->format('Y-m-d'),
                'origen'                 => $nc->origen,
                'id_area'                => $nc->id_area,
                'tipo_accion'            => $nc->tipo_accion,
                'detectada_por'          => $nc->detectada_por,
                'cargo'                  => $nc->cargo,
                'descripcion'            => $nc->descripcion,
                'resp_inmediata_nombre'  => $nc->resp_inmediata_nombre,
                'resp_inmediata_cargo'   => $nc->resp_inmediata_cargo,
                'status_correccion'      => $nc->status_correccion,
                'resp_correctiva_nombre' => $nc->resp_correctiva_nombre,
                'resp_correctiva_cargo'  => $nc->resp_correctiva_cargo,
                'fecha_implem_acc_corr'  => $nc->fecha_implem_acc_corr?->format('Y-m-d'),
                'fecha_seguim_acc_corr'  => $nc->fecha_seguim_acc_corr?->format('Y-m-d'),
                'status_acc_corr'        => $nc->status_acc_corr,
                'status_seguim_acc_corr' => $nc->status_seguim_acc_corr,
                'accion_eficaz'          => $nc->accion_eficaz,
                'fecha_cierre'           => $nc->fecha_cierre?->format('Y-m-d'),
                'registros'              => $nc->registros,
                'observaciones'          => $nc->observaciones,
            ],
            'acciones_inmediatas'  => $nc->accionesInmediatas->map(fn($a) => ['id' => $a->id, 'descripcion' => $a->descripcion]),
            'acciones_correctivas' => $nc->accionesCorrectivas->map(fn($a) => ['id' => $a->id, 'descripcion' => $a->descripcion]),
            'documentos'           => $nc->documentos->map(fn($d) => [
                'id'              => $d->id,
                'tipo'            => $d->tipo,
                'nombre_original' => $d->nombre_original,
                'url'             => route('nc.doc.ver', $d->id),
            ]),
        ]);
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    public function update(Request $request, int $id)
    {
        $this->checkGestionar();
        $usuario = PermisoService::usuarioActual();

        $nc = NoConformidad::findOrFail($id);

        $request->validate([
            'fecha_deteccion' => ['required', 'date'],
            'origen'          => ['required', 'string', 'max:100'],
            'tipo_accion'     => ['required', 'integer', 'in:1,2'],
            'detectada_por'   => ['required', 'string', 'max:150'],
            'cargo'           => ['required', 'string', 'max:100'],
            'descripcion'     => ['required', 'string'],
        ]);

        $nc->update([
            'fecha_deteccion'         => $request->fecha_deteccion,
            'origen'                  => $request->origen,
            'id_area'                 => $request->filled('id_area') ? (int) $request->id_area : null,
            'tipo_accion'             => (int) $request->tipo_accion,
            'detectada_por'           => $request->detectada_por,
            'cargo'                   => $request->cargo,
            'descripcion'             => $request->descripcion,
            'resp_inmediata_nombre'   => $request->input('resp_inmediata_nombre', ''),
            'resp_inmediata_cargo'    => $request->input('resp_inmediata_cargo', ''),
            'status_correccion'       => (int) $request->input('status_correccion', 0),
            'resp_correctiva_nombre'  => $request->input('resp_correctiva_nombre', ''),
            'resp_correctiva_cargo'   => $request->input('resp_correctiva_cargo', ''),
            'fecha_implem_acc_corr'   => $request->filled('fecha_implem_acc_corr') ? $request->fecha_implem_acc_corr : null,
            'fecha_seguim_acc_corr'   => $request->filled('fecha_seguim_acc_corr') ? $request->fecha_seguim_acc_corr : null,
            'status_acc_corr'         => (int) $request->input('status_acc_corr', 0),
            'status_seguim_acc_corr'  => (int) $request->input('status_seguim_acc_corr', 0),
            'accion_eficaz'           => (int) $request->input('accion_eficaz', 0),
            'fecha_cierre'            => $request->filled('fecha_cierre') ? $request->fecha_cierre : null,
            'registros'               => $request->input('registros') ?: null,
            'observaciones'           => $request->input('observaciones') ?: null,
        ]);

        // Reemplazar descripciones de acciones (docs existentes se conservan)
        AccionInmediata::where('id_nc', $id)->delete();
        AccionCorrectiva::where('id_nc', $id)->delete();

        $this->guardarAcciones($id, $request, 1, $usuario->id);
        $this->guardarAcciones($id, $request, 2, $usuario->id);

        return response()->json([
            'ok'     => true,
            'mensaje'=> "No Conformidad N° {$nc->num_nc} actualizada correctamente.",
        ]);
    }

    // ── DESTROY ───────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        $this->checkGestionar();
        $usuario = PermisoService::usuarioActual();

        $nc = NoConformidad::with('documentos')->findOrFail($id);

        foreach ($nc->documentos as $doc) {
            Storage::disk('local')->delete('nc_docs/' . $doc->archivo);
        }

        $nc->delete(); // cascade elimina acciones + docs

        return response()->json([
            'ok'     => true,
            'mensaje'=> "No Conformidad N° {$nc->num_nc} eliminada.",
        ]);
    }

    // ── DOCUMENTOS ───────────────────────────────────────────────────────────

    public function eliminarDoc(int $id)
    {
        $this->checkGestionar();

        $doc = NoConformidadDoc::findOrFail($id);
        Storage::disk('local')->delete('nc_docs/' . $doc->archivo);
        $doc->delete();

        return response()->json(['ok' => true]);
    }

    public function verDoc(int $id, Request $request)
    {
        $this->checkVer();
        $doc  = NoConformidadDoc::findOrFail($id);
        $path = Storage::disk('local')->path('nc_docs/' . $doc->archivo);

        if (! file_exists($path)) abort(404);

        // ?download=1 → fuerza descarga; sin parámetro → visualización inline
        if ($request->boolean('download')) {
            return response()->download($path, $doc->nombre_original);
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . rawurlencode($doc->nombre_original) . '"',
        ]);
    }

    // ── EXPORTAR EXCEL ───────────────────────────────────────────────────────

    public function exportar()
    {
        $this->checkVer();
        $areasMap = Area::orderBy('id')->pluck('descripcion', 'id')->toArray();

        $ncs = NoConformidad::with(['accionesInmediatas', 'accionesCorrectivas'])
            ->orderBy('num_nc')
            ->get()
            ->map(function ($nc) use ($areasMap) {
                $nc->area_nombre = $areasMap[$nc->id_area] ?? '—';
                return $nc;
            });

        // ── Colores de grupo ─────────────────────────────────────────────────
        $cId    = '1E3A6E'; // Azul — identificación
        $cInm   = '1A5C3A'; // Verde — medidas inmediatas
        $cCorr  = '7B4810'; // Naranja — acciones correctivas
        $cVerif = '4B1A7B'; // Morado — verificación
        $cSub   = 'F1F5F9'; // Fondo sub-encabezado
        $white  = 'FFFFFF';
        $black  = '000000';

        // ── Definición de columnas ───────────────────────────────────────────
        // [encabezado fila1, encabezado fila2, ancho, grupo, callback($nc)]
        $cols = [
            ['N° NC',             '',                      7,   $cId,    fn($nc) => $nc->num_nc],
            ['Fecha de Detección','',                      14,  $cId,    fn($nc) => $nc->fecha_deteccion?->format('d-m-Y') ?? ''],
            ['Origen',            '',                      22,  $cId,    fn($nc) => $nc->origen],
            ['Área',              '',                      22,  $cId,    fn($nc) => $nc->area_nombre],
            ['Tipo de Acción',    'Correctiva',            13,  $cId,    fn($nc) => $nc->tipo_accion == 1 ? 'X' : ''],
            ['',                  'Oportunidad de Mejora', 13,  $cId,    fn($nc) => $nc->tipo_accion == 2 ? 'X' : ''],
            ['Detectada Por',     'Nombre',                22,  $cId,    fn($nc) => $nc->detectada_por],
            ['',                  'Cargo',                 20,  $cId,    fn($nc) => $nc->cargo],
            ['Descripción del Hallazgo', '',               45,  $cId,    fn($nc) => $nc->descripcion],
            // ── Medidas inmediatas ────────────────────────────────────────
            ['Medidas Inmediatas','Descripción de la Acción Inmediata', 50, $cInm,
                fn($nc) => $nc->accionesInmediatas->map(fn($a,$i) => ($i+1).'.- '.$a->descripcion)->implode("\n")],
            ['',                  'Responsable — Nombre',  24,  $cInm,   fn($nc) => $nc->resp_inmediata_nombre],
            ['',                  'Responsable — Cargo',   22,  $cInm,   fn($nc) => $nc->resp_inmediata_cargo],
            ['',                  'Status Corrección',     18,  $cInm,   fn($nc) => $nc->status_correccion ? 'Resuelta' : 'Pendiente'],
            // ── Acciones correctivas ──────────────────────────────────────
            ['Acciones Correctivas','Descripción de la Acción Correctiva', 50, $cCorr,
                fn($nc) => $nc->accionesCorrectivas->map(fn($a,$i) => ($i+1).'.- '.$a->descripcion)->implode("\n")],
            ['',                  'Responsable — Nombre',  24,  $cCorr,  fn($nc) => $nc->resp_correctiva_nombre],
            ['',                  'Responsable — Cargo',   22,  $cCorr,  fn($nc) => $nc->resp_correctiva_cargo],
            ['',                  'Fecha Implementación',  18,  $cCorr,  fn($nc) => $nc->fecha_implem_acc_corr?->format('d-m-Y') ?? ''],
            ['',                  'Fecha Seguimiento',     18,  $cCorr,  fn($nc) => $nc->fecha_seguim_acc_corr?->format('d-m-Y') ?? ''],
            ['',                  'Status Acc. Correctiva',18,  $cCorr,  fn($nc) => $nc->status_acc_corr ? 'Resuelta' : 'Pendiente'],
            ['',                  'Status Seguimiento',    18,  $cCorr,  fn($nc) => $nc->status_seguim_acc_corr ? 'Resuelta' : 'Pendiente'],
            // ── Verificación ──────────────────────────────────────────────
            ['Verificación de la Eficacia','¿Acción Eficaz?', 14, $cVerif,
                fn($nc) => match((int)$nc->accion_eficaz){1=>'Sí',2=>'No',default=>'—'}],
            ['',                  'Fecha Cierre',          14,  $cVerif, fn($nc) => $nc->fecha_cierre?->format('d-m-Y') ?? ''],
            ['',                  'Registros',             22,  $cVerif, fn($nc) => $nc->registros ?? ''],
            ['',                  'Observaciones',         30,  $cVerif, fn($nc) => $nc->observaciones ?? ''],
        ];

        $totalCols = count($cols);

        // ── Crear spreadsheet ────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matriz NC');

        $thinBorder = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . 'B0BEC5']],
            ],
        ];

        // ── Fila 1: encabezados de grupo (merged por sección) ────────────────
        $groupRanges = [];   // [startCol, endCol, label, color]
        $prevGroup   = null;
        $groupStart  = 1;

        foreach ($cols as $ci => $col) {
            $colNum  = $ci + 1;
            $grpLabel = $col[0];
            $grpColor = $col[3];

            if ($grpLabel !== '' && $grpLabel !== $prevGroup) {
                if ($prevGroup !== null) {
                    $groupRanges[] = [$groupStart, $colNum - 1, $prevGroup, $prevColor];
                }
                $groupStart = $colNum;
                $prevGroup  = $grpLabel;
                $prevColor  = $grpColor;
            }
        }
        $groupRanges[] = [$groupStart, $totalCols, $prevGroup, $prevColor];

        foreach ($groupRanges as [$start, $end, $label, $color]) {
            $startCell = $this->xlCol($start) . '1';
            $endCell   = $this->xlCol($end)   . '1';
            $sheet->setCellValue($startCell, strtoupper($label));
            if ($start !== $end) {
                $sheet->mergeCells("{$startCell}:{$endCell}");
            }
            $sheet->getStyle("{$startCell}:{$endCell}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => 'FF'.$white], 'size' => 9],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.$color]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                                'wrapText'   => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                                 'color'       => ['argb' => 'FF'.'FFFFFF']]],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(22);
        }

        // ── Fila 2: sub-encabezados ──────────────────────────────────────────
        foreach ($cols as $ci => $col) {
            $colNum   = $ci + 1;
            $colLetter = $this->xlCol($colNum);
            $subLabel  = $col[1] !== '' ? $col[1] : strtoupper($col[0]);
            $grpColor  = $col[3];

            $sheet->setCellValue("{$colLetter}2", $subLabel);

            // Sub-encabezado con color más claro derivado del grupo
            $subFill = match($grpColor) {
                $cInm   => 'D4EDDA',
                $cCorr  => 'FFF3CD',
                $cVerif => 'E8D5FF',
                default => 'DBEAFE',
            };
            $subText = match($grpColor) {
                $cInm   => '155724',
                $cCorr  => '856404',
                $cVerif => '4B1A7B',
                default => '1E3A6E',
            };

            $sheet->getStyle("{$colLetter}2")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => 'FF'.$subText], 'size' => 8],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.$subFill]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                                'wrapText'   => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                                 'color'       => ['argb' => 'FFB0BEC5']]],
            ]);
            $sheet->getColumnDimension($colLetter)->setWidth($col[2]);
        }
        $sheet->getRowDimension(2)->setRowHeight(32);

        // ── Filas de datos ───────────────────────────────────────────────────
        foreach ($ncs as $ri => $nc) {
            $row       = $ri + 3;
            $rowBg     = $ri % 2 === 0 ? 'FFFFFF' : 'F8FAFC';
            $maxLines  = 1;

            foreach ($cols as $ci => $col) {
                $colLetter = $this->xlCol($ci + 1);
                $value     = ($col[4])($nc);
                $grpColor  = $col[3];

                // Fondo de celda por grupo (alternado)
                $cellBg = match($grpColor) {
                    $cInm   => $ri % 2 === 0 ? 'F0FDF4' : 'E8F5E9',
                    $cCorr  => $ri % 2 === 0 ? 'FFFDF0' : 'FFF8E1',
                    $cVerif => $ri % 2 === 0 ? 'FAF5FF' : 'F3E8FF',
                    default => $rowBg,
                };

                $sheet->setCellValue("{$colLetter}{$row}", $value);
                $sheet->getStyle("{$colLetter}{$row}")->applyFromArray([
                    'font'      => ['size' => 8, 'color' => ['argb' => 'FF1F2937']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.$cellBg]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true,
                                    'horizontal' => in_array($ci, [0,4,5,11,12])
                                        ? Alignment::HORIZONTAL_CENTER
                                        : Alignment::HORIZONTAL_LEFT],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                                     'color'       => ['argb' => 'FFE5E7EB']]],
                ]);

                // Calcular altura estimada por saltos de línea
                $lines = substr_count((string)$value, "\n") + 1;
                if ($lines > $maxLines) $maxLines = $lines;
            }

            // Altura de fila adaptativa (mínimo 18px, ~14px por línea)
            $sheet->getRowDimension($row)->setRowHeight(max(18, $maxLines * 14 + 4));
        }

        // Freeze panes en fila 3 para que los encabezados queden fijos
        $sheet->freezePane('A3');

        // ── Descargar ────────────────────────────────────────────────────────
        $filename = 'Matriz_NC_' . now()->format('Y-m-d') . '.xlsx';
        $writer   = new XlsxWriter($spreadsheet);

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $filename,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control'       => 'max-age=0',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    /** Convierte número de columna (1-based) a letra(s) Excel: 1→A, 27→AA */
    private function xlCol(int $n): string
    {
        $col = '';
        while ($n > 0) {
            $n--;
            $col = chr(65 + ($n % 26)) . $col;
            $n   = intdiv($n, 26);
        }
        return $col;
    }

    // ── IMPORTAR EXCEL ────────────────────────────────────────────────────────

    public function importar(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden importar No Conformidades.'], 403);
        }

        $request->validate([
            'archivo_excel' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'archivo_excel.required' => 'Selecciona un archivo Excel.',
            'archivo_excel.mimes'    => 'El archivo debe ser .xlsx o .xls.',
        ]);

        try {
            $file        = $request->file('archivo_excel');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();

            // Mapa de áreas en BD: nombre (lowercase) → id
            $areasMap = Area::all()->mapWithKeys(fn($a) => [
                mb_strtolower(trim($a->descripcion)) => $a->id,
            ])->toArray();

            // NCs ya existentes para detectar duplicados
            $existentes = NoConformidad::pluck('num_nc')->flip()->toArray();

            $importadas = [];
            $omitidas   = [];   // ya existían
            $errores    = [];

            // ── Recorrer filas desde la 9 en adelante ────────────────────────
            $maxRow   = $sheet->getHighestRow();
            $ncActual = null; // acumula datos del bloque actual

            for ($row = 9; $row <= $maxRow + 1; $row++) {
                $numNcCell = trim((string) $sheet->getCell("B{$row}")->getCalculatedValue());

                // Detectar inicio de nuevo NC o fin del archivo
                $esNuevoNC = is_numeric($numNcCell) && (int)$numNcCell > 0;
                $esFin     = $row > $maxRow;

                if (($esNuevoNC || $esFin) && $ncActual !== null) {
                    // Procesar el bloque acumulado
                    $num = (int) $ncActual['num_nc'];

                    if (isset($existentes[$num])) {
                        $omitidas[] = $num;
                    } else {
                        try {
                            $this->crearNcDesdeBloque($ncActual, $areasMap, $usuario->id);
                            $importadas[] = $num;
                            $existentes[$num] = true;
                        } catch (\Throwable $e) {
                            $errores[] = "NC {$num}: " . $e->getMessage();
                        }
                    }
                    $ncActual = null;
                }

                if ($esFin) break;

                if ($esNuevoNC) {
                    // Leer todas las columnas del bloque (master cell)
                    $ncActual = [
                        'num_nc'                 => (int) $numNcCell,
                        'fecha_deteccion'        => $this->xlFecha($sheet->getCell("C{$row}")->getValue()),
                        'origen'                 => trim((string) $sheet->getCell("D{$row}")->getCalculatedValue()),
                        'area_nombre'            => trim((string) $sheet->getCell("E{$row}")->getCalculatedValue()),
                        'col_f'                  => trim((string) $sheet->getCell("F{$row}")->getCalculatedValue()),
                        'col_g'                  => trim((string) $sheet->getCell("G{$row}")->getCalculatedValue()),
                        'detectada_por'          => trim((string) $sheet->getCell("H{$row}")->getCalculatedValue()),
                        'cargo'                  => trim((string) $sheet->getCell("I{$row}")->getCalculatedValue()),
                        'descripcion'            => trim((string) $sheet->getCell("J{$row}")->getCalculatedValue()),
                        'acc_inmediata_texto'    => trim((string) $sheet->getCell("K{$row}")->getCalculatedValue()),
                        'resp_inmediata_nombre'  => trim((string) $sheet->getCell("L{$row}")->getCalculatedValue()),
                        'resp_inmediata_cargo'   => trim((string) $sheet->getCell("M{$row}")->getCalculatedValue()),
                        'status_correccion_txt'  => trim((string) $sheet->getCell("N{$row}")->getCalculatedValue()),
                        'acc_correctiva_texto'   => trim((string) $sheet->getCell("O{$row}")->getCalculatedValue()),
                        'resp_correctiva_nombre' => trim((string) $sheet->getCell("P{$row}")->getCalculatedValue()),
                        'resp_correctiva_cargo'  => trim((string) $sheet->getCell("Q{$row}")->getCalculatedValue()),
                        'fecha_implem'           => $this->xlFecha($sheet->getCell("R{$row}")->getValue()),
                        'fecha_seguim'           => $this->xlFecha($sheet->getCell("S{$row}")->getValue()),
                        'status_acc_corr_txt'    => trim((string) $sheet->getCell("T{$row}")->getCalculatedValue()),
                        'status_seguim_txt'      => trim((string) $sheet->getCell("U{$row}")->getCalculatedValue()),
                        'col_v'                  => trim((string) $sheet->getCell("V{$row}")->getCalculatedValue()),
                        'col_w'                  => trim((string) $sheet->getCell("W{$row}")->getCalculatedValue()),
                        'fecha_cierre'           => $this->xlFecha($sheet->getCell("X{$row}")->getValue()),
                        'registros'              => trim((string) $sheet->getCell("Y{$row}")->getCalculatedValue()),
                        'observaciones'          => trim((string) $sheet->getCell("Z{$row}")->getCalculatedValue()),
                    ];
                }
                // (filas intermedias del bloque fusionado se ignoran — las celdas mergeadas
                //  devuelven null en PhpSpreadsheet para celdas no-master)
            }

            return response()->json([
                'ok'        => true,
                'importadas'=> $importadas,
                'omitidas'  => $omitidas,
                'errores'   => $errores,
                'mensaje'   => sprintf(
                    '%d NC%s importada%s correctamente%s%s.',
                    count($importadas),
                    count($importadas) !== 1 ? 's' : '',
                    count($importadas) !== 1 ? 's' : '',
                    count($omitidas) ? ', ' . count($omitidas) . ' omitida(s) por ya existir' : '',
                    count($errores)  ? ', ' . count($errores)  . ' con error' : ''
                ),
            ]);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Error al leer el archivo: ' . $e->getMessage()], 422);
        }
    }

    // ── HELPERS ──────────────────────────────────────────────────────────────

    /**
     * Crea un NoConformidad (+ acciones) a partir del bloque leído del Excel.
     */
    private function crearNcDesdeBloque(array $b, array $areasMap, int $idUsuario): void
    {
        // Tipo de acción: F=X → 1 (Correctiva), G=X → 2 (Oportunidad de Mejora)
        $tipoAccion = strtoupper($b['col_g']) === 'X' ? 2 : 1;

        // Área: buscar por nombre exacto (case-insensitive) o parcial
        $idArea = $this->resolverArea($b['area_nombre'], $areasMap);

        // Status: "Resuelta" → 1, cualquier otra → 0
        $statusCorr   = strtolower(trim($b['status_correccion_txt'])) === 'resuelta' ? 1 : 0;
        $statusAcc    = strtolower(trim($b['status_acc_corr_txt']))   === 'resuelta' ? 1 : 0;
        $statusSeguim = strtolower(trim($b['status_seguim_txt']))     === 'resuelta' ? 1 : 0;

        // Eficacia: V=SÍ → 1, W=NO → 2, ninguno → 0
        if (strtoupper($b['col_v']) === 'X') {
            $eficaz = 1;
        } elseif (strtoupper($b['col_w']) === 'X') {
            $eficaz = 2;
        } else {
            $eficaz = 0;
        }

        // Limpiar N/A
        $limpiar = fn($v) => ($v === '' || strtoupper($v) === 'N/A' || $v === '-') ? null : $v;

        $nc = NoConformidad::create([
            'num_nc'                 => $b['num_nc'],
            'fecha_deteccion'        => $b['fecha_deteccion'],
            'origen'                 => $b['origen'] ?: 'Sin origen',
            'id_area'                => $idArea,
            'tipo_accion'            => $tipoAccion,
            'detectada_por'          => $b['detectada_por'] ?: '—',
            'cargo'                  => $b['cargo'] ?: '—',
            'descripcion'            => $b['descripcion'] ?: '(sin descripción)',
            'resp_inmediata_nombre'  => $b['resp_inmediata_nombre'],
            'resp_inmediata_cargo'   => $b['resp_inmediata_cargo'],
            'status_correccion'      => $statusCorr,
            'resp_correctiva_nombre' => $b['resp_correctiva_nombre'],
            'resp_correctiva_cargo'  => $b['resp_correctiva_cargo'],
            'fecha_implem_acc_corr'  => $b['fecha_implem'],
            'fecha_seguim_acc_corr'  => $b['fecha_seguim'],
            'status_acc_corr'        => $statusAcc,
            'status_seguim_acc_corr' => $statusSeguim,
            'accion_eficaz'          => $eficaz,
            'fecha_cierre'           => $b['fecha_cierre'],
            'registros'              => $limpiar($b['registros']),
            'observaciones'          => $limpiar($b['observaciones']),
            'id_usuario'             => $idUsuario,
        ]);

        // Acciones inmediatas
        foreach ($this->parsearAcciones($b['acc_inmediata_texto']) as $desc) {
            AccionInmediata::create(['id_nc' => $nc->id, 'descripcion' => $desc]);
        }

        // Acciones correctivas
        foreach ($this->parsearAcciones($b['acc_correctiva_texto']) as $desc) {
            AccionCorrectiva::create(['id_nc' => $nc->id, 'descripcion' => $desc]);
        }
    }

    /**
     * Convierte un valor de celda Excel al formato Y-m-d.
     * Acepta seriales numéricos o cadenas de fecha.
     */
    private function xlFecha(mixed $val): ?string
    {
        if ($val === null || $val === '' || $val === 0) return null;
        try {
            if (is_numeric($val)) {
                return XlDate::excelToDateTimeObject((float) $val)->format('Y-m-d');
            }
            // Intentar parsear cadena de texto
            return \Carbon\Carbon::parse((string) $val)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Divide el texto de acciones (lista numerada o saltos de línea) en un array.
     */
    private function parsearAcciones(string $texto): array
    {
        $texto = trim($texto);
        if ($texto === '' || strtoupper($texto) === 'N/A') return [];

        // Dividir por patrón de lista numerada: "1.- ", "2.- ", "1. ", etc.
        $partes = preg_split('/(?:^|\n)\s*\d+[\.\-]+\s*/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
        if (count($partes) > 1) {
            // Eliminar sub-etiquetas de evidencia al final ("Evidencia X: ...")
            return array_values(array_filter(array_map(function ($p) {
                $p = preg_replace('/\n?\s*Evidencia\s*\d*\s*:.*$/uis', '', $p);
                return trim($p);
            }, $partes)));
        }

        // Fallback: dividir por saltos de línea
        $lineas = array_filter(array_map('trim', explode("\n", $texto)));
        return array_values($lineas) ?: [$texto];
    }

    /**
     * Resuelve el id de área a partir de un nombre (exacto o parcial).
     */
    private function resolverArea(string $nombre, array $areasMap): ?int
    {
        if ($nombre === '') return null;
        $key = mb_strtolower(trim($nombre));

        // Coincidencia exacta
        if (isset($areasMap[$key])) return $areasMap[$key];

        // Coincidencia parcial: buscar si el nombre del Excel contiene el nombre del área
        foreach ($areasMap as $areaKey => $id) {
            if (str_contains($key, $areaKey) || str_contains($areaKey, $key)) {
                return $id;
            }
        }

        return null; // No encontrada → null
    }

    /**
     * Guarda las acciones y archivos de evidencia para un tipo dado.
     * $tipo: 1 = inmediata, 2 = correctiva
     */
    private function guardarAcciones(int $idNc, Request $request, int $tipo, int $idUsuario): void
    {
        $keyDesc = $tipo === 1 ? 'acciones_inmediatas' : 'acciones_correctivas';
        $keyFile = $tipo === 1 ? 'docs_inmediatas'     : 'docs_correctivas';

        $descripciones = $request->input($keyDesc, []);
        $archivos      = $request->file($keyFile, []);

        foreach ($descripciones as $i => $desc) {
            $desc = trim($desc ?? '');
            if ($desc === '') continue;

            if ($tipo === 1) {
                AccionInmediata::create(['id_nc' => $idNc, 'descripcion' => $desc]);
            } else {
                AccionCorrectiva::create(['id_nc' => $idNc, 'descripcion' => $desc]);
            }
        }

        // Guardar archivos de evidencia (no ligados a una acción específica)
        foreach ((array) $archivos as $file) {
            if (! $file || ! $file->isValid()) continue;
            $uuid = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension() ?: 'pdf');
            Storage::disk('local')->putFileAs('nc_docs', $file, $uuid);
            NoConformidadDoc::create([
                'id_nc'           => $idNc,
                'archivo'         => $uuid,
                'nombre_original' => $file->getClientOriginalName(),
                'tipo'            => $tipo,
                'id_usuario'      => $idUsuario,
            ]);
        }
    }
}
