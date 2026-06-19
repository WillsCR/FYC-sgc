<?php

namespace App\Http\Controllers;

use App\Models\MatrizTrabajador;
use App\Services\PermisoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MatrizCompetenciasController extends Controller
{
    private const CARPETA_ID = 105;

    private function tieneAcceso(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin()
            || PermisoService::can('ver',   'carpeta', self::CARPETA_ID)
            || PermisoService::can('carga', 'carpeta', self::CARPETA_ID));
    }

    private function puedeGestionar(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin() || PermisoService::can('carga', 'carpeta', self::CARPETA_ID));
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        if (! $this->tieneAcceso()) abort(403);

        $query = MatrizTrabajador::query();

        if ($s = $request->input('nombres'))    $query->where('nombres',   'like', "%{$s}%");
        if ($s = $request->input('apellidos'))  $query->where('apellidos', 'like', "%{$s}%");
        if ($s = $request->input('rut'))        $query->where('rut',       'like', "%{$s}%");
        if ($s = $request->input('cargo'))      $query->where('cargo',     'like', "%{$s}%");
        if ($s = $request->input('contrato'))   $query->where('contrato',  'like', "%{$s}%");
        if ($request->filled('cumple'))         $query->where('cumple', (bool) $request->input('cumple'));

        $trabajadores   = $query->orderBy('apellidos')->orderBy('nombres')->get();
        $puedeGestionar = $this->puedeGestionar();
        $usuario        = PermisoService::usuarioActual();

        return view('matriz-competencias.index', compact('trabajadores', 'puedeGestionar', 'usuario'));
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        if (! $this->puedeGestionar()) return response()->json(['error' => 'Sin permiso'], 403);

        $request->validate([
            'nombres'   => 'required|string|max:200',
            'apellidos' => 'required|string|max:200',
        ]);

        $t = MatrizTrabajador::create($this->campos($request));

        $this->procesarArchivos($request, $t);

        return response()->json(['success' => true, 'id' => $t->id, 'message' => 'Trabajador creado']);
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    public function update(Request $request, MatrizTrabajador $trabajador)
    {
        if (! $this->puedeGestionar()) return response()->json(['error' => 'Sin permiso'], 403);

        $request->validate([
            'nombres'   => 'required|string|max:200',
            'apellidos' => 'required|string|max:200',
        ]);

        $trabajador->update($this->campos($request));
        $this->procesarArchivos($request, $trabajador);

        return response()->json(['success' => true, 'message' => 'Trabajador actualizado']);
    }

    // ── DESTROY ───────────────────────────────────────────────────────────────

    public function destroy(MatrizTrabajador $trabajador)
    {
        if (! $this->puedeGestionar()) return response()->json(['error' => 'Sin permiso'], 403);

        foreach (['cert_titulo_ruta', 'cv_ruta'] as $campo) {
            if ($trabajador->$campo && Storage::disk('local')->exists($trabajador->$campo)) {
                Storage::disk('local')->delete($trabajador->$campo);
            }
        }

        $trabajador->delete();
        return response()->json(['success' => true, 'message' => 'Trabajador eliminado']);
    }

    // ── DATOS (JSON para modal) ───────────────────────────────────────────────

    public function datos(MatrizTrabajador $trabajador)
    {
        return response()->json([
            'id'                 => $trabajador->id,
            'nombres'            => $trabajador->nombres,
            'apellidos'          => $trabajador->apellidos,
            'rut'                => $trabajador->rut,
            'cargo'              => $trabajador->cargo,
            'contrato'           => $trabajador->contrato,
            'nivel_estudios'     => $trabajador->nivel_estudios,
            'especialidad'       => $trabajador->especialidad,
            'cumple'             => $trabajador->cumple,
            'cert_titulo_nombre' => $trabajador->cert_titulo_nombre,
            'cv_nombre'          => $trabajador->cv_nombre,
        ]);
    }

    // ── DESCARGAR ARCHIVO ─────────────────────────────────────────────────────

    public function descargar(MatrizTrabajador $trabajador, string $tipo)
    {
        if (! $this->tieneAcceso()) abort(403);

        $ruta   = $tipo === 'cert' ? $trabajador->cert_titulo_ruta : $trabajador->cv_ruta;
        $nombre = $tipo === 'cert' ? $trabajador->cert_titulo_nombre : $trabajador->cv_nombre;

        if (! $ruta || ! Storage::disk('local')->exists($ruta)) abort(404);

        return Storage::disk('local')->download($ruta, $nombre);
    }

    public function destroyArchivo(MatrizTrabajador $trabajador, string $tipo)
    {
        if (! $this->puedeGestionar()) return response()->json(['error' => 'Sin permiso'], 403);

        if ($tipo === 'cert') {
            if ($trabajador->cert_titulo_ruta && Storage::disk('local')->exists($trabajador->cert_titulo_ruta)) {
                Storage::disk('local')->delete($trabajador->cert_titulo_ruta);
            }
            $trabajador->update(['cert_titulo_nombre' => null, 'cert_titulo_ruta' => null, 'cert_titulo_mime' => null]);
        } elseif ($tipo === 'cv') {
            if ($trabajador->cv_ruta && Storage::disk('local')->exists($trabajador->cv_ruta)) {
                Storage::disk('local')->delete($trabajador->cv_ruta);
            }
            $trabajador->update(['cv_nombre' => null, 'cv_ruta' => null, 'cv_mime' => null]);
        }

        return response()->json(['success' => true, 'message' => 'Documento eliminado']);
    }

    public function ver(MatrizTrabajador $trabajador, string $tipo)
    {
        if (! $this->tieneAcceso()) abort(403);

        $ruta   = $tipo === 'cert' ? $trabajador->cert_titulo_ruta : $trabajador->cv_ruta;
        $mime   = $tipo === 'cert' ? $trabajador->cert_titulo_mime : $trabajador->cv_mime;
        $nombre = $tipo === 'cert' ? $trabajador->cert_titulo_nombre : $trabajador->cv_nombre;

        if (! $ruta || ! Storage::disk('local')->exists($ruta)) abort(404);

        return response(Storage::disk('local')->get($ruta), 200, [
            'Content-Type'        => $mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $nombre . '"',
        ]);
    }

    // ── EXPORTAR ──────────────────────────────────────────────────────────────

    public function exportar(Request $request)
    {
        if (! $this->tieneAcceso()) abort(403);

        $query = MatrizTrabajador::query();
        if ($s = $request->input('nombres'))    $query->where('nombres',   'like', "%{$s}%");
        if ($s = $request->input('apellidos'))  $query->where('apellidos', 'like', "%{$s}%");
        if ($s = $request->input('rut'))        $query->where('rut',       'like', "%{$s}%");
        if ($s = $request->input('cargo'))      $query->where('cargo',     'like', "%{$s}%");
        if ($s = $request->input('contrato'))   $query->where('contrato',  'like', "%{$s}%");
        if ($request->filled('cumple'))         $query->where('cumple', (bool) $request->input('cumple'));

        $trabajadores = $query->orderBy('apellidos')->orderBy('nombres')->get();

        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('Matriz Competencias');

        $headers = ['N°','NOMBRES','APELLIDOS','RUT','CARGO','CONTRATO',
                    'NIVEL DE ESTUDIOS','ESPECIALIDAD','CERT. TÍTULO','CURRICULUM VITAE','CUMPLE'];

        foreach ($headers as $col => $h) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $ws->setCellValue($cell, $h);
        }

        $ws->getStyle('A1:K1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B3A6B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['rgb' => 'FFFFFF']]],
        ]);
        $ws->getRowDimension(1)->setRowHeight(20);

        $row = 2;
        foreach ($trabajadores as $i => $t) {
            $ws->setCellValue("A{$row}", $i + 1);
            $ws->setCellValue("B{$row}", $t->nombres);
            $ws->setCellValue("C{$row}", $t->apellidos);
            $ws->setCellValue("D{$row}", $t->rut);
            $ws->setCellValue("E{$row}", $t->cargo);
            $ws->setCellValue("F{$row}", $t->contrato);
            $ws->setCellValue("G{$row}", $t->nivel_estudios);
            $ws->setCellValue("H{$row}", $t->especialidad);
            $ws->setCellValue("I{$row}", $t->cert_titulo_nombre ? 'Sí' : 'No');
            $ws->setCellValue("J{$row}", $t->cv_nombre ? 'Sí' : 'No');
            $ws->setCellValue("K{$row}", $t->cumple ? 'SÍ' : 'NO');

            $color = $t->cumple ? 'DCFCE7' : 'FEE2E2';
            $ws->getStyle("K{$row}")->getFill()
               ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);

            $row++;
        }

        foreach (['A'=>5,'B'=>20,'C'=>20,'D'=>14,'E'=>24,'F'=>24,
                  'G'=>22,'H'=>22,'I'=>14,'J'=>14,'K'=>10] as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }

        if ($row > 2) {
            $ws->getStyle("A2:K" . ($row - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                               'color'       => ['rgb' => 'D1D5DB']]],
            ]);
        }

        $filename = 'matriz_competencias_' . now()->format('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ── IMPORTAR ──────────────────────────────────────────────────────────────

    public function importar(Request $request)
    {
        if (! $this->puedeGestionar()) return response()->json(['error' => 'Sin permiso'], 403);

        $request->validate(['archivo_excel' => 'required|file']);
        $ext = strtolower($request->file('archivo_excel')->getClientOriginalExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'xlsm', 'xlsb'])) {
            return response()->json(['error' => 'Formato no válido. Use .xlsx, .xls o .xlsm'], 422);
        }

        try {
            $filePath   = $request->file('archivo_excel')->getPathname();
            $readerType = in_array($ext, ['xlsm', 'xlsb']) ? 'Xlsx' : IOFactory::identify($filePath);
            $reader     = IOFactory::createReader($readerType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $importados = 0;
            $omitidos   = 0;

            foreach ($spreadsheet->getAllSheets() as $ws) {
                // Detectar fila encabezado: buscar "NOMBRES" en cols B-E
                $filaInicio = null;
                for ($r = 1; $r <= 15; $r++) {
                    for ($c = 2; $c <= 5; $c++) {
                        $val = strtolower(trim((string) $ws->getCellByColumnAndRow($c, $r)->getValue()));
                        if (str_contains($val, 'nombre')) { $filaInicio = $r + 1; break 2; }
                    }
                }
                if (! $filaInicio) continue;

                foreach ($ws->getRowIterator($filaInicio) as $row) {
                    $ri = $row->getRowIndex();

                    $nombres    = trim((string) $ws->getCell('C' . $ri)->getFormattedValue());
                    $apellidos  = trim((string) $ws->getCell('D' . $ri)->getFormattedValue());
                    $rut        = trim((string) $ws->getCell('E' . $ri)->getFormattedValue());
                    $cargo      = trim((string) $ws->getCell('F' . $ri)->getFormattedValue()) ?: null;
                    $contrato   = trim((string) $ws->getCell('G' . $ri)->getFormattedValue()) ?: null;
                    $nivel      = trim((string) $ws->getCell('H' . $ri)->getFormattedValue()) ?: null;
                    $especialidad = trim((string) $ws->getCell('I' . $ri)->getFormattedValue()) ?: null;
                    $cumpleRaw  = strtolower(trim((string) $ws->getCell('L' . $ri)->getValue()));
                    $cumple     = in_array($cumpleRaw, ['si', 'sí', '1', 'x', 'yes', 'true']);

                    if (! $nombres || ! $apellidos) continue;

                    // Evitar duplicado por RUT o nombres+apellidos
                    $existe = $rut
                        ? MatrizTrabajador::where('rut', $rut)->exists()
                        : MatrizTrabajador::where('nombres', $nombres)->where('apellidos', $apellidos)->exists();

                    if ($existe) { $omitidos++; continue; }

                    MatrizTrabajador::create([
                        'nombres'       => $nombres,
                        'apellidos'     => $apellidos,
                        'rut'           => $rut ?: null,
                        'cargo'         => $cargo,
                        'contrato'      => $contrato,
                        'nivel_estudios'=> $nivel,
                        'especialidad'  => $especialidad,
                        'cumple'        => $cumple,
                    ]);
                    $importados++;
                }
            }

            return response()->json([
                'success'    => true,
                'importados' => $importados,
                'omitidos'   => $omitidos,
                'mensaje'    => "Importados {$importados} trabajadores" . ($omitidos ? " ({$omitidos} omitidos por duplicado)" : '') . '.',
            ]);

        } catch (\Throwable $e) {
            \Log::error('MatrizCompetencias::importar — ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function campos(Request $request): array
    {
        return [
            'nombres'         => $request->nombres,
            'apellidos'       => $request->apellidos,
            'rut'             => $request->rut        ?: null,
            'cargo'           => $request->cargo      ?: null,
            'contrato'        => $request->contrato   ?: null,
            'nivel_estudios'  => $request->nivel_estudios  ?: null,
            'especialidad'    => $request->especialidad    ?: null,
            'cumple'          => (bool) $request->cumple,
        ];
    }

    private function procesarArchivos(Request $request, MatrizTrabajador $t): void
    {
        if ($request->hasFile('cert_titulo')) {
            if ($t->cert_titulo_ruta && Storage::disk('local')->exists($t->cert_titulo_ruta)) {
                Storage::disk('local')->delete($t->cert_titulo_ruta);
            }
            $f = $request->file('cert_titulo');
            $path = Storage::disk('local')->putFileAs(
                "matriz_competencias/{$t->id}",
                $f,
                "cert_{$t->id}_" . time() . '.' . $f->getClientOriginalExtension()
            );
            $t->update(['cert_titulo_nombre' => $f->getClientOriginalName(),
                        'cert_titulo_ruta'   => $path,
                        'cert_titulo_mime'   => $f->getMimeType()]);
        }

        if ($request->hasFile('cv')) {
            if ($t->cv_ruta && Storage::disk('local')->exists($t->cv_ruta)) {
                Storage::disk('local')->delete($t->cv_ruta);
            }
            $f = $request->file('cv');
            $path = Storage::disk('local')->putFileAs(
                "matriz_competencias/{$t->id}",
                $f,
                "cv_{$t->id}_" . time() . '.' . $f->getClientOriginalExtension()
            );
            $t->update(['cv_nombre' => $f->getClientOriginalName(),
                        'cv_ruta'   => $path,
                        'cv_mime'   => $f->getMimeType()]);
        }
    }
}
