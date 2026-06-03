<?php

namespace App\Http\Controllers;

use App\Models\MatrizTrabajador;
use App\Models\MatrizCurso;
use App\Services\PermisoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MatrizCursosController extends Controller
{
    private const MC_CARPETA_ID = 99; // sgc_carpetas3 id para Matriz Cursos

    private function tieneAcceso(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin() || PermisoService::can('carga', 'carpeta', self::MC_CARPETA_ID));
    }

    private function userId(): int
    {
        return PermisoService::usuarioActual()?->id ?? (int) session('usuario_id', 1);
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        if (! $this->tieneAcceso()) abort(403);

        $usuario        = PermisoService::usuarioActual();
        $puedeGestionar = $this->tieneAcceso();

        $query = MatrizTrabajador::with('cursos');

        // Filtros
        if ($s = $request->input('nombres'))   $query->where('nombres',   'like', "%{$s}%");
        if ($s = $request->input('apellidos')) $query->where('apellidos', 'like', "%{$s}%");
        if ($s = $request->input('rut'))       $query->where('rut',       'like', "%{$s}%");
        if ($s = $request->input('cargo'))     $query->where('cargo',     'like', "%{$s}%");
        if ($s = $request->input('contrato'))  $query->where('contrato',  'like', "%{$s}%");
        if ($s = $request->input('trabajador_id')) $query->where('id', $s);

        $trabajadores = $query->orderBy('apellidos')->orderBy('nombres')->get();
        $todosLosTrabajadores = MatrizTrabajador::orderBy('apellidos')->orderBy('nombres')->get();

        return view('matriz-cursos.index', compact(
            'trabajadores', 'todosLosTrabajadores', 'usuario', 'puedeGestionar'
        ));
    }

    // ── TRABAJADORES ──────────────────────────────────────────────────────────

    public function storeTrabajador(Request $request)
    {
        if (! $this->tieneAcceso()) abort(response()->json(['error' => 'Sin permiso'], 403));

        $request->validate([
            'nombres'   => 'required|string|max:200',
            'apellidos' => 'required|string|max:200',
        ]);

        $t = MatrizTrabajador::create([
            'nombres'   => $request->nombres,
            'apellidos' => $request->apellidos,
            'rut'       => $request->rut       ?: null,
            'cargo'     => $request->cargo     ?: null,
            'contrato'  => $request->contrato  ?: null,
        ]);

        return response()->json(['success' => true, 'id' => $t->id, 'message' => 'Trabajador creado']);
    }

    public function updateTrabajador(Request $request, MatrizTrabajador $trabajador)
    {
        if (! $this->tieneAcceso()) abort(response()->json(['error' => 'Sin permiso'], 403));

        $request->validate([
            'nombres'   => 'required|string|max:200',
            'apellidos' => 'required|string|max:200',
        ]);

        $trabajador->update([
            'nombres'   => $request->nombres,
            'apellidos' => $request->apellidos,
            'rut'       => $request->rut      ?: null,
            'cargo'     => $request->cargo    ?: null,
            'contrato'  => $request->contrato ?: null,
        ]);

        return response()->json(['success' => true, 'message' => 'Trabajador actualizado']);
    }

    public function destroyTrabajador(MatrizTrabajador $trabajador)
    {
        if (! $this->tieneAcceso()) abort(response()->json(['error' => 'Sin permiso'], 403));

        // Eliminar archivos de cursos
        foreach ($trabajador->cursos as $curso) {
            if ($curso->archivo_ruta && Storage::disk('local')->exists($curso->archivo_ruta)) {
                Storage::disk('local')->delete($curso->archivo_ruta);
            }
        }
        $trabajador->delete();

        return response()->json(['success' => true, 'message' => 'Trabajador eliminado']);
    }

    public function datosTrabajador(MatrizTrabajador $trabajador)
    {
        $trabajador->load('cursos');
        $cursos = $trabajador->cursos->map(fn($c) => [
            'id'                   => $c->id,
            'curso'                => $c->curso,
            'entidad_acreditadora' => $c->entidad_acreditadora,
            'fecha_realizacion'    => $c->fecha_realizacion?->format('Y-m-d'),
            'fecha_vencimiento'    => $c->fecha_vencimiento?->format('Y-m-d'),
            'correo_aviso'         => $c->correo_aviso,
            'archivo_nombre'       => $c->archivo_nombre,
            'semaforo'             => $c->semaforo,
        ]);

        return response()->json([
            'id'        => $trabajador->id,
            'nombres'   => $trabajador->nombres,
            'apellidos' => $trabajador->apellidos,
            'rut'       => $trabajador->rut,
            'cargo'     => $trabajador->cargo,
            'contrato'  => $trabajador->contrato,
            'cursos'    => $cursos,
        ]);
    }

    // ── CURSOS ────────────────────────────────────────────────────────────────

    public function storeCurso(Request $request, MatrizTrabajador $trabajador)
    {
        if (! $this->tieneAcceso()) abort(response()->json(['error' => 'Sin permiso'], 403));

        $request->validate(['curso' => 'required|string|max:300']);

        $curso = MatrizCurso::create([
            'id_trabajador'        => $trabajador->id,
            'curso'                => $request->curso,
            'entidad_acreditadora' => $request->entidad_acreditadora ?: null,
            'fecha_realizacion'    => $request->fecha_realizacion    ?: null,
            'fecha_vencimiento'    => $request->fecha_vencimiento    ?: null,
            'correo_aviso'         => $request->correo_aviso         ?: null,
        ]);

        if ($request->hasFile('archivo')) {
            $this->subirArchivo($request->file('archivo'), $curso);
        }

        return response()->json(['success' => true, 'message' => 'Curso agregado', 'id' => $curso->id]);
    }

    public function updateCurso(Request $request, MatrizCurso $curso)
    {
        if (! $this->tieneAcceso()) abort(response()->json(['error' => 'Sin permiso'], 403));

        $request->validate(['curso' => 'required|string|max:300']);

        $curso->update([
            'curso'                => $request->curso,
            'entidad_acreditadora' => $request->entidad_acreditadora ?: null,
            'fecha_realizacion'    => $request->fecha_realizacion    ?: null,
            'fecha_vencimiento'    => $request->fecha_vencimiento    ?: null,
            'correo_aviso'         => $request->correo_aviso         ?: null,
        ]);

        if ($request->hasFile('archivo')) {
            // Eliminar archivo anterior
            if ($curso->archivo_ruta && Storage::disk('local')->exists($curso->archivo_ruta)) {
                Storage::disk('local')->delete($curso->archivo_ruta);
            }
            $this->subirArchivo($request->file('archivo'), $curso);
        }

        return response()->json(['success' => true, 'message' => 'Curso actualizado']);
    }

    public function destroyCurso(MatrizCurso $curso)
    {
        if (! $this->tieneAcceso()) abort(response()->json(['error' => 'Sin permiso'], 403));

        if ($curso->archivo_ruta && Storage::disk('local')->exists($curso->archivo_ruta)) {
            Storage::disk('local')->delete($curso->archivo_ruta);
        }
        $curso->delete();

        return response()->json(['success' => true, 'message' => 'Curso eliminado']);
    }

    public function descargarArchivo(MatrizCurso $curso)
    {
        if (! $curso->archivo_ruta || ! Storage::disk('local')->exists($curso->archivo_ruta)) {
            abort(404);
        }
        return Storage::disk('local')->download($curso->archivo_ruta, $curso->archivo_nombre);
    }

    // ── IMPORTAR EXCEL ────────────────────────────────────────────────────────

    public function importar(Request $request)
    {
        if (! $this->tieneAcceso()) abort(response()->json(['error' => 'Sin permiso'], 403));

        $request->validate(['archivo_excel' => 'required|file|mimes:xlsx,xls']);

        try {
            $spreadsheet = IOFactory::load($request->file('archivo_excel')->getPathname());

            $hojasSaltar = ['control capacitaciones'];
            $importados  = 0;
            $errores     = 0;
            $detalle     = [];

            foreach ($spreadsheet->getAllSheets() as $ws) {
                $nombreHoja = $ws->getTitle();
                if (in_array(strtolower(trim($nombreHoja)), $hojasSaltar)) continue;

                // Detectar fila de inicio de datos (primera fila con RUT en col D)
                $filaInicio = null;
                for ($r = 6; $r <= 15; $r++) {
                    $rut = trim((string) $ws->getCell('D' . $r)->getValue());
                    if ($rut && preg_match('/\d{6,}/', str_replace(['.', '-'], '', $rut))) {
                        $filaInicio = $r;
                        break;
                    }
                }
                if (! $filaInicio) continue;

                $contHoja = 0;
                foreach ($ws->getRowIterator($filaInicio) as $row) {
                    $ri = $row->getRowIndex();
                    $nombreCompleto = trim((string) $ws->getCell('C' . $ri)->getFormattedValue());
                    $rut            = trim((string) $ws->getCell('D' . $ri)->getFormattedValue());
                    $cargo          = trim((string) $ws->getCell('E' . $ri)->getFormattedValue());
                    $contrato       = trim((string) $ws->getCell('F' . $ri)->getFormattedValue());
                    $curso          = trim((string) $ws->getCell('G' . $ri)->getFormattedValue());
                    $entidad        = trim((string) $ws->getCell('H' . $ri)->getFormattedValue()) ?: null;
                    $correo         = trim((string) $ws->getCell('M' . $ri)->getFormattedValue()) ?: null;

                    // Fechas: pueden ser seriales Excel o strings como "5/20/2025"
                    $fechaReal = $this->parsearFecha($ws->getCell('I' . $ri)->getValue());
                    $fechaVenc = $this->parsearFecha($ws->getCell('J' . $ri)->getValue());

                    // Saltar filas sin datos mínimos
                    if (! $nombreCompleto || ! $rut || ! $curso) continue;
                    if (! preg_match('/\d/', $rut)) continue; // saltar si RUT no tiene dígitos

                    try {
                        // Buscar o crear trabajador por RUT
                        $rutLimpio = strtolower(trim(preg_replace('/\s+/', '', $rut)));
                        $trabajador = MatrizTrabajador::whereRaw(
                            "REPLACE(REPLACE(LOWER(rut),' ',''),'.','') = ?",
                            [str_replace('.', '', $rutLimpio)]
                        )->first();

                        if (! $trabajador) {
                            [$nombres, $apellidos] = $this->splitNombre($nombreCompleto);
                            $trabajador = MatrizTrabajador::create([
                                'nombres'   => $nombres,
                                'apellidos' => $apellidos,
                                'rut'       => $rut,
                                'cargo'     => $cargo ?: null,
                                'contrato'  => $contrato ?: null,
                            ]);
                        } else {
                            // Actualizar cargo/contrato si estaban vacíos
                            $updates = [];
                            if (! $trabajador->cargo    && $cargo)    $updates['cargo']    = $cargo;
                            if (! $trabajador->contrato && $contrato) $updates['contrato'] = $contrato;
                            if ($updates) $trabajador->update($updates);
                        }

                        // Evitar duplicados: mismo curso + entidad + fecha_realizacion
                        $existe = MatrizCurso::where('id_trabajador', $trabajador->id)
                            ->where('curso', $curso)
                            ->where(function ($q) use ($entidad) {
                                $entidad ? $q->where('entidad_acreditadora', $entidad)
                                         : $q->whereNull('entidad_acreditadora');
                            })
                            ->exists();

                        if (! $existe) {
                            MatrizCurso::create([
                                'id_trabajador'        => $trabajador->id,
                                'curso'                => $curso,
                                'entidad_acreditadora' => $entidad,
                                'fecha_realizacion'    => $fechaReal,
                                'fecha_vencimiento'    => $fechaVenc,
                                'correo_aviso'         => $correo,
                            ]);
                            $importados++;
                            $contHoja++;
                        }
                    } catch (\Exception $e) {
                        $errores++;
                        \Log::error("Matriz import row {$ri} [{$nombreHoja}]: " . $e->getMessage());
                    }
                }
                $detalle[] = "{$nombreHoja}: {$contHoja} curso(s)";
            }

            return response()->json([
                'success'    => true,
                'importados' => $importados,
                'errores'    => $errores,
                'mensaje'    => "Importados {$importados} cursos" . ($errores ? " ({$errores} errores)" : '') . '. ' . implode(' | ', $detalle),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 400);
        }
    }

    private function splitNombre(string $nombreCompleto): array
    {
        $partes = preg_split('/\s+/', trim($nombreCompleto));
        $total  = count($partes);
        if ($total <= 2) {
            return [$partes[0] ?? '', implode(' ', array_slice($partes, 1))];
        }
        // Convención chilena: 2 nombres + 2 apellidos
        $nombres   = implode(' ', array_slice($partes, 0, 2));
        $apellidos = implode(' ', array_slice($partes, 2));
        return [$nombres, $apellidos];
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
            foreach (['m/d/Y', 'd/m/Y', 'Y-m-d', 'd-m-Y', 'm-d-Y'] as $fmt) {
                $obj = \DateTime::createFromFormat($fmt, $str);
                if ($obj && $obj->format($fmt) === $str) return $obj->format('Y-m-d');
            }
            // Fallback: strtotime
            $ts = strtotime($str);
            if ($ts) return date('Y-m-d', $ts);
        } catch (\Exception $e) {}
        return null;
    }

    // ── HELPER ────────────────────────────────────────────────────────────────

    private function subirArchivo($file, MatrizCurso $curso): void
    {
        $ext     = $file->getClientOriginalExtension();
        $nombre  = $file->getClientOriginalName();
        $guardado = "mc_{$curso->id_trabajador}_{$curso->id}_" . time() . ".{$ext}";
        $path    = Storage::disk('local')->putFileAs(
            "matriz_cursos/{$curso->id_trabajador}",
            $file,
            $guardado
        );
        $curso->update([
            'archivo_nombre' => $nombre,
            'archivo_ruta'   => $path,
            'archivo_mime'   => $file->getMimeType(),
        ]);
    }
}
