<?php

namespace App\Http\Controllers;

use App\Models\CertCalidad;
use App\Services\PermisoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CertCalidadController extends Controller
{
    /** sgc_carpetas3 id para Certificados de Calidad */
    private const CC_CARPETA_ID = 11;

    private function tieneAcceso(): bool
    {
        $u = PermisoService::usuarioActual();
        return $u && ($u->esAdmin() || PermisoService::can('carga', 'carpeta', self::CC_CARPETA_ID));
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        if (! $this->tieneAcceso()) abort(403);

        $usuario        = PermisoService::usuarioActual();
        $puedeGestionar = $this->tieneAcceso();

        $query = CertCalidad::query();

        if ($s = $request->input('descripcion'))    $query->where('descripcion',    'like', "%{$s}%");
        if ($s = $request->input('tipo'))           $query->where('tipo_certificado','like', "%{$s}%");
        if ($s = $request->input('contrato'))       $query->where('contrato',        'like', "%{$s}%");
        if ($s = $request->input('marca'))          $query->where('marca',           'like', "%{$s}%");
        if ($request->input('solo_aplica'))         $query->where('aplica', true);
        if ($request->input('solo_critico'))        $query->where('critico', true);

        $registros = $query->orderBy('numero')->orderBy('descripcion')->get();

        return view('cert-calidad.index', compact('registros', 'usuario', 'puedeGestionar'));
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        if (! $this->tieneAcceso()) return response()->json(['error' => 'Sin permiso'], 403);

        $request->validate(['descripcion' => 'required|string|max:400']);

        $cert = CertCalidad::create([
            'numero'               => $request->numero               ?: null,
            'contrato'             => $request->contrato             ?: null,
            'descripcion'          => $request->descripcion,
            'tipo_certificado'     => $request->tipo_certificado     ?: null,
            'aplica'               => (bool) $request->aplica,
            'critico'              => (bool) $request->critico,
            'procedimiento_asociado' => $request->procedimiento_asociado ?: null,
            'marca'                => $request->marca                ?: null,
            'modelo'               => $request->modelo               ?: null,
            'vencimiento'          => $request->vencimiento          ?: null,
            'observaciones'        => $request->observaciones        ?: null,
        ]);

        if ($request->hasFile('archivo')) {
            $this->subirArchivo($request->file('archivo'), $cert);
        }

        return response()->json(['success' => true, 'id' => $cert->id, 'message' => 'Registro creado']);
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    public function update(Request $request, CertCalidad $cert)
    {
        if (! $this->tieneAcceso()) return response()->json(['error' => 'Sin permiso'], 403);

        $request->validate(['descripcion' => 'required|string|max:400']);

        $cert->update([
            'numero'               => $request->numero               ?: null,
            'contrato'             => $request->contrato             ?: null,
            'descripcion'          => $request->descripcion,
            'tipo_certificado'     => $request->tipo_certificado     ?: null,
            'aplica'               => (bool) $request->aplica,
            'critico'              => (bool) $request->critico,
            'procedimiento_asociado' => $request->procedimiento_asociado ?: null,
            'marca'                => $request->marca                ?: null,
            'modelo'               => $request->modelo               ?: null,
            'vencimiento'          => $request->vencimiento          ?: null,
            'observaciones'        => $request->observaciones        ?: null,
        ]);

        if ($request->hasFile('archivo')) {
            if ($cert->archivo_ruta && Storage::disk('local')->exists($cert->archivo_ruta)) {
                Storage::disk('local')->delete($cert->archivo_ruta);
            }
            $this->subirArchivo($request->file('archivo'), $cert);
        }

        return response()->json(['success' => true, 'message' => 'Registro actualizado']);
    }

    // ── DESTROY ───────────────────────────────────────────────────────────────

    public function destroy(CertCalidad $cert)
    {
        if (! $this->tieneAcceso()) return response()->json(['error' => 'Sin permiso'], 403);

        if ($cert->archivo_ruta && Storage::disk('local')->exists($cert->archivo_ruta)) {
            Storage::disk('local')->delete($cert->archivo_ruta);
        }
        $cert->delete();

        return response()->json(['success' => true, 'message' => 'Registro eliminado']);
    }

    // ── DESCARGAR ─────────────────────────────────────────────────────────────

    public function descargar(CertCalidad $cert)
    {
        if (! $cert->archivo_ruta || ! Storage::disk('local')->exists($cert->archivo_ruta)) {
            abort(404);
        }
        return Storage::disk('local')->download($cert->archivo_ruta, $cert->archivo_nombre);
    }

    // ── DATOS (JSON para modal editar) ────────────────────────────────────────

    public function datos(CertCalidad $cert)
    {
        return response()->json([
            'id'                   => $cert->id,
            'numero'               => $cert->numero,
            'contrato'             => $cert->contrato,
            'descripcion'          => $cert->descripcion,
            'tipo_certificado'     => $cert->tipo_certificado,
            'aplica'               => $cert->aplica,
            'critico'              => $cert->critico,
            'procedimiento_asociado' => $cert->procedimiento_asociado,
            'marca'                => $cert->marca,
            'modelo'               => $cert->modelo,
            'vencimiento'          => $cert->vencimiento?->format('Y-m-d'),
            'observaciones'        => $cert->observaciones,
            'archivo_nombre'       => $cert->archivo_nombre,
            'semaforo'             => $cert->semaforo,
        ]);
    }

    // ── IMPORTAR EXCEL ────────────────────────────────────────────────────────

    public function importar(Request $request)
    {
        if (! $this->tieneAcceso()) return response()->json(['error' => 'Sin permiso'], 403);

        $request->validate(['archivo_excel' => 'required|file|mimes:xlsx,xls,xlsm']);

        try {
            $spreadsheet = IOFactory::load($request->file('archivo_excel')->getPathname());

            // Sólo procesamos la hoja "LM Certificados"
            $hojasObjetivo = ['lm certificados', 'certificados'];
            $importados    = 0;
            $omitidos      = 0;

            foreach ($spreadsheet->getAllSheets() as $ws) {
                $nombreHoja = strtolower(trim($ws->getTitle()));
                if (! in_array($nombreHoja, $hojasObjetivo)) continue;

                // Detectar fila de inicio: primera fila con texto en col D (descripción)
                $filaInicio = null;
                for ($r = 8; $r <= 20; $r++) {
                    $desc = trim((string) $ws->getCell('D' . $r)->getValue());
                    if ($desc && strlen($desc) > 3) {
                        $filaInicio = $r;
                        break;
                    }
                }
                if (! $filaInicio) continue;

                foreach ($ws->getRowIterator($filaInicio) as $row) {
                    $ri  = $row->getRowIndex();
                    $num = trim((string) $ws->getCell('B' . $ri)->getFormattedValue());
                    $contrato   = trim((string) $ws->getCell('C' . $ri)->getFormattedValue());
                    $descripcion = trim((string) $ws->getCell('D' . $ri)->getFormattedValue());
                    $tipo       = trim((string) $ws->getCell('E' . $ri)->getFormattedValue()) ?: null;
                    $aplica     = (trim((string) $ws->getCell('F' . $ri)->getValue()) === '1' || strtolower(trim((string) $ws->getCell('F' . $ri)->getValue())) === 'x');
                    $noAplica   = (trim((string) $ws->getCell('G' . $ri)->getValue()) === '1' || strtolower(trim((string) $ws->getCell('G' . $ri)->getValue())) === 'x');
                    $procedimiento = trim((string) $ws->getCell('H' . $ri)->getFormattedValue()) ?: null;
                    $critico    = (trim((string) $ws->getCell('I' . $ri)->getValue()) === '1' || strtolower(trim((string) $ws->getCell('I' . $ri)->getValue())) === 'x');
                    $marca      = trim((string) $ws->getCell('K' . $ri)->getFormattedValue()) ?: null;
                    $modelo     = trim((string) $ws->getCell('L' . $ri)->getFormattedValue()) ?: null;
                    $vencimiento = $this->parsearFecha($ws->getCell('M' . $ri)->getValue());
                    $obs        = trim((string) $ws->getCell('N' . $ri)->getFormattedValue()) ?: null;

                    if (! $descripcion) continue;

                    // Evitar duplicados por descripcion + contrato
                    $existe = CertCalidad::where('descripcion', $descripcion)
                        ->where(function ($q) use ($contrato) {
                            $contrato ? $q->where('contrato', $contrato) : $q->whereNull('contrato');
                        })->exists();

                    if ($existe) { $omitidos++; continue; }

                    CertCalidad::create([
                        'numero'               => is_numeric($num) ? (int) $num : null,
                        'contrato'             => $contrato  ?: null,
                        'descripcion'          => $descripcion,
                        'tipo_certificado'     => $tipo,
                        'aplica'               => $aplica,
                        'critico'              => $critico,
                        'procedimiento_asociado' => $procedimiento,
                        'marca'                => $marca,
                        'modelo'               => $modelo,
                        'vencimiento'          => $vencimiento,
                        'observaciones'        => $obs,
                    ]);
                    $importados++;
                }
            }

            return response()->json([
                'success'    => true,
                'importados' => $importados,
                'omitidos'   => $omitidos,
                'mensaje'    => "Importados {$importados} registros" . ($omitidos ? " ({$omitidos} omitidos por duplicado)" : '') . '.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 400);
        }
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

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

    private function subirArchivo($file, CertCalidad $cert): void
    {
        $ext     = $file->getClientOriginalExtension();
        $nombre  = $file->getClientOriginalName();
        $guardado = "cc_{$cert->id}_" . time() . ".{$ext}";
        $path    = Storage::disk('local')->putFileAs(
            "cert_calidad",
            $file,
            $guardado
        );
        $cert->update([
            'archivo_nombre' => $nombre,
            'archivo_ruta'   => $path,
            'archivo_mime'   => $file->getMimeType(),
        ]);
    }
}
